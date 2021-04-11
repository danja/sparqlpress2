<?php

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
        $data = array('SCAN POSTS');
        return new WP_REST_Response( $data, 200 );
    }

    public function register_routes()
    {
        error_log('register_routes called');
        register_rest_route($this->namespace, '/' . $this->base, array(
                'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
                'callback'            => array($this, 'scan_posts'),
                //  'permission_callback' => array( $this, 'create_item_permissions_check' ),
                // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
        ));
    }
}
?>