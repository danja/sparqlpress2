<?php

include_once 'arc2-adapter.php';

$arc2_adapter = ARC2_Adapter::getInstance();
 
$endpoint = $arc2_adapter->getEndpoint();

if (!$endpoint->isSetUp()) {
    $endpoint->setUp(); 
}

/* request handling */
// $endpoint->go();



$endpoint->handleRequest();
// $endpoint->sendHeaders();
echo $endpoint->getResult();

// error_log('STORE_ADMIN');
error_log(json_encode($endpoint, JSON_PRETTY_PRINT));

?>