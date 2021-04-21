<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/danja/sparqlpress2
 * @since             1.0.0
 * @package           SparqlPress
 *
 * @wordpress-plugin
 * Plugin Name:       SparqlPress
 * Plugin URI:        https://github.com/danja/sparqlpress2
 * Description:       Adds SPARQL capabilities
 * Version:           1.0.0
 * Author:            Danny Ayers
 * Author URI:        http://hyperdata.it
 * License:           MIT
 * License URI:       https://en.wikipedia.org/wiki/MIT_License
 * Text Domain:       sparqlpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SPARQLPRESS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/sparqlpress-activator.php
 */
function activate_sparqlpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/sparqlpress-activator.php';
	SparqlPress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/sparqlpress-deactivator.php
 */
function deactivate_sparqlpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/sparqlpress-deactivator.php';
	SparqlPress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sparqlpress' );
register_deactivation_hook( __FILE__, 'deactivate_sparqlpress' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

// error_log(plugin_dir_path( __FILE__ ) . 'core/sparqlpress-core.php');

require plugin_dir_path( __FILE__ ) . 'core/sparqlpress-core.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sparqlpress() {
	global $sparqlpress;
	load_plugin_textdomain('sparqlpress'); ////
	$sparqlpress = new SparqlPress();
	do_action('sparqlpress_init');
	$sparqlpress->run();
	
}
add_action('plugins_loaded', 'run_sparqlpress');
// run_sparqlpress();
