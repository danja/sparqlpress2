<?php

require_once dirname(__FILE__).'/../arc2/vendor/autoload.php';
// used as singleton

class ARC2_Adapter extends WP_REST_Controller
{

    private static $instance = null;

    var $namespace = 'sparqlpress/v1';
    var $base = 'create_store';
    var $config = array(
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

    public function __construct()
    {
    }

    public static function getInstance()
    {
      if (self::$instance == null)
      {
        self::$instance = new ARC2_Adapter();
      }
   
      return self::$instance;
    }

    public function create_store()
    {
        error_log('create_store called');
        require dirname(__FILE__) . '/../arc2/vendor/autoload.php';

        $store = ARC2::getStore($this->config);

        // since version 2.3+
        $store->createDBCon();

        if (!$store->isSetUp()) {
            $store->setUp();
        }

        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;
    }

    public function getEndpoint()
    {
        return ARC2::getStoreEndpoint($this->config);
    }

    public function register_routes()
    {
        error_log('ARC2_Adapter->register_routes called');
        register_rest_route($this->namespace, '/' . $this->base, array(
            'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
            'callback'            => array($this, 'create_store'),
            //  'permission_callback' => array( $this, 'create_item_permissions_check' ),
            // 'args'                => $this->get_endpoint_args_for_item_schema( true ),
        ));
    }
}
