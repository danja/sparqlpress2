<?php
/*
Plugin Name: SparqlPress
Plugin URI: http://wiki.foaf-project.org/SparqlPress
Description: Easy-to-use access to the <a href="http://linkeddata.org/">Linked Data Web</a>.
Author: SparqlPress
Version: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
Tested up to: 2.3.2
Author URI: http://wiki.foaf-project.org/SparqlPress
*/

class sparqlpress {

  function sparqlpress() { $this->__construct(); }
  function __construct() {
  	global $wp;
    $this->path = substr(__FILE__, 1);
    while (strpos($this->path, '/') && !file_exists(ABSPATH . 'wp-content/plugins/' . $this->path))
      $this->path = preg_replace('|^[^/]+/|', '', $this->path);
    $this->location = get_option('siteurl') . '/wp-content/plugins/' . dirname($this->path) . '/';
    $this->options = get_option('sparqlpress');
    if (!is_array($this->options))
      $this->options = array();

    // Style and pages
    add_action('admin_head', array(&$this, 'action_admin_head'));
    add_action('admin_menu', array(&$this, 'action_admin_menu'));

    // Handle special requests
    add_action('template_redirect', array(&$this, 'action_template_redirect'));

    // Login form warning
    add_action('login_form', array(&$this, 'action_login_form'));

    // Libraries...
    include_once('arc/ARC2.php');
    if (!class_exists('Services_JSON'))
      include_once('pear/JSON.php');

    // Data Generation, using WP info
    #  include_once('foaf/skos/sioc');

    // Data Storage, infrastructure (read)
    include_once('store.php');
    include_once('endpoint.php');

    // Data Storage, infrastructure (write)
    include_once('scutter.php');

    // Data Linking, administration of linking
    include_once('linking/accounts.php');
    include_once('linking/grouping.php');
    include_once('linking/gsg.php');
    include_once('linking/rapleaf.php');
    include_once('linking/sha1sum.php');
    include_once('linking/me.php');
    include_once('linking/accounts-as-homepages.php');

    // Data Usage, use of infrastructure and linking
    include_once('widgets/foafnaut-html.php');
    include_once('widgets/triplecount.php');
    include_once('display/comments.php');

    // Debug
    include_once('debug.php');
  }

  function action_admin_head() {
    print '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->location . 'sparqlpress.css" />';
  }

  function action_admin_menu() {
    global $sparqlpress;

    // Options page
    add_options_page('SparqlPress Options', 'SparqlPress', 'manage_options', basename(__FILE__), array(&$this, 'option_page_handler'));
    add_action('sparqlpress_option_page_submit', array(&$this, 'option_page_submit'), 2);
    add_menu_page('SparqlPress', 'SparqlPress', 'read', 'sparqlpress', array(&$this, 'info_page_handler'));
    add_submenu_page('sparqlpress', 'SparqlPress', 'Information', 'read', 'sparqlpress', array(&$this, 'info_page_handler'));

    // User pages
    if ($sparqlpress->store && $sparqlpress->store->isSetup()) {
      add_submenu_page('sparqlpress', 'SparqlPress Queries', 'Queries', 'edit_others_posts', 'sparqlpress/queries', array(&$this, 'queries_page_handler'));
      add_submenu_page('sparqlpress', 'SparqlPress Options', 'Options', 'manage_options', basename(__FILE__), array(&$this, 'option_page_handler'));
    }
  }

  function action_login_form() {
    print '
<div style="border: 1px solid; background: #fff">
  <p style="color: #f00" align="center"><strong>Warning</strong></p>
  <p style="color: #000">By creating an account or logging on this site, you allow it to reuse machine-readable data about yourself that is available on the Web (FOAF profile, RSS feeds, ...) in order to enhance the browsing and querying capabilities of the website. Those features are provided thanks to <a style="color: #000" href="http://wiki.foaf-project.org/SparqlPress">SparqlPress</a> and <a style="color: #000" href="http://www.w3.org/2001/sw/">Semantic Web technologies</a></p>
</div>';
  }

  function action_template_redirect() {
    global $wp, $sparqlpress;
    if (is_404() && preg_match('|^sparqlpress/(.+?)/?$|', $wp->request, $M) && !preg_match('|^admin$|', $M[1])) {
      $hook = preg_replace('|[^_0-9a-z]|', '_', preg_replace('|/|', '_', $M[1]));
      do {
        do_action('sparqlpress_request_'.$hook);
      } while (preg_match('|^(.+)_[^_]+$|', $hook, $M) && ($hook = $M[1]));
      if ($sparqlpress->store && $sparqlpress->store->isSetup()) {
        // Look for described resource in store
        $resource = get_option('siteurl') . '/' . $wp->request;
        $r = $sparqlpress->store->query('CONSTRUCT { ?s ?p ?o } WHERE { GRAPH <' . $resource . '> {?s ?p ?o} }');
        if ($r && sizeof($r['result'])) {
          header('X-SparqlPress-QueryTime: ' . $r['query_time']);
          $ep = ARC2::getStoreEndpoint($sparqlpress->store->a);
          $body = $ep->getConstructResultDoc($r);
          $ep->sendHeaders();
          print $body;
          exit;
        }
      }
    }
  }

