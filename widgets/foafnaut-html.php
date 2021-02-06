<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

function widget_sparqlpress_foafnaut_html_init() {

  // Check for the required API functions
  if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
    return;

  // This saves options and prints the widget's config form.
  function widget_sparqlpress_foafnaut_html_control() {

    $options = $newoptions = get_option('widget_sparqlpress_foafnaut_html');
    if ( $_POST['widget_sparqlpress_foafnaut_html-submit'] ) {
      $newoptions['title'] = strip_tags(stripslashes($_POST['widget_sparqlpress_foafnaut_html-title']));
      $newoptions['type'] = stripslashes($_POST['widget_sparqlpress_foafnaut_html-type']);
      $newoptions['query_first'] = stripslashes($_POST['widget_sparqlpress_foafnaut_html-query_first']);
      $newoptions['query_next'] = stripslashes($_POST['widget_sparqlpress_foafnaut_html-query_next']);
    }
    if ( $options != $newoptions ) {
      $options = $newoptions;
      update_option('widget_sparqlpress_foafnaut_html', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $type = htmlspecialchars($options['type'], ENT_QUOTES);
    $query_first = htmlspecialchars($options['query_first'], ENT_QUOTES);
    $query_next = htmlspecialchars($options['query_next'], ENT_QUOTES);
    ?>
    <label for="widget_sparqlpress_foafnaut_html-title" style="line-height:35px;display:block;">Title</label>
    <input style="width: 450px;" id="widget_sparqlpress_foafnaut_html-title" name="widget_sparqlpress_foafnaut_html-title" type="text" value="<?php echo $title; ?>" />
    <?php 
    if (sizeof($queries = apply_filters('sparqlpress_foafnaut_queries', array()))) {
      print '
<label for="widget_sparqlpress_foafnaut_html_queries" style="line-height:35px;display:block;">Predefined queries:</label>
<select id="widget_sparqlpress_foafnaut_html_queries" onChange="' . "
document.getElementById('widget_sparqlpress_foafnaut_html-query_first').value =
document.getElementById('widget_sparqlpress_foafnaut_html_queries_first_' + (document.getElementById('widget_sparqlpress_foafnaut_html_queries').options[selectedIndex].title)).title ;
document.getElementById('widget_sparqlpress_foafnaut_html-query_next').value = 
document.getElementById('widget_sparqlpress_foafnaut_html_queries_next_' + (document.getElementById('widget_sparqlpress_foafnaut_html_queries').options[selectedIndex].title)).title ;
" . '">
<option title="q">Select...</option>';
      foreach ($queries as $i => $query)
        print '<option title="q' . ($i+1) . '">' . htmlspecialchars($query['title']) . '</option>';
      print '</select>';
      foreach ($queries as $i => $query)
        print '<span style="display:none" id="widget_sparqlpress_foafnaut_html_queries_first_q' . ($i+1) . '" title="' . htmlspecialchars($query['first']) . '"></span><span style="display:none" id="widget_sparqlpress_foafnaut_html_queries_next_q' . ($i+1) . '" title="' . htmlspecialchars($query['next']) . '"></span>';
      print '<span style="display:none" id="widget_sparqlpress_foafnaut_html_queries_first_q" title=""></span><span style="display:none" id="widget_sparqlpress_foafnaut_html_queries_next_q" title=""></span>';
    } ?>
    <label for="widget_sparqlpress_foafnaut_html-query_first" style="line-height:35px;display:block;">Custom SPARQL Query, initial result (<a href="#">help</a>)</label>
    <textarea style="width: 450px; height: 180px;" id="widget_sparqlpress_foafnaut_html-query_first" name="widget_sparqlpress_foafnaut_html-query_first"><?php echo $query_first; ?></textarea>
    <label for="widget_sparqlpress_foafnaut_html-query_next" style="line-height:35px;display:block;">Custom SPARQL Query, subsequent results (<a href="#">help</a>)</label>
    <textarea style="width: 450px; height: 180px;" id="widget_sparqlpress_foafnaut_html-query_next" name="widget_sparqlpress_foafnaut_html-query_next"><?php echo $query_next; ?></textarea>
    <input type="hidden" id="widget_sparqlpress_foafnaut_html-submit" name="widget_sparqlpress_foafnaut_html-submit" value="1" />
    <?php
  }

  // This prints CSS and JavaScript for the widget
  function widget_sparqlpress_foafnaut_html_header() {
    $location = get_option('siteurl') . '/wp-content/plugins/sparqlpress/';
    echo '<link rel="stylesheet" href="' . $location . 'widgets/foafnaut-html.css" type="text/css">';
    echo '<script src="' . $location . 'widgets/foafnaut-html.js" type="text/javascript"></script>';
  }

  // This prints the widget
  function widget_sparqlpress_foafnaut_html($args) {

    // Options.
    extract($args);
    $options = get_option('widget_sparqlpress_foafnaut_html');
    $title = $options['title'];
    $type = $options['type'];
    $query_first = $options['query_first'];
    $query_next = $options['query_next'];
    if ( empty($title) )
      $title = 'FOAFNaut';

    // Get initial query result.
    if (class_exists('ARC2')) {
      $store = ARC2::getComponent('RemoteEndpointPlugin', array('endpoint_url'=>get_bloginfo('siteurl') . '/sparqlpress/sparql'));
      $row = $store->query(preg_replace('|\s+|', ' ', $query_first), 'row');
      if (!isset($row['slabel']) || !isset($row['sid']) || ($e = $store->getErrors())) {
        if (!is_array($e) || !sizeof($e))
          $e = array('No results from initial query.');
        print '<!-- Error in SparqlPress FOAFNaut HTML Widget, subject not found: ' . join(' ', $e) . ' -->';
        return;
      }
      list($preq, $postq) = explode('%23%23%23%23%23', $store->getEndpointURL() . '?query=' . urlencode(preg_replace('|\s+|', ' ', $query_next)) . '&format=json');
    } else {
      print '<!-- ARC2 not found -->';
      return;
    }

    // Output.
    echo $before_widget;
    echo $before_title . $title . $after_title;
    echo '<script type="text/javascript">
maxifoafnaut=function() {
  var container=document.getElementById("blubContainer");
  container.parentNode.className = "foafnaut";
  container.parentNode.style.position="absolute";
  container.parentNode.style.left="0px";
  container.parentNode.style.top="0px";
  start("'.$preq.'", "'.$postq.'");
  deleteBlub("dummy");
  firstblub=new Blub(300,300,0,0,"'.$row['slabel'].'","'.$row['stype'].'","'.$row['sid'].'","'.$row['slink'].'",true);
  selectBlub("'.$row['sid'].'");
}
minifoafnaut=function() {
  var container=document.getElementById("blubContainer");
  container.parentNode.className = "foafnaut minifoafnaut";
  container.parentNode.style.position="relative";
  while (container.hasChildNodes())
    container.removeChild(container.childNodes[0]);
  blubs=[];
  if (window.SVGElement && gsvgEl)
    gsvgEl.parentNode.removeChild(gsvgEl);
  foafnautonload();
}
foafnautonload=function() {
  firstblub=new Blub(0,0,0,0,"Click Me!","http://xmlns.com/foaf/0.1/Person","dummy","",false);
  firstblub.group.onclick=function(){maxifoafnaut();};
}
if (typeof window.onload!="function") {
  window.onload=foafnautonload;
} else {
  var oldonload=window.onload;
  window.onload=function(){ oldonload(); foafnautonload();}
}
</script>';
    echo '
<div class="foafnaut minifoafnaut">
  <div id="info"><div id="infoContents">
    <a id="button" onclick="minifoafnaut()">minimize</a>
    <h1>foafnaut</h1>
    <div id="label">&nbsp;</div>
  </div></div>
  <div id="blubContainer">
  </div>
</div>';
    echo $after_widget;
  }

  function sparqlpress_foafnaut_queries($queries) {
    $queries[] = array(
        'title' => 'Friends & Contacts', 
        'first' => '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT ?slabel ?stype ?sid ?slink
WHERE { ?s ?p <' . get_option('siteurl') . '/> .
        ?s a foaf:Person ; foaf:name ?slabel ; ?ifp ?sid .
        ?ifp a owl:InverseFunctionalProperty .
        OPTIONAL { ?s a ?stype } .
        OPTIONAL { ?s foaf:homepage ?slink } .
        OPTIONAL { ?s foaf:weblog ?slink } .
      } LIMIT 1', 
        'next' => '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?ptype ?o ?olabel ?otype ?oid ?olink
WHERE { ?s ?sifp ?sid ; ?ptype ?o .
        ?sifp a owl:InverseFunctionalProperty .
        ?o foaf:name ?olabel ; ?oifp ?oid ; a ?otype .
        ?oifp a owl:InverseFunctionalProperty .
        OPTIONAL { ?o foaf:homepage ?olink } .
        OPTIONAL { ?o foaf:weblog ?olink } .
        FILTER regex(str(?sid), "#####") .
        FILTER ( ?otype = foaf:Person )
      } GROUP BY ?o ORDER BY ASC(?oid)');
    return $queries;
  }
  
  // This registers the widget
  function widget_sparqlpress_foafnaut_html_register() {
    register_sidebar_widget('FOAFNaut', 'widget_sparqlpress_foafnaut_html');
    register_widget_control('FOAFNaut', 'widget_sparqlpress_foafnaut_html_control', 460, 615);
    add_filter('sparqlpress_foafnaut_queries', 'sparqlpress_foafnaut_queries');
    if (is_active_widget('widget_sparqlpress_foafnaut_html'))
      add_action('wp_head', 'widget_sparqlpress_foafnaut_html_header');
  }

  // Tell Dynamic Sidebar about our new widget and its control
  widget_sparqlpress_foafnaut_html_register();

}

widget_sparqlpress_foafnaut_html_init();

?>