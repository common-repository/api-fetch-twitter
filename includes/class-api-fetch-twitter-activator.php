<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/includes
 * @author     Matthew Lee <info@cba-records.com>
 */
class Api_Fetch_Twitter_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		// Register Default Twitter API Fetch Options.
		if ( empty( get_option( 'api_fetch_twitter_options' ) ) ) {
			add_option(
				'api_fetch_twitter_options',
				array(
					'api_key'       => __( 'Twitter API Key', 'api-fetch-twitter' ),
					'api_secret'    => __( 'Twitter API Secret', 'api-fetch-twitter' ),
					'access_token'  => __( 'Twitter Access Token', 'api-fetch-twitter' ),
					'access_secret' => __( 'Twitter Access Secret', 'api-fetch-twitter' ),
					'user_name'     => '',
					'user_id'       => '',
				)
			);
		}

		// Register hourly cron job.
		if ( ! wp_next_scheduled( 'api_fetch_twitter_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'api_fetch_twitter_cron' );
		}

	}

}