  function option_page_handler() {
    global $sparqlpress;
    if (isset($_POST['sparqlpress_action'])) {
      check_admin_referer('sparqlpress-options');
      do_action('sparqlpress_option_page_submit');
      update_option('sparqlpress', $sparqlpress->options);
    }
    $this->option_page_form();
  }

  function option_page_submit() {
    global $sparqlpress;
    if ('create'==$_POST['sparqlpress_action']) {
      if ($sparqlpress->store && $sparqlpress->store->isSetup()) {
        print '<div id="message" class="updated fade"><p>SparqlPress is now ready to go! You can check the status of SparqlPress on the <a href="admin.php?page=sparqlpress">SparqlPress Information Page</a>.</p></div>';
      } else
        print '<div id="message" class="updated fade"><p>Ooops. Something went wrong trying to start SparqlPress. Please check your server error logs.</p></div>';
    } else
      print '<div id="message" class="updated fade"><p>SparqlPress options were updated.</p></div>';
  }

  function option_page_form() {
    global $sparqlpress;
    print '
      <div class="wrap sparqlpress">
        <form method="post" action="">';
    wp_nonce_field('sparqlpress-options');
    if ($sparqlpress->store && $sparqlpress->store->isSetUp()) {
      print '
        <h2>SparqlPress Options</h2>
          <input type="hidden" name="sparqlpress_action" value="update" />
          <p class="submit">
            <input type="submit" name="Submit" value="' . __('Update Options') . '" />
          </p>';
    } else
      print '<input type="hidden" name="sparqlpress_action" value="create" />';
    do_action('sparqlpress_option_page_form');
    if ($sparqlpress->store && $sparqlpress->store->isSetUp()) {
      print '
        <p class="submit">
          <input type="submit" name="Submit" value="' . __('Update Options') . '" />
        </p>';
    }
    print '
      </form></div>';
  }

  function info_page_handler() {
    global $sparqlpress;

    if($sparqlpress->store->isSetUp()){ // DANNY
      print('<b>TRUE<b>');
    } else {
      print('<b>FALSE<b>');
    }

    if (!$sparqlpress->store || !$sparqlpress->store->isSetUp()) {
      print '<div id="message" class="updated fade"><p>The SparqlPress store has not been <a href="options-general.php?page=' . basename(__FILE__) . '">configured</a> or isn\'t working correctly.</p></div>';
      return;
    }
    print '
      <div class="wrap sparqlpress">
        <h2>SparqlPress Information and Statistics</h2>';
    $stats = apply_filters('sparqlpress_info_stats', array());
    print '<table><tr>';  // DANNY STATS
    foreach ($stats as $stat)
      print '<th>' . key($stat) . '</th>';
    print '</tr><tr>';
    foreach ($stats as $stat)
      print '<td style="text-align: right">' . current($stat) . '</td>';
    print '</table>
      </div>';
  }

  function queries_page_handler() {
    global $sparqlpress;
    if (isset($_REQUEST['query'])) {
      print '
        <div class="wrap sparqlpress">
          <h2>Query Results</h2>';
      $r = $sparqlpress->store->query(stripslashes($_REQUEST['query']), 'raw');
      if ($e = $sparqlpress->store->getErrors())
        print '<div id="message" class="updated fade"><p>Oops: ' . join(' ', $e) . '.</p></div>';
      else {
        $ep = ARC2::getStoreEndpoint($sparqlpress->store->a);
        print '<table>' . $ep->getHTMLTableRows($r['rows'], $r['variables']) . '</table>';
      }
      print '</div>';
    }
    print '
      <div class="wrap sparqlpress">
        <h2>SparqlPress Queries</h2>
        <form method="post" enctype="application/x-www-form-urlencoded" action="">
          <input type="hidden" name="sparqlpress_queries_action" value="go" />
        ';
    if (sizeof($queries = apply_filters('sparqlpress_queries', array()))) {
      print '
<div class="field">
<label for="queries">Predefined queries:</label>
<select id="queries" onChange="' . "
document.getElementById('query').value=document.getElementById('queries').options[selectedIndex].title;
" . '">
<option title="">Select...</option>';
      foreach ($queries as $query)
        print '<option title="' . htmlspecialchars(current($query)) . '">' . htmlspecialchars(key($query)) . '</option>';
      print '</select></div>';
    } else
      print '<p>No canned queries were found</p>';
    print '
      <textarea id="query" name="query" rows="15" cols="80">' . stripslashes($_POST['query']) . ' </textarea>
      <p class="submit">
        <input type="submit" name="Submit" value="' . __('Send Query') . '" />
      </p>
      </div>';
  }

  function sha1($iri, $die=true) {
    if (function_exists('sha1'))
      $sha1 = sha1($iri);
    elseif (function_exists('mhash'))
      $sha1 = bin2hex(mhash(MHASH_SHA1, $iri));
    elseif (function_exists('hash'))
      $sha1 = hash('sha1', $iri);
    else
      die('Oops, no sha1-functionality found...');
    return $sha1;
  }

}

function sparqlpress_init() {
  global $sparqlpress;
  load_plugin_textdomain('sparqlpress');
  $sparqlpress = new sparqlpress();
  // Initialise objects etc.
  do_action('sparqlpress_init');
}

// Delay plugin execution to ensure Dynamic Sidebar etc. has a chance to load first
add_action('plugins_loaded', 'sparqlpress_init');

?>
