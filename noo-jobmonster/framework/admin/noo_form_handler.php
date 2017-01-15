<?php
if( !class_exists('Noo_Form_Handler') ) :
class Noo_Form_Handler {
	public static $no_html = array();
	public static $allowed_html = array(
				'a' => array(
					'href' => array(),
					'target' => array(),
					'title' => array(),
					'rel' => array(),
					'class' => array(),
				),
				'img' => array(
					'src' => array(),
					'class' => array(),
				),
				'h1' => array(
					'class' => array(),
				),
				'h2' => array(
					'class' => array(),
				),
				'h3' => array(
					'class' => array(),
				),
				'h4' => array(
					'class' => array(),
				),
				'h5' => array(
					'class' => array(),
				),
				'p' => array(
					'class' => array(),
				),
				'br' => array(
					'class' => array(),
				),
				'hr' => array(
					'class' => array(),
				),
				'span' => array(
					'class' => array(),
				),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
				'small' => array(),
				'b' => array(),
				'i' => array(),
				'u' => array(),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
				'blockquote' => array(
					'class' => array(),
				),
				'iframe' => array(
					'src' => array(),
					'width' => array(),
				),
			);

	public static function init(){
		add_action( 'init', array( __CLASS__, 'lost_password_action' ) );
		add_action( 'init', array( __CLASS__, 'reset_password_action' ) );
		add_action( 'init', array( __CLASS__, 'edit_company_action' ) );
		add_action( 'init', array( __CLASS__, 'edit_candidate_profile_action' ) );
		add_action( 'init', array( __CLASS__, 'edit_job_action' ) );
		add_action( 'init', array( __CLASS__, 'edit_resume_action' ) );
		add_action( 'init', array( __CLASS__, 'edit_job_alert_action' ) );
		add_action( 'init', array( __CLASS__, 'delete_job_alert_action' ) );
		add_action( 'init', array( __CLASS__, 'manage_job_applied_action' ) );
		
		// page step post job action
		// add_action( 'init', array( __CLASS__, 'post_job_action' ) );
		// add_action( 'init', array( __CLASS__, 'preview_job_action' ) );
		
		add_action('wp_ajax_add_new_job_location', array(__CLASS__,'add_new_job_location_action'));
		
		add_action( 'init', array( __CLASS__, 'manage_job_action' ) );
		add_action( 'init', array( __CLASS__, 'manage_application_action' ) );
		
		add_action( 'init', array( __CLASS__, 'apply_job_action' ) );
		add_action( 'init', array( __CLASS__, 'apply_job_via_linkedin_action' ) );
		add_action( 'init', array( __CLASS__, 'manage_resume_action' ) );
		add_action( 'init', array( __CLASS__, 'delete_bookmark_action' ) );

//		add_action( 'init', array( __CLASS__, 'company_send_contact_action' ) );

		add_action( 'wp_ajax_nopriv_noo_ajax_send_contact', array(__CLASS__, 'ajax_send_contact') );
		add_action( 'wp_ajax_noo_ajax_send_contact', array(__CLASS__, 'ajax_send_contact') );
		
		add_action( 'wp_ajax_noo_approve_reject_application_modal', array(__CLASS__, 'approve_reject_application_modal') );
		//add_action( 'wp_ajax_noo_approve_reject_application_action', array(__CLASS__, 'approve_reject_application_action') );
		add_action( 'wp_ajax_noo_employer_message_application_modal', array(__CLASS__, 'employer_message_application_modal') );
		
		add_action( 'wp_ajax_nopriv_noo_ajax_login', array(__CLASS__, 'ajax_login') );
		add_action( 'wp_ajax_noo_ajax_login', array(__CLASS__, 'ajax_login_priv') );
		add_action( 'wp_ajax_nopriv_noo_ajax_register', array(__CLASS__, 'ajax_register') );
		add_action( 'wp_ajax_noo_ajax_register', array(__CLASS__, 'ajax_register') );
		
		
		add_action( 'wp_ajax_nopriv_noo_bookmark_job', array(__CLASS__, 'ajax_bookmark_job') );
		add_action( 'wp_ajax_noo_bookmark_job', array(__CLASS__, 'ajax_bookmark_job') );
		// add_action( 'wp_ajax_nopriv_noo_update_password', array(__CLASS__, 'ajax_update_password') );
		add_action( 'wp_ajax_noo_update_password', array(__CLASS__, 'ajax_update_password') );
		add_action( 'wp_ajax_noo_update_email', array(__CLASS__, 'ajax_update_email') );
	}
	
	public static function add_new_job_location_action(){
		if(!is_user_logged_in())
			$result['success'] = false;
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			$result['success'] = false;
		
		if(!is_user_logged_in()){
			$result['success'] = false;
		}
		
		check_ajax_referer('noo-member-security','security');
		$new_location = isset($_POST['location']) ?  trim(stripslashes($_POST['location'])) : '';
		if(!empty($new_location)){
			$result = array();
			if ( ($t = get_term_by( 'slug', sanitize_title( $new_location ), 'job_location' ) )) {
				$result['success'] = true;
				$result['location_id'] = $t->term_id;
				$result['location_slug'] = $t->slug;
				$result['location_value'] = $t->slug;
				$result['location_title'] = $t->name;
			}else{
				$n_l = wp_insert_term( $new_location, 'job_location' );
				if ($n_l && !is_wp_error($n_l) && ($loca = get_term( absint($n_l['term_id']), 'job_location' ))) {
					$result['success'] = true;
					$result['location_id'] = $loca->term_id;
					$result['location_slug'] = $loca->slug;
					$result['location_value'] = $loca->slug;
					$result['location_title'] = $loca->name;
				}
			}
		}
		wp_send_json($result);
	}
	

	public static function lost_password_action(){
		global $wpdb, $wp_hasher;
	
		if(is_user_logged_in()) {
			return;
		}
	
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
	
		if ( empty( $_POST[ 'action' ] ) || 'lost_password' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'lost-password' ) )
			return;
	
