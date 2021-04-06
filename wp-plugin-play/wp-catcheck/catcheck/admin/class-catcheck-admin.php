<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CatCheck
 * @subpackage CatCheck/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CatCheck
 * @subpackage CatCheck/admin
 * @author     Your Name <email@example.com>
 */
class CatCheck_Admin
{

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

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->init_menu();
	}

	function init_menu()
	{
		function catcheck_admin_menu()
		{
			/*
			function catcheck_admin_page_contents() {
				?>
					<h1>
						<?php esc_html_e( 'Welcome to my custom admin page.', 'my-plugin-textdomain' ); ?>
					</h1>
				<?php
			}

			error_log('catcheck_admin_menu() called');
			
			add_menu_page(
				__('CatCheck', 'my-textdomain'),
				__('CatCheck', 'my-textdomain'),
				'manage_options',
				'sample-page',
				'catcheck_admin_page_contents',
				'dashicons-admin-generic',
				68
			);
			*/
			add_menu_page(
				__('CatCheck', 'my-textdomain'),
				__('CatCheck', 'my-textdomain'),
				'manage_options',
				'admin-index', // was sample-page
				function () {
					$index = dirname(__FILE__) . '/index.php';
					include $index;
					error_log('include called');
					error_log($index);
				},
				'dashicons-admin-generic',
				68
			);
		}

		add_action('admin_menu', 'catcheck_admin_menu');
		error_log('here');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CatCheck_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CatCheck_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/plugin-name-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CatCheck_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CatCheck_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/plugin-name-admin.js', array('jquery'), $this->version, false);
	}
}
