<?php

require_once dirname(__FILE__) . '/../arc2/vendor/autoload.php';
// used as singleton



class ARC2_Adapter extends WP_REST_Controller
{
    private static $instance = null;

    private $store;

    var $namespace = 'sparqlpress/v1';

    private $config;
  

    public function __construct()
    {
        global $wpdb;

        $this->config = array(
  /*
            'db_host' => $_SERVER['SERVER_NAME'],
            'db_name' => $wpdb->dbname,
            'db_user' => $wpdb->dbuser,
            'db_pwd' => $wpdb->dbpassword,
            'store_name' => 'sparqlpress',

             'db_host' => 'localhost',
            'db_name' => 'arc_',
            'db_user' => 'danny',
            'db_pwd' => 'maria',
            'store_name' => 'arc_tests',

             'db_host' => 'localhost',
            'db_name' => 'bitnami_wordpress',
            'db_user' => 'bn_wordpress',
            'db_pwd' => '915cfedeac',
            'store_name' => 'arc_tests',
    */
            
    'db_host' => $_SERVER['SERVER_NAME'],
    'db_name' => $wpdb->dbname,
    'db_user' => $wpdb->dbuser,
    'db_pwd' => $wpdb->dbpassword,
    'store_name' => 'sparqlpress',
            
    
            /* network */
            /* 'proxy_host' => '192.168.1.1',
               'proxy_port' => 8080,
            */
    
            /* parsers */
            'bnode_prefix' => 'bn',
            /* sem html extraction */
            'sem_html_formats' => 'rdfa microformats',
    
            /* endpoint */
            'endpoint_features' => array(
                'select', 'construct', 'ask', 'describe',
                'load', 'insert', 'delete',
                'dump' /* dump is a special command for streaming SPOG export */
            ),
            'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
            'endpoint_read_key' => '', /* optional */
            'endpoint_write_key' => '', /* optional, but without one, everyone can write! */
            'endpoint_max_limit' => 250, /* optional */
        );
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ARC2_Adapter();
        }

        return self::$instance;
    }

    public function init_store()
    {
        require dirname(__FILE__) . '/../arc2/vendor/autoload.php';

        $this->store = ARC2::getStore($this->config);

        // since version 2.3+
        $this->store->createDBCon();

        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }
    }

    public function create_store() // REST call
    {
        error_log('-----');
        error_log('create_store called');
        $this->init_store();

        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;
    }



    public function getEndpoint()
    {
        return ARC2::getStoreEndpoint($this->config);
    }

    
    // rename, or return results..?
    public function get_results()
    {
        $endpoint = $this->getEndpoint();

        if (!$endpoint->isSetUp()) {
            $endpoint->setUp();
        }

        $endpoint->handleRequest();
        $endpoint->sendHeaders();

        $results = $endpoint->getResult();
        error_log("Result = ");
        error_log($results);
        echo $results;

        
        // error_log(json_encode($endpoint, JSON_PRETTY_PRINT));
        // $endpoint->go();
    }


    public function add_turtle($turtle)
    {

        error_log('add_turtle($turtle)');
        if (is_null($this->store)) {
            $this->init_store();
        } // NOTE GRAPH SPECIFIED
        // $this->store->insert($turtle, 'https://danbri.org/foaf.rdf', $keep_bnode_ids = 0);
        //  $this->store->query('INSERT DATA {'.$turtle.'}');

        $base = 'http://example.com/';

        $parser = ARC2::getTurtleParser();

        $parser->parse($base, $turtle);
        $this->store->insert($parser->getTriples(), '', $keep_bnode_ids = 0);

        /*
        INSERT INTO <http://example.com/> {
            <#foo> <bar> "baz" .
           }
           */
    }

    public function register_routes()
    {
        error_log('ARC2_Adapter->register_routes called');
        register_rest_route($this->namespace, '/create_store', array(
            'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
            'callback'            => array($this, 'create_store'),
            //  'permission_callback' => array( $this, 'create_item_permissions_check' ),
            // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
            'permission_callback' => '__return_true'
        ));
        register_rest_route($this->namespace, '/sparql', array(
            // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
            'methods'  => WP_REST_Server::ALLMETHODS,
            // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
            'callback' => array($this, 'get_results'),
            'permission_callback' => '__return_true'
        ));
    }
}