		if ( empty( $_POST['user_login'] ) ) {
			noo_message_add( __( 'Enter a username or e-mail address.', 'noo' ), 'error' );
			return false;
	
		} else {
			// Check on username first, as customers can use emails as usernames.
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}
	
		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $_POST['user_login'] ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		}
	
		do_action( 'lostpassword_post' );
	
		if ( ! $user_data ) {
			noo_message_add( __( 'Invalid username or e-mail.', 'noo' ), 'error' );
			return false;
		}
	
		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			noo_message_add( __( 'Invalid username or e-mail.', 'noo' ), 'error' );
			return false;
		}
	
		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
	
		do_action( 'retrieve_password', $user_login );
	
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
	
		if ( ! $allow ) {
	
			noo_message_add( __( 'Password reset is not allowed for this user', 'noo' ), 'error' );
	
			return false;
	
		} elseif ( is_wp_error( $allow ) ) {
	
			noo_message_add( $allow->get_error_message(), 'error' );
	
			return false;
		}
	
		$key = wp_generate_password( 20, false );
	
		do_action( 'retrieve_password_key', $user_login, $key );
	
		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
	
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
	
		// Send email notification
		$message = __('Someone requested that the password be reset for the following account:', 'noo') . '<br/><br/>';
		$message .= sprintf(__('Username: %s', 'noo'), $user_login) . '<br/><br/>';
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'noo') . '<br/><br/>';
		$message .= __('To reset your password, visit the following address:', 'noo') . "<br/><br/>";
		// $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
		$reset_link = Noo_Member::resetpassword_url();
		$reset_link = esc_url_raw( add_query_arg( array( 'key' => $key, 'login' => rawurlencode($user_login) ), $reset_link ) );
		$message .= '<a href="' .$reset_link . '" >'.$reset_link.'</a><br/>';
	
		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option we want to reverse this for the plain text arena of emails.
		 */
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
		$title = sprintf( __('[%s] Password Reset', 'noo'), $blogname );
	
		/**
		 * Filter the subject of the password reset email.
		 *
		 * @since 2.8.0
		 *
		 * @param string $title Default email title.
		*/
		$title = apply_filters( 'retrieve_password_title', $title );
		/**
		 * Filter the message body of the password reset mail.
		 *
		 * @since 2.8.0
		 *
		 * @param string $message Default mail message.
		 * @param string $key     The activation key.
		*/
		$message = apply_filters( 'retrieve_password_message', $message, $key );
	
		if ( $message && !noo_mail( $user_email, wp_specialchars_decode( $title ), $message, '', 'noo_user_password_reset' ) ) {
			noo_message_add( __( 'The e-mail could not be sent', 'noo' ), 'error' );
		} else {
			noo_message_add( __( 'Check your e-mail for the confirmation link.', 'noo' ) );
		}
		return true;
	}
	
	public static function reset_password_action(){
		global $wpdb, $wp_hasher;
	
		if(is_user_logged_in()) {
			return;
		}
	
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
	
		if ( empty( $_POST[ 'action' ] ) || 'reset_password' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'reset-password' ) )
			return;
	
		$rp_key = isset( $_POST['rp_key'] ) ? $_POST['rp_key'] : '';
    	$rp_login = isset( $_POST['rp_login'] ) ? $_POST['rp_login'] : '';
    	$password = isset( $_POST['password'] ) ? $_POST['password'] : '';

    	if ( empty( $rp_key ) || empty( $rp_login ) ) {
			noo_message_add(__('Missing key or username.','noo'), 'error');
			wp_redirect( wp_lostpassword_url() );
			exit;
		}

    	if ( empty( $password ) ) {
			noo_message_add(__('You must enter the new password.','noo'), 'error');
			wp_redirect( wp_lostpassword_url() );
			exit;
		}

		$user = check_password_reset_key( $rp_key, $rp_login );
		if ( ! $user || is_wp_error( $user ) ) {
			if ( $user && $user->get_error_code() === 'expired_key' ) {
				noo_message_add(__('Your reset key is expired.','noo'), 'error');
				wp_redirect( wp_lostpassword_url() );
			} else {
				noo_message_add(__('Invalid reset link.','noo'), 'error');
				wp_redirect( wp_lostpassword_url() );
			}
			exit;
		}

		$errors = new WP_Error();

		/**
		 * Fires before the password reset procedure is validated.
		 *
		 * @since 3.5.0
		 *
		 * @param object           $errors WP Error object.
		 * @param WP_User|WP_Error $user   WP_User object if the login and reset key match. WP_Error object otherwise.
		 */
		do_action( 'validate_password_reset', $errors, $user );

		if ( ( ! $errors->get_error_code() ) && isset( $_POST['password'] ) && !empty( $_POST['password'] ) ) {
			reset_password($user, $_POST['password']);

			noo_message_add(__('Your password is reset.','noo'));
			wp_safe_redirect( Noo_Member::get_login_url() );
		} else {
			noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.','noo'));
		}
		
		exit;
	}
	
	public static function apply_job_via_linkedin_action(){
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;

		if ( empty( $_POST[ 'action' ] ) || 'apply_job_via_linkedin' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-apply-job-via-linkedin' ) ) {
			return;
		}
		
		try{
			$cover_letter = isset( $_POST['linkedin-cover-letter'] ) ? wp_kses_post( stripslashes( $_POST['linkedin-cover-letter'] ) ) : '';
			$profile_data = isset($_POST['in-profile-data']) ? json_decode( stripslashes( $_POST['in-profile-data'] ) ) : '';
			
			$job_id              = absint( $_POST['job_id'] );
			$job                 = get_post( $job_id );
			
			if ( empty( $job_id ) || ! $job || 'noo_job' !== $job->post_type ) {
				throw new Exception( __( 'Invalid job', 'noo' ) );
			}

			$meta = array();
			if( isset( $_POST['_attachment'] ) && !empty( $_POST['_attachment'] ) ) {
				$meta['_attachment'] = esc_url($_POST['_attachment']);
			}
			$application_id = Noo_Application::new_job_application(
				$job_id,
				$profile_data->formattedName,
				$profile_data->emailAddress,
				$cover_letter,
				$meta
			);
			do_action( 'new_job_application', $application_id );
			do_action( 'new_job_apply_via_linkedin', $job_id, $profile_data, $cover_letter );
			if ( ! $application_id ) {
				noo_message_add( __( 'There\'s an unknown error. Please retry or contact Administrator.', 'noo' ),'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}
			noo_message_add(__('Your job application has been submitted successfully','noo'));
			wp_safe_redirect(get_permalink($job_id));
			exit();
		}catch (Exception $e){
			noo_message_add($e->getMessage(),'error');
		}
	}
	
	public static function apply_job_action(){
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'apply_job' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-apply-job' ) ) {
			return;
		}
		
		try {
			// Get data from the form
			$candidate_name      = sanitize_text_field( $_POST['candidate_name'] );
			$candidate_email     = sanitize_text_field( $_POST['candidate_email'] );
			$job_id              = absint( $_POST['job_id'] );
			$job                 = get_post( $job_id );
			if ( empty( $job_id ) || ! $job || 'noo_job' !== $job->post_type ) {
				noo_message_add( __( 'Invalid job', 'noo' ),'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}
			do_action( 'new_job_application_before', $job_id );
			
			$employer = get_userdata($job->author);
			
			$captcha_code = isset( $_POST['security_code'] ) ? strtolower( $_POST['security_code'] ) : '';
			$captcha_input = isset( $_POST['noo_captcha'] ) ? strtolower( $_POST['noo_captcha'] ) : '';
			if ( $captcha_input !== $captcha_code ) {
				noo_message_add( __( 'Invalid confirmation code, please enter your code again.', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			if ( isset( $_POST['g-recaptcha-response'] ) && !noo_recaptcha_verification() ) {
				noo_message_add( __( 'Please complete the Recaptcha challenge.', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			if ( empty( $candidate_name ) ) {
				noo_message_add( __( 'Please enter your name', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}
			if ( empty( $candidate_email ) || ! is_email( $candidate_email ) ) {
				noo_message_add( __( 'Please provide a valid email address', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}
			
			$application_args = array(
				'post_type'=>'noo_application',
				'posts_per_page' => -1,
				'post_status'=>array('publish','pending','rejected'),
				'post_parent'=>$job_id,
				'meta_query'=>array(
					array(
						'key' => '_candidate_email',
						'value' => $candidate_email,
					),
				)
			);
			$application = new WP_Query($application_args);
			if ( $application->post_count ) {
				noo_message_add( __( 'You have already applied for this job', 'noo' ), 'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			$meta 				= array();
			$application_attachment = jm_get_application_setting( 'application_attachment', 'enabled' ) == 'enabled';
			$allow_multiple_attachment = jm_get_application_setting( 'allow_multiple_attachment','') == 'yes';
			$application_resume = jm_get_application_setting( 'application_resume', 'enabled' ) == 'enabled' && jm_resume_enabled();
			$require_attachment = jm_get_application_setting( 'require_attachment', 'yes') == 'yes';

			$meta['_attachment'] = '';
			if( $application_attachment && isset($_FILES['application_attachment']) ) {
				$meta['_attachment'] = self::upload_file( 'application_attachment', jm_get_allowed_attach_file_types(), $allow_multiple_attachment );
			}
			$meta['_attachment'] = apply_filters( 'noo_application_attachment', $meta['_attachment'], $job_id, $candidate_email );

			$meta['_resume'] = '';
			if( $application_resume && isset($_POST['resume']) ) {
				$resume_id = absint($_POST['resume']);
				if(!empty( $resume_id ) && 'noo_resume' === get_post_type( $resume_id ) ){
					$meta['_resume'] = $resume_id;
				}
			}
			$meta['_resume'] = apply_filters( 'noo_application_resume', $meta['_resume'], $job_id, $candidate_email );

			if( $require_attachment && empty($meta['_attachment']) && empty($meta['_resume']) ){
				noo_message_add( __( 'Please upload CV file or select a resume', 'noo' ), 'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			$fields = jm_get_application_custom_fields();

			$application_message = '';
			if( isset( $_POST['application_message'] ) ) {
				$application_message = wp_kses( $_POST['application_message'], self::$allowed_html );
				if( isset( $fields['application_message'] ) )
					unset( $field['application_message'] );
			}
			
			if( !empty( $fields ) ) {
				foreach ($fields as $field) {
					$field_id = jm_application_custom_fields_name( $field['name'], $field );

					if( isset( $_POST[$field_id] ) ) {
						$meta[$field_id] = noo_sanitize_field( $_POST[$field_id], $field );
					}
				}
			}
			
			if ( !$application_id = Noo_Application::new_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $meta ) ) {
				noo_message_add( __( 'Could not add a new job application', 'noo' ),'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			do_action( 'new_job_application', $application_id );
			noo_message_add(__('Your job application has been submitted successfully', 'noo'));
			wp_safe_redirect(get_permalink($job_id));
			exit();
		}catch (Exception $e){
			noo_message_add($e->getMessage(),'error');
			wp_safe_redirect(get_permalink($job_id));
			exit();
		}
		return;
	}

	public  static  function company_send_contact_action(){
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;

		if ( empty( $_POST[ 'action' ] ) || 'company_send_contact' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-company_send_contact' ) ) {
			return;
		}

		try {
			// Get data from the form
			$from_name      = sanitize_text_field( $_POST['from_name'] );
			$from_email     = sanitize_text_field( $_POST['from_email'] );
			$from_message     = sanitize_text_field( $_POST['from_message'] );
			$company_email     = sanitize_email( $_POST['to_email'] );

			$job_id              = absint( $_POST['job_id'] );

			if ( isset( $_POST['g-recaptcha-response'] ) && !noo_recaptcha_verification() ) {
				noo_message_add( __( 'Please complete the Recaptcha challenge.', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			$captcha_code = isset( $_POST['security_code'] ) ? strtolower( $_POST['security_code'] ) : '';
			$captcha_input = isset( $_POST['noo_captcha'] ) ? strtolower( $_POST['noo_captcha'] ) : '';
			if ( $captcha_input !== $captcha_code ) {
				noo_message_add( __( 'Invalid confirmation code, please enter your code again.', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			if ( empty( $from_name ) ) {
				noo_message_add( __( 'Please enter your name', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			if ( empty( $from_email ) || ! is_email( $from_email ) ) {
				noo_message_add( __( 'Please provide a valid email address', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			$from_message = str_replace( '[nl]', "\n", sanitize_text_field( str_replace( "\n", '[nl]', strip_tags( stripslashes( $from_message ) ) ) ) );
			if ( empty( $from_message )) {
				noo_message_add( __( 'Please write your application message', 'noo' ) ,'error');
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

			$subject = sprintf( __( 'New message from %s', 'noo' ),  get_bloginfo( 'name' ) );
			$message = '';
			$message .= sprintf(__('You get a contact from %s', 'noo'), $from_name) . '<br/><br/>';
			$message .= sprintf(__('From email %s', 'noo'), $from_email) . '<br/><br/>';
			$message .= sprintf(__('Message :', 'noo')) . '<br/><br/>';
			$message .= $from_message . '<br/><br/>';
			$result = noo_mail($company_email, $subject, $message, array(), 'noo_send_contact_company');
			if( $result ) {
				noo_message_add( __( 'Contact sent success.', 'noo' ));
				wp_safe_redirect(get_permalink($job_id));
				exit();
			}

		}catch (Exception $e){
			noo_message_add($e->getMessage(),'error');
			wp_safe_redirect(get_permalink($job_id));
			exit();
		}
		return;
	}

	public static  function ajax_send_contact(){

		$result['success'] = true;

		if( !check_ajax_referer('noo-ajax-send-contact', 'security', false) ) {
			$result['success'] = false;
			$result['message'] = '<span class="error-response">'.__( 'Your session has expired or you have submitted an invalid form.', 'noo' ).'</span>';
			wp_send_json($result);
		} else{
			$from_name      = sanitize_text_field( $_POST['from_name'] );
			$from_email     = sanitize_text_field( $_POST['from_email'] );
			$from_message = str_replace( '[nl]', "\n", sanitize_text_field( str_replace( "\n", '[nl]', strip_tags( stripslashes($_POST['from_message'] ) ) ) ) );
			$company_email     = sanitize_email( $_POST['to_email'] );

			if ( isset( $_POST['g-recaptcha-response'] ) && !noo_recaptcha_verification() ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Please complete the Recaptcha challenge', 'noo' ).'</span>';
				wp_send_json($result);
			}

			$captcha_code = isset( $_POST['security_code'] ) ? strtolower( $_POST['security_code'] ) : '';
			$captcha_input = isset( $_POST['noo_captcha'] ) ? strtolower( $_POST['noo_captcha'] ) : '';
			if ( $captcha_input !== $captcha_code ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Invalid confirmation code, please enter your code again.', 'noo' ).'</span>';
				wp_send_json($result);
			}

			if ( empty( $from_name ) ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Please enter your name', 'noo' ).'</span>';
				wp_send_json($result);
			}

			if ( empty( $from_email ) || ! is_email( $from_email ) ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Please provide a valid email address', 'noo' ).'</span>';
				wp_send_json($result);
			}

			if ( empty( $from_message )) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Please write your application message', 'noo' ).'</span>';
				wp_send_json($result);
			}

			if($result['success']){
				$subject = sprintf( __( 'New message from %s', 'noo' ),  get_bloginfo( 'name' ) );
				$message = '';
				$message .= sprintf(__('You get a contact from %s', 'noo'), $from_name) . '<br/><br/>';
				$message .= sprintf(__('From email: %s', 'noo'), $from_email) . '<br/><br/>';
				$message .= sprintf(__('Message :', 'noo')) . '<br/><br/>';
				$message .= $from_message . '<br/><br/>';
				$result_send = noo_mail($company_email, $subject, $message, array(), 'noo_send_contact_company');

				if ( $result_send) {
					$result['success'] = true;
					$result['message'] = '<span class="success-response">'.__( 'Contact sent success.', 'noo' ).'</span>';
					wp_send_json($result);
				}
			}

		}
	}
	
	public static function upload_file( $field_key, $allowed_file_types = array(), $is_multiple = false ) {
		if ( isset( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ]['name'] ) ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			include_once( ABSPATH . 'wp-admin/includes/media.php' );
				
			$file               = $_FILES[ $field_key ];
			$all_mime_types		= get_allowed_mime_types();
			$allowed_mime_types = array();

			if( !empty( $allowed_file_types ) ) {
				foreach ($allowed_file_types as $type) {
					foreach ($all_mime_types as $key => $value) {
						if( $type == $key || in_array($type, explode('|', $key) ) ) {
							$allowed_mime_types[$type] = $all_mime_types[$key];
						}
					}
				}
			} else {
				$allowed_mime_types = $all_mime_types;
			}

			if( $is_multiple && is_array( $file['name'] ) ) {
				$results = array();
				foreach ($file['name'] as $index => $name) {
					if( empty( $name ) ) continue;
					$a_file = array(
						'name' => $name,
						'type' => $file['type'][$index],
						'tmp_name' => $file['tmp_name'][$index],
						'error' => $file['error'][$index],
						'size' => $file['size'][$index],
					);

					$result = self::_process_a_file( $a_file, $allowed_mime_types );
					if( !empty( $result ) ) {
						$results[] = $result;
					}
				}

				return ( empty( $results ) ? false : $results );
			} else {
				if( !empty( $file['name'] ) ) {
					return self::_process_a_file( $file, $allowed_mime_types );
				}
			}
		}

		return false;
	}

	private static function _process_a_file( $file, $allowed_mime_types = array() ) {
		if ( ! in_array( $file["type"], $allowed_mime_types ) ) {
			throw new Exception( sprintf( __( 'Only the following file types are allowed: %s', 'noo'), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		}

		add_filter( 'upload_dir',  array( __CLASS__, 'upload_dir' ) );
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		remove_filter('upload_dir', array( __CLASS__, 'upload_dir' ) );

		if ( ! empty( $upload['error'] ) ) {
			return false;
		} else {
			return $upload['url'];
		}
	}
	
	public static function upload_dir( $pathdata ) {
		$subdir             = '/jobmonster/' . uniqid();
		$pathdata['path']   = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
		$pathdata['url']    = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );
		return $pathdata;
	}
	
	public static function edit_company_action(){
		if(!is_user_logged_in())
			return;
		else $user_ID = get_current_user_id(); 
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) 
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'edit_company' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'edit-company' ) ) {
			return;
		}
		
		$company_id = self::save_company( $_POST, $user_ID);
		if( $company_id ) {
			noo_message_add(__('Company updated','noo'));
		}
		wp_safe_redirect(Noo_Member::get_company_profile_url());
		die;
		
	}
	
	public static function post_job_action(){
		if(!Noo_Member::is_logged_in()){
			wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'job_package')) . '#jform');
			return;
		}
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if(!isset($_POST['page_id']))
			return;
		
		$page_temp = get_page_template_slug($_POST['page_id']);
		
		if('page-post-job.php' !== $page_temp)
			return;
		else
			unset($_POST['page_id']); // unset to prevent strange behaviour of insert new page.
		
		if ( empty( $_POST[ 'action' ] ) || 'post_job' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-post-job' ) ) {
			return;
		}
		
		$_POST['post_status'] = 'draft'; // Job post is alway draft. Need to be reviewed first.

		$job_id = self::save_job($_POST);

		if(is_wp_error($job_id)){
			noo_message_add(__('You can not post job','noo'),'error');
			wp_safe_redirect(Noo_Member::get_member_page_url());
			exit;
		}else{
			$location = array('action'=>'preview_job','job_id'=>$job_id);
			wp_safe_redirect(esc_url_raw(add_query_arg($location)) . '#jform');
			exit;
		}
		
	}
	
	public static function preview_job_action(){
		
		if(!Noo_Member::is_logged_in()){
			wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'job_package')));
			return;
		}
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if(!isset($_POST['page_id']))
			return;
			
		$page_temp = get_page_template_slug($_POST['page_id']);
		
		if('page-post-job.php' !== $page_temp)
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'preview_job' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-post-job' ) ) {
			return;
		}
		if(empty($_POST['job_id']))
			return;

		$submit_agreement = jm_get_job_setting( 'submit_agreement', null);
		if( is_null( $submit_agreement ) || !empty( $submit_agreement ) ) {
			if( !isset( $_POST['agreement'] ) || empty( $_POST['agreement'] ) ) {
				noo_message_add(__('You must agree with our condition.','noo'),'error');
				$location = array('action'=>'preview_job','job_id'=>$_POST['job_id']);
				wp_safe_redirect(esc_url_raw(add_query_arg($location)));
				exit;
			}
		}
		
		$job_id = absint($_POST['job_id']);
		$job_need_approve = jm_get_job_setting( 'job_approve','' ) == 'yes';

		if(jm_is_woo_job_posting()){
			if(jm_get_job_posting_remain() > 0){
				jm_increase_job_posting_count(get_current_user_id());
				if( !$job_need_approve ) {
					wp_update_post(array(
						'ID'=>$job_id,
						'post_status'=>'publish',
					));
					jm_set_job_expired( $job_id );
				} else {
					wp_update_post(array(
						'ID'=>$job_id,
						'post_status'=>'pending',
					));
					update_post_meta($job_id, '_in_review', 1);
				}
				noo_message_add(__('Job successfully added','noo'));
				Noo_Job::send_notification($job_id);
				wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
				exit;
			}else{
				global $woocommerce;

				wp_update_post(array(
					'ID'=>$job_id,
					'post_status'=>'pending_payment',
				));
				// update_post_meta($job_id, '_waiting_payment', 1);

				if(isset($_POST['package_id'])){
					jm_increase_job_posting_count(get_current_user_id());

					$job_package = wc_get_product( absint($_POST['package_id']) );
					$quantity = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
					$passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $job_package->id, $quantity );
					if($job_package->is_type( 'job_package' ) && $passed_validation){
						// Add the product to the cart
						$woocommerce->cart->empty_cart();
						if($woocommerce->cart->add_to_cart( $job_package->id, $quantity,'','',array('_job_id'=>$job_id))){
							//woocommerce_add_to_cart_message( $job_package->id );
							wp_safe_redirect($woocommerce->cart->get_checkout_url());
							die;
						}

					}
				} else {
					wp_update_post(array(
						'ID'=>$job_id,
						'post_status'=>'trashed',
					));
				}
			}
		}else{
			jm_increase_job_posting_count(get_current_user_id());

			if( !$job_need_approve ) {
				wp_update_post(array(
					'ID'=>$job_id,
					'post_status'=>'publish',
				));
				jm_set_job_expired( $job_id );
			} else {
				wp_update_post(array(
					'ID'=>$job_id,
					'post_status'=>'pending',
				));
				update_post_meta($job_id, '_in_review', 1);
			}
			noo_message_add(__('Job successfully added','noo'));
			Noo_Job::send_notification($job_id);
			wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
			exit;
		}
	}
	
	public static function edit_job_action(){
		if(!is_user_logged_in())
			return;
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'edit_job' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'edit-job' ) ) {
			return;
		}
		unset( $_POST['post_status'] );
		$job_id = self::save_job($_POST);
		if(is_wp_error($job_id)){
			noo_message_add(__('There\'s a problem when you editing this Job, please try again or contact the Administrator','noo'),'error');
			wp_safe_redirect(Noo_Member::get_member_page_url());
		}else{
			if(isset($_POST['is_edit'])){
				noo_message_add(__('Job updated','noo'));
			}else{
				noo_message_add(__('Job saved','noo'));
			}
			wp_safe_redirect(Noo_Member::get_edit_job_url($job_id));
		}
		exit();
	}
	
	public static function approve_reject_application_modal(){
		
		if(!is_user_logged_in()){
			die(-1);
		}
		
		check_ajax_referer('noo-member-security','security');
		
		$application_id = isset($_POST['application_id']) ? esc_html($_POST['application_id']) : 0;
		$hander = isset($_POST['hander']) ? $_POST['hander'] : '';
		ob_start();
		Noo_Member::modal_application($application_id,$hander);
		$output = ob_get_clean();
		if(empty($output))
			die(-1);
		else 
			echo trim($output);
		die();
	}
	
	public static function manage_application_action(){
		if(!is_user_logged_in())
			return;
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'application-manage-action' ) ) {
			if ( isset( $_REQUEST['application_id'] ) ) {
				$ids = explode( ',', $_REQUEST['application_id'] );
			} elseif ( !empty( $_REQUEST['ids'] ) ) {
				$ids = array_map('intval', $_REQUEST['ids']);
			}
			$msg_title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : '';
			$msg_body = isset($_REQUEST['message']) ? wp_kses_post( trim( stripslashes( $_REQUEST['message'] ) ) ) : '';
			$employer = wp_get_current_user();

			if ( is_multisite() )
				$blogname = $GLOBALS['current_site']->site_name;
			else
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

			try {
				switch ($action){
					case 'approve':
						$approved = 0;
						foreach ((array) $ids as $application_id) {
							if( !Noo_Application::can_edit_application( get_current_user_id(), $application_id ) ) {
								continue;
							}
							$application = get_post( $application_id );
							if( !$application || $application->post_status != 'pending' ) {
								continue;
							}
							$job_id = $application->post_parent;
							$company_id = jm_get_job_company( $job_id );
							$company_title = !empty( $company_id ) ? get_the_title( $company_id ) : $employer->display_name;

							if(!wp_update_post(array(
								'ID'=>$application_id,
								'post_status'=>'publish',
							))){
								wp_die( __('Error when approving application.','noo') );
							}
							update_post_meta($application_id, '_employer_message_title', $msg_title);
							update_post_meta($application_id, '_employer_message_body', $msg_body);

							do_action('manage_application_action_approve',$application_id);

							$to = noo_get_post_meta($application_id,'_candidate_email');
							$candidate_name = get_the_title($application_id);
							if(is_email($to)){
								//candidate email

								$subject = jm_et_get_setting('candidate_approved_subject');

								$array_subject = array(
									'[job_title]' => get_the_title($job_id),
									'[job_company]' => $company_title,
									'[responded_title]' => $msg_title,
									'[site_name]' => $blogname,
									'[site_url]' => esc_url(home_url('')),
								);

								$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

								$array_message = array(
									'[job_title]' => get_the_title($job_id),
									'[job_url]' => get_permalink($job_id),
									'[job_company]' => $company_title,
									'[candidate_name]' => $candidate_name,
									'[responded_title]' => $msg_title,
									'[responded]' => $msg_body,
									'[application_manage_url]' => Noo_Member::get_endpoint_url('manage-job-applied'),
									'[site_name]' => $blogname,
									'[site_url]' => esc_url(home_url('')),
								);

								$message = jm_et_get_setting('candidate_approved_content');
								$message = str_replace(array_keys($array_message), $array_message, $message);

								$subject = jm_et_custom_field('job',$job_id, $subject);
								$message = jm_et_custom_field('job',$job_id, $message);

								noo_mail($to, $subject, $message,array(),'noo_notify_job_apply_approve_candidate');
							}
							$approved ++;
						}
						if( $approved > 0 ) {
							noo_message_add( sprintf( _n( 'Approved %s application', 'Approved %s applications', $approved, 'noo' ), $approved ) );
						} else {
							noo_message_add( __( 'No application approved', 'noo' ) );
						}
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-application') );
						die;
						break;
					break;
					case 'reject':
						$rejected = 0;
						foreach ((array) $ids as $application_id){
							if( !Noo_Application::can_edit_application( get_current_user_id(), $application_id ) ) {
								continue;
							}

							$application = get_post( $application_id );
							if( !$application || $application->post_status != 'pending' ) {
								continue;
							}
							$job_id = $application->post_parent;
							$company_id = jm_get_job_company( $job_id );
							$company_title = !empty( $company_id ) ? get_the_title( $company_id ) : $employer->display_name;
							if(!wp_update_post(array(
								'ID'=>$application_id,
								'post_status'=>'rejected',
							))){
								wp_die( __('Error when rejecting application.','noo') );
							}
							update_post_meta($application_id, '_employer_message_title', $msg_title);
							update_post_meta($application_id, '_employer_message_body', $msg_body);
						
							do_action('manage_application_action_reject',$application_id);

							$to = noo_get_post_meta($application_id,'_candidate_email');
							$candidate_name = get_the_title($application_id);
							if(is_email($to)){
								//candidate email
								$subject = jm_et_get_setting('candidate_rejected_subject');

								$array_subject = array(
									'[job_title]' => get_the_title($job_id),
									'[job_company]' => $company_title,
									'[responded_title]' => $msg_title,
									'[site_name]' => $blogname,
									'[site_url]' => esc_url(home_url('')),
								);

								$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

								$array_message = array(
									'[job_title]' => get_the_title($job_id),
									'[job_url]' => get_permalink($job_id),
									'[job_company]' => $company_title,
									'[candidate_name]' => $candidate_name,
									'[responded_title]' => $msg_title,
									'[responded]' => $msg_body,
									'[application_manage_url]' => Noo_Member::get_endpoint_url('manage-job-applied'),
									'[site_name]' => $blogname,
									'[site_url]' => esc_url(home_url('')),
								);

								$message = jm_et_get_setting('candidate_rejected_content');
								$message = str_replace(array_keys($array_message), $array_message, $message);

								$subject = jm_et_custom_field('job',$job_id, $subject);
								$message = jm_et_custom_field('job',$job_id, $message);

								noo_mail($to, $subject, $message,array(),'noo_notify_job_apply_reject_candidate');
							}
							$rejected++;
						}
						if( $rejected > 0 ) {
							noo_message_add( sprintf( _n( 'Rejected %s application', 'Rejected %s applications', $rejected, 'noo' ), $rejected ) );
						} else {
							noo_message_add( __( 'No application rejected', 'noo' ) );
						}
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-application') );
						die;
						break;
					break;
					case 'delete':
						$deleted = 0;
						foreach ((array) $ids as $application_id){
							if( !Noo_Application::can_trash_application( get_current_user_id(), $application_id ) ) {
								continue;
							}
							
							// if ( !wp_delete_post($application_id) )
							// Version 2.7.0 Making application inactive instead of move to trash.
							if ( !wp_update_post(array( 'ID' => $application_id, 'post_status' => 'inactive' )) ) {
								wp_die( __('Error when deleting application.','noo') );
							}
							
							$deleted++;
						}
						if( $deleted > 0 ) {
							noo_message_add( sprintf( _n( 'Deleted %s application', 'Deleted %s applications', $deleted, 'noo' ), $deleted ) );
						} else {
							noo_message_add( __( 'No application deleted', 'noo' ) );
						}
						do_action('manage_application_action_delete',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-application') );
						die;
					break;
					default:
					break;
				}
			}catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		}
	}
	
	public static function manage_job_action(){
		if(!is_user_logged_in())
			return;

		$employer_id = get_current_user_id();
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'job-manage-action' ) ) {
			if ( isset( $_REQUEST['job_id'] ) ) {
				$ids = explode( ',', $_REQUEST['job_id'] );
			} elseif ( !empty( $_REQUEST['ids'] ) ) {
				$ids = array_map('intval', $_REQUEST['ids']);
			}
			try {
				switch ($action){
					case 'publish':
						$published = 0;
						foreach ((array) $ids as $job_id){
							$job = get_post($job_id);
							if ( $job->post_type !== 'noo_job' )
								return;
							if( !Noo_Member::can_change_job_state( $job_id, $employer_id ) ){
								continue;
							}
							if ( $job->post_author != $employer_id &&  !current_user_can( 'edit_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
							}
							if(!wp_update_post(array(
								'ID'=>$job_id,
								'post_status'=>'publish',
							))){
								wp_die( __('There was an error publishing this job.','noo') );
							}
							jm_set_job_expired( $job_id );
							$published++;
						}
						if( $published > 0 ) {
							noo_message_add( sprintf( _n( 'Published %s job', 'Published %s jobs', $published, 'noo' ), $published ) );
						} else {
							noo_message_add( __( 'No job published', 'noo' ) );
						}
						do_action('manage_job_action_publish',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
						die;
						break;
					case 'unpublish':
						$unpublished = 0;
						foreach ((array) $ids as $job_id){
							$job = get_post($job_id);
							if ( $job->post_type !== 'noo_job' )
								return;
							if( !Noo_Member::can_change_job_state( $job_id, $employer_id ) ){
								continue;
							}
							if ( $job->post_author != $employer_id &&  !current_user_can( 'edit_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
							}
							if(!wp_update_post(array(
								'ID'=>$job_id,
								'post_status'=>'inactive',
							))){
								wp_die( __('There was an error unpublishing this job.','noo') );
							}
							$unpublished++;
						}
						if( $unpublished > 0 ) {
							noo_message_add( sprintf( _n( 'Unpublished %s job', 'Unpublished %s jobs', $unpublished, 'noo' ), $unpublished ) );
						} else {
							noo_message_add( __( 'No job unpublished', 'noo' ) );
						}
						do_action('manage_job_action_pending',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
						die;
						break;
					case 'featured':
						if(!jm_can_set_feature_job()){
							noo_message_add(__('You do not have sufficient permissions set job to featured! Please check your plan package!','noo'),'error');
							wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
							die;
						}
						$job_id = reset($ids);
						$job = get_post($job_id);
						if ( !Noo_Member::can_edit_job( $job_id, $employer_id ) )
							return;
						
						if(get_post_status($job_id) == 'expired'){
							noo_message_add(__('You cannot change expired jobs to featured ones.','noo'),'notice');
							wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
							die;
						}
						if ( $job->post_author != $employer_id &&  !current_user_can( 'edit_post', $job_id ) ) {
							wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
						}
				
						$featured = noo_get_post_meta( $job_id, '_featured' );
				
						if ( 'yes' !== $featured ) {
							update_post_meta( $job_id, '_featured', 'yes' );
							update_user_meta($job->post_author, '_job_featured', absint(get_user_meta($job->post_author,'_job_featured',true)) + 1 );
							noo_message_add(__('Job set to featured successfully.','noo'));
						}
						
						do_action('manage_job_action_featured',$job_id);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
						die;
						break;
					case 'edit':
						break;
					case 'delete':
						$deleted=0;
						foreach ((array) $ids as $job_id){
							$job = get_post($job_id);
							if ( $job->post_type !== 'noo_job' )
								return;
							if ( $job->post_author != $employer_id && !current_user_can( 'delete_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
							}

							$old_status = get_post_status( $job_id );
							$in_review = (bool) noo_get_post_meta( $job_id, '_in_review', '' );
							// $waiting_payment = (bool) noo_get_post_meta( $job_id, '_waiting_payment', '' );
							
							if ( !wp_delete_post($job_id) ) {
								wp_die( __('Error in deleting.','noo') );
							}

							// Correct the job count.
							// if( 'pending' == $old_status && ( $in_review || $waiting_payment ) ) {
							if( ( 'pending' == $old_status && $in_review ) || 'pending_payment' == $old_status ) {
								jm_decrease_job_posting_count( $employer_id );
								$featured = noo_get_post_meta( $job_id, '_featured' );
								if( $featured == 'yes' ) {
									$job_featured = jm_get_feature_job_added( $employer_id );
									update_user_meta($employer_id, '_job_featured', max( $job_featured - 1, 0 ) );
								}
							}

							$deleted++;
						}
						if( $deleted > 0 ) {
							noo_message_add( sprintf( _n( 'Deleted %s job', 'Deleted %s jobs', $deleted, 'noo' ), $deleted ) );
						} else {
							noo_message_add( __( 'No job deleted', 'noo' ) );
						}
						do_action('manage_job_action_delete',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job') );
						die;
					break;
					case 'deletes':
						
					break;
				}
			}catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		}
	}
	
	public static  function ajax_login(){
		check_ajax_referer('noo-ajax-login','security');
		$info = array();
		$info['user_login'] = $_POST['log'];
		$info['user_password'] = $_POST['pwd'];

		$info['remember'] = (isset( $_POST['remember'] ) && $_POST['remember'] === true) ? true : false ;
		$info = apply_filters('noo_ajax_login_info', $info);
		
		$secure_cookie = is_ssl() ? true : false;
		$user_signon = wp_signon( $info, $secure_cookie );

		// it's possible that an old user used email instead of username
		if( is_wp_error( $user_signon ) && Noo_Member::get_setting('register_using_email') && is_email( $info['user_login'] ) ) {
			$user = get_user_by( 'email', $info['user_login'] );
			if( $user != false ) {
				$info['user_login'] = $user->user_login;
			}

			$user_signon = wp_signon( $info, $secure_cookie );
		}

		if ( is_wp_error( $user_signon ) ){
			$error_msg = $user_signon->get_error_message();
			wp_send_json(array( 'loggedin' => false, 'message' => '<span class="error-response">' . $error_msg . '</span>' ));
		} else {
			$redirecturl = isset( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : '';
			$redirecturl = apply_filters( 'noo_login_redirect', $redirecturl, $user_signon );
			$redirecturl = apply_filters( 'login_redirect', $redirecturl, $redirecturl, $user_signon ); // Enable redirect from some plugin
			wp_send_json(array('loggedin'=>true, 'redirecturl' => $redirecturl, 'message'=> '<span class="success-response">' . __( 'Login successful, redirecting...','noo' ) . '</span>' ));
		}
		die;
	}
	
	public static  function ajax_login_priv(){
		$link = "javascript:window.location.reload();return false;";
		wp_send_json(array('loggedin'=>false, 'message'=> sprintf(__('You have already logged in. Please <a href="#" onclick="%s">refresh</a> page','noo'),$link)));
		die();
	}
	
	public static  function ajax_register(){
		$result = array( 'success' => true );

		if( !check_ajax_referer('noo-ajax-register', 'security', false) ) {
			$result['success'] = false;
			$result['message'] = '<span class="error-response">'.__( 'Your session has expired or you have submitted an invalid form.', 'noo' ).'</span>';
		}
		if( $result['success'] && !get_option('users_can_register') ) {
			$result['success'] = false;
			$result['message'] = __( 'This site does not allow registration.', 'noo' );
		}
		if ( isset( $_POST['g-recaptcha-response'] ) && !noo_recaptcha_verification() ) {
			$result['success'] = false;
			$result['message'] = '<span class="error-response">'.__( 'Please complete the Recaptcha challenge', 'noo' ).'</span>';
		}
		$result = apply_filters( 'noo_register_validation', $result, $_POST );
		if( $result['success'] ) {
			$user_args = array();
			$user_args['user_login'] = isset($_POST['user_login']) ? stripslashes( esc_html( $_POST['user_login'] ) ) : '';
			$user_args['user_email'] = isset($_POST['user_email']) ?  stripslashes( esc_html( $_POST['user_email'] ) ) : '';
			$user_args['user_password']  = isset($_POST['user_password']) ? stripslashes( esc_html( $_POST['user_password'] ) ) : '';
			$user_args['cuser_password'] = isset($_POST['cuser_password']) ? stripslashes( esc_html( $_POST['cuser_password'] ) ) : '';
			if( isset($_POST['first_name']) ) {
				$user_args['first_name'] = stripslashes( esc_html( $_POST['first_name'] ) );
			}
			if( isset($_POST['last_name']) ) {
				$user_args['last_name'] = stripslashes( esc_html( $_POST['last_name'] ) );
			}
			if( isset($_POST['name']) ) {
				$user_args['display_name'] = stripslashes( esc_html( $_POST['name'] ) );
			}

			/**
			 * Checking name social login
			 */
			if ( !empty( $_POST['user_name'] ) ) {
				$user_args['display_name'] = stripslashes( esc_html( $_POST['user_name'] ) );
			}

			$allow_register = Noo_Member::get_setting('allow_register', 'both');
			switch( $allow_register ) {
				case 'candidate':
					$user_args['role'] = Noo_Member::CANDIDATE_ROLE;
					break;
				case 'employer':
					$user_args['role'] = Noo_Member::EMPLOYER_ROLE;
					break;
				default:
					$user_args['role'] = isset($_POST['user_role']) ? stripslashes( esc_html( $_POST['user_role'] ) ) : '';
					break;
			}

			if( empty( $user_args['user_login'] ) && Noo_Member::get_setting( 'register_using_email', false ) ) {
				$user_args['user_login'] = $user_args['user_email'];
			}

			$user_args = apply_filters( 'noo_register_user_data', $user_args, $_POST );
			$user_id = self::_register_new_user( $user_args );
			if ( is_wp_error( $user_id ) ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.$user_id->get_error_message().'</span>';
				$result['redirecturl'] = '';

				$result = apply_filters( 'noo_register_error_result', $result, $user_id );
			} else {
				if( $user_args['role'] == Noo_Member::CANDIDATE_ROLE ) {
					jm_candidate_save_custom_fields( $user_id, $_POST ); // save custom fields if there's any
				} elseif( $user_args['role'] == Noo_Member::EMPLOYER_ROLE ) {
					// Create new company
					$company_data = array(
						'post_title'     => '',
						'post_type'      => 'noo_company',
						'comment_status' => 'closed',
						'post_status'	 => 'publish',
						'post_author'	 => $user_id
					);
					
					$company_id = wp_insert_post( $company_data );
					
					if( !is_wp_error( $company_id ) ) {
						update_user_meta( $user_id, 'employer_company', $company_id );

						// Save custom fields
						jm_company_save_custom_fields( $company_id, $_POST );
					}
				}

				$redirect_to = '';
				if( $user_args['role'] == Noo_Member::CANDIDATE_ROLE )
					$redirect_to = Noo_Member::get_candidate_profile_url();
				elseif( $user_args['role'] == Noo_Member::EMPLOYER_ROLE )
					$redirect_to = Noo_Member::get_company_profile_url();

				$redirect_to = isset($_POST['redirect_to']) && !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : $redirect_to;
				$redirect_to = apply_filters( 'registration_redirect', $redirect_to ); // Enable redirect from some plugin

				$filter_tag = 'noo_register_redirect' . ( !empty($user_args['role']) ? '_'.$user_args['role'] : '' );
				
				$result['success'] = true;
				$result['message'] = '<span class="success-response">'.__( 'Registration completed.', 'noo' ).'</span>';
				$result['redirecturl'] = apply_filters($filter_tag, $redirect_to);
			}
		}

		wp_send_json($result);
	}
	
	public static function _register_new_user( $args = array() ) {
		$defaults = array( 
			'user_login'             => '',
			'user_email'             => '',
			'user_password'          => '',
			'cuser_password'         => '',
			'role'                   => '',
			'first_name'             => '',
			'last_name'              => '',
			'display_name'           => ''
		);
		extract( wp_parse_args( $args, $defaults) );

		$errors = new WP_Error();
		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );
	
		// Check the username was sanitized
		$allowed_roles = apply_filters( 'noo_allowed_register_role', array( Noo_Member::CANDIDATE_ROLE, Noo_Member::EMPLOYER_ROLE ) );
		if( !in_array( $role, $allowed_roles ) ){
			$errors->add( 'empty_role', __( 'Please choose a role for your account.', 'noo' ) );
		}elseif ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( 'Please enter a username.', 'noo' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'noo' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( 'This username was registered. Please choose another one.', 'noo' ) );
		}
	
		// Check the email address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __( 'Please type your email address.', 'noo' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( 'The email address isn\'t correct.', 'noo' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( 'This email was already registered, please choose another one.', 'noo' ) );
		}
		//Check the password
	
		if(strlen($user_password) < 6){
			$errors->add( 'minlength_password', __( 'Password must be at least six characters long.', 'noo' ) );
		}elseif (empty($cuser_password)){
			$errors->add( 'not_cpassword', __( 'Please enter the password confirmation.', 'noo' ) );
		}elseif ($user_password != $cuser_password){
			$errors->add( 'unequal_password', __( 'Passwords do not match.', 'noo' ) );
		}
	
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		$errors = apply_filters( 'noo_registration_errors', $errors, $args );
	
		if ( is_wp_error( $errors ) && $errors->get_error_code() ) {
			return $errors;
		}
		
		$user_pass = $user_password;
		$new_user = array(
			'user_login' => $sanitized_user_login,
			'user_pass'  => $user_pass,
			'user_email' => $user_email,
			'role'       => $role,
		);
		if( !empty( $first_name ) ) {
			$new_user['first_name'] = $first_name;
		}
		if( !empty( $last_name ) ) {
			$new_user['last_name'] = $last_name;
		}
		if( !empty( $display_name ) ) {
			$new_user['display_name'] = $display_name;
		}

		$user_id = wp_insert_user( apply_filters( 'noo_create_user_data', $new_user ) );
		//$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
	
		if ( ! $user_id ) {
			$errors->add( 'registerfail', __( 'Couldn\'t register you... please contact the site administrator', 'noo' ) );
			return $errors;
		} else {
			do_action( 'noo_new_user_registered', $user_id, $args );
		}
	
		update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the Password change nag.

		/**
		 * Checking info social
		 */
		if ( !empty( $_POST['using'] ) ) :
			$post_userid = isset($_POST['userid']) ? stripslashes( esc_html(  $_POST['userid'] ) ) : '';
			if ( $_POST['using'] == 'gg' ) :

				update_user_meta( $user_id, 'id_google', $post_userid );

			elseif ( $_POST['using'] == 'fb' ) :

				update_user_meta( $user_id, 'id_facebook', $post_userid );

			elseif ( $_POST['using'] == 'linkedin' ) :

				update_user_meta( $user_id, 'id_linkedin', $post_userid );

			endif;
		endif;

		$user = get_userdata( $user_id );
		
		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		// user email
		$to = $user->user_email;

		if( $role == Noo_Member::CANDIDATE_ROLE ) {
			$subject = jm_et_get_setting('candidate_registration_subject');

			$array_subject = array(
				'[user_name]' => isset( $user->display_name ) && !empty( $user->display_name ) ? $user->display_name : $user->user_login,
				'[user_email]' => $user->user_email,
				'[user_registered]' => $user->user_registered,
				'[site_name]' => $blogname,
				'[site_url]' => esc_url(home_url('')),
			);
			$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

			$message = jm_et_get_setting('candidate_registration_content');
			$message = str_replace(array_keys($array_subject), $array_subject, $message);

			noo_mail($to, $subject, $message,'noo_notify_register_candidate');

		} else if( $role == Noo_Member::EMPLOYER_ROLE ) {

			$subject = jm_et_get_setting('employer_registration_subject');

			$array_subject = array(
				'[user_name]' => isset( $user->display_name ) && !empty( $user->display_name ) ? $user->display_name : $user->user_login,
				'[user_email]' => $user->user_email,
				'[user_registered]' => $user->user_registered,
				'[site_name]' => $blogname,
				'[site_url]' => esc_url(home_url('')),
			);
			$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

			$message = jm_et_get_setting('employer_registration_content');
			$message = str_replace(array_keys($array_subject), $array_subject, $message);

			noo_mail($to, $subject, $message,array(),'noo_notify_register_employer');
		}
			
		// notification to admin
		wp_new_user_notification( $user_id );
			
		// /**
		//  * Fires after a new user registration has been recorded.
		//  *
		//  * @since 4.4.0
		//  *
		//  * @param int $user_id ID of the newly registered user.
		//  */
		// do_action( 'register_new_user', $user_id );

		$data_login['user_login']             = $user->user_login;
		$data_login['user_password']          = $user_password;
		$secure_cookie						  = is_ssl() ? true : false;
		$user_login                           = wp_signon( $data_login, $secure_cookie );
	
		//@todo
		
		//wp_set_auth_cookie($user_id);
		return $user_id;
	}

	public static function ajax_update_email() {
		if ( !is_user_logged_in() ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'You have not logged in yet', 'noo' ).'</span>',
			);

			wp_send_json($result);
			return;
		}

		if( !check_ajax_referer('update-email', 'security', false) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'Your session has expired or you have submitted an invalid form.', 'noo' )
			);

			wp_send_json($result);
			return;
		}

		$current_user = wp_get_current_user();

		$user_id		= $current_user->ID;
		$submit_user_id	= intval( $_POST['user_id'] );
		if( $user_id != $submit_user_id ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__('There\'s an unknown error. Please retry or contact Administrator.', 'noo').'</span>',
			);
		} else {
			$no_html			= array();
			$new_email			= wp_kses( $_POST['new_email'] ,$no_html) ;
			$new_email_confirm	= wp_kses( $_POST['new_email_confirm'] ,$no_html) ;

			if( empty( $new_email ) || empty( $new_email_confirm ) ){
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">'.__('The new email is blank.', 'noo').'</span>',
				);
			} elseif($new_email != $new_email_confirm){
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">'.__('Emails do not match.', 'noo').'</span>',
				);
			} else {
				$user = array(
					'ID'			=> $user_id,
					'user_email'	=> $new_email
				);

				$update_result = wp_update_user( $user );

				if( is_wp_error( $update_result ) && $update_result->get_error_code() ) {
					$result = array(
						'success'     => false,
						'message' => '<span class="error-response">'.$update_result->get_error_message().'</span>',
					);
				} else {
					$result = array(
						'success'     => true,
						'message'	=> '<span class="success-response">'.__( 'Email updated successfully.', 'noo' ).'</span>',
						'redirecturl'=>apply_filters('noo_update_password_redirect', ''),
					);
				}
			}
		}

		wp_send_json($result);
	}

	public static function ajax_update_password() {
		if ( !is_user_logged_in() ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'You have not logged in yet', 'noo' ).'</span>',
			);

			wp_send_json($result);
			return;
		}

		if( !check_ajax_referer('update-password', 'security', false) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'Your session has expired or you have submitted an invalid form.', 'noo' )
			);

			wp_send_json($result);
			return;
		}

		$current_user = wp_get_current_user();

		$user_id		= $current_user->ID;
		$submit_user_id	= intval( $_POST['user_id'] );
		if( $user_id != $submit_user_id ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__('There\'s an unknown error. Please retry or contact Administrator.', 'noo').'</span>',
			);
		} else {
			$no_html			= array();
			$old_pass			= wp_kses( $_POST['old_pass'] ,$no_html) ;
			$new_pass			= wp_kses( $_POST['new_pass'] ,$no_html) ;
			$new_pass_confirm	= wp_kses( $_POST['new_pass_confirm'] ,$no_html) ;

			if( empty( $new_pass ) || empty( $new_pass_confirm ) ){
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">'.__('The new password is blank.', 'noo').'</span>',
				);
			} elseif($new_pass != $new_pass_confirm){
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">'.__('Passwords do not match.', 'noo').'</span>',
				);
			} else {
				$user = get_user_by( 'id', $user_id );
				if ( $user && wp_check_password( $old_pass, $user->data->user_pass, $user->ID) ){
					wp_set_password( $new_pass, $user->ID );

					$result = array(
						'success'     => true,
						'message'	=> '<span class="success-response">'.__( 'Password updated successfully.', 'noo' ).'</span>',
						'redirecturl'=>apply_filters('noo_update_password_redirect', ''),
					);
				} else {
					$result = array(
						'success' => false,
						'message' => '<span class="error-response">'.__('Old Password is not correct.', 'noo').'</span>',
					);
				}
			}
		}

		wp_send_json($result);
	}

	public static function ajax_bookmark_job() {
		if ( !is_user_logged_in() ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'You have not logged in yet', 'noo' ).'</span>',
			);

			wp_send_json($result);
			return;
		}

		if( !check_ajax_referer('noo-bookmark-job', 'security', false) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__( 'Your session has expired or you have submitted an invalid form.', 'noo' ).'</span>',
			);

			wp_send_json($result);
			return;
		}

		if( ! isset($_POST['job_id']) || empty($_POST['job_id']) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">'.__('There\'s an unknown error. Please retry or contact Administrator.', 'noo').'</span>',
			);

			wp_send_json($result);
			return;
		}

		$current_user = wp_get_current_user();

		$user_id		= $current_user->ID;
		$job_id			= $_POST['job_id'];

		if( jm_is_job_bookmarked($user_id, $job_id) ) {
			if( jm_job_clear_bookmarked($user_id, $job_id) ) {
				$result = array(
					'success' => true,
					'message' => '<span class="success-response">'.__( 'Bookmark removed.', 'noo' ),
					'message_text' => __( 'Bookmark Job', 'noo' )
				);
				wp_send_json($result);
			}
		} else {
			if( jm_job_set_bookmarked($user_id, $job_id) ) {
				$result = array(
					'success' => true,
					'message' => '<span class="success-response">'.__( 'Job bookmarked.', 'noo' ),
					'message_text' => __( 'Bookmarked.', 'noo' )
				);
				wp_send_json($result);
			}
		}
		
		$result = array(
			'success' => false,
			'message' => '<span class="error-response">'.__('There\'s an unknown error. Please retry or contact Administrator.', 'noo').'</span>',
		);

		wp_send_json($result);
	}
	
	public static function save_job($args=''){
		try{
			$defaults=array(
				'job_id'      =>'',
				'position'    =>'',
				'desc'        =>'',
				'feature'     =>'no',//isset($args['feature']) && $args['feature'] == 'yes' ? $args['feature'] : 'no',
				'location'    =>'',
				'type'        =>'',
				'category'    =>'',
				'cover_image' => '',
				'_use_company_location'    =>true,
				'post_status' => 'draft'
			);
			$args = wp_parse_args($args,$defaults);
			unset($args['page_id']); // unset to prevent strange behaviour of insert new page.
			$no_html = array();
			$job_data = array(
				'post_title'     => wp_kses( $args['position'], $no_html ),
				'post_content'   => wp_kses_post( $args['desc']),
				'post_type'      => 'noo_job',
				'comment_status' => 'closed',
			);
			$post_id = new WP_Error();
			$new_job = false;
			if(!empty($args['job_id']) && isset($args['is_edit'])){
				$job_data['ID'] = $args['job_id'];
				$post_id = wp_update_post($job_data);
			}else{
				if(jm_can_post_job()){
					$post_id = wp_insert_post($job_data);
					$new_job = true;
				}
			}
			if(!is_wp_error($post_id)){
				if( isset( $args['type'] ) && !empty( $args['type'] ) ) {
					$types = is_array( $args['type'] ) ? $args['type'] : array( $args['type'] );
					foreach ($types as $cat) {
						$types_validated[] = wp_kses( $cat, $no_html );
					}
					wp_set_post_terms( $post_id, $types_validated, 'job_type', false );
				}

				if( isset( $args['category'] ) && !empty( $args['category'] ) ) {
					$categories = is_array( $args['category'] ) ? $args['category'] : array( $args['category'] );
					$categories_validated = array();
					foreach ($categories as $cat) {
						$categories_validated[] = wp_kses( $cat, $no_html );
					}
					wp_set_post_terms( $post_id, $categories_validated, 'job_category', false );
				}

				if( isset( $args['tag'] ) && !empty( $args['tag'] ) ) {
					$tags = is_array( $args['tag'] ) ? $args['tag'] : array( $args['tag'] );
					$tags_validated = array();
					foreach ($tags as $tag) {
						$tags_validated[] = wp_kses( $tag, $no_html );
					}
					wp_set_post_terms( $post_id, $tags_validated, 'job_tag', false );
				}
				
				//update_post_meta($post_id, '_location', $args['location']);
				if(!empty($args['location'])){
					$location_arr = array();
					foreach ((array) $args['location'] as $l){
						$slug = trim($l);
						if ( ($t = get_term_by( 'slug', sanitize_title( $slug ), 'job_location' ) )) {
							$location_arr[] = $t->term_id;
						}else{
							$n_l = wp_insert_term( $slug, 'job_location' );
							if ($n_l && !is_wp_error($n_l) && ($loca = get_term( absint($n_l['term_id']), 'job_location' ))) {
								$location_arr[] = $loca->term_id;
							}
						}
					}
					if(!empty($location_arr)){
						wp_set_post_terms( $post_id, $location_arr, 'job_location', false );
					}
				}

				if( isset( $args['_use_company_location'] ) && !empty( $args['_use_company_location'] ) ) {
					$use_company_location = strtotime( $args['_use_company_location'] );
					if( !$use_company_location ) {
						$use_company_location = wp_kses( $args['_use_company_location'], $no_html );
					}
					update_post_meta( $post_id, '_use_company_location', $use_company_location );
				}

				if( isset( $args['cover_image'] ) ) {
					$old_image = noo_get_post_meta( $post_id, '_cover_image' );
					if( $old_image != $args['cover_image'] ) {
						update_post_meta($post_id, '_cover_image', wp_kses( $args['cover_image'], $no_html ) );
						if( is_numeric( $old_image ) ) {
							wp_delete_attachment($old_image, true);
						}
					}
				}
				update_post_meta($post_id, '_application_email', wp_kses( $args['application_email'], $no_html ));
				$custom_apply_link = jm_get_setting('noo_job_linkedin', 'custom_apply_link' );
				if( $custom_apply_link == 'employer' ) {
					update_post_meta($post_id, '_custom_application_url', wp_kses( $args['custom_application_url'], $no_html ));
				}

				// Update custom fields
				jm_job_save_custom_fields( $post_id, $args );
				
				//
				do_action('noo_save_job',$post_id);
			}
			if(isset($_POST['company_name']) && !jm_get_employer_company() && Noo_Member::is_employer() ){
				self::save_company($_POST);
			}

			do_action('noo_after_save_job',$post_id);
			return $post_id;
		}catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}
	
	public static function edit_resume_action(){
		if(!is_user_logged_in())
			return;
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'edit_resume' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'edit-resume' ) ) {
			return;
		}

		if( !isset( $_POST['resume_id'] ) || empty( $_POST['resume_id'] ) ) {
			// No insert new
			jm_force_redirect(Noo_Member::get_post_resume_url());
		}

		// Edit resume
		$resume_id = self::_save_resume($_POST);
		if( $resume_id ) {
			$resume_id = self::_save_detail_resume($_POST);
		}
		
		if( !$resume_id ) {
			// noo_message_add(__('There\'s an unknown problem when saving resume, please try again.','noo'));
			jm_force_redirect(Noo_Member::get_endpoint_url('manage-resume'));
		} else {
			noo_message_add(__('Resume saved','noo'));
			jm_force_redirect(Noo_Member::get_endpoint_url('manage-resume'));
		}
		
		exit();
	}

	public static function employer_message_application_modal(){
		
		if(!is_user_logged_in()){
			die(-1);
		}
		
		check_ajax_referer('noo-member-security','security');
		
		$application_id = isset($_POST['application_id']) ? absint($_POST['application_id']) : 0;
		$mode = isset($_POST['mode']) ? absint($_POST['mode']) : 0;
		
		ob_start();
		Noo_Member::modal_employer_message($application_id,$mode);
		$output = ob_get_clean();
		if(empty($output))
			die(-1);
		else 
			echo trim($output);
		die();
	}

	public static function manage_job_applied_action(){
		if(!is_user_logged_in())
			return;
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'job-applied-manage-action' ) ) {
			if ( isset( $_REQUEST['application_id'] ) ) {
				$ids = explode( ',', $_REQUEST['application_id'] );
			} elseif ( !empty( $_REQUEST['ids'] ) ) {
				$ids = array_map('intval', $_REQUEST['ids']);
			}
			try {
				switch ($action){
					case 'withdraw':
						$withdrawn = 0;
						foreach ((array) $ids as $application_id){
							if( !Noo_Application::can_trash_application( get_current_user_id(), $application_id ) ) {
								continue;
							}
							
							if ( !wp_update_post(array( 'ID' => $application_id, 'post_status' => 'inactive' )) ) {
								wp_die( __('Error when withdrawing application.','noo') );
							}
							
							$withdrawn++;
						}
						if( $withdrawn > 0 ) {
							noo_message_add( sprintf( _n( 'Withdrawn %s application', 'Withdrawn %s applications', $withdrawn, 'noo' ), $withdrawn ) );
						} else {
							noo_message_add( __( 'No application withdrawn', 'noo' ) );
						}
						do_action('manage_application_action_withdraw',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job-applied') );
						die;
					break;
					case 'delete':
						$deleted = 0;
						foreach ((array) $ids as $application_id){
							if( !Noo_Application::can_delete_application( get_current_user_id(), $application_id ) ) {
								continue;
							}
							
							if ( !wp_delete_post($application_id) ) {
								wp_die( __('Error when deleting application.','noo') );
							}
							
							$deleted++;
						}
						if( $deleted > 0 ) {
							noo_message_add( sprintf( _n( 'Deleted %s application', 'Deleted %s applications', $deleted, 'noo' ), $deleted ) );
						} else {
							noo_message_add( __( 'No application deleted', 'noo' ) );
						}
						do_action('manage_application_action_delete',$ids);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-job-applied') );
						die;
					break;
					default:
					break;
				}
			}catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		}
	}
	
	public static function edit_job_alert_action(){
		if(!is_user_logged_in())
			return;
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'edit_job_alert' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'edit-job-alert' ) ) {
			return;
		}
		$job_alert_id = self::_save_job_alert($_POST);
		
		if( $job_alert_id === false ) {
			wp_safe_redirect(Noo_Member::get_endpoint_url('add-job-alert'));
		} else {
			noo_message_add(__('Job alert saved','noo'));
			wp_safe_redirect(Noo_Member::get_endpoint_url('job-alert'));
		}
		
		exit();
	}
	
	public static function delete_job_alert_action(){
		if(!is_user_logged_in())
			return;
		
		if ( 'GET' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_GET[ 'action' ] ) || 'delete_job_alert' !== $_GET[ 'action' ] || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edit-job-alert' ) ) {
			return;
		}
		$job_alert_id = isset($_GET['job_alert_id']) ? $_GET['job_alert_id'] : '';
		
		if( empty( $job_alert_id ) ) {
			noo_message_add(__('There was a problem deleting this job alert','noo'));
			wp_safe_redirect(Noo_Member::get_endpoint_url('job-alert'));
		} else {
			wp_delete_post( $job_alert_id );
			noo_message_add(__('Job alert deleted','noo'));
			wp_safe_redirect(Noo_Member::get_endpoint_url('job-alert'));
		}
		
		exit();
	}
	
	public static function edit_candidate_profile_action(){
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'edit_candidate' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'edit-candidate' ) ) {
			return;
		}

		if(!is_user_logged_in())
			return;
		
		$candidate_id = isset( $_POST['candidate_id'] ) ? absint( $_POST['candidate_id'] ) : '';
		if( empty( $candidate_id) ) {
			noo_message_add(__('Missing Candidate ID.', 'noo'));
		} elseif( $candidate_id != get_current_user_id() ) {
			noo_message_add(__('You can not edit someone else\'s profile.', 'noo'));
		} else {
			$no_html = self::$no_html;

			$name = isset( $_POST['name'] ) ? wp_kses( $_POST['name'], $no_html ) : '';
			$email = isset( $_POST['email'] ) ? wp_kses( $_POST['email'], $no_html ) : '';
			$desc	= isset( $_POST['description'] ) ? wp_kses_post($_POST['description'] ) : '';

			$candidate = array(
				'ID'			=> $candidate_id,
				'description'	=> $desc
			);

			if( !empty($name) ) {
				$splitted_name = explode( ' ', $name, 2 );
				$first_name =  $splitted_name[0];
				$last_name = !empty($splitted_name[1]) ? $splitted_name[1]: '';
			} else {
				$first_name = isset( $_POST['first_name'] ) && !empty( $_POST['first_name'] ) ? wp_kses( $_POST['first_name'], $no_html ) : '';
				$last_name = isset( $_POST['last_name'] ) && !empty( $_POST['last_name'] ) ? wp_kses( $_POST['last_name'], $no_html ) : '';
			}

			if (!empty($first_name) or !empty($last_name)){
				$candidate['first_name'] = $first_name;
				$candidate['last_name'] = $last_name;
				$candidate['display_name'] = $first_name . ' ' . $last_name;
			}

			if( !empty($email) && is_email( $email ) ) {
				$candidate['user_email'] = $email;
			}

			$user_id = wp_update_user( $candidate );

			if( is_wp_error( $user_id ) && $user_id->get_error_code() ) {
				noo_message_add( $user_id->get_error_message(), 'error' );
			} elseif( $user_id != $candidate_id ) {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'));
			} else {

				if( isset( $_POST['profile_image'] ) ) {
					$old_profile_image = get_user_meta( $user_id, 'profile_image', true );
					if( $old_profile_image != $_POST['profile_image'] ) {
						update_user_meta( $user_id,'profile_image', sanitize_text_field( $_POST['profile_image'] ) );
						if( is_numeric( $old_profile_image ) ) {
							wp_delete_attachment($old_profile_image, true);
						}
					}
				}

				jm_candidate_save_custom_fields($candidate_id, $_POST);
				noo_message_add(__('Your profile is updated successfully','noo'));

				do_action( 'noo_save_candidate_profile', $candidate_id, $_POST );
			}
		}
		wp_safe_redirect(Noo_Member::get_candidate_profile_url());
		die;
	}
	
	public static function post_resume_action(){
		if(!Noo_Member::is_logged_in()){
			wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'login')) . '#jform');
			return;
		}
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if(!isset($_POST['page_id']))
			return;
		
		$page_temp = get_page_template_slug($_POST['page_id']);
		
		if('page-post-resume.php' !== $page_temp)
			return;

		if ( empty( $_POST[ 'action' ] ) || 'resume_post' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-post-resume' ) ) {
			return;
		}

		$_POST['post_status'] = 'draft'; // Resume post is alway draft. Need to be reviewed first.
		$resume_id = self::_save_resume($_POST);
		if( $resume_id ) {
			$_POST['resume_id'] = $resume_id;
			$resume_id = self::_save_detail_resume($_POST);
		}

		$location = array();
		if($resume_id === false){
			$location['resume_id'] = $resume_id;
			$location['action'] = 'resume_post';
		} else {
			$location['resume_id'] = $resume_id;
			$location['action'] = 'resume_preview';
		}

		wp_safe_redirect(esc_url_raw(add_query_arg($location)) . '#jform');
		exit;
	}
	
	public static function preview_resume_action(){
		if(!Noo_Member::is_logged_in()){
			wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'login')) . '#jform');
			return;
		}
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) )
			return;
		
		if(!isset($_POST['page_id']))
			return;
			
		$page_temp = get_page_template_slug($_POST['page_id']);
		if('page-post-resume.php' !== $page_temp)
			return;
		
		if ( empty( $_POST[ 'action' ] ) || 'resume_preview' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'noo-post-resume' ) ) {
			return;
		}

		$resume_id = isset($_POST['resume_id']) ? $_POST['resume_id'] : '';
		if( empty( $resume_id ) ) {
			return;
		}

		$candidate_id = isset($_POST['candidate_id']) ? $_POST['candidate_id'] : '';
		if( empty( $candidate_id ) ) {
			return;
		}

		if( !Noo_Member::can_post_resume($candidate_id) ) {
			return;
		}

		if( !Noo_Member::is_resume_owner( $candidate_id, $resume_id ) ) {
			return;
		}

		$resume_need_approve = (bool) jm_get_resume_setting( 'resume_approve','' );
		if(jm_is_woo_resume_posting()){
			if(jm_get_resume_posting_remain() > 0){
				jm_increase_resume_posting_count(get_current_user_id());
				if( !$resume_need_approve ) {
					wp_update_post(array(
						'ID'=>$resume_id,
						'post_status'=>'publish',
					));
				} else {
					wp_update_post(array(
						'ID'=>$resume_id,
						'post_status'=>'pending',
					));
					update_post_meta($resume_id, '_in_review', 1);
				}
				noo_message_add(__('Resume successfully added','noo'));
				Noo_Resume::notify_candidate($resume_id);
				jm_force_redirect( Noo_Member::get_endpoint_url('manage-job') );
			}else{
				global $woocommerce;

				wp_update_post(array(
					'ID'=>$resume_id,
					'post_status'=>'pending_payment',
				));

				if(isset($_POST['package_id'])){
					jm_increase_resume_posting_count(get_current_user_id());

					$resume_package = wc_get_product( absint($_POST['package_id']) );
					$quantity = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
					$passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $resume_package->id, $quantity );
					if($resume_package->is_type( 'resume_package' ) && $passed_validation){
						// Add the product to the cart
						$woocommerce->cart->empty_cart();
						if($woocommerce->cart->add_to_cart( $resume_package->id, $quantity,'','',array('_resume_id'=>$resume_id))){
							//woocommerce_add_to_cart_message( $resume_package->id );
							jm_force_redirect($woocommerce->cart->get_checkout_url());
						}

					}
				} else {
					wp_update_post(array(
						'ID'=>$resume_id,
						'post_status'=>'trashed',
					));
					jm_force_redirect( Noo_Member::get_endpoint_url('manage-resume') );
				}
			}
		}else{
			if( !$resume_need_approve ) {
				wp_update_post(array(
					'ID'=>$resume_id,
					'post_status'=>'publish',
				));
			} else {
				wp_update_post(array(
					'ID'=>$resume_id,
					'post_status'=>'pending',
				));
				update_post_meta($resume_id, '_in_review', 1);
			}
			noo_message_add(__('Resume successfully added','noo'));
			Noo_Resume::notify_candidate($resume_id);
			jm_force_redirect( Noo_Member::get_endpoint_url('manage-resume') );
		}

		// $resume = array(
		// 	'ID'     => intval( $resume_id ),
		// 	'post_status'	 => 'publish',
		// );

		// wp_update_post($resume);

		// // Add email
		// Noo_Resume::notify_candidate($resume_id);

		// wp_safe_redirect(Noo_Member::get_endpoint_url('manage-resume'));
		// die;
	}

	private static function _save_resume($args=''){
		try{
			$defaults=array(
				'candidate_id'=>'',
				'resume_id'=>'',
				'title'=>'',
				'desc'=>'',
				'status'=>'draft',
			);
			$args = wp_parse_args($args,$defaults);
			if( empty($args['candidate_id']) ) {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error');
				return false;
			}

			if( !empty($args['resume_id']) ) {
				if( !Noo_Member::is_resume_owner( $args['candidate_id'], $args['resume_id'] ) ) {
					noo_message_add(__('Sorry, you can\'t edit this resume.', 'noo'), 'error');
					return false;
				}
			} elseif( !Noo_Member::can_post_resume($args['candidate_id']) ) {
				noo_message_add(__('Sorry, you can\'t post resume.', 'noo'), 'error');
				return false;
			}

			$no_html = self::$no_html;

			$resume = array(
				'post_title'     => wp_kses( $args['title'], $no_html ),
				'post_content'   => wp_kses_post($args['desc']),
				'post_type'      => 'noo_resume',
				'post_status'	 => wp_kses( $args['status'], $no_html ),
				'post_author'	 => absint( $args['candidate_id'] )
			);
			if( empty($resume['post_title']) ) {
				noo_message_add(__('This resume needs a title.', 'noo'), 'error');
				return false;
			}
			$new_resume = false;
			if(!empty($args['resume_id'])){
				$resume['ID'] = intval( $args['resume_id'] );
				unset( $resume['post_status'] );
				$post_id = wp_update_post($resume);
			}else{
				$post_id = wp_insert_post($resume);
				$new_resume = true;
			}
			if(!is_wp_error($post_id) && $post_id){

				$fields = jm_get_resume_custom_fields();

				if($fields) {
					foreach ($fields as $field) {

						$id = jm_resume_custom_fields_name( $field['name'], $field );

						$value = isset( $args[$id] ) ? wp_kses( $args[$id], $no_html ) : '';

						if( $id == '_job_category' || $id == '_job_location' ) {
							$value = !is_array( $value ) ? array( $value ) : $value;
							$value = json_encode($value);
						}

						update_post_meta($post_id, $id, $value);
						
					}
				}

				$file_cv = isset( $args['file_cv'] ) ? wp_kses( $args['file_cv'], $no_html ) : '';
				update_post_meta($post_id, '_noo_file_cv', $file_cv);

				$url_video = isset( $args['url_video'] ) ? wp_kses( $args['url_video'], $no_html ) : '';
				update_post_meta($post_id, '_noo_url_video', $url_video);
				
				// Set viewable
				if( empty( $args['resume_id'] ) ) {
					$max_viewable_resumes = intval( jm_get_resume_setting( 'max_viewable_resumes', 1 ) );
					if( $max_viewable_resumes > 0 ) {
						// @TODO: change this code when we have approve/reject resume function
						$viewable_resumes = absint( Noo_Resume::count_viewable_resumes( get_current_user_id() ) );
						if( $viewable_resumes < $max_viewable_resumes ) {
							update_post_meta( $post_id, '_viewable', 'yes' );
						}
					} elseif( $max_viewable_resumes == -1 ) {
						update_post_meta( $post_id, '_viewable', 'yes' );
					}
				}
			} else {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error');
				return false;
			}
			do_action('noo_after_save_resume',$post_id);
			if ($new_resume){
			do_action('noo_after_new_resume',$post_id);
			}
			return $post_id;
		}catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}
	
	private static function _save_detail_resume($args=''){
		try{
			$defaults=array(
				'resume_id'=>'',
				'_education_school'=>'',
				'_education_qualification'=>'',
				'_education_date'=>'',
				'_education_school'=>'',
				'_education_note'=>'',
				'_experience_employer'=>'',
				'_experience_job'=>'',
				'_experience_date'=>'',
				'_experience_note'=>'',
				'_skill_name'=>'',
				'_skill_percent'=>'',
			);
			$args = wp_parse_args($args,$defaults);

			$no_html = self::$no_html;

			if( empty($args['candidate_id']) || !is_numeric($args['candidate_id']) || empty($args['resume_id']) || !is_numeric($args['resume_id'])  ) {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error');
				return false;
			}

			if( !empty($args['resume_id']) ) {
				if( !Noo_Member::is_resume_owner( $args['candidate_id'], $args['resume_id'] ) ) {
					noo_message_add(__('Sorry, you can\'t edit this resume.', 'noo'), 'error');
					return false;
				}
			} elseif( !Noo_Member::can_post_resume($args['candidate_id']) ) {
				noo_message_add(__('Sorry, you can\'t post resume.', 'noo'), 'error');
				return false;
			}

			if( jm_get_resume_setting('enable_education', '1') ) {
				$education_school = $args['_education_school'];
				$education_qualification = $args['_education_qualification'];
				$education_date = $args['_education_date'];
				$education_note = $args['_education_note'];

				if( isset( $education_school ) && !empty( $education_school ) ) {
					$education_count = count( $education_school );
					for( $index = 0; $index < $education_count; $index++ ) {
						$education_school[$index] = addcslashes( stripslashes( wp_kses( $education_school[$index], $no_html ) ), '"' );
						$education_qualification[$index] = addcslashes( stripslashes( wp_kses( $education_qualification[$index], $no_html ) ), '"' );
						$education_date[$index] = addcslashes( stripslashes( wp_kses( $education_date[$index], $no_html ) ), '"' );
						$education_note[$index] = addcslashes( stripslashes( htmlentities( wp_kses( $education_note[$index], self::$allowed_html ), ENT_QUOTES ) ), '"' );
					}
				}

				update_post_meta($args['resume_id'], '_education_school', json_encode($education_school, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_education_qualification', json_encode($education_qualification, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_education_date', json_encode($education_date, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_education_note', json_encode($education_note, JSON_UNESCAPED_UNICODE));
			}

			if( jm_get_resume_setting('enable_experience', '1') ) {
				$experience_employer = $args['_experience_employer'];
				$experience_job = $args['_experience_job'];
				$experience_date = $args['_experience_date'];
				$experience_note = $args['_experience_note'];

				if( isset( $experience_employer ) && !empty( $experience_employer ) ) {
					$experience_count = count( $experience_employer );
					for( $index = 0; $index < $experience_count; $index++ ) {
						$experience_employer[$index] = addcslashes( stripslashes( wp_kses( $experience_employer[$index], $no_html ) ), '"' );
						$experience_job[$index] = addcslashes( stripslashes( wp_kses( $experience_job[$index], $no_html ) ), '"' );
						$experience_date[$index] = addcslashes( stripslashes( wp_kses( $experience_date[$index], $no_html ) ), '"' );
						$experience_note[$index] = addcslashes( stripslashes( htmlentities( wp_kses( $experience_note[$index], self::$allowed_html ), ENT_QUOTES ) ), '"' );
					}
				}

				update_post_meta($args['resume_id'], '_experience_employer', json_encode($experience_employer, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_experience_job', json_encode($experience_job, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_experience_date', json_encode($experience_date, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_experience_note', json_encode($experience_note, JSON_UNESCAPED_UNICODE));
			}

			if( jm_get_resume_setting('enable_skill', '1') ) {
				$skill_name = $args['_skill_name'];
				$skill_percent = $args['_skill_percent'];

				if( isset( $skill_name ) && !empty( $skill_name ) ) {
					$skill_count = count( $skill_name );
					for( $index = 0; $index < $skill_count; $index++ ) {
						$skill_name[$index] = addcslashes( stripslashes( wp_kses( $skill_name[$index], $no_html ) ), '"' );
						$skill_percent[$index] = addcslashes( stripslashes( wp_kses( $skill_percent[$index], $no_html ) ), '"' );
					}
				}

				update_post_meta($args['resume_id'], '_skill_name', json_encode($skill_name, JSON_UNESCAPED_UNICODE));
				update_post_meta($args['resume_id'], '_skill_percent', json_encode($skill_percent, JSON_UNESCAPED_UNICODE));
			}

			do_action('noo_save_detail_resume',$args['resume_id']);
			return $args['resume_id'];
		}catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	private static function _save_job_alert($args=''){
		try{
			$defaults=array(
				'candidate_id'=>get_current_user_id(),
				'job_alert_id'=>'',
				'title'=>'',
				'keywords'=>'',
				'job_location'=>'',
				'job_category'=>'',
				'job_type'=>'',
				'status'=>'publish',
			);
			$args = wp_parse_args($args,$defaults);
			if( empty($args['candidate_id']) ) {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error');
				return false;
			}

			if( !Noo_Member::is_logged_in() ) {
				noo_message_add(__('Sorry, you can\'t post job_alert.', 'noo'), 'error');
				return false;
			}

			if( !empty($args['job_alert_id']) && $args['candidate_id'] != get_post_field( 'post_author', $args['job_alert_id'] ) ) {
				noo_message_add(__('Sorry, you can\'t edit this job alert.', 'noo'), 'error');
				return false;
			}

			$no_html = self::$no_html;

			$candidate_id = intval( $args['candidate_id'] );

			$job_alert = array(
				'post_title'     => wp_kses( $args['title'], $no_html ),
				'post_type'      => 'noo_job_alert',
				'post_status'	 => wp_kses( $args['status'], $no_html ),
				'post_author'	 => $candidate_id
			);
			if( empty($job_alert['post_title']) ) {
				noo_message_add(__('Your job alert needs a name.', 'noo'), 'error');
				return false;
			}

			if(!empty($args['job_alert_id'])){
				$job_alert['ID'] = intval( $args['job_alert_id'] );
				if( !Noo_Member::is_job_alert_owner( $candidate_id, $job_alert['ID'] ) ) {
					noo_message_add(__('Sorry, you can\'t edit this job alert.', 'noo'), 'error');
					return false;
				}
				$post_id = wp_update_post($job_alert);
			}else{
				$post_id = wp_insert_post($job_alert);
			}

			if(!is_wp_error($post_id)){
				update_post_meta($post_id, '_keywords', wp_kses( $args['keywords'], $no_html ));
				update_post_meta($post_id, '_job_location', json_encode(wp_kses( $args['job_location'], $no_html )));
				update_post_meta($post_id, '_job_category', json_encode(wp_kses( $args['job_category'], $no_html )));
				update_post_meta($post_id, '_job_type', wp_kses( $args['job_type'], $no_html ));

				$frequency = wp_kses( $args['frequency'], $no_html );
				$old_frequency = noo_get_post_meta( $post_id, '_frequency' );
				if( $frequency != $old_frequency ) {
					update_post_meta($post_id, '_frequency', $frequency);

					// Schedule new alert
					Noo_Job_Alert::set_alert_schedule( $post_id, $frequency );
				}

				do_action('noo_save_job_alert',$post_id);
			} else {
				noo_message_add(__('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error');
				return false;
			}


			do_action('noo_after_save_job_alert',$post_id);
			return $post_id;
		}catch (Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	public static function manage_resume_action(){
		if(!is_user_logged_in())
			return;
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'resume-manage-action' ) ) {
			$resume_id = '';
			if ( isset( $_REQUEST['resume_id'] ) ) {
				$resume_id = absint( $_REQUEST['resume_id'] );
			} elseif ( !empty( $_REQUEST['id'] ) ) {
				$resume_id = absint( $_REQUEST['id']);
			}

			if( empty( $resume_id ) ) {
				wp_die( __('There\'s an unknown error. Please retry or contact Administrator.', 'noo') );
			}
			try {
				switch ($action){
					case 'edit':
						break;
					case 'toggle_viewable':
						$resume = get_post($resume_id);
						if ( empty( $resume ) || $resume->post_type !== 'noo_resume' ) {
							noo_message_add( __( 'Can not find this resume.', 'noo' ), 'error' );
							break;
						}
						if ( !Noo_Member::is_resume_owner( get_current_user_id(), $resume_id ) ) {
							noo_message_add( __( 'You can not edit this resume.', 'noo' ), 'error' );
							break;
						}
						$current_viewable = noo_get_post_meta( $resume_id, '_viewable', '' );
						if( $current_viewable == 'yes' ) {
							update_post_meta( $resume_id, '_viewable', 'no' );
						} else {
							$max_viewable_resumes = absint( jm_get_resume_setting('max_viewable_resumes', 1) );

							if( $max_viewable_resumes > 0 ) {
								$viewable_resumes = absint( Noo_Resume::count_viewable_resumes( get_current_user_id() ) );

								if( $viewable_resumes >= $max_viewable_resumes ) {
									noo_message_add( sprintf( _n( 'You have already had %d viewable resume.', 'You have already had %d viewable resumes', $max_viewable_resumes, 'noo' ), $max_viewable_resumes ), 'error' );
								}

								update_post_meta( $resume_id, '_viewable', 'yes' );
							}
						}

						noo_message_add(sprintf(__('Resume visibility was changed successfully.','noo'),$deleted));
						do_action('manage_resume_action_viewable',$resume_id);
						wp_safe_redirect( Noo_Member::get_endpoint_url('manage-resume') );
						break;
					case 'delete':
						$resume = get_post($resume_id);
						if ( empty( $resume ) || $resume->post_type !== 'noo_resume' ) {
							noo_message_add( __( 'Can not find this resume.', 'noo' ), 'error' );
							break;
						}
						if ( !Noo_Member::is_resume_owner( get_current_user_id(), $resume_id ) ) {
							noo_message_add( __( 'You can not delete this resume.', 'noo' ), 'error' );
							break;
						}
						if ( !wp_delete_post($resume_id) ) {
							noo_message_add( __('Error in deleting.','noo'), 'error' );
						}

						noo_message_add( __('Resume was deleted successfully.','noo') );
						do_action('manage_resume_action_delete',$resume_id);
					break;
				}

				wp_safe_redirect( Noo_Member::get_endpoint_url('manage-resume') );
				die;
			}catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		}
	}

	public static function delete_bookmark_action(){
		if(!is_user_logged_in())
			return;
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bookmark-job-manage-action' ) ) {
			$job_id = '';
			if ( isset( $_REQUEST['job_id'] ) ) {
				$job_id = absint( $_REQUEST['job_id'] );
			}

			if( empty( $job_id ) ) {
				noo_message_add( __('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error' );
			}
			try {
				switch ($action){
					case 'delete_bookmark':
						$user_id = get_current_user_id();
						$job = get_post($job_id);
						if ( empty( $job ) || $job->post_type !== 'noo_job' ) {
							noo_message_add( __( 'Can not find this job.', 'noo' ), 'error' );
							break;
						}

						if ( jm_job_clear_bookmarked($user_id, $job_id) ) {
							noo_message_add( __( 'Bookmark cleared.', 'noo' ), 'success' );
						} else {
							noo_message_add(  __('There\'s an unknown error. Please retry or contact Administrator.', 'noo'), 'error' );
						}
						break;
				}

				wp_safe_redirect( Noo_Member::get_endpoint_url('bookmark-job') );
				die;
			}catch (Exception $e){
				throw new Exception($e->getMessage());
			}
		}
	}
	
	public static function current_action(){
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];
		
		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];
	}
	
	public static function save_company($args='', $user_id = null){
		$defaults=array(
			'company_id'=>'',
			'company_name'=>'',
			'company_desc'=>'',
			'company_logo'=>'',
			'cover_image'=>'',
			'_website'=>'',
			'_googleplus'=>'',
			'_twitter'=>'',
			'_facebook'=>'',
			'_linkedin'=>'',
			'_instagram'=>''
		);
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

		$no_html = self::$no_html;

		$company_name = isset( $args['company_name'] ) ? wp_kses( $args['company_name'], $no_html ) : '';
		// if( empty( $company_name ) ) {
		// 	noo_message_add(__('Company Name can\'t be blank.', 'noo'), 'error');
		// 	return false;
		// }
		$args = wp_parse_args($args,$defaults);
		$company_data = array(
			'post_title'     => $company_name,
			'post_content'   => wp_kses_post($args['company_desc']),
			'post_type'      => 'noo_company',
			'comment_status' => 'closed',
			'post_status'	 => 'publish',
			'post_author'	 => $user_id
		);
		
		if( !empty( $args['company_id'] ) && 'noo_company' == get_post_type( $args['company_id'] ) ){
			$company_data['ID'] = $args['company_id'];
			$company_id = wp_update_post($company_data);
		}else{
			$company_id = wp_insert_post($company_data);
		}
		
		if(!is_wp_error($company_id)){

			if( isset( $args['company_logo'] ) ) {
				$old_image = noo_get_post_meta( $company_id, '_logo' );
				if( $old_image != $args['company_logo'] ) {
					update_post_meta($company_id, '_logo', wp_kses( $args['company_logo'], $no_html ) );
					if( is_numeric( $old_image ) ) {
						wp_delete_attachment($old_image, true);
					}
				}
			}
			if( isset( $args['cover_image'] ) ) {
				$old_image = noo_get_post_meta( $company_id, '_cover_image' );
				if( $old_image != $args['cover_image'] ) {
					update_post_meta($company_id, '_cover_image', wp_kses( $args['cover_image'], $no_html ) );
					if( is_numeric( $old_image ) ) {
						wp_delete_attachment($old_image, true);
					}
				}
			}

			jm_company_save_custom_fields( $company_id, $args );

			if( !empty( $company_name ) ) {
				wp_update_user( array( 'ID' => $user_id, 'display_name' => $company_name ) );
			}

			if( empty( $args['company_id'] ) ) {
				update_user_meta($user_id, 'employer_company', $company_id);
				$package_data = jm_get_job_posting_info( $user_id );
				if( isset( $package_data['company_featured'] ) && $package_data['company_featured'] ) {
					update_post_meta( $company_id, '_company_featured', 'yes' );
				}

				do_action('noo_new_company',$company_id, $args);
			}
			
			do_action('noo_save_company',$company_id, $args);
			return $company_id;
		} else {
			noo_message_add( $company_id->get_error_message(), 'error' );
	
			return false;
		}
		return $company_id;
	}
}
Noo_Form_Handler::init();
endif;