<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Delight_Lead
 * @subpackage Delight_Lead/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Delight_Lead
 * @subpackage Delight_Lead/admin
 * @author     Your Name <email@example.com>
 */
class Delight_Lead_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $delight_lead, $version ) {

		$this->delight_lead = $delight_lead;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->delight_lead, plugin_dir_url( __FILE__ ) . 'css/delight-lead-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->delight_lead, plugin_dir_url( __FILE__ ) . 'js/delight-lead-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add admin menu pages
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menus() {
		add_menu_page(
			'Delight Lead Settings',
			'Delight Lead',
			'manage_options',
			'delight-lead-settings',
			array( $this, 'settings_page' ),
			'dashicons-feedback',
			30
		);
		
		add_submenu_page(
			'delight-lead-settings',
			'Feedback Submissions',
			'Feedback Submissions',
			'manage_options',
			'delight-lead-submissions',
			array( $this, 'submissions_page' )
		);
		
		// Hidden submenu page for single submission view
		add_submenu_page(
			null,
			'View Submission',
			'View Submission',
			'manage_options',
			'delight-lead-view',
			array( $this, 'view_submission_page' )
		);
	}

	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting( 'delight_lead_settings', 'delight_lead_admin_emails' );
	}

	/**
	 * Render settings page
	 *
	 * @since    1.0.0
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1>Delight Lead Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'delight_lead_settings' );
				do_settings_sections( 'delight_lead_settings' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">Admin Emails</th>
						<td>
							<textarea name="delight_lead_admin_emails" rows="3" cols="50"><?php echo esc_textarea( get_option('delight_lead_admin_emails') ); ?></textarea>
							<p class="description">Enter email addresses separated by commas. These emails will receive form submissions.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render submissions page
	 *
	 * @since    1.0.0
	 */
	public function submissions_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'delight_lead_feedback';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
		
		if ( ! $table_exists ) {
			echo '<div class="wrap"><h1>Feedback Submissions</h1>';
			echo '<div class="notice notice-error"><p>Database table does not exist. Please deactivate and reactivate the plugin.</p></div>';
			echo '</div>';
			return;
		}
		
		$submissions = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
		
		?>
		<div class="wrap">
			<h1>Feedback Submissions</h1>
			<?php if ( empty( $submissions ) ) : ?>
				<p>No submissions yet.</p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>Syrup Tried</th>
							<th>Preferred</th>
							<th>Interest</th>
							<th>Price Range</th>
							<th>Name</th>
							<th>Email</th>
							<th>Date</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $submissions as $submission ) : ?>
						<tr>
							<td><?php echo $submission->id; ?></td>
							<td><?php echo esc_html( $submission->syrup_tried ); ?></td>
							<td><?php echo esc_html( $submission->preferred_syrup ); ?></td>
							<td><?php echo esc_html( $submission->buying_interest ); ?></td>
							<td><?php echo esc_html( $submission->price_range ); ?></td>
							<td><?php echo esc_html( $submission->taster_name ); ?></td>
							<td><?php echo esc_html( $submission->taster_email ); ?></td>
							<td><?php echo $submission->created_at; ?></td>
							<td>
								<a href="<?php echo admin_url( 'admin.php?page=delight-lead-view&id=' . $submission->id ); ?>" class="button button-small">View</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render single submission view page
	 *
	 * @since    1.0.0
	 */
	public function view_submission_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'delight_lead_feedback';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
		
		if ( ! $table_exists ) {
			echo '<div class="wrap"><h1>View Submission</h1>';
			echo '<div class="notice notice-error"><p>Database table does not exist. Please deactivate and reactivate the plugin.</p></div>';
			echo '</div>';
			return;
		}
		
		$submission_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		
		if ( $submission_id === 0 ) {
			echo '<div class="wrap"><h1>View Submission</h1>';
			echo '<div class="notice notice-error"><p>Invalid submission ID.</p></div>';
			echo '</div>';
			return;
		}
		
		$submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $submission_id ) );
		
		if ( ! $submission ) {
			echo '<div class="wrap"><h1>View Submission</h1>';
			echo '<div class="notice notice-error"><p>Submission not found.</p></div>';
			echo '</div>';
			return;
		}
		
		?>
		<div class="wrap">
			<h1>View Submission #<?php echo $submission->id; ?></h1>
			
			<div class="card">
				<h2>Contact Information</h2>
				<table class="form-table">
					<tr>
						<th>Name:</th>
						<td><?php echo esc_html( $submission->taster_name ); ?></td>
					</tr>
					<tr>
						<th>Email:</th>
						<td><?php echo esc_html( $submission->taster_email ); ?></td>
					</tr>
					<tr>
						<th>Submission Date:</th>
						<td><?php echo $submission->created_at; ?></td>
					</tr>
				</table>
			</div>
			
			<div class="card">
				<h2>Tasting Feedback</h2>
				<table class="form-table">
					<tr>
						<th>Which syrup did you try today?</th>
						<td><?php echo esc_html( $submission->syrup_tried ); ?></td>
					</tr>
					<tr>
						<th>Which one did you prefer?</th>
						<td><?php echo esc_html( $submission->preferred_syrup ); ?></td>
					</tr>
					<tr>
						<th>How would you describe the flavour?</th>
						<td><?php echo esc_html( $submission->flavor_descriptors ); ?></td>
					</tr>
					<?php if ( ! empty( $submission->other_flavor ) ) : ?>
					<tr>
						<th>Other Flavor:</th>
						<td><?php echo esc_html( $submission->other_flavor ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th>How would you use this syrup at home?</th>
						<td><?php echo esc_html( $submission->home_usage ); ?></td>
					</tr>
					<?php if ( ! empty( $submission->other_usage ) ) : ?>
					<tr>
						<th>Other Usage:</th>
						<td><?php echo esc_html( $submission->other_usage ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th>Would you be interested in buying this?</th>
						<td><?php echo esc_html( $submission->buying_interest ); ?></td>
					</tr>
					<tr>
						<th>What price range feels right?</th>
						<td><?php echo esc_html( $submission->price_range ); ?></td>
					</tr>
					<?php if ( ! empty( $submission->suggestions ) ) : ?>
					<tr>
						<th>Any thoughts, ideas, or suggestions?</th>
						<td><?php echo esc_html( $submission->suggestions ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th>Join Founding Tasters?</th>
						<td><?php echo esc_html( $submission->join_founding_tasters ); ?></td>
					</tr>
				</table>
			</div>
			
			<p>
				<a href="<?php echo admin_url( 'admin.php?page=delight-lead-submissions' ); ?>" class="button">← Back to Submissions</a>
			</p>
		</div>
		
		<style>
			.card {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 20px;
				margin: 20px 0;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}
			.card h2 {
				margin-top: 0;
				color: #23282d;
			}
			.form-table th {
				width: 300px;
				font-weight: 600;
			}
		</style>
		<?php
	}

}
