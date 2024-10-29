<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cba-records.com
 * @since             1.0.0
 * @package           Api_Fetch_Twitter
 *
 * @wordpress-plugin
 * Plugin Name:       API Fetch Twitter
 * Plugin URI:        https://cba-records.com
 * Description:       Fetch and Display Tweets and Retweets
 * Version:           1.0.0
 * Author:            Matthew Lee
 * Author URI:        https://profiles.wordpress.org/teatreeman/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       api-fetch-twitter
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
define( 'API_FETCH_TWITTER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-api-fetch-twitter-activator.php
 */
function activate_api_fetch_twitter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-fetch-twitter-activator.php';
	Api_Fetch_Twitter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-api-fetch-twitter-deactivator.php
 */
function deactivate_api_fetch_twitter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-fetch-twitter-deactivator.php';
	Api_Fetch_Twitter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_api_fetch_twitter' );
register_deactivation_hook( __FILE__, 'deactivate_api_fetch_twitter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-api-fetch-twitter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_api_fetch_twitter() {

	$plugin = new Api_Fetch_Twitter();
	$plugin->run();

}
run_api_fetch_twitter();
