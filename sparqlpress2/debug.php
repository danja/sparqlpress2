<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_debug_init');

function sparqlpress_debug_init() {
  global $sparqlpress;
  $sparqlpress->debug = false;
  add_action('sparqlpress_option_page_form', 'sparqlpress_debug_option_page_form', 11);
  add_action('sparqlpress_option_page_submit', 'sparqlpress_debug_option_page_submit', 11);
  //  if (!is_array($sparqlpress->options['debug']))
  if (!array_key_exists('debug', $sparqlpress->options))  
    $sparqlpress->options['debug'] = array('debug_active' => 0);
  if ($sparqlpress->options['debug']['debug_active']) {
  	$sparqlpress->debug = true;
	  add_action('admin_menu', 'sparqlpress_debug_action_admin_menu');
    add_filter('sparqlpress_debug', 'sparqlpress_debug_debug');
	}

  function sparqlpress_debug_option_page_form() {
    global $sparqlpress;
    if (!$sparqlpress->store || !$sparqlpress->store->isSetUp()) {
      // Initial.
      print '
        <div class="field">
          <input type="checkbox" id="sparqlpress_debug" name="sparqlpress_debug" value="t" />
          <label for="sparqlpress_debug">Also activate debugging</label>
        </div>';
    } else {
      // Basic configuration.
      print '
        <fieldset><legend><a href="admin.php?page=sparqlpress/debug">SparqlPress Debug</a></legend>
          <input type="hidden" name="sparqlpress_debug_action" value="update" />
          <div class="field">
            <input type="checkbox" id="sparqlpress_debug_active" name="sparqlpress_debug_active" value="t" ' . ($sparqlpress->options['debug']['debug_active'] ? 'checked="checked"' : '') . ' />
            <label for="sparqlpress_debug_active">Activate debugging information</a>
          </div>
        </fieldset>';
    }
  }

  function sparqlpress_debug_option_page_submit() {
    global $sparqlpress;
    if ($_POST['sparqlpress_debug']=='t')
      $sparqlpress->options['debug']['debug_active'] = 1;
    if (isset($_POST['sparqlpress_debug_action']) && is_array($sparqlpress->options['debug'])) {
      foreach ($sparqlpress->options['debug'] as $k => $v)
        $sparqlpress->options['debug'][$k] = $_POST['sparqlpress_'.$k];
    }
  }

  function sparqlpress_debug_action_admin_menu() {
    global $sparqlpress;
    if ($sparqlpress->debug)
      add_submenu_page('sparqlpress', 'SparqlPress Debug', 'Debug', 'publish_posts', 'sparqlpress/debug', 'sparqlpress_debug_page_handler');
  }

  function sparqlpress_debug_page_handler() {
    global $sparqlpress;
    if (!$sparqlpress->debug) {
      print '<div id="message" class="updated fade"><p>Debug is not active?</p></div>';
      return;
    }
    print '
      <div class="wrap sparqlpress">
        <h2>SparqlPress Debug</h2>';
    if (sizeof($debugs = apply_filters('sparqlpress_debug', array()))) {
    	foreach ($debugs as $debug) {
		    print '<h3>' . key($debug) . '</h3>';
		    print current($debug);
    	}
  	}
    print '</div>';
  }

  function sparqlpress_debug_debug($debugs) {
    global $sparqlpress;
    // SparqlPress options dump
  	$debugs[] = array('SparqlPress Options' => '<pre>' . print_r($sparqlpress->options, true) . '</pre>');
  	// ARC settings dump
    if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
  		$tbl = $sparqlpress->scutter->getTablePrefix() . 'setting';
	    $sql = "SELECT k, val FROM " . $tbl;
	    $rs = mysql_query($sql, $sparqlpress->scutter->getDBCon());
	    $rows = array();
	    while ($row = mysql_fetch_assoc($rs))
    		$rows[$row['k']] = $row['val'];
  		$debugs[] = array('ARC Settings' => '<pre>' . print_r($rows, true) . '</pre>');
    }
  	return $debugs;
  }

}

?>
