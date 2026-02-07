<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Delight_Lead
 * @subpackage Delight_Lead/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Delight_Lead
 * @subpackage Delight_Lead/includes
 * @author     Your Name <email@example.com>
 */
class Delight_Lead_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'delight_lead_feedback';
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			syrup_tried varchar(50) NOT NULL,
			preferred_syrup varchar(50) NOT NULL,
			flavor_descriptors text NOT NULL,
			other_flavor text,
			home_usage varchar(50) NOT NULL,
			other_usage text,
			buying_interest varchar(20) NOT NULL,
			price_range varchar(20) NOT NULL,
			suggestions text,
			join_founding_tasters varchar(10) NOT NULL,
			taster_name varchar(100),
			taster_email varchar(100),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// Create default options for admin emails
		add_option('delight_lead_admin_emails', '');
	}

}
