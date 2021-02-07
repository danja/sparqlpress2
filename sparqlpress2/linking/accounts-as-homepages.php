<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_accounts_as_homepages_init');

function sparqlpress_linking_accounts_as_homepages_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp())
    add_filter('sparqlpress_scutter_updated_graphs', 'sparqlpress_linking_accounts_as_homepages_updated_graphs');

  function sparqlpress_linking_accounts_as_homepages_updated_graphs($graphs) {
    global $sparqlpress;

		// Loop through graphs
		foreach ($graphs as $graph) {
	    // Assert that account profile pages are also homepages
    	$r = $sparqlpress->scutter->query('
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
CONSTRUCT { ?person foaf:homepage ?page }
WHERE {
  GRAPH <' . $graph . '> { 
    ?person foaf:holdsAccount ?account .
    ?account foaf:accountProfilePage ?page
  }
}', 'raw', '', true);
    	if (!is_array($r) || !sizeof($r))
      	continue;
			$sparqlpress->scutter->insert($r, $graph, true);
	  }

    return $graphs;
  }

}

?>