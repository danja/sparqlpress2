<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/danja/wp-catcheck
 * @since             1.0.0
 * @package           CatCheck
 *
 * @wordpress-plugin
 * Plugin Name:       CatCheck
 * Plugin URI:        https://github.com/danja/wp-catcheck
 * Description:       A simple category checker
 * Version:           1.0.0
 * Author:            Danny Ayers
 * Author URI:        http://hyperdata.it
 * License:           MIT
 * License URI:       https://en.wikipedia.org/wiki/MIT_License
 * Text Domain:       catcheck
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
define( 'CATCHECK_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-catcheck-activator.php
 */
function activate_catcheck() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-catcheck-activator.php';
	CatCheck_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-catcheck-deactivator.php
 */
function deactivate_catcheck() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-catcheck-deactivator.php';
	CatCheck_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_catcheck' );
register_deactivation_hook( __FILE__, 'deactivate_catcheck' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-catcheck.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_catcheck() {

	$plugin = new CatCheck();
	$plugin->run();

}
run_catcheck();
