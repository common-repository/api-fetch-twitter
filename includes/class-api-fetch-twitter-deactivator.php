<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/includes
 * @author     Matthew Lee <info@cba-records.com>
 */
class Api_Fetch_Twitter_Deactivator {

	/**
	 * Fired on deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		 $timestamp = wp_next_scheduled( 'api_fetch_twitter_cron' );
		 wp_unschedule_event( $timestamp, 'api_fetch_twitter_cron' );
	}

}
