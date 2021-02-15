<?php
include_once("/home/danny/sparqlpress/sparqlpress2/arc/ARC2.php");
include_once("/home/danny/sparqlpress/sparqlpress2/arc/src/ARC2/Store/Adapter/AbstractAdapter.php");
include_once("/home/danny/sparqlpress/sparqlpress2/arc/vendor/thingengineer/mysqli-database-class/MysqliDb.php");

include_once("/home/danny/sparqlpress/sparqlpress2/arc/src/ARC2/Store/Adapter/MysqliDbExtended.php");
include_once("/home/danny/sparqlpress/sparqlpress2/arc/src/ARC2/Store/Adapter/mysqliAdapter.php");


$config = array(
  /* db */
  'db_name' => 'arc__test_db',
  'db_user' => 'user',
  'db_pwd' => 'secret',
  /* store */
  'store_name' => 'sparqlpress_test',
  /* stop after 100 errors */
  'max_errors' => 100,
);

echo '20';

$store = ARC2::getStore($config);
// since version 2.3+
$store->createDBCon();

echo '26';

if (!$store->isSetUp()) {
  $store->setUp();
}

echo '32';
?>

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
$r = '<h2>People</h2>';
echo '123' . $r;
if ($rows = $store->query($q, 'rows')) {
  echo '123' . $rows;
  foreach ($rows as $row) {
    $r .= '<li>' . 'asdasd' . '</li>'; // $row['name']
  }
}

echo $r ? '<ul>' . $r . '</ul>' : 'no named persons found';
