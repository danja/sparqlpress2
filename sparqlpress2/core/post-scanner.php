<?php

global $sparqlpress;

class Post_Scanner extends WP_REST_Controller
{
    var $namespace = 'sparqlpress/v1';
    var $base = 'scan_posts';



    public function __construct()
    {
    }

    public function scan_posts($params)
    {
        error_log('scan_posts called');
        $args = array(
            'numberposts' => -1
        );

        $all_posts = get_posts($args);

        $turtle = '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
                   @prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
                   @prefix owl: <http://www.w3.org/2002/07/owl#> .
                   @prefix skos: <http://www.w3.org/2004/02/skos/core#> .
                   @prefix dc: <http://purl.org/dc/terms/> .    
                   @prefix schema: <https://schema.org/> .';

        foreach ($all_posts as $post) {

            error_log(json_encode($post));
            $turtle = $turtle . PHP_EOL . ' <' . $post->guid . '> a schema:BlogPosting ;'
            . PHP_EOL . ' dc:title "' . $post->post_title . '" .';
            
        }
        //          echo "<br/><br/><br/>ID<br/>";
        //          echo $post->ID; 
        //          echo "<br/><br/><br/>ID<br/>";
        //          echo $post->guid;

        error_log($turtle);

        // $parser = ARC2::getTurtleParser();
        $arc2_adapter = ARC2_Adapter::getInstance();

        $arc2_adapter->add_turtle($turtle);

        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;

        // http://localhost/wordpress/wp-admin/admin.php?page=store-admin

        // $data = array('SCAN POSTS');
        // return new WP_REST_Response( $data, 200 );
    }

    // move to admin file
    public function register_routes()
    {
        error_log('register_routes in post_scanner called');
        register_rest_route($this->namespace, '/' . $this->base, array(
            'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
            'callback'            => array($this, 'scan_posts'),
            'permission_callback' => '__return_true'
            //  'permission_callback' => array( $this, 'create_item_permissions_check' ),
            // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
        ));
    }
}
