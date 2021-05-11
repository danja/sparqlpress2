<?php

// set_time_limit(0);

error_log('endpoint.php run');
error_log($_SERVER['REQUEST_METHOD']);

global $sparqlpress;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  error_log("HANDLING POST");

    $arc2_adapter = ARC2_Adapter::getInstance();

    $endpoint = $arc2_adapter->getEndpoint();

    if (!$endpoint->isSetUp()) {
        $endpoint->setUp();
    }


  //  $endpoint->go();

    $endpoint->handleRequest();
    $endpoint->sendHeaders();

    error_log("Result = ");
 error_log($endpoint->getResult());


echo $endpoint->getResult();


    // error_log(json_encode($endpoint, JSON_PRETTY_PRINT));
    // $endpoint->go();
    // exit();
} 
?>

<div id="yasgui"></div>
<script>
    const yasgui = new Yasgui(document.getElementById("yasgui"), {
        requestConfig: {
            endpoint: "http://localhost/wordpress/wp-json/sparqlpress/v1/sparql"
        },
        copyEndpointOnNewTab: true,
    });


    Yasgui.defaults.endpointCatalogueOptions.getData = () => {
      return [
        //List of objects should contain the endpoint field
        //Feel free to include any other fields (e.g. a description or icon
        //that you'd like to use when rendering)
        {
          endpoint: "http://localhost/wordpress/wp-json/sparqlpress/v1/sparql"
        },
        {
          endpoint: "https://dbpedia.org/sparql"
        },
        {
          endpoint: "https://query.wikidata.org"
        }
        // ...
      ];
    }

    /*
    var tab = yasgui.addTab(
        true, // set as active tab
        {
            ...Yasgui.Tab.getDefaults(),
            name: "Posts"
        }
    );
    */
    var query = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\n" +
        "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>\n" +
        "PREFIX owl: <http://www.w3.org/2002/07/owl#>\n" +
        "PREFIX skos: <http://www.w3.org/2004/02/skos/core#>\n" +
        "PREFIX dc: <http://purl.org/dc/terms/>\n" +
        "PREFIX schema: <https://schema.org/>\n" +
        "SELECT * WHERE {\n" +
        "?post a schema:BlogPosting \n" +
        "}\n";
    tab.setQuery(query);
</script>