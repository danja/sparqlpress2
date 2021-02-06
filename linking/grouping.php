<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_grouping_init');

function sparqlpress_linking_grouping_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
    add_action('admin_menu', 'sparqlpress_linking_grouping_action_admin_menu');
    add_action('sparqlpress_scutter_completed', 'sparqlpress_linking_grouping_scutter_completed');
  }
  if ($sparqlpress->store && $sparqlpress->store->isSetUp()) {
    add_action('sparqlpress_request_linking_grouping', 'sparqlpress_linking_grouping_request');
    add_filter('sparqlpress_foafnaut_queries', 'sparqlpress_linking_grouping_foafnaut_queries');
  }

  function sparqlpress_linking_grouping_action_admin_menu() {
    global $sparqlpress;
    add_submenu_page('sparqlpress', 'SparqlPress Grouping', 'Grouping', 'publish_posts', 'sparqlpress/linking/grouping', 'sparqlpress_linking_grouping_page_handler');
  }

  function sparqlpress_linking_grouping_page_handler() {
    global $sparqlpress;
    if (!$sparqlpress->scutter || !$sparqlpress->scutter->isSetUp()) {
      print '<div id="message" class="updated fade"><p>Grouping needs an active scutter.</p></div>';
      return;
    }
    if (isset($_POST['sparqlpress_linking_grouping_action'])) {
      check_admin_referer('sparqlpress-linking-grouping');
      sparqlpress_linking_grouping_page_submit();
    }
    sparqlpress_linking_grouping_page_form();
  }

  function sparqlpress_linking_grouping_page_form() {
    global $sparqlpress;
    $user = wp_get_current_user();
    if ('edit'==@$_POST['sparqlpress_linking_grouping_action']) {
      $name = $_POST['sparqlpress_linking_grouping_group'];
      $group_name = sanitize_title($name);
      $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user->user_login . '/' . $group_name . '/';
      $row = $sparqlpress->scutter->query('
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> 
SELECT ?query
WHERE {
  GRAPH <' . $base .'> {
    ?group a foaf:Group ; sparql:query ?query .
  }
} LIMIT 1', 'row');
      $query = $row['query'];
    }
    print '
      <div class="wrap sparqlpress">
        <h2>SparqlPress Grouping</h2>
        <form method="post" action="">
          <fieldset><legend>' . (''!=$name?'Edit Group':'New Group') . '</legend>';
    wp_nonce_field('sparqlpress-linking-grouping');
    print '
            <input type="hidden" name="sparqlpress_linking_grouping_action" value="' . (''!=$name?'update':'create') . '" />
            <div class="field">
              <label for="sparqlpress_linking_grouping_name">Name:</label><br />
              <input ' . (''!=$name?'readonly="readonly" ':'') . 'type="text" size="20" id="sparqlpress_linking_grouping_name" name="sparqlpress_linking_grouping_name" value="' . $name . '" />
            </div>
            <div class="field">
              <label for="sparqlpress_linking_grouping_query">Node Selection (help):</label><br />
              <textarea id="sparqlpress_linking_grouping_query" name="sparqlpress_linking_grouping_query" rows="7" cols="80">' . $query . ' </textarea>
            </div>
            <p class="submit">
              <input type="submit" name="Submit" value="' . (''!=$name?'Update Group':'Create Group') . '" />
            </p>
          </fieldset>
        </form>';
    $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user->user_login . '/';
    if (''==$name && ($rows = $sparqlpress->scutter->query('
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> 
SELECT ?name ?modified ?query COUNT(?member) AS ?members
WHERE {
  GRAPH ?g {
    ?g foaf:primaryTopic ?group .
    ?group a foaf:Group ; foaf:name ?name ; sparql:query ?query }
  OPTIONAL { ?group dct:modified ?modified }
  OPTIONAL { ?group foaf:member ?member }
FILTER regex(str(?g), "^' . $base . '")
} GROUP BY ?name ORDER BY ?name', 'rows')) && is_array($rows) && sizeof($rows)) {
      print '
        <form name="sparqlpress_linking_grouping_groups" method="post" action="">
          <fieldset><legend>Existing Groups</legend>';
      wp_nonce_field('sparqlpress-linking-grouping');
      print '
          <input type="hidden" id="sparqlpress_linking_grouping_action" name="sparqlpress_linking_grouping_action" value="" />
          <input type="hidden" id="sparqlpress_linking_grouping_group" name="sparqlpress_linking_grouping_group" value="" />
          <table class="widefat"><thead>
            <tr>
              <th>Name</th>
              <th>Updated</th>
              <th>Members</th>
              <th>Query</th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </thead><tbody>';
      $alternate = 1;
      foreach ($rows as $row) {
        $group_name = sanitize_title($row['name']);
        print '<tr' . ($alternate?' class="alternate"':'') . '>';
        print '<td style="vertical-align: top"><a href="' . $base . $group_name . '/#theGroup">' . htmlspecialchars($row['name']) . '</a></td>';
        print '<td style="vertical-align: top">' . htmlspecialchars(str_replace('T', ' ', $row['modified'])) . '</td>';
        print '<td style="vertical-align: top; text-align: right">' . htmlspecialchars($row['members']) . '</td>';
        print '<td style="vertical-align: top">' . str_replace("\n", '<br/>', htmlspecialchars($row['query'])) . '</td>';
        print '<td style="vertical-align: top"><a class="edit" href="?refresh" onclick="' . "this.href='javascript:void()'; document.getElementById('sparqlpress_linking_grouping_group').value='" . $row['name']. "';document.getElementById('sparqlpress_linking_grouping_action').value='refresh';document.sparqlpress_linking_grouping_groups.submit();" . '">Refresh</td>';
        print '<td style="vertical-align: top"><a class="edit" href="?edit" onclick="' . "this.href='javascript:void()'; document.getElementById('sparqlpress_linking_grouping_group').value='" . $row['name']. "';document.getElementById('sparqlpress_linking_grouping_action').value='edit';document.sparqlpress_linking_grouping_groups.submit();" . '">Edit</td>';
        print '<td style="vertical-align: top"><a class="delete" href="?delete" onclick="' . "this.href='javascript:void()'; document.getElementById('sparqlpress_linking_grouping_group').value='" . $row['name']. "';document.getElementById('sparqlpress_linking_grouping_action').value='delete';document.sparqlpress_linking_grouping_groups.submit();" . '">Delete</td>';
        print '</tr>';
        $alternate = 1 - $alternate;
      }
      print '
          </tbody></table>
          </fieldset>
        </form>';
    }
    print '
      </div>';
  }

  function sparqlpress_linking_grouping_page_submit() {
    global $sparqlpress;
    if ('refresh'==$_POST['sparqlpress_linking_grouping_action']) {
      $name = $_POST['sparqlpress_linking_grouping_group'];
      $group_name = sanitize_title($name);
      $user = wp_get_current_user();
      $count = sparqlpress_linking_grouping_update_group($user->user_login, $group_name);
      print '<div id="message" class="updated fade"><p>The group "' . $name . '" has been refreshed with ' . $count . ' members.</p></div>';
      return $r;
    } elseif ('delete'==$_POST['sparqlpress_linking_grouping_action']) {
      $name = $_POST['sparqlpress_linking_grouping_group'];
      $group_name = sanitize_title($name);
      $user = wp_get_current_user();
      $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user->user_login . '/' . $group_name . '/';
      $r = $sparqlpress->scutter->query('DELETE FROM <' . $base . '#theGroup>');
      $r = $sparqlpress->scutter->query('DELETE FROM <' . $base . '>');
      print '<div id="message" class="updated fade"><p>The group "' . $name . '" has been deleted.</p></div>';
      return $r;
    } elseif ('create'==$_POST['sparqlpress_linking_grouping_action']
        || 'update'==$_POST['sparqlpress_linking_grouping_action']) {
      # Validate input.
      $name = $_POST['sparqlpress_linking_grouping_name'];
      $group_name = sanitize_title($name);
      if (empty($name) || empty($group_name)) {
        print '<div id="message" class="updated fade"><p>Invalid group name.</p></div>';
        return;
      }
      $query = stripslashes($_POST['sparqlpress_linking_grouping_query']);
      $parser = ARC2::getSPARQLParser();
      $parser->parse($query);
      if ($e = $parser->getErrors()) {
        print '<div id="message" class="updated fade"><p>Error parsing query: ' . join(' ', $e) . '</p></div>';
        return;
      }
      
      # Query must be "SELECT ?member"
      if ('select'!=$parser->r['query']['type']
          || 'member'!=$parser->r['query']['result_vars'][0]['val']
          || 'var'!=$parser->r['query']['result_vars'][0]['type']) {
        print '<div id="message" class="updated fade"><p>Query must be "SELECT ?member ...".</p></div>';
        return;
      }

      # Create and fill template with group definition.
      $user = wp_get_current_user();
      $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user->user_login . '/' . $group_name . '/';
      $t = '@prefix dct: <http://purl.org/dc/terms/> .
          @prefix foaf: <http://xmlns.com/foaf/0.1/> .
          @prefix sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> .
          <> foaf:primaryTopic <#theGroup> .
          <#theGroup> a foaf:Group ;
            foaf:maker [
              a foaf:Person ;
              foaf:name ?makername ;
              ?makerifp ?makerifpval .
            ] ;
            sparql:query ?query ;
            dct:created ?date ;
            foaf:name ?name .';
      list($makerifp, $makerifpval) = (''!=$user->user_email?array('http://xmlns.com/foaf/0.1/mbox_sha1sum', $sparqlpress->sha1('mailto:' . $user->user_email)):
          (''!=$user->user_url && 'http://'!=$user->user_url?array('http://xmlns.com/foaf/0.1/homepage', $user->user_url):
          array('http://xmlns.com/foaf/0.1/weblog', get_author_posts_url($user->id))));
      $v = array('name' => $name, 'date' => gmdate('Y-m-d\TH:i:s\Z'), 'query' => $query,
          'makername' => $user->display_name, 'makerifp' => $makerifp, 'makerifpval' => $makerifpval);
      if (!($i = $sparqlpress->scutter->getFilledTemplate($t, $v, $base))
          || !sizeof($i) || ($e = $sparqlpress->scutter->getErrors())) {
        print '<div id="message" class="updated fade"><p>Unable to update group' . ($e?': '.join(' ', $e):'') . '</p></div>';
        return;
      }

      # Insert into storage and update.
      $sparqlpress->scutter->query('DELETE FROM <' . $base . '>');
      $r = $sparqlpress->scutter->insert($i, $base);
      if ($e = $sparqlpress->scutter->getErrors()) {
        print '<div id="message" class="updated fade"><p>Unable to store group definition: ' . join(' ', $e) . '</p></div>';
        return;
      }
      $c = sparqlpress_linking_grouping_update_group($user->user_login, $group_name);
      print '<div id="message" class="updated fade"><p>The group "' . $name . '" has been updated with ' . $c . ' members.</p></div>';
      return $r;
    }
  }

  function sparqlpress_linking_grouping_scutter_completed() {
    global $sparqlpress;
    
    # Find groups to be updated.
    $base = get_option('siteurl') . '/sparqlpress/linking/grouping/';
    if (($rows = $sparqlpress->scutter->query('
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> 
SELECT DISTINCT ?g
WHERE { GRAPH ?g { ?g foaf:primaryTopic ?group . ?group a foaf:Group }
FILTER regex(str(?g), "^' . $base . '")
}', 'rows')) && is_array($rows) && sizeof($rows)) {
      foreach ($rows as $row) {
        if (preg_match('|^' . preg_quote($base, '|') . '([^/]+)/([^/]+)/$|', $row['g'], $M))
          sparqlpress_linking_grouping_update_group($M[1], $M[2]);
      }
    }
  }

  function sparqlpress_linking_grouping_update_group($user_login, $group_name) {
    global $sparqlpress;

    # Find group to be updated.
    $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user_login . '/' . $group_name . '/';
    if (($row = $sparqlpress->scutter->query('
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> 
SELECT ?query
WHERE {
  GRAPH <' . $base . '> {
    ?g foaf:primaryTopic ?group .
    ?group a foaf:Group ; sparql:query ?query .
  }
}', 'row')) && is_array($row) && sizeof($row)) {
      # Remove previous group member list and modification time.
      $sparqlpress->scutter->query('DELETE FROM <' . $base . '#theGroup>');

      # Run group query...
      $r = $sparqlpress->scutter->query($row['query'], 'rows');

      # Generate group member list.
      $t = '@prefix dct: <http://purl.org/dc/terms/> .
          @prefix foaf: <http://xmlns.com/foaf/0.1/> . 
          <' . $base . '#theGroup> dct:modified "' . gmdate('Y-m-d\TH:i:s\Z') . '" . ';
      foreach ($r as $row)
        $t .= '<' . $base . '#theGroup> foaf:member <' . $row['member'] . '> .';

      # Insert updated member list.
      $r = $sparqlpress->scutter->insert($t, $base . '#theGroup', true);
      return $r['t_count']-1;
    }
  }

  function sparqlpress_linking_grouping_request() {
    global $wp, $sparqlpress;
    $base = get_option('siteurl') . '/' . $wp->request . '/';
    $q = 'CONSTRUCT { ?s ?p ?o } WHERE { GRAPH ?g {?s ?p ?o} FILTER regex(str(?g), "^' . $base . '") }';
    $r = $sparqlpress->store->query($q);
    if ($r && sizeof($r['result'])) {
      header('X-SparqlPress-QueryTime: ' . $r['query_time']);
      $ep = ARC2::getStoreEndpoint($sparqlpress->store->a);
      $body = $ep->getConstructResultDoc($r);
      $ep->sendHeaders();
      print $body;
      exit;
    }
  }

  function sparqlpress_linking_grouping_foafnaut_queries($queries) {
    global $sparqlpress;

    $user = wp_get_current_user();
    $base = get_option('siteurl') . '/sparqlpress/linking/grouping/' . $user->user_login . '/';
    $rows = $sparqlpress->store->query('
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX sparql: <http://www.w3.org/2005/08/sparql-protocol-query/#> 
SELECT ?name ?g COUNT(?member) AS ?members
WHERE {
  GRAPH ?g {
    ?g foaf:primaryTopic ?group .
    ?group a foaf:Group ; foaf:name ?name }
  ?group foaf:member ?member .
  FILTER regex(str(?g), "^' . $base . '") }
GROUP BY ?name ORDER BY ?name', 'rows');
    if (!is_array($rows) || !sizeof($rows))
      return $queries;
    foreach ($rows as $row) {
      $queries[] = array(
          'title' => 'Group: ' . $row['name'], 
          'first' => '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT ?slabel ?stype ?sid ?slink
WHERE { <' . $row['g'] . '#theGroup> foaf:maker ?s .
        ?s foaf:name ?slabel ; ?ifp ?sid .
        ?ifp a owl:InverseFunctionalProperty .
        OPTIONAL { ?s a ?stype } .
        OPTIONAL { ?s foaf:homepage ?slink } .
        OPTIONAL { ?s foaf:weblog ?slink } .
      } LIMIT 1', 
          'next' => '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?ptype ?o ?olabel ?otype ?oid ?olink
WHERE { <' . $row['g'] . '#theGroup> ?ptype ?o .
        ?o foaf:name ?olabel ; ?oifp ?oid ; a ?otype .
        ?oifp a owl:InverseFunctionalProperty .
        OPTIONAL { ?o foaf:homepage ?olink } .
        OPTIONAL { ?o foaf:weblog ?olink } .
        FILTER regex(str(?sid), "#####") .
        FILTER ( ?otype = foaf:Person )
      } GROUP BY ?o ORDER BY ASC(?oid)');
      return $queries;
    }
  }

}

?>