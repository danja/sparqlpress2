<?php

error_log('endpoint.php run');
error_log($_SERVER['REQUEST_METHOD']);

global $sparqlpress;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //   $sparqlpress->endpoint->go();

    $arc2_adapter = ARC2_Adapter::getInstance();

    $endpoint = $arc2_adapter->getEndpoint();

    if (!$endpoint->isSetUp()) {
        $endpoint->setUp();
    }

    
    $endpoint->handleRequest();
    $endpoint->sendHeaders();

    error_log($endpoint->getResult());

    echo $endpoint->getResult();

    // error_log(json_encode($endpoint, JSON_PRETTY_PRINT));
// $endpoint->go();
exit();

}


?>



<div id="yasgui"></div>
<script>
    const yasgui = new Yasgui(document.getElementById("yasgui"));
</script>