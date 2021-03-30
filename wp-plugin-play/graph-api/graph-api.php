<?php
/**
 * @package Graph API
 * @version 0.1
 */
/*
Plugin Name: Graph API
Plugin URI: https://github.com/cgutteridge/graph-api
Description: This provides a lightweight graph query to talk to the content of a wordpress site.
Author: Christopher Gutteridge
Version: 0.1
Author URI: https://twitter.com/cgutteridge
*/

require_once( "JRNL_Graph.php" );

/**
 * This function is where we register our routes for our example endpoint.
 */
function graph_api_register_example_routes() {
    // set up CORS to allow cross site rest.
    // I'm not 100% sure this shouldn't be locked down more to only impact headers on the graph_api REST
    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter( 'rest_pre_serve_request', function( $value ) {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
        header( 'Access-Control-Allow-Credentials: true' );
        return $value;
    });

    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'graph-api/v1', '/query', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'graph_api_get_graph',
    ) );
}
# 15 is to run later than other plugins
add_action( 'rest_api_init', 'graph_api_register_example_routes', 15 ); 


function graph_api_get_graph() {
    # nb email must not be made public
# array_key_exists('action', $_GET)

    $result = array();
  //  if(!$_GET['action'] || $_GET['action'] == 'ident' ) {
    if(!array_key_exists('action', $_GET) || $_GET['action'] == 'ident' ) {
        $result = graph_api_action_ident($_GET);
    }
    elseif( $_GET['action'] == 'nodeTypes' ) {
        $result = graph_api_action_nodeTypes($_GET);
    }
    elseif( $_GET['action'] == 'nodes' ) {
        $result = graph_api_action_nodes($_GET);
    }
    else {
        $result = array( "ok"=>false, "action"=>"error" );
    }
    return rest_ensure_response( $result );
}

function graph_api_action_ident($params) {
    $result = array(
        "ok"=>true,
        "action"=>"ident",
        "lga_version"=>1.0,
        "generator_uri"=>"https://github.com/cgutteridge/graph-api",
        "generator_title"=>"Wordpress",
        "features"=>array( "ident", "nodeTypes", "nodes", "nodes.data", "nodes.follow", "nodes.types", "nodes.ids" ),
        "title"=>get_bloginfo("name"),
        "description"=>get_bloginfo("description")
    );
    return $result;
}

function graph_api_action_nodeTypes($params) {
    $jg = new JRNL_Graph();
    $jg->populateEverything();
    $g = $jg->asData();

    $result = array( "action"=>"nodeTypes", "ok"=>true );
    $types = array();
    foreach( $g["nodes"] as $node ) {
        $type = "";
        if( isset( $node["type"] ) ) { $type = $node["type"]; }
        if( !isset( $result["nodeTypes"][$type] ) ) { $result["nodeTypes"][$type] = array("count"=>0); }
        $result["nodeTypes"][$type]["count"]++;
    }
    return $result;
}

function graph_api_action_nodes($params) {
    $jg = new JRNL_Graph();
    $jg->populateEverything();
    $g = $jg->asData();

    $result = array( "action"=>"nodes", "ok"=>true, "nodes"=>array() );

    $matchedNodes = array();
    if( isset( $params['ids'] ) ) {
        foreach( preg_split( "/\s+,\s+/", trim( $params["ids"] ) ) as $id ) {
            if( isset( $g["nodes"][$id] ) ) {
                $result["nodes"][$id] = $g["nodes"][$id];
                $matchedNodes[$id] = true;
            }
        }
    }
    if( isset( $params['types'] ) ) {
        $types = preg_split( "/\s*,\s*/", trim( $params["types"] ) );
        foreach( $g["nodes"] as $id=>$node ) {
            if( isset( $result["nodes"][$id] ) ) { continue; }
            foreach( $types as $type ) {
                if( $type == $node["type"] ) {
                    $result["nodes"][$id] = $node;
                    $matchedNodes[$id] = true;
                    continue;
                }
            }
        }
    }

    if( isset( $params["follow"] ) ) {
        $result["links"] = array();
        $linkTypes = preg_split( "/\s*,\s*/", trim( $params["follow"] ) );
        foreach( $g["links"] as $link ) {
            foreach( $linkTypes as $type ) {
                if( ($type == $link["type"] || $type == "*") && isset($matchedNodes[$link["subject"]]) && isset($g["nodes"][$link["object"]])) {
                    $result["links"][] = $link;
                    if( !isset( $result["nodes"][$link["object"]] ) ) {
                        $result["nodes"][$link["object"]] = $g["nodes"][$link["object"]];
                    }
                    continue; # don't add the link twice
                }
                if( ($type == "^".$link["type"] || $type == "*") && isset($matchedNodes[$link["object"]]) && isset($g["nodes"][$link["subject"]]) ) {
                    $result["links"][] = $link;
                    if( !isset( $result["nodes"][$link["subject"]] ) ) {
                        $result["nodes"][$link["subject"]] = $g["nodes"][$link["subject"]];
                    }
                }
            }
        }
    }
    
   // if( !$params['data'] ) {
    if( !array_key_exists('data', $params) ) {
        // just return the title & type & id for each node
        foreach( $result["nodes"] as $id=>&$node ) {
            unset( $node["data"] );
        }
    }

    return $result;
}

