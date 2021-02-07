<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_rapleaf_init');

function sparqlpress_linking_rapleaf_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
  if (!is_array($sparqlpress->options['linking_rapleaf']))
    $sparqlpress->options['linking_rapleaf'] = array(
        'linking_rapleaf_api_key' => '');
    add_action('sparqlpress_option_page_form', 'sparqlpress_linking_rapleaf_option_page_form', 11);
    add_action('sparqlpress_option_page_submit', 'sparqlpress_linking_rapleaf_option_page_submit', 11);
    add_filter('sparqlpress_scutter_updated_nearby_graph', 'sparqlpress_linking_rapleaf_updated_nearby_graph');
    add_filter('sparqlpress_info_stats', 'sparqlpress_linking_rapleaf_info_stats');
    add_filter('sparqlpress_queries', 'sparqlpress_linking_rapleaf_queries');
  }

  function sparqlpress_linking_rapleaf_option_page_form() {
    global $sparqlpress;
    if ($sparqlpress->store && $sparqlpress->store->isSetUp() &&
        $sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
      // Basic configuration.
      print '
        <fieldset><legend><a href="http://www.rapleaf.com/">Rapleaf</a></legend>
          <input type="hidden" name="sparqlpress_linking_rapleaf_action" value="update" />
          <div class="field">
            <label for="sparqlpress_linking_rapleaf_api_key">API key:</label>
            <input type="text" size="40" id="sparqlpress_linking_rapleaf_api_key" name="sparqlpress_linking_rapleaf_api_key" value="' . $sparqlpress->options['linking_rapleaf']['linking_rapleaf_api_key'] . '" />
          </div>
        </fieldset>';
    }
  }

  function sparqlpress_linking_rapleaf_option_page_submit() {
    global $sparqlpress;
    if (isset($_POST['sparqlpress_linking_rapleaf_action']) && is_array($sparqlpress->options['linking_rapleaf'])) {
      foreach ($sparqlpress->options['linking_rapleaf'] as $k => $v)
        $sparqlpress->options['linking_rapleaf'][$k] = $_POST['sparqlpress_'.$k];
    }
  }

  function sparqlpress_linking_rapleaf_updated_nearby_graph($graph) {
    global $sparqlpress;

    // Get API key
    if (!is_array($sparqlpress->options['linking_rapleaf'])
        || empty($sparqlpress->options['linking_rapleaf']['linking_rapleaf_api_key']))
      return $graph;

    // Find email addresses in graph
    $rows = $sparqlpress->scutter->query('
SELECT DISTINCT ?mailto
WHERE {
  GRAPH <' . $graph . '> { ?s ?p ?mailto } 
  FILTER regex(str(?mailto), "^mailto:")
}', 'rows');
    $emails = array();
    foreach ($rows as $row) {
      if (preg_match('|^mailto:(.+@.+\..+)$|', $row['mailto'], $M))
         $emails[$M[1]] = $M[1];
    }
    sparqlpress_linking_rapleaf_get_person($emails);
    return $graph;
  }

  function sparqlpress_linking_rapleaf_get_person($emails) {
    global $sparqlpress;

    // For each email address, try to GET Rapleaf description
    foreach ($emails as $email) {
      // Construct query URL
      $q = 'http://api.rapleaf.com/v2/person/' . $email;
      $r = $sparqlpress->scutter->get($q . '?api_key=' . $sparqlpress->options['linking_rapleaf']['linking_rapleaf_api_key']);
      if (is_array($r) && isset($r['code']) && '200'==$r['code']) {
        // Regex-parsing...
        if (preg_match_all('|<membership[^>]+?site="([^"]+?)"[^>]+?profile_url="([^"]+?)"[^>]*?/>|', $r['body'], $MM, PREG_SET_ORDER)) {
        	// Add Rapleaf account
          if (preg_match('|<reputation>.+?<profile_url>([^<]+)</profile_url>|', $r['body'], $M))
	        	$MM[] = array('', 'rapleaf.com', $M[1]);
          // Extract name
          if (preg_match('|<basics>\s*<name>([^<]+)</name>|', $r['body'], $M)) {
          	$name = $M[1];
          } else
            $name = false;
          // Generate turtle doc.
           $t = '@prefix foaf: <http://xmlns.com/foaf/0.1/> . 
               @prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> . ';
          foreach ($MM as $M) {
            $M[1] = html_entity_decode($M[1]);
            $M[2] = html_entity_decode($M[2]);
            $t .= '<' . $q . '> foaf:topic [ a foaf:Person ;
                foaf:mbox <mailto:' . $email . '> ; ' . ($name?'foaf:name "' . $name . '" ;':'') . '
                foaf:mbox_sha1sum "' . $sparqlpress->sha1('mailto:' . $email) . '" ;
                foaf:holdsAccount [
                  a foaf:OnlineAccount ;
                  foaf:name "' . $M[1] . '" ;
                  foaf:accountServiceHomepage <http://' . $M[1] . '/> ;
                  foaf:accountProfilePage <' . $M[2] . '> ] ] . ';
          }
          // Replace into store.
          $r = $sparqlpress->scutter->query('DELETE FROM <' . $q . '>');
          $r = $sparqlpress->scutter->insert($t, $q);
        }
      }
    }
  }

  function sparqlpress_linking_rapleaf_info_stats($stats) {
    global $sparqlpress;
    $stats[] = $sparqlpress->scutter->query('PREFIX foaf: <http://xmlns.com/foaf/0.1/> SELECT DISTINCT COUNT(?g) AS ?RapleafResults WHERE { GRAPH ?g { ?s foaf:topic ?p . ?p foaf:holdsAccount ?a } FILTER regex(str(?g), "^http://api.rapleaf.com/v2/person") }', 'row');
    return $stats;
  }

  function sparqlpress_linking_rapleaf_queries($queries) {
    global $sparqlpress;
    $queries[] = array('Linking/Rapleaf: Online Accounts' => '
PREFIX foaf: <http://xmlns.com/foaf/0.1/> 
SELECT DISTINCT ?Name ?Mail ?Service ?Profile
WHERE { 
  ?p foaf:mbox ?Mail .
  GRAPH ?g {
    ?p foaf:holdsAccount ?Account .
    ?Account foaf:accountServiceHomepage ?Service .
    ?Account foaf:accountProfilePage ?Profile .
  }
  OPTIONAL { ?p foaf:name ?Name }
  FILTER regex(str(?g), "^http://api.rapleaf.com/v2/person")
} GROUP BY ?Account ORDER BY ?Name ?Mail LIMIT 10');
    return $queries;
  }

}

?>