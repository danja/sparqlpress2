<?php

require_once dirname(__FILE__) . '/../arc2/vendor/autoload.php';
// used as singleton



class ARC2_Adapter extends WP_REST_Controller
{
    private static $instance = null;

    private $store;

    // var $namespace = 'sparqlpress/v1';

    private $base_store_config;


    public function __construct()
    {
        global $wpdb;

        $this->base_store_config = array(
            'db_host' => $_SERVER['SERVER_NAME'],
            'db_name' => $wpdb->dbname,
            'db_user' => $wpdb->dbuser,
            'db_pwd' => $wpdb->dbpassword,
            'store_name' => 'sparqlpress_system',

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

    public function init_store($store_name)
    {
        require dirname(__FILE__) . '/../arc2/vendor/autoload.php';

       

        if (isset($store_name) && trim($store_name) !== '') {
            $store_config = array_merge(array(), $this->base_store_config); // clone

            $store_config['store_name']= trim($store_name);
        } else {
            $store_config = $this->base_store_config;
        }

        error_log(json_encode($this->base_store_config));
        error_log(json_encode($store_config));
        
        $this->store = ARC2::getStore($store_config);

        // since version 2.3+
        $this->store->createDBCon();

        if (!$this->store->isSetUp()) {
            $this->store->setUp();
        }
    }

    public function create_store($stuff) // REST call
    {
        $params = $stuff->get_params();
        // error check needed
        error_log($params['store_name']);

        $store_name = $params['store_name'];
        error_log('-----');
        error_log('create_store called');
        $this->init_store($store_name);

        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;
    }


    // https://developer.wordpress.org/reference/functions/media_handle_upload/
    public function upload_data() // REST call
    {
        error_log('-----');
        error_log('upload_data called');

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['sparqlpress_data'];

        $upload_overrides = array(
            'test_form' => false //? inexplicably necessary
        );

        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        error_log('movefile = ');
        error_log(print_r($movefile, true));

        if ($movefile && !isset($movefile['error'])) {
            error_log('seems ok...');
            echo __('File is valid, and was successfully uploaded.', 'textdomain') . "\n";

            $turtle = file_get_contents($movefile['file']);
            $this->add_turtle($turtle);
            //  var_dump( $movefile );
        } else {
            /*
             * Error generated by _wp_handle_upload()
             * @see _wp_handle_upload() in wp-admin/includes/file.php
             */
            echo $movefile['error'];
            error_log($movefile['error']);
        }

        error_log(json_encode($movefile));

        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;
    }


    public function getEndpoint()
    {
        return ARC2::getStoreEndpoint($this->base_store_config);
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

    public function scan_page($stuff) // REST call
    {
        $params = $stuff->get_params();
        // error check needed
        // error_log($params['remote_url']);

        $remote = $params['remote_url'];

        // need cURL or similar
        $contents = file_get_contents($remote);

        $this->extract_triples($contents, $remote);

        // redirect to admin page
        $url = get_site_url() . '/wp-admin/admin.php?page=store-admin';
        wp_redirect($url);
        exit;
    }

    // wrapper for extractors
    public function extract_triples($string, $subject = null, $mime = 'text/plain')
    {
        //        error_log($string);

        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $matches);

        $urls = $matches[0];

        $turtle = '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
                   @prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
                   @prefix owl: <http://www.w3.org/2002/07/owl#> .
                   @prefix skos: <http://www.w3.org/2004/02/skos/core#> .
                   @prefix dc: <http://purl.org/dc/terms/> .    
                   @prefix schema: <https://schema.org/> .';

        error_log("THIS IS URLS");
        error_log(print_r($urls, true));

        foreach ($urls as $url) {
            error_log("URL");
            error_log(print_r($url, true));
            $turtle = $turtle . PHP_EOL . '<' . $subject . '> dc:related <' . $url . '> .';
        }

        error_log($turtle);

        $this->add_turtle($turtle);
    }
}
