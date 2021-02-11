<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

function widget_sparqlpress_triplecount_init() {

  // Check for the required API functions
  if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
    return;

  // This saves options and prints the widget's config form.
  function widget_sparqlpress_triplecount_control() {

    $options = $newoptions = get_option('widget_sparqlpress_triplecount');
    if ( $_POST['widget_sparqlpress_triplecount-submit'] ) {
      $newoptions['title'] = strip_tags(stripslashes($_POST['widget_sparqlpress_triplecount-title']));
    }
    if ( $options != $newoptions ) {
      $options = $newoptions;
      update_option('widget_sparqlpress_triplecount', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    ?>
    <label for="widget_sparqlpress_triplecount-title" style="line-height:35px;display:block;">Title:</label>
    <input style="width: 250px;" id="widget_sparqlpress_triplecount-title" name="widget_sparqlpress_triplecount-title" type="text" value="<?php echo $title; ?>" />
    <input type="hidden" id="widget_sparqlpress_triplecount-submit" name="widget_sparqlpress_triplecount-submit" value="1" />
    <?php
  }

  // This prints the widget
  function widget_sparqlpress_triplecount($args) {
  	global $sparqlpress;
    if (!$sparqlpress->store || !$sparqlpress->store->isSetup())
    	return;

    // Options.
    extract($args);
    $options = get_option('widget_sparqlpress_triplecount');
    $title = $options['title'];
    if ( empty($title) )
      $title = 'Linked Data';

    // Get triple count.
    $r = $sparqlpress->store->query('SELECT COUNT(?s) as ?TripleCount WHERE { ?s ?p ?o }', 'row');
    if (empty($r['TripleCount']))
    	$r['TripleCount'] = 0;

    // Output.
    echo $before_widget;
    echo $before_title . $title . $after_title;
    echo '<button id="widget_sparqlpress_triplecount_button" onclick="document.location=\'http://wiki.foaf-project.org/SparqlPress\';">
      <span id="widget_sparqlpress_triplecount_button_1">
        <span id="widget_sparqlpress_triplecount_count">' . number_format($r['TripleCount']) . '</span>
				<span id="widget_sparqlpress_triplecount_triples">triples</span>
      </span>
      <span id="widget_sparqlpress_triplecount_button_2">
				<span id="widget_sparqlpress_triplecount_by">stored here by</span>
				<span id="widget_sparqlpress_triplecount_sp">SparqlPress</span>
      </span>
    </a></button>';
    echo $after_widget;
  }

	function widget_sparqlpress_triplecount_header() {
		global $sparqlpress;
		?>
<style type="text/css">
#widget_sparqlpress_triplecount_button {
  background: url(<?php echo $sparqlpress->location; ?>files/sparqlpress-button.png);
  text-align: center;
  width: 160px;
  padding: 10px;
}
#widget_sparqlpress_triplecount_button span {display:block; font-weight:bold}
#widget_sparqlpress_triplecount_button:hover #widget_sparqlpress_triplecount_button_1{display:none}
#widget_sparqlpress_triplecount_button:hover #widget_sparqlpress_triplecount_button_2,#widget_sparqlpress_triplecount_button_1{display:block}
#widget_sparqlpress_triplecount_button #widget_sparqlpress_triplecount_button_2{display:none}
#widget_sparqlpress_triplecount_count, #widget_sparqlpress_triplecount_sp {font-size: 150%}
</style>
		<?php
  }

  // This registers the widget
  function widget_sparqlpress_triplecount_register() {
    wp_register_sidebar_widget('TripleCount', 'widget_sparqlpress_triplecount');
    wp_register_widget_control('TripleCount', 'widget_sparqlpress_triplecount_control', 300, 95);
    if (is_active_widget('widget_sparqlpress_triplecount'))
    	add_action('wp_head', 'widget_sparqlpress_triplecount_header');
  }

  // Tell Dynamic Sidebar about our new widget and its control
  widget_sparqlpress_triplecount_register();

}

widget_sparqlpress_triplecount_init();

?>