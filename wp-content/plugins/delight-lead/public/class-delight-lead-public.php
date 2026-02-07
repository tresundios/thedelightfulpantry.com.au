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
	 * Register AJAX actions
	 *
	 * @since    1.0.0
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_delight_lead_submit', array( $this, 'ajax_handle_form_submission' ) );
		add_action( 'wp_ajax_nopriv_delight_lead_submit', array( $this, 'ajax_handle_form_submission' ) );
	}

	/**
	 * Handle AJAX form submission
	 *
	 * @since    1.0.0
	 */
	public function ajax_handle_form_submission() {
		// Verify nonce
		if ( ! isset( $_POST['delight_lead_nonce'] ) || ! wp_verify_nonce( $_POST['delight_lead_nonce'], 'delight_lead_form_submit' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// Ensure database table exists
		$this->ensure_database_table_exists();
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'delight_lead_feedback';

		// Sanitize and prepare data
		$syrup_tried = sanitize_text_field( $_POST['syrup_tried'] );
		$preferred_syrup = sanitize_text_field( $_POST['preferred_syrup'] );
		$flavor_descriptors = isset( $_POST['flavor_descriptors'] ) ? implode( ', ', array_map( 'sanitize_text_field', $_POST['flavor_descriptors'] ) ) : '';
		$other_flavor = sanitize_text_field( $_POST['other_flavor'] );
		$home_usage = sanitize_text_field( $_POST['home_usage'] );
		$other_usage = sanitize_text_field( $_POST['other_usage'] );
		$buying_interest = sanitize_text_field( $_POST['buying_interest'] );
		$price_range = sanitize_text_field( $_POST['price_range'] );
		$suggestions = sanitize_textarea_field( $_POST['suggestions'] );
		$join_founding_tasters = sanitize_text_field( $_POST['join_founding_tasters'] );
		$taster_name = isset( $_POST['taster_name'] ) ? sanitize_text_field( $_POST['taster_name'] ) : '';
		$taster_email = isset( $_POST['taster_email'] ) ? sanitize_email( $_POST['taster_email'] ) : '';

		// Insert data into database
		$result = $wpdb->insert(
			$table_name,
			array(
				'syrup_tried' => $syrup_tried,
				'preferred_syrup' => $preferred_syrup,
				'flavor_descriptors' => $flavor_descriptors,
				'other_flavor' => $other_flavor,
				'home_usage' => $home_usage,
				'other_usage' => $other_usage,
				'buying_interest' => $buying_interest,
				'price_range' => $price_range,
				'suggestions' => $suggestions,
				'join_founding_tasters' => $join_founding_tasters,
				'taster_name' => $taster_name,
				'taster_email' => $taster_email,
				'created_at' => current_time( 'mysql' )
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// Log error if insertion fails
		if ( $result === false ) {
			error_log( 'Delight Lead: Failed to insert data into database. Error: ' . $wpdb->last_error );
			wp_send_json_error( 'Database insertion failed' );
		}

		// Send email notification
		$this->send_notification_email( array(
			'syrup_tried' => $syrup_tried,
			'preferred_syrup' => $preferred_syrup,
			'flavor_descriptors' => $flavor_descriptors,
			'other_flavor' => $other_flavor,
			'home_usage' => $home_usage,
			'other_usage' => $other_usage,
			'buying_interest' => $buying_interest,
			'price_range' => $price_range,
			'suggestions' => $suggestions,
			'join_founding_tasters' => $join_founding_tasters,
			'taster_name' => $taster_name,
			'taster_email' => $taster_email
		) );

		wp_send_json_success( 'Form submitted successfully' );
	}

	/**
	 * Handle form submission (fallback for non-AJAX)
	 *
	 * @since    1.0.0
	 */
	public function handle_form_submission() {
		// Ensure database table exists
		$this->ensure_database_table_exists();
		
		if ( isset( $_POST['delight_lead_submit'] ) && isset( $_POST['delight_lead_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['delight_lead_nonce'], 'delight_lead_form_submit' ) ) {
				wp_die( 'Security check failed' );
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'delight_lead_feedback';

			// Sanitize and prepare data
			$syrup_tried = sanitize_text_field( $_POST['syrup_tried'] );
			$preferred_syrup = sanitize_text_field( $_POST['preferred_syrup'] );
			$flavor_descriptors = isset( $_POST['flavor_descriptors'] ) ? implode( ', ', array_map( 'sanitize_text_field', $_POST['flavor_descriptors'] ) ) : '';
			$other_flavor = sanitize_text_field( $_POST['other_flavor'] );
			$home_usage = sanitize_text_field( $_POST['home_usage'] );
			$other_usage = sanitize_text_field( $_POST['other_usage'] );
			$buying_interest = sanitize_text_field( $_POST['buying_interest'] );
			$price_range = sanitize_text_field( $_POST['price_range'] );
			$suggestions = sanitize_textarea_field( $_POST['suggestions'] );
			$join_founding_tasters = sanitize_text_field( $_POST['join_founding_tasters'] );
			$taster_name = isset( $_POST['taster_name'] ) ? sanitize_text_field( $_POST['taster_name'] ) : '';
			$taster_email = isset( $_POST['taster_email'] ) ? sanitize_email( $_POST['taster_email'] ) : '';

			// Insert data into database
			$result = $wpdb->insert(
				$table_name,
				array(
					'syrup_tried' => $syrup_tried,
					'preferred_syrup' => $preferred_syrup,
					'flavor_descriptors' => $flavor_descriptors,
					'other_flavor' => $other_flavor,
					'home_usage' => $home_usage,
					'other_usage' => $other_usage,
					'buying_interest' => $buying_interest,
					'price_range' => $price_range,
					'suggestions' => $suggestions,
					'join_founding_tasters' => $join_founding_tasters,
					'taster_name' => $taster_name,
					'taster_email' => $taster_email,
					'created_at' => current_time( 'mysql' )
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			// Log error if insertion fails
			if ( $result === false ) {
				error_log( 'Delight Lead: Failed to insert data into database. Error: ' . $wpdb->last_error );
			}

			// Send email notification
			$this->send_notification_email( array(
				'syrup_tried' => $syrup_tried,
				'preferred_syrup' => $preferred_syrup,
				'flavor_descriptors' => $flavor_descriptors,
				'other_flavor' => $other_flavor,
				'home_usage' => $home_usage,
				'other_usage' => $other_usage,
				'buying_interest' => $buying_interest,
				'price_range' => $price_range,
				'suggestions' => $suggestions,
				'join_founding_tasters' => $join_founding_tasters,
				'taster_name' => $taster_name,
				'taster_email' => $taster_email
			) );

			// Redirect to prevent form resubmission
			wp_redirect( add_query_arg( 'success', '1', wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Ensure database table exists
	 *
	 * @since    1.0.0
	 */
	private function ensure_database_table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'delight_lead_feedback';
		
		// Check if table exists
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
		
		if ( ! $table_exists ) {
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
		}
	}

	/**
	 * Send notification email to admin emails
	 *
	 * @since    1.0.0
	 * @param    array    $data    Form submission data
	 */
	private function send_notification_email( $data ) {
		$admin_emails = get_option( 'delight_lead_admin_emails' );
		if ( empty( $admin_emails ) ) {
			return;
		}

		$emails = array_map( 'trim', explode( ',', $admin_emails ) );
		$subject = 'New Tasting Feedback Submission - The Delightful Pantry';
		
		$message = "New tasting feedback submission received:\n\n";
		$message .= "Name: " . $data['taster_name'] . "\n";
		$message .= "Email: " . $data['taster_email'] . "\n\n";
		$message .= "Syrup Tried: " . $data['syrup_tried'] . "\n";
		$message .= "Preferred Syrup: " . $data['preferred_syrup'] . "\n";
		$message .= "Flavor Descriptors: " . $data['flavor_descriptors'] . "\n";
		if ( ! empty( $data['other_flavor'] ) ) {
			$message .= "Other Flavor: " . $data['other_flavor'] . "\n";
		}
		$message .= "Home Usage: " . $data['home_usage'] . "\n";
		if ( ! empty( $data['other_usage'] ) ) {
			$message .= "Other Usage: " . $data['other_usage'] . "\n";
		}
		$message .= "Buying Interest: " . $data['buying_interest'] . "\n";
		$message .= "Price Range: " . $data['price_range'] . "\n";
		if ( ! empty( $data['suggestions'] ) ) {
			$message .= "Suggestions: " . $data['suggestions'] . "\n";
		}
		$message .= "Join Founding Tasters: " . $data['join_founding_tasters'] . "\n";

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		
		foreach ( $emails as $email ) {
			if ( is_email( $email ) ) {
				wp_mail( $email, $subject, $message, $headers );
			}
		}
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
	 * Shortcode callback for displaying tasting feedback form.
	 *
	 * @since    1.0.0
	 * @param    array    $atts       Shortcode attributes.
	 * @return   string               The HTML output.
	 */
	public function delight_lead_form_callback( $atts ) {
		ob_start();
		?>
		<style>
			.delight-lead-form {
				max-width: 600px;
				margin: 0 auto;
				padding: 20px;
				background: #f9f9f9;
				border-radius: 8px;
			}
			.delight-lead-form h2 {
				text-align: center;
				color: #2c5f2d;
				margin-bottom: 20px;
			}
			.delight-lead-form .form-group {
				margin-bottom: 20px;
			}
			.delight-lead-form label {
				display: block;
				margin-bottom: 8px;
				font-weight: bold;
				color: #333;
			}
			.delight-lead-form input[type="radio"],
			.delight-lead-form input[type="checkbox"] {
				margin-right: 8px;
			}
			.delight-lead-form input[type="text"],
			.delight-lead-form input[type="email"],
			.delight-lead-form textarea {
				width: 100%;
				padding: 10px;
				border: 1px solid #ddd;
				border-radius: 4px;
			}
			.delight-lead-form .checkbox-group label {
				display: inline-block;
				margin-right: 15px;
				font-weight: normal;
			}
			.delight-lead-form .submit-btn {
				background: #2c5f2d;
				color: white;
				padding: 12px 30px;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				font-size: 16px;
			}
			.delight-lead-form .submit-btn:hover {
				background: #1e4a1f;
			}
			.delight-lead-form .founding-tasters {
				background: #e8f5e8;
				padding: 15px;
				border-radius: 6px;
				margin-top: 20px;
			}
			
			/* Confetti Animation Styles */
			.confetti-container {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				pointer-events: none;
				z-index: 9999;
			}
			.confetti {
				position: absolute;
				width: 10px;
				height: 10px;
				background: #2c5f2d;
				animation: confetti-fall 3s linear forwards;
			}
			@keyframes confetti-fall {
				0% {
					transform: translateY(-100vh) rotate(0deg);
					opacity: 1;
				}
				100% {
					transform: translateY(100vh) rotate(720deg);
					opacity: 0;
				}
			}
			.thank-you-message {
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background: #2c5f2d;
				color: white;
				padding: 30px 40px;
				border-radius: 10px;
				text-align: center;
				z-index: 10000;
				box-shadow: 0 10px 30px rgba(0,0,0,0.3);
				animation: fadeInScale 0.5s ease-out;
			}
			@keyframes fadeInScale {
				0% {
					opacity: 0;
					transform: translate(-50%, -50%) scale(0.8);
				}
				100% {
					opacity: 1;
					transform: translate(-50%, -50%) scale(1);
				}
			}
			.thank-you-message h3 {
				margin: 0 0 10px 0;
				font-size: 24px;
			}
			.thank-you-message p {
				margin: 0;
				font-size: 16px;
			}
		</style>

		<?php if ( isset( $_GET['success'] ) && $_GET['success'] == '1' ) : ?>
			<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
				Thank you for your feedback! We appreciate your input.
			</div>
		<?php endif; ?>

		<div class="delight-lead-form" id="delightLeadForm">
			<h2>The Delightful Pantry — Tasting Feedback</h2>
			<p>Thank you for trying our Botanical Syrups. Your feedback helps shape our very first batch release.</p>
			
			<form method="post" action="" id="feedbackForm">
				<?php wp_nonce_field( 'delight_lead_form_submit', 'delight_lead_nonce' ); ?>
				
				<div class="form-group">
					<label>1. Which syrup did you try today?</label>
					<div>
						<input type="radio" name="syrup_tried" value="Cinnamon & Molasses" required> Cinnamon & Molasses<br>
						<input type="radio" name="syrup_tried" value="Cinnamon & Root"> Cinnamon & Root<br>
						<input type="radio" name="syrup_tried" value="Both"> Both
					</div>
				</div>

				<div class="form-group">
					<label>2. Which one did you prefer?</label>
					<div>
						<input type="radio" name="preferred_syrup" value="Cinnamon & Molasses" required> Cinnamon & Molasses<br>
						<input type="radio" name="preferred_syrup" value="Cinnamon & Root"> Cinnamon & Root<br>
						<input type="radio" name="preferred_syrup" value="I liked both equally"> I liked both equally<br>
						<input type="radio" name="preferred_syrup" value="Not for me"> Not for me
					</div>
				</div>

				<div class="form-group">
					<label>3. How would you describe the flavour? (Tick as many as you like)</label>
					<div class="checkbox-group">
						<label><input type="checkbox" name="flavor_descriptors[]" value="Warm"> Warm</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Earthy"> Earthy</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Aromatic"> Aromatic</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Rich"> Rich</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Smooth"> Smooth</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Bold"> Bold</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Comforting"> Comforting</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Unique"> Unique</label>
						<label><input type="checkbox" name="flavor_descriptors[]" value="Other"> Other:</label>
						<input type="text" name="other_flavor" placeholder="Please specify">
					</div>
				</div>

				<div class="form-group">
					<label>4. How would you use this syrup at home?</label>
					<div>
						<input type="radio" name="home_usage" value="Tea" required> Tea<br>
						<input type="radio" name="home_usage" value="Sparkling water"> Sparkling water<br>
						<input type="radio" name="home_usage" value="Coffee"> Coffee<br>
						<input type="radio" name="home_usage" value="Cocktails / mocktails"> Cocktails / mocktails<br>
						<input type="radio" name="home_usage" value="Porridge / desserts"> Porridge / desserts<br>
						<input type="radio" name="home_usage" value="Baking"> Baking<br>
						<input type="radio" name="home_usage" value="Other"> Other: <input type="text" name="other_usage" placeholder="Please specify">
					</div>
				</div>

				<div class="form-group">
					<label>5. Would you be interested in buying this when it launches?</label>
					<div>
						<input type="radio" name="buying_interest" value="Yes" required> Yes<br>
						<input type="radio" name="buying_interest" value="Maybe"> Maybe<br>
						<input type="radio" name="buying_interest" value="Not right now"> Not right now
					</div>
				</div>

				<div class="form-group">
					<label>6. What price range feels right for a 250ml bottle?</label>
					<div>
						<input type="radio" name="price_range" value="$12-$15" required> $12–$15<br>
						<input type="radio" name="price_range" value="$15-$18"> $15–$18<br>
						<input type="radio" name="price_range" value="$18-$22"> $18–$22<br>
						<input type="radio" name="price_range" value="$22+"> $22+<br>
						<input type="radio" name="price_range" value="Not sure"> Not sure
					</div>
				</div>

				<div class="form-group">
					<label>7. Any thoughts, ideas, or suggestions?</label>
					<textarea name="suggestions" rows="4" placeholder="Share your thoughts with us..."></textarea>
				</div>

				<div class="founding-tasters">
					<h3>🌿 Become a Founding Taster</h3>
					<p>Be part of our very first chapter. Join our Founding Tasters list to receive early access to our launch, first notice of limited-batch releases, invitations to future tasting events, and a special thank-you offer when we launch.</p>
					
					<div class="form-group">
						<label>8. Want to join our Founding Tasters list for early access?</label>
						<div>
							<input type="radio" name="join_founding_tasters" value="Yes" required onchange="toggleContactFields()"> Yes, add me<br>
							<input type="radio" name="join_founding_tasters" value="No" required onchange="toggleContactFields()"> No, thank you
						</div>
					</div>

					<div class="form-group" id="contactFields" style="display: none;">
						<label>Name:</label>
						<input type="text" name="taster_name" id="tasterName">
					</div>

					<div class="form-group" id="emailField" style="display: none;">
						<label>Email:</label>
						<input type="email" name="taster_email" id="tasterEmail">
					</div>
				</div>

				<button type="submit" name="delight_lead_submit" class="submit-btn">Submit Feedback</button>
			</form>
		</div>

		<script>
		// Function to toggle contact fields based on Founding Tasters selection
		function toggleContactFields() {
			const yesOption = document.querySelector('input[name="join_founding_tasters"][value="Yes"]');
			const contactFields = document.getElementById('contactFields');
			const emailField = document.getElementById('emailField');
			const nameInput = document.getElementById('tasterName');
			const emailInput = document.getElementById('tasterEmail');
			
			if (yesOption && yesOption.checked) {
				contactFields.style.display = 'block';
				emailField.style.display = 'block';
				nameInput.required = true;
				emailInput.required = true;
			} else {
				contactFields.style.display = 'none';
				emailField.style.display = 'none';
				nameInput.required = false;
				emailInput.required = false;
				nameInput.value = '';
				emailInput.value = '';
			}
		}

		document.getElementById('feedbackForm').addEventListener('submit', function(e) {
			e.preventDefault();
			
			// Validate form
			const joinOption = document.querySelector('input[name="join_founding_tasters"]:checked');
			if (joinOption && joinOption.value === 'Yes') {
				const nameInput = document.getElementById('tasterName');
				const emailInput = document.getElementById('tasterEmail');
				
				if (!nameInput.value.trim()) {
					alert('Please enter your name to join the Founding Tasters list.');
					nameInput.focus();
					return;
				}
				
				if (!emailInput.value.trim() || !emailInput.validity.valid) {
					alert('Please enter a valid email address to join the Founding Tasters list.');
					emailInput.focus();
					return;
				}
			}
			
			// Show loading state
			const submitBtn = document.querySelector('.submit-btn');
			const originalText = submitBtn.textContent;
			submitBtn.textContent = 'Submitting...';
			submitBtn.disabled = true;
			
			// Create form data
			const formData = new FormData(this);
			formData.append('action', 'delight_lead_submit');
			
			// Submit via AJAX
			fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Show confetti and thank you message
					showConfettiAndThankYou();
					
					// Clear form
					this.reset();
					// Reset contact fields visibility
					toggleContactFields();
				} else {
					// Show error message
					alert('Error: ' + (data.data || 'Unknown error occurred'));
				}
				
				// Reset button
				submitBtn.textContent = originalText;
				submitBtn.disabled = false;
			})
			.catch(error => {
				console.error('Error:', error);
				alert('An error occurred while submitting the form. Please try again.');
				submitBtn.textContent = originalText;
				submitBtn.disabled = false;
			});
		});

		function showConfettiAndThankYou() {
			// Hide the form
			const formContainer = document.getElementById('delightLeadForm');
			formContainer.style.display = 'none';
			
			// Create confetti container
			const confettiContainer = document.createElement('div');
			confettiContainer.className = 'confetti-container';
			
			// Create confetti pieces
			const colors = ['#2c5f2d', '#4a7c59', '#8fbc8f', '#ffd700', '#ff6b6b'];
			for (let i = 0; i < 50; i++) {
				const confetti = document.createElement('div');
				confetti.className = 'confetti';
				confetti.style.left = Math.random() * 100 + '%';
				confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
				confetti.style.animationDelay = Math.random() * 2 + 's';
				confetti.style.width = (Math.random() * 10 + 5) + 'px';
				confetti.style.height = (Math.random() * 10 + 5) + 'px';
				confettiContainer.appendChild(confetti);
			}
			
			// Create thank you message
			const thankYouMessage = document.createElement('div');
			thankYouMessage.className = 'thank-you-message';
			thankYouMessage.innerHTML = `
				<h3>🎉 Thank You!</h3>
				<p>Your feedback has been received and we truly appreciate your input.</p>
			`;
			
			// Add to page
			document.body.appendChild(confettiContainer);
			document.body.appendChild(thankYouMessage);
			
			// Define reset function globally
			window.resetForm = function() {
				confettiContainer.remove();
				thankYouMessage.remove();
				formContainer.style.display = 'block';
				// Reset form fields
				document.getElementById('feedbackForm').reset();
			};
		}
		</script>
		<?php
		return ob_get_clean();
	}

}
