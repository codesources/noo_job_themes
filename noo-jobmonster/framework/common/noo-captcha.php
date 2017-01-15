<?php

// --- Simple captcha ( Session )

/* -------------------------------------------------------
 * Create functions noo_show_captcha_image
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_show_captcha_image' ) ) :
	
	function noo_show_captcha_image() {
		$noo_job_linkedin = get_option('noo_job_linkedin');
		$md5_hash = md5(rand(0,999)); 
		$security_code = substr($md5_hash, 15, 5);
		$image_captcha = NOO_FRAMEWORK_FUNCTION_URI . '/noo-captcha.php?code=' . $security_code;

		?>
		<div class="form-group row">
			<div class="col-sm-3" style="margin-top: 3px;text-align: right;min-width: 130px;">
				<img class="security_code" data-security-code="<?php echo $security_code; ?>" src="<?php echo $image_captcha; ?>" alt="<?php echo $image_captcha; ?>" />
				<input type="hidden" name="security_code" value="<?php echo $security_code; ?>" />
			</div>
			<div class="col-sm-8">
				<input class="form-control security_input" type="text" name="noo_captcha" placeholder="<?php _e( 'Enter the text you see', 'noo' ); ?>" required />
			</div>
		</div>
		<?php

	}

	// add_action( 'noo_company_contact_form', 'noo_show_captcha_image' );
	$noo_member_setting = get_option('noo_member');
	$noo_job_linkedin = get_option('noo_job_linkedin');

	if ( isset($noo_member_setting['register_using_captcha']) ) :

		$recaptcha_key = jm_get_3rd_api_setting('google_recaptcha_key', '');
		if (!empty( $recaptcha_key )){
			add_action( 'noo_register_form', 'noo_recaptcha' );
		} else{
			add_action( 'noo_register_form', 'noo_show_captcha_image' );
		}


	endif;

	if ( isset($noo_job_linkedin['apply_job_using_captcha'])) :
		$recaptcha_key = jm_get_3rd_api_setting('google_recaptcha_key', '');
		if (!empty( $recaptcha_key )){
			add_action( 'after_apply_job_form', 'noo_recaptcha' );
		} else{
			add_action( 'after_apply_job_form', 'noo_show_captcha_image' );
		}

	endif;

endif;

/** ====== END noo_show_captcha_image ====== **/

/* -------------------------------------------------------
 * Create functions noo_captcha_validation
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_captcha_validation' ) ) :
	
	function noo_captcha_validation( $result = array(), $POST = array() ) {
		if( isset( $result['success'] ) && $result['success'] === false ) {
			return $result;
		}

		$noo_member_setting = get_option('noo_member');
		if ( isset($noo_member_setting['register_using_captcha']) ) {
			$captcha_code = isset( $POST['security_code'] ) ? strtolower( $POST['security_code'] ) : '';
			$captcha_input = isset( $POST['noo_captcha'] ) ? strtolower( $POST['noo_captcha'] ) : '';
			if ( $captcha_input !== $captcha_code ) {
				$result['success'] = false;
				$result['message'] = '<span class="error-response">'.__( 'Invalid confirmation code, please enter your code again.', 'noo' ).'</span>';
			}
		}
		
		return $result;
	}

	add_filter( 'noo_register_validation', 'noo_captcha_validation', 10, 2 );

endif;


// Google reCAPTCHA.

if( !function_exists('noo_recaptcha') ) :
	function noo_recaptcha(){
		$recaptcha_key = jm_get_3rd_api_setting('google_recaptcha_key', '');
		?>
		<div class="form-group row noo-recaptcha">
			<div class="col-sm-3">
			</div>
			<div class="col-sm-8">
				<div class="g-recaptcha" data-sitekey="<?php echo esc_html($recaptcha_key); ?>"></div>
			</div>
		</div>
	<?php
	}
endif;

if( !function_exists('noo_recaptcha_verification') ) :
	function noo_recaptcha_verification() {
		$recaptcha_secret_key = jm_get_3rd_api_setting('google_recaptcha_secret_key', '');

		$response = isset( $_POST['g-recaptcha-response'] ) ? esc_attr( $_POST['g-recaptcha-response'] ) : '';

		$remote_ip = $_SERVER["REMOTE_ADDR"];

		$request = wp_remote_get(
			'https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret_key.'&response=' . $response . '&remoteip=' . $remote_ip
		);

		$response_body = wp_remote_retrieve_body( $request );

		$result = json_decode( $response_body, true );

		return $result['success'];
	}
endif;

function noo_show_captcha_contact_form(){
	if (noo_get_option('noo_single_captcha_contact_form', 0)){
		$recaptcha_key = jm_get_3rd_api_setting('google_recaptcha_key', '');
		if (!empty( $recaptcha_key )){
			add_action( 'noo_company_contact_form', 'noo_recaptcha' );
		} else{
			add_action( 'noo_company_contact_form', 'noo_show_captcha_image' );
		}
	}
}

add_action('init', 'noo_show_captcha_contact_form');

