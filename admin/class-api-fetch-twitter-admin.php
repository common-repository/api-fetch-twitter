<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/admin
 * @author     Matthew Lee <info@cba-records.com>
 */
class Api_Fetch_Twitter_Admin {

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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/api-fetch-twitter-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/api-fetch-twitter-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 *  Add menu to WordPress admin.
	 *
	 *  @since 1.0.0
	 */
	public function add_menu() {

		add_menu_page( 'API Fetch Twitter',  __( 'API Settings', 'api-fetch-twitter' ), 'manage_options', $this->plugin_name . '-api-settings', array( $this, 'api_settings' ), 'dashicons-twitter', 10 );

	}

	/**
	 *  Load plugin administration page.
	 *
	 *  @since 1.0.0
	 */
	public function api_settings() {

		include plugin_dir_path( __FILE__ ) . 'partials/api-fetch-twitter-admin-display.php';

	}

	/**
	 *  Register plugin settings page.
	 *
	 *  @since 1.0.0
	 */
	public function twitter_api_settings() {

		// Validate inputted API settings.
		register_setting( 'api_fetch_twitter_options', 'api_fetch_twitter_options', array( $this, 'api_fetch_twitter_options_validate' ) );

		// Display error or success messages.
		settings_errors( 'api_fetch_twitter_errors', true, false );

		// Add API Settings section.
		add_settings_section( 'api_settings', __( 'API Settings', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_text' ), 'api_fetch_twitter' );

		// Register settings fields.
		add_settings_field( 'api_fetch_twitter_setting_api_key', __( 'API Key', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_api_key' ), 'api_fetch_twitter', 'api_settings' );
		add_settings_field( 'api_fetch_twitter_setting_api_secret', __( 'API Secret', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_api_secret' ), 'api_fetch_twitter', 'api_settings' );
		add_settings_field( 'api_fetch_twitter_setting_access_token', __( 'Aceess Token', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_access_token' ), 'api_fetch_twitter', 'api_settings' );
		add_settings_field( 'api_fetch_twitter_setting_access_secret', __( 'Aceess Secret', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_access_secret' ), 'api_fetch_twitter', 'api_settings' );
		add_settings_field( 'api_fetch_twitter_setting_user_name', __( 'User Name', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_user_name' ), 'api_fetch_twitter', 'api_settings' );
		add_settings_field( 'api_fetch_twitter_setting_user_id', __( 'User ID', 'api-fetch-twitter' ), array( $this, 'api_fetch_twitter_setting_user_id' ), 'api_fetch_twitter', 'api_settings' );

	}

	/**
	 *  Validate API Keys and User Name.
	 *
	 *  @since 1.0.0
	 *
	 *  @param array $input Array of form inputs.
	 *  @return Array modified form inputs.
	 */
	public function api_fetch_twitter_options_validate( $input ) {

		if ( preg_match( '/api-fetch-twitter-api-settings/', wp_get_referer() ) ) {

			$type      = 'updated';
			$message   = __( 'API details successfully validated', 'api-fetch-twitter' );
			$api_check = Api_Fetch_Twitter_Admin_Twitter::validate_api_details( $input );

			if ( 200 !== $api_check['status'] ) {

				$type    = 'error';
				$message = 'Twitter API Fetch: ' . $api_check['message'];

			} else {

				$input['user_id'] = $api_check['message'];

				Api_Fetch_Twitter_Admin_Twitter::create_twitter_xml( $input );

			}

			add_settings_error(
				'api_fetch_twitter_errors',
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);

		}
		return $input;
	}

	/**
	 *  Display information about how to display plugin settings.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_text() {
		echo '<p>' .  __( 'To display tweets simply add the shortcode [api-fetch-twitter] to your sidebar', 'api-fetch-twitter' ) . '</p>';
	}

	/**
	 *  Input for Twitter API Key.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_api_key() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_api_key' name='api_fetch_twitter_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' style='width:80%;'/>";
	}

	/**
	 *  Input for Twitter API Secret.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_api_secret() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_setting_api_secret' name='api_fetch_twitter_options[api_secret]' type='password' value='" . esc_attr( $options['api_secret'] ) . "'  style='width:80%;'/>";
	}

	/**
	 *  Input for Twitter Access Token.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_access_token() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_setting_access_token' name='api_fetch_twitter_options[access_token]' type='text' value='" . esc_attr( $options['access_token'] ) . "'  style='width:80%;'/>";
	}

	/**
	 *  Input for Twitter Access Secret.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_access_secret() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_setting_access_secret' name='api_fetch_twitter_options[access_secret]' type='password' value='" . esc_attr( $options['access_secret'] ) . "'  style='width:80%;'/>";
	}

	/**
	 *  Input for Twitter User Name.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_user_name() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_setting_user_name' name='api_fetch_twitter_options[user_name]' type='text' value='" . esc_attr( $options['user_name'] ) . "'  style='width:40%;'/>";
	}

	/**
	 *  Input for Twitter User ID.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_setting_user_id() {
		$options = get_option( 'api_fetch_twitter_options' );
		echo "<input id='api_fetch_twitter_setting_user_id' name='api_fetch_twitter_options[user_id]' type='text' value='" . esc_attr( $options['user_id'] ) . "'  style='width:40%;' readonly/>";
	}

	/**
	 *  Create Cron Job.
	 *
	 *  @since 1.0.0
	 */
	public function api_fetch_twitter_cron() {

		$options = get_option( 'api_fetch_twitter_options' );
		if ( '' !== $options['user_id'] ) {
			Api_Fetch_Twitter_Admin_Twitter::create_twitter_xml( $options );
		}
	}

}
