<?php

include_once("path/to/arc/ARC2.php");


$config = array(
  /* db */
  'db_name' => 'arc_db',
  'db_user' => 'user',
  'db_pwd' => 'secret',
  /* store */
  'store_name' => 'sparqlpress',
  /* stop after 100 errors */
  'max_errors' => 100,
);
$store = ARC2::getStore($config);
// since version 2.3+
$store->createDBCon();

if (!$store->isSetUp()) {
  $store->setUp();
}

/* LOAD will call the Web reader, which will call the
format detector, which in turn triggers the inclusion of an
appropriate parser, etc. until the triples end up in the store. */
$store->query('LOAD <http://example.com/home.html>');

/* list names */
$q = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
  SELECT ?person ?name WHERE {
    ?person a foaf:Person ; foaf:name ?name .
  }
';
$r = '';
if ($rows = $store->query($q, 'rows')) {
  foreach ($rows as $row) {
    $r .= '<li>' . $row['name'] . '</li>';
  }
}

echo $r ? '<ul>' . $r . '</ul>' : 'no named persons found';

?>
