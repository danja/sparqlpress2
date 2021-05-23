<?php



/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    SparqlPress
 * @subpackage SparqlPress/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SparqlPress
 * @subpackage SparqlPress/admin
 * @author     Your Name <email@example.com>
 */
class SparqlPress_Admin
{

	var $namespace = 'sparqlpress/v1';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		error_log('SparqlPress_Admin __construct called');
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->init_menu();

		$this->arc2_adapter = ARC2_Adapter::getInstance();

		$post_scanner = new Post_Scanner();
		//	add_action('rest_api_init', array( $arc2_adapter , 'register_routes' ) );
		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('rest_api_init', array($post_scanner, 'register_routes'));
	}

	// was in arc2_adapter
	public function register_routes()
	{
		error_log('sparqlpress-admin->register_routes called');
		register_rest_route($this->namespace, '/create_store', array(
			'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
			'callback'            => array($this->arc2_adapter, 'create_store'),
			'permission_callback' => '__return_true'
		));

					//  'permission_callback' => array( $this, 'create_item_permissions_check' ),
			// 'args'                => $this->get_endpoint_args_for_item_schema( true ),

		register_rest_route($this->namespace, '/sparql', array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => array($this->arc2_adapter, 'get_results'),
			'permission_callback' => '__return_true'
		));
		register_rest_route($this->namespace, '/add_data', array(
			'methods'             => WP_REST_Server::ALLMETHODS, // CREATABLE for POST
			'callback'            => array($this->arc2_adapter, 'add_data'),
			'permission_callback' => '__return_true'
		));
	}


	function init_menu()
	{
		function sparqlpress_admin_menu()
		{
			add_menu_page(
				__('SparqlPress', 'my-textdomain'),
				__('SparqlPress', 'my-textdomain'),
				'manage_options',
				'admin-index',
				function () {
					$index = dirname(__FILE__) . '/index.php';
					include $index;
					//	error_log('include called');
					//	error_log($index);
				},
				'dashicons-admin-generic',
				68
			);
		}

		add_action('admin_menu', 'sparqlpress_admin_menu');

		/*
			add_submenu_page( 
				string $parent_slug, 
				string $page_title, 
				string $menu_title, 
				string $capability, 
				string $menu_slug, 
				callable $function = '', 
				int $position = null )
			*/
		function sparqlpress_adminfoaf_submenu()
		{
			add_submenu_page(
				'admin-index',
				__('Danbri FOAF', 'textdomain'),
				__('DANBRI', 'textdomain'),
				'manage_options',
				'danbri-foaf',
				function () {
					$page = dirname(__FILE__) . '/danbri-foaf.php';
					include $page;
					error_log('include called');
					error_log($page);
				}
			);
		}
		add_action('admin_menu', 'sparqlpress_adminfoaf_submenu');


		// BETTER?  error_log(plugin_dir_path( __FILE__ ) . 'core/sparqlpress-core.php');

		function sparqlpress_endpoint_submenu()
		{
			add_submenu_page(
				'admin-index',
				__('SPARQL', 'textdomain'),
				__('SPARQL', 'textdomain'),
				'manage_options',
				'endpoint', // SPARQL Endpoint 
				function () {
					$page = plugin_dir_path(__FILE__) . '../core/endpoint.php';
					include_once $page;
				}
			);
		}
		add_action('admin_menu', 'sparqlpress_endpoint_submenu');

		function sparqlpress_store_admin_submenu()
		{
			add_submenu_page(
				'admin-index',
				__('Store Admin', 'textdomain'),
				__('Store Admin', 'textdomain'),
				'manage_options',
				'store-admin',
				function () {
					$page = dirname(__FILE__) . '/store-admin.php';
					include $page;
					error_log('include called');
					error_log($page);
				}
			);
		}
		add_action('admin_menu', 'sparqlpress_store_admin_submenu');

		error_log('sparqlpress_store_admin_submenu() called');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/sparqlpress-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in SparqlPress_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SparqlPress_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style('yasgui-css', plugins_url('css/yasgui.min.css', __FILE__));
		wp_enqueue_script('yasgui-js', plugins_url('js/yasgui.min.js', __FILE__));
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/sparqlpress-admin.js', array('jquery'), $this->version, false);
	}
}
