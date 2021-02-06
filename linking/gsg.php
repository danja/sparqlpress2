<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_gsg_init');

function sparqlpress_linking_gsg_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
    add_filter('sparqlpress_scutter_updated_nearby_graph', 'sparqlpress_linking_gsg_updated_nearby_graph');
    add_filter('sparqlpress_info_stats', 'sparqlpress_linking_gsg_info_stats');
  }

  function sparqlpress_linking_gsg_updated_nearby_graph($graph) {
    global $sparqlpress;

    // Check graph itself    
    $nodelists = array($graph => array($graph));

    // Find nodes and identifying properties in graph
    $rows = $sparqlpress->scutter->query('
SELECT DISTINCT ?node ?ifp ?ifpval
WHERE {
  GRAPH <' . $graph . '> { ?node ?ifp ?ifpval } .
  ?ifp a owl:InverseFunctionalProperty .
}', 'rows');
    foreach ($rows as $row) {
      if (preg_match('|^http://|', $row['ifpval']))
        $nodelists[$row['node']][] = $row['ifpval'];
      elseif ('http://xmlns.com/foaf/0.1/mbox_sha1sum'==$row['ifp'])
        $nodelists[$row['node']][] = 'sgn://mboxsha1/?pk=' . $row['ifpval'];
    }
    sparqlpress_linking_gsg_get_me_links($nodelists);
    return $graph;
  }

  function sparqlpress_linking_gsg_get_me_links($nodelists) {
    global $sparqlpress;

    // For each list of nodes in nodelists, try to find more URLs with GSG
    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    foreach ($nodelists as $nodelist) {
      // Escape URLs in node list
      foreach ($nodelist as $i => $node)
        $nodelist[$i] = str_replace('%2F', '/', str_replace('%3A', ':', urlencode($node)));
      // Construct query URL
      $q = join(',', $nodelist);
      $q = 'http://socialgraph.apis.google.com/lookup?fme=1&q=' . $q;
      $r = $sparqlpress->scutter->get($q);
      if (is_array($r) && isset($r['code']) && '200'==$r['code']) {
        // Parse JSON results
        $gsg = $json->decode($r['body']);
        if (!is_array($gsg) || !is_array($gsg['nodes']))
          continue;
        $urls = array();
        foreach ($gsg['nodes'] as $n => $info) {
          // Add found URLs to scutter
          $urls[] = $n;
          if (isset($info['attributes']) && is_array($info['attributes'])) {
            foreach ($info['attributes'] as $k => $a) {
              if ('photo'!=$k)
                $urls[] = $a;
            }
          }
        }
        foreach ($urls as $url) {
          if (preg_match('|^http://|', $url)
              && !preg_match('|crschmidt|', $url)) # @@@ Remove me...
            $sparqlpress->scutter->addGraph($url, $q);
        }
      }
    }
  }

  function sparqlpress_linking_gsg_info_stats($stats) {
    global $sparqlpress;
    $stats[] = $sparqlpress->scutter->query('PREFIX scutter: <'.$sparqlpress->scutter->ns().'> SELECT COUNT(?g) AS ?GsgResults WHERE { GRAPH ?g { ?r scutter:origin ?o } FILTER regex(str(?o), "^http://socialgraph.apis.google.com/lookup") }', 'row');
    return $stats;
  }

}

?>