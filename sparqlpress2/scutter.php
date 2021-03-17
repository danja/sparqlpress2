<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

// DANNY
include 'ARC2_ScutterStorePlugin.php';

add_action('sparqlpress_init', 'sparqlpress_scutter_init');

function sparqlpress_scutter_init() {
  global $sparqlpress, $wpdb;
  $sparqlpress->scutter = false;
  // if (!is_array($sparqlpress->options['scutter']))
  if (!array_key_exists('scutter', $sparqlpress->options))
    $sparqlpress->options['scutter'] = array(
        'scutter_active' => 0,
        'scutter_ifps' => array(),
        'scutter_seealso_levels' => 2,
        'scutter_cron_interval' => 60*5,
        'scutter_cron_cycles' => 5,
        'scutter_minimum_interval' => 60*60,
        'scutter_maximum_interval' => 60*60*24*7,
        'scutter_interval' => 60*60*12);
  add_filter('cron_schedules', 'sparqlpress_scutter_cron_schedules');
  if (!wp_next_scheduled('sparqlpress_scutter_cron_action_hook'))
    wp_schedule_event(time() + $sparqlpress->options['scutter']['scutter_cron_interval'], 'sparqlpress_scutter', 'sparqlpress_scutter_cron_action_hook');
  add_action('sparqlpress_scutter_cron_action_hook', 'sparqlpress_scutter_cron_action');
  if (!defined('DOING_CRON') && !defined('WP_ADMIN'))
    return;
  add_action('sparqlpress_option_page_form', 'sparqlpress_scutter_option_page_form', 5);
  add_action('sparqlpress_option_page_submit', 'sparqlpress_scutter_option_page_submit', 5);
  if ($sparqlpress->store && $sparqlpress->options['scutter']['scutter_active']) {
    $sparqlpress->scutter = ARC2::getComponent('ScutterStorePlugin', array_merge($sparqlpress->store->a, $sparqlpress->options['scutter'], array('store_name' => $wpdb->prefix . 'sparqlpress_scutter')));
    add_action('admin_menu', 'sparqlpress_scutter_action_admin_menu');
    add_filter('sparqlpress_info_stats', 'sparqlpress_scutter_info_stats');
    add_filter('sparqlpress_queries', 'sparqlpress_scutter_queries');
    add_filter('sparqlpress_scutter_updated_graphs', 'sparqlpress_scutter_updated_graphs', 3);
    add_filter('sparqlpress_scutter_updated_graph', 'sparqlpress_scutter_updated_graph', 3);
    add_filter('sparqlpress_scutter_updated_nearby_graph', 'sparqlpress_scutter_updated_nearby_graph', 3);
  }

  function sparqlpress_scutter_option_page_form() {
    global $sparqlpress;
    if (!$sparqlpress->store || !$sparqlpress->store->isSetUp()) {
      // Initial.
      print '
        <div class="field">
          <input type="checkbox" id="sparqlpress_scutter" name="sparqlpress_scutter" checked="checked" value="t" />
          <label for="sparqlpress_scutter">Also set up <a href="http://wiki.foaf-project.org/Scutter">scutter</a> for automatic updates and linking</label>
        </div>';
    } else {
      // Basic configuration.
      print '
        <fieldset><legend><a href="admin.php?page=sparqlpress/scutter">Scutter</a></legend>
          '.(wp_next_scheduled('sparqlpress_scutter_cron_action_hook')?'
          <div style="float:right">
            ' . (($sparqlpress->scutter && $sparqlpress->scutter->isSetup() && $sparqlpress->scutter->hasSetting('scutter_running_since'))
            ? 'Scutter running since:<br/>
            ' . $sparqlpress->scutter->getSetting('scutter_running_since')
            : 'Next scheduled run:<br/>
            ' . gmdate('Y-m-d\TH:i\Z', wp_next_scheduled('sparqlpress_scutter_cron_action_hook'))).'
          </div>':'').'
          <input type="hidden" name="sparqlpress_scutter_action" value="update" />
          <div class="field">
            <input type="checkbox" id="sparqlpress_scutter_active" name="sparqlpress_scutter_active" value="t" ' . ($sparqlpress->options['scutter']['scutter_active'] ? 'checked="checked"' : '') . ' />
            <label for="sparqlpress_scutter_active">Activate the scutter</label>
          </div>
          <div class="field">
            <label for="sparqlpress_scutter_seealso_levels">Levels of rdfs:seeAlso to follow:</label>
            <input type="text" size="2" id="sparqlpress_scutter_seealso_levels" name="sparqlpress_scutter_seealso_levels" value="' . $sparqlpress->options['scutter']['scutter_seealso_levels'] . '" />
          </div>
          <div class="field">
            <label for="sparqlpress_scutter_cron_interval">Cron schedule interval (seconds):</label>
            <input type="text" size="4" id="sparqlpress_scutter_cron_interval" name="sparqlpress_scutter_cron_interval" value="' . $sparqlpress->options['scutter']['scutter_cron_interval'] . '" />
          </div>
          <div class="field">
            <label for="sparqlpress_scutter_cron_cycles">Cron cycles between read/write sync:</label>
            <input type="text" size="4" id="sparqlpress_scutter_cron_cycles" name="sparqlpress_scutter_cron_cycles" value="' . $sparqlpress->options['scutter']['scutter_cron_cycles'] . '" />
          </div>
          <div class="field">
            <label for="sparqlpress_scutter_interval">Default update interval per resource (seconds):</label>
            <input type="text" size="8" id="sparqlpress_scutter_interval" name="sparqlpress_scutter_interval" value="' . $sparqlpress->options['scutter']['scutter_interval'] . '" />
            </div>
          <div class="field">
            <label for="sparqlpress_scutter_minimum_interval">Minimum update interval per resource (seconds):</label>
            <input type="text" size="8" id="sparqlpress_scutter_minimum_interval" name="sparqlpress_scutter_minimum_interval" value="' . $sparqlpress->options['scutter']['scutter_minimum_interval'] . '" />
          </div>
          <div class="field">
            <label for="sparqlpress_scutter_maximum_interval">Maximum update interval per resource (seconds):</label>
            <input type="text" size="8" id="sparqlpress_scutter_maximum_interval" name="sparqlpress_scutter_maximum_interval" value="' . $sparqlpress->options['scutter']['scutter_maximum_interval'] . '" />
         </div>';
      // Smushing.
      if ($sparqlpress->scutter && $sparqlpress->scutter->isSetup()) {
        $ifps = $sparqlpress->scutter->query('PREFIX owl: <http://www.w3.org/2002/07/owl#> SELECT DISTINCT ?g ?ifp ?label ?comment WHERE { GRAPH ?g { ?ifp a owl:InverseFunctionalProperty } . OPTIONAL { ?ifp rdfs:label ?label; rdfs:comment ?comment } } GROUP BY ?ifp', 'rows');
        $current = $sparqlpress->options['scutter']['scutter_ifps'];
        if (!$current)
          $current = array();
        if (sizeof($ifps)) {
          print '
            <div class="field">
              <p>Identifying properties:</p>';
          print '<input type="checkbox" id="sparqlpress_scutter_ifps" onclick="' . "f=this.parentNode;for(i=0;i<f.childNodes.length;i++){c=f.childNodes[i];if(c.type=='checkbox'){c.checked=this.checked;}}" . '" /> <label for="sparqlpress_scutter_ifps"><em>Select all</em></label><br />';
          foreach ($ifps as $ifp) {
            if (empty($ifp['label']))
              $ifp['label'] = $ifp['ifp'];
            else
              $ifp['label'] .= ' (' . $ifp['ifp'] . ')';
            $ifp['label'] .= ' from ' . $ifp['g'];
            if (empty($ifp['comment']))
              $ifp['comment'] = $ifp['ifp'];
            $chk_code = in_array($ifp['ifp'], $current) ? ' checked="checked"' : '';
            print '<input type="checkbox" id="sparqlpress_scutter_ifps_' . md5($ifp['ifp']) . '" name="sparqlpress_scutter_ifps[]" value="' . $ifp['ifp'] . '"' . $chk_code . ' /> <label for="sparqlpress_scutter_ifps_' . md5($ifp['ifp']) . '" title="' . $ifp['comment'] . '">' . $ifp['label'] . '</label><br />';
          }
          print '
            </div>';
        }
      }
      print '
        </fieldset>';
    }
  }

  function sparqlpress_scutter_option_page_submit() {

    global $sparqlpress, $wpdb;

    if (array_key_exists('sparqlpress_scutter', $_POST) && $_POST['sparqlpress_scutter']=='t') {
      $sparqlpress->options['scutter']['scutter_active'] = 1;
      if (!$sparqlpress->scutter)
        $sparqlpress->scutter = ARC2::getComponent('ScutterStorePlugin', array_merge($sparqlpress->store->a, $sparqlpress->options['scutter'], array('store_name' => $wpdb->prefix . 'sparqlpress_scutter')));
      if (!$sparqlpress->scutter->isSetUp())
        $sparqlpress->scutter->setUp();
      print '<div class="updated fade"><p>The scutter has been configured and initialised. In a few minutes, the first resources should automagically be fetched and stored for you to explore. Should you know of some relevant URLs that the scutter won\'t be able to find on its own, you can submit them manually on the <a href="admin.php?page=sparqlpress/scutter">SparqlPress Scutter page</a>.</p></div>';
    }

    if (array_key_exists('sparqlpress_store_reset', $_POST) && $_POST['sparqlpress_store_reset']=='t' && $sparqlpress->scutter->isSetUp())
      $sparqlpress->scutter->reset();

    if (array_key_exists('sparqlpress_store_delete', $_POST) && $_POST['sparqlpress_store_delete']=='t' && $sparqlpress->scutter->isSetUp())
      $sparqlpress->scutter->drop();

    if (isset($_POST['sparqlpress_scutter_action']) && is_array($sparqlpress->options['scutter'])) {
      foreach ($sparqlpress->options['scutter'] as $k => $v){
        if(array_key_exists('sparqlpress_'.$k, $_POST)){ // DANNY
          $sparqlpress->options['scutter'][$k] = $_POST['sparqlpress_'.$k];
        }
        $sparqlpress->options['scutter']['scutter_cron_cycles_current'] = 1;
        if (wp_next_scheduled('sparqlpress_scutter_cron_action_hook')) {
          wp_clear_scheduled_hook('sparqlpress_scutter_cron_action_hook');
          wp_schedule_event(time() + $sparqlpress->options['scutter']['scutter_cron_interval'], 'sparqlpress_scutter', 'sparqlpress_scutter_cron_action_hook');
        }
      }
    }
    if ($sparqlpress->scutter)
      $sparqlpress->scutter->setSetting('ifps', $sparqlpress->options['scutter']['scutter_ifps']);
  }

  function sparqlpress_scutter_action_admin_menu() {
    global $sparqlpress;
    if ($sparqlpress->scutter && $sparqlpress->scutter->isSetup())
      add_submenu_page('sparqlpress', 'SparqlPress Scutter', 'Scutter', 'publish_posts', 'sparqlpress/scutter', 'sparqlpress_scutter_page_handler');
  }

  function sparqlpress_scutter_page_handler() {
    global $sparqlpress;
    if (isset($_POST['sparqlpress_scutter_action'])) {
      check_admin_referer('sparqlpress-scutter');
      sparqlpress_scutter_page_submit();
    }
    sparqlpress_scutter_page_form();
  }

  function sparqlpress_scutter_page_form() {
    global $sparqlpress;
    if (!$sparqlpress->scutter || !$sparqlpress->scutter->isSetUp()) {
      print '<div id="message" class="updated fade"><p>Scutter is not active?</p></div>';
      return;
    }
    print '
      <div class="wrap sparqlpress">
        <form method="post" action="">';
    wp_nonce_field('sparqlpress-scutter');
    print '
        <h2>SparqlPress Scutter</h2>
          <input type="hidden" name="sparqlpress_scutter_action" value="go" />
          <p class="submit">
            <input type="submit" name="Submit" value="' . __('Fetch Next!') . '" />
          </p>
        </form>
        <form method="post" action="">';
    wp_nonce_field('sparqlpress-scutter');
    print '
          <input type="hidden" name="sparqlpress_scutter_action" value="add" />
          '.(wp_next_scheduled('sparqlpress_scutter_cron_action_hook')?'
          <div style="float:right">
          ' . (($sparqlpress->scutter && $sparqlpress->scutter->isSetup() && $sparqlpress->scutter->hasSetting('scutter_running_since'))
          ? 'Scutter running since:<br/>
          ' . $sparqlpress->scutter->getSetting('scutter_running_since')
          : 'Next scheduled run:<br/>
          ' . gmdate('Y-m-d\TH:i\Z', wp_next_scheduled('sparqlpress_scutter_cron_action_hook'))).'
          </div>':'').'
          <div class="field">
            <label for="sparqlpress_scutter_graph">Add URL to scutter:</label>
            <input type="text" size="60" id="sparqlpress_scutter_graph" name="sparqlpress_scutter_graph" value="" />
          </div>
          <p class="submit">
            <input type="submit" name="Submit" value="' . __('Add Graph') . '" />
          </p>
        </form>
        <form method="post" action="">';
    wp_nonce_field('sparqlpress-scutter');
    print '
          <input type="hidden" name="sparqlpress_scutter_action" value="skip" />
          <div class="field">
            <label for="sparqlpress_scutter_graph">Drop URL from scutter:</label>
            <input type="text" size="60" id="sparqlpress_scutter_graph" name="sparqlpress_scutter_graph" value="" />
          </div>
          <p class="submit">
            <input type="submit" name="Submit" value="' . __('Skip Graph') . '" />
          </p>
        </form>';
    $r = $sparqlpress->scutter->query('
PREFIX scutter: <' . $sparqlpress->scutter->ns() . '>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT ?URL ?DateTime ?Status ?ContentType ?Error WHERE {
  ?r scutter:source ?URL ; scutter:latestFetch ?f .
  ?f dct:date ?DateTime ; scutter:status ?Status ; scutter:error ?e .
  ?e dct:description ?Error .
  OPTIONAL { ?f scutter:contentType ?ContentType }
} ORDER BY DESC(?DateTime) LIMIT 10', 'raw');
    if (sizeof($r['rows'])) {
      // Break long values...
      foreach ($r['rows'] as $i => $row) {
        $r['rows'][$i]['DateTime'] = str_replace('T', ' ', $row['DateTime']);
        $row['URL'] = preg_replace('|^http://|', '', $row['URL']);
        if (preg_match('|^(.+?)\?(.+?)$|', $row['URL'], $M))
          $row['URL'] = $M[1] . ' ?' . $M[2];
        while (preg_match('|^(.+?\S)&(.+?)$|', $row['URL'], $M))
          $row['URL'] = $M[1] . ' &' . $M[2];
        $r['rows'][$i]['URL'] = $row['URL'];
      }
      $ep = ARC2::getStoreEndpoint($sparqlpress->scutter->a);
      print '
        <h2>Recent Scutter Errors</h2>
        <table>' . $ep->getHTMLTableRows($r['rows'], $r['variables']) . '</table>';
    }
    print '</div>';
  }

  function sparqlpress_scutter_page_submit() {
    global $sparqlpress;
    if (!$sparqlpress->scutter || !$sparqlpress->scutter->isSetUp()) {
      print '<div id="message" class="updated fade"><p>Scutter is not active?</p></div>';
      return;
    }
    if ('add'==$_POST['sparqlpress_scutter_action']) {
      // Add (or queue) graph in scutter
      $user = wp_get_current_user();
      if (!$sparqlpress->scutter->addGraph($_POST['sparqlpress_scutter_graph'], array(
          'a foaf:Person', 'foaf:name "' . $user->display_name . '"',
          (''!=$user->user_email?'foaf:mbox_sha1sum "' . $sparqlpress->sha1('mailto:' . $user->user_email) . '"':
          (''!=$user->user_url && 'http://'!=$user->user_url?'foaf:homepage <' . $user->user_url . '>':
          'foaf:weblog <' . get_author_posts_url($user->id) . '>'))), false)) {
        $sparqlpress->scutter->queueGraph($_POST['sparqlpress_scutter_graph']);
        print '<div id="message" class="updated fade"><p>The graph ' . htmlspecialchars($_POST['sparqlpress_scutter_graph']) . ' was queued for immediate fetching.</p></div>';
      } elseif ($e = $sparqlpress->scutter->getErrors())
        print '<div class="updated fade"><p>' . htmlspecialchars(join("\n", $e)) . '</p></div>';
      else
        print '<div id="message" class="updated fade"><p>The graph ' . htmlspecialchars($_POST['sparqlpress_scutter_graph']) . ' was added to the scutter store and queued for fetching.</p></div>';
    } elseif ('skip'==$_POST['sparqlpress_scutter_action']) {
      // Skip graph in scutter
      $user = wp_get_current_user();
      $sparqlpress->scutter->skipGraph($_POST['sparqlpress_scutter_graph'], 'Dropped by user', array(
          'a foaf:Person', 'foaf:name "' . $user->display_name . '"',
          (''!=$user->user_email?'foaf:mbox_sha1sum "' . $sparqlpress->sha1('mailto:' . $user->user_email) . '"':
          (''!=$user->user_url && 'http://'!=$user->user_url?'foaf:homepage <' . $user->user_url . '>':
          'foaf:weblog <' . get_author_posts_url($user->id) . '>'))), true);
      if ($e = $sparqlpress->scutter->getErrors())
        print '<div class="updated fade"><p>' . htmlspecialchars(join("\n", $e)) . '</p></div>';
      else
        print '<div id="message" class="updated fade"><p>The graph ' . htmlspecialchars($_POST['sparqlpress_scutter_graph']) . ' is now being ignored by the scutter.</p></div>';
    } elseif ('go'==$_POST['sparqlpress_scutter_action']) {
      // Run scutter
      $r = sparqlpress_scutter_cron_action(1);
      if ($e = $sparqlpress->scutter->getErrors())
        print '<div class="updated fade"><p>' . htmlspecialchars(join("\n", $e)) . '</p></div>';
      else
        print '<div id="message" class="updated fade"><p title="' . join(' ', $r['graphs']) . '">' . $r['g_count'] . ' ' . ($r['g_count']!=1?'graphs were':'graph was') . ' fetched' . (isset($r['t_count']) && $r['t_count']?', resulting in a total of ' . $r['t_count'] . ' triples':'') . '.</p></div>';
      print '<!-- ' . print_r($r, true) . ' -->';
    }
  }

  function sparqlpress_scutter_info_stats($stats) {
    global $sparqlpress;
    $stats[] = $sparqlpress->scutter->query('PREFIX scutter: <'.$sparqlpress->scutter->ns().'> SELECT COUNT(?r) as ?Representations { ?r scutter:source ?url }', 'row');
    $stats[] = $sparqlpress->scutter->query('PREFIX scutter: <'.$sparqlpress->scutter->ns().'> SELECT COUNT(?s) as ?Skipped { ?r scutter:skip ?s }', 'row');
    $stats[] = $sparqlpress->scutter->query('PREFIX scutter: <'.$sparqlpress->scutter->ns().'> SELECT COUNT(?f) as ?Fetches { ?r scutter:fetch ?f }', 'row');
    $stats[] = $sparqlpress->scutter->query('PREFIX scutter: <'.$sparqlpress->scutter->ns().'> PREFIX mysql: <http://web-semantics.org/ns/mysql/> PREFIX dct: <http://purl.org/dc/terms/> SELECT COUNT(?r) as ?Queued WHERE { ?r scutter:source ?url . OPTIONAL { ?r scutter:skip ?s } . OPTIONAL { ?r scutter:latestFetch ?f . ?f scutter:interval ?interval ; dct:date ?date } FILTER ( !BOUND(?s) && (!BOUND(?f) || (mysql:unix_timestamp(mysql:replace(mysql:replace(?date,"T",""),"Z","")) + ?interval < mysql:unix_timestamp("' . gmdate('Y-m-d H:i:s') . '") ) ) ) }', 'row');
    return $stats;
  }

  function sparqlpress_scutter_queries($queries) {
    global $sparqlpress;
    $queries[] = array('Scutter: Recent Fetches' => '
PREFIX scutter: <' . $sparqlpress->scutter->ns() . '>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT ?URL ?DateTime ?Status ?ContentType ?RawTripleCount ?Error
WHERE {
  ?r scutter:source ?URL ; scutter:latestFetch ?f .
  ?f dct:date ?DateTime ; scutter:status ?Status ; scutter:rawTripleCount ?RawTripleCount .
  OPTIONAL { ?f scutter:contentType ?ContentType }
  OPTIONAL { ?f scutter:error ?e . ?e dct:description ?Error } }
ORDER BY DESC(?DateTime) LIMIT 10');
    $queries[] = array('Scutter: Skipped Representations' => '
PREFIX scutter: <' . $sparqlpress->scutter->ns() . '>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT ?URL ?SkippedReason ?LastFetchDateTime ?LastFetchStatus
WHERE {
  ?r scutter:source ?URL ; scutter:skip ?skip .
  ?skip dct:description ?SkippedReason .
  OPTIONAL {
    ?r scutter:latestFetch ?f .
    ?f dct:date ?LastFetchDateTime ; scutter:status ?LastFetchStatus } }
ORDER BY DESC(?LastFetchDateTime) LIMIT 10');
    $queries[] = array('Scutter: Queued Fetches' => '
PREFIX scutter: <'.$sparqlpress->scutter->ns().'>
PREFIX mysql: <http://web-semantics.org/ns/mysql/>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT ?QueuedResource
WHERE {
  ?r scutter:source ?QueuedResource .
  OPTIONAL { ?r scutter:skip ?s } .
  OPTIONAL { ?r scutter:latestFetch ?f . ?f scutter:interval ?interval ; dct:date ?date }
  FILTER ( !BOUND(?s) && (!BOUND(?f) || (mysql:unix_timestamp(mysql:replace(mysql:replace(?date,"T",""),"Z","")) + ?interval < mysql:unix_timestamp("' . gmdate('Y-m-d H:i:s') . '") ) ) )
} LIMIT 10');
    return $queries;
  }

  function sparqlpress_scutter_updated_graphs($graphs) {
    foreach ($graphs as $graph)
      apply_filters('sparqlpress_scutter_updated_graph', $graph);
    return $graphs;
  }

  function sparqlpress_scutter_updated_graph($graph) {
    global $sparqlpress;

    // Follow rdfs:seeAlso?
    $level = (int)$sparqlpress->options['scutter']['scutter_seealso_levels'];
    if (!$level)
       return $graph;
     else
       $level--;

     // Local graph?
     if (preg_match('|^' . preg_quote(get_option('siteurl'), '|') . '|', $graph)) {
       apply_filters('sparqlpress_scutter_updated_nearby_graph', $graph);
       return $graph;
     }

     // User/Agent submitted graph?
     $q = '
PREFIX scutter: <' . $sparqlpress->scutter->ns() . '>
PREFIX ical: <http://www.w3.org/2002/12/cal/ical#>
ASK {
  ?r scutter:source <' . $graph . '> ; scutter:origin ?origin .
  ?origin a ical:Vevent .
  FILTER isBlank(?origin) }';
    if ($sparqlpress->scutter->query($q, 'raw')) {
       apply_filters('sparqlpress_scutter_updated_nearby_graph', $graph);
       return $graph;
    }

    // Subsequent levels
    while ($level) {
      $level--;
      $q = '';
      for ($i = 0; $i < $level; $i++)
        $q .= 'GRAPH ?g' . $i . ' { ?d' . $i . ' rdfs:seeAlso ?g' . ($i + 1) . ' } . ';
      $q .= 'GRAPH ?g' . $level . ' { ?d' . $level . ' rdfs:seeAlso <' . $graph . '> } . ';
      $q = '
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX scutter: <' . $sparqlpress->scutter->ns() . '>
PREFIX ical: <http://www.w3.org/2002/12/cal/ical#>
ASK { {
  ' . $q . '
  FILTER regex(str(?g0), "^' . get_option('siteurl') . '")
} UNION {
  ' . $q . '
  ?r scutter:source ?g0 .
  ?r scutter:origin ?origin .
  ?origin a ical:Vevent .
  FILTER isBlank(?origin) } }';
      if ($sparqlpress->scutter->query($q, 'raw')) {
         apply_filters('sparqlpress_scutter_updated_nearby_graph', $graph);
         return $graph;
      }
    }
    return $graph;
  }

  function sparqlpress_scutter_updated_nearby_graph($graph) {
    global $sparqlpress;

    $q = '
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT DISTINCT ?url
WHERE {
  GRAPH <' . $graph . '> { ?x rdfs:seeAlso ?url }
  FILTER isIri(?url)
}';
    $rows = $sparqlpress->scutter->query($q, 'rows');
    foreach ($rows as $row)
      $r = $sparqlpress->scutter->addGraph($row['url'], $graph, false);
    return $graph;
  }

}

function sparqlpress_scutter_cron_schedules() {
  global $sparqlpress;
  return array('sparqlpress_scutter' => array(
      'interval' => $sparqlpress->options['scutter']['scutter_cron_interval'],
      'display' => 'Every '. $sparqlpress->options['scutter']['scutter_cron_interval'] .' seconds'));
}

function sparqlpress_scutter_cron_action($count=0) {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetup()) {
    ignore_user_abort(true);
    set_time_limit($sparqlpress->options['scutter']['scutter_cron_interval']);
    ini_set('max_execution_time', $sparqlpress->options['scutter']['scutter_cron_interval']);
    // First?
    if (!$sparqlpress->scutter->query('ASK { ?graph <' . $sparqlpress->scutter->ns() . 'source> <' . get_option('siteurl') . '/> }', 'raw')) {
      // Get initial graphs from interested components.
      $graphs = apply_filters('sparqlpress_scutter_initial_graphs', array());
      $graphs[] = 'http://purl.org/net/sparqlpress/initial.rdf';
      $graphs[] = get_option('siteurl') . '/';
      foreach ($graphs as $graph)
        $sparqlpress->scutter->addGraph($graph, array('a foaf:Agent', 'foaf:name "SparqlPress Scutter"'));
    }
    // Clone?
    if (!$sparqlpress->options['scutter']['scutter_cron_cycles_current'])
      $sparqlpress->options['scutter']['scutter_cron_cycles_current'] = $sparqlpress->options['scutter']['scutter_cron_cycles'];
    else
      $sparqlpress->options['scutter']['scutter_cron_cycles_current']--;
    update_option('sparqlpress', $sparqlpress->options);
    if (!$sparqlpress->options['scutter']['scutter_cron_cycles_current']) {
      if (defined('DOING_CRON')) {
        print '.';
        flush();
      }
      // Smush...
      if ($sparqlpress->scutter->hasSetting('ifps')
          && is_array($sparqlpress->scutter->getSetting('ifps'))
          && sizeof($sparqlpress->scutter->getSetting('ifps'))) {
        $r = $sparqlpress->scutter->consolidate();
      }
      do_action('sparqlpress_scutter_completed');
      // Clone...
      $sparqlpress->scutter->cloneTables($sparqlpress->store->getName());
      if (defined('DOING_CRON')) {
        print '.';
        flush();
      }
      return $r;
    }
    // Normal operation, end after count or one minute before next scheduled action.
    if (defined('DOING_CRON')) {
      print str_repeat('.', 100);
      flush();
    }
    $c = $count;
    $end = wp_next_scheduled('sparqlpress_scutter_cron_action_hook') - 60;
    if (!(time() < $end))
      $end = time() + 1;
    while ($c || (!$count && (time() < $end))) {
      $r = $sparqlpress->scutter->go(1);
      if (!$sparqlpress->scutter->getErrors() && isset($r['t_count']) && $r['t_count'] && is_array($r['graphs'])) {
        // Tell interested components about updated graph(s).
        apply_filters('sparqlpress_scutter_updated_graphs', $r['graphs']);
      }
      if (!$r['g_count'])
        break;
      if (defined('DOING_CRON')) {
        print str_repeat('.', $r['g_count']);
        flush();
      }
      if ($c)
        $c--;
    }
    if (defined('DOING_CRON')) {
      print str_repeat('.', 100);
      flush();
    }
    return $r;
  }
}

?>
