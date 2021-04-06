<?php

// include_once("../arc2/ARC2.php");

require dirname(__FILE__).'/../arc2/vendor/autoload.php';

$config = array(
    /* db */
    'db_host' => 'localhost', /* default: localhost */
    'db_name' => 'arc_test',
    'db_user' => 'danny',
    'db_pwd' => 'maria',
    /* store */
    'store_name' => 'arc_tests',
    /* network */
    /*
    'proxy_host' => '192.168.1.1',
    'proxy_port' => 8080,
    */
    /* parsers */
    'bnode_prefix' => 'bn',
    /* sem html extraction */
    'sem_html_formats' => 'rdfa microformats',
  );
  $store = ARC2::getStore($config);

  // since version 2.3+
 $store->createDBCon();
  
  if (!$store->isSetUp()) {
    $store->setUp();
  }

  $store->query('LOAD <https://danbri.org/foaf.rdf>');

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