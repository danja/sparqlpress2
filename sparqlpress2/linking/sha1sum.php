<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_sha1sum_init');

function sparqlpress_linking_sha1sum_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp())
    add_filter('sparqlpress_scutter_updated_graphs', 'sparqlpress_linking_sha1sum_updated_graphs');

  function sparqlpress_linking_sha1sum_updated_graphs($graphs) {
    global $sparqlpress;

    // Loop through graphs.
    foreach ($graphs as $graph) {

      // Find foaf:mbox statements in graph
      $rows = $sparqlpress->scutter->query('
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?agent ?mailto
WHERE {
  GRAPH <' . $graph . '> { ?agent foaf:mbox ?mailto } 
  FILTER (isIri(?mailto) && regex(str(?mailto), "^mailto:"))
}', 'rows');

      // Generate turtle doc foaf:mbox_sha1sum statements
      $t = '@prefix foaf: <http://xmlns.com/foaf/0.1/> . ';
      foreach ($rows as $row)
        $t .= '<' . $row['agent'] . '> foaf:mbox_sha1sum "' . $sparqlpress->sha1($row['mailto']) . '" . ';

      // Insert into store
      $sparqlpress->scutter->insert($t, $graph, '', true);
    }

    return $graphs;
  }

}

?>