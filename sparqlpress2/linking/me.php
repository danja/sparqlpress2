<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_me_init');

function sparqlpress_linking_me_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp())
    add_filter('sparqlpress_scutter_updated_nearby_graph', 'sparqlpress_linking_me_updated_nearby_graph');

  function sparqlpress_linking_me_updated_nearby_graph($graph) {
    global $sparqlpress;

    // Find relevant identifying properties in graph
    $rows = $sparqlpress->scutter->query('
SELECT DISTINCT ?ifpval
WHERE {
  GRAPH <' . $graph . '> { ?x ?ifp ?ifpval } .
  ?ifp a owl:InverseFunctionalProperty .
  FILTER isIri(?ifpval)
}', 'rows');
    if (!is_array($rows))
      return $graph;

    // Add each HTTP URI found
    foreach ($rows as $row) {
      if (preg_match('|^http://|', $row['ifpval']))
        $sparqlpress->scutter->addGraph($row['ifpval'], $graph);
    }

    return $graph;
  }

}

?>