<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Delight_Lead
 * @subpackage Delight_Lead/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Delight_Lead
 * @subpackage Delight_Lead/public
 * @author     Your Name <email@example.com>
 */
class Delight_Lead_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $delight_lead;

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
	public function __construct( $delight_lead, $version ) {

		$this->delight_lead = $delight_lead;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->delight_lead, plugin_dir_url( __FILE__ ) . 'css/delight-lead-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Delight_Lead_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Delight_Lead_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->delight_lead, plugin_dir_url( __FILE__ ) . 'js/delight-lead-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the Hello World shortcode.
	 *
	 * @since    1.0.0
	 */
	public function register_delight_lead_form_shortcode() {
		add_shortcode( 'delight_lead_form', array( $this, 'delight_lead_form_callback' ) );
	}

	/**
	 * Shortcode callback for displaying Hello World message.
	 *
	 * @since    1.0.0
	 * @param    array    $atts       Shortcode attributes.
	 * @return   string               The HTML output.
	 */
	public function delight_lead_form_callback( $atts ) {
		return '<h1>Hello World</h1>';
	}

}
