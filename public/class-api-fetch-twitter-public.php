<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cba-records.com
 * @since      1.0.0
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Api_Fetch_Twitter
 * @subpackage Api_Fetch_Twitter/public
 * @author     Matthew Lee <info@cba-records.com>
 */
class Api_Fetch_Twitter_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/api-fetch-twitter-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'google-fonts-sura', 'https://fonts.googleapis.com/css?family=Sura', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/api-fetch-twitter-public.js', array( 'jquery' ), $this->version, false );
		
		wp_localize_script(
				$this->plugin_name,
				'API_FETCH_TWITTER',
				array(
					'image_dir' => plugin_dir_url( __FILE__ ) . 'images/',
					'feed'      => plugin_dir_url( __FILE__ ) . 'feeds/twitter-' . get_current_blog_id() . '.xml',
				)
			);

	}
	
	/**
	 *  Register shortcode.
	 *
	 *  @since 1.0.0
	 */
	public function register_shortcode() {

		add_shortcode( 'api-fetch-twitter', array( $this, 'api_fetch_twitter' ) );
	}

	/**
	 *  Shortcode.
	 *
	 *  @since 1.0.0
	 *
	 *  @param Array $atts Shortcode attributes.
	 */
	public function api_fetch_twitter( $atts ) {

		extract(
			shortcode_atts(
				array(

					'width'  => '',
					'height' => '',

				),
				$atts
			)
		); // phpcs:ignore

		$html  = '<div id="api-fetch-twitter-header">';
		$html .= '<img src="' . esc_url( plugin_dir_url( __FILE__ ) . 'images/bird_blue_48.png' ) . '" alt="Twitter" />';
		$html .= '</div>';
		$html .= '<div id="api-fetch-twitter"></div>';

		return $html;
	}

}
