<?php

/**
 * Class NOO_JobMonster_MC4WP_Integration
 *
 * @ignore
 */

if( defined( 'WPSS_VERSION' ) ) :
	if( !function_exists( 'jm_wpss_register_form_append' ) ) {
		function jm_wpss_register_form_append() {
			if( is_user_logged_in() ) { return; }
			global $spamshield_options,$wpss_reg_form_complete;
			if( empty( $spamshield_options ) ) { $spamshield_options = get_option('spamshield_options'); }
			rs_wpss_update_session_data($spamshield_options);

			/* Check if registration spam shield is disabled, or this function has already been run (usually by 3rd party plugin) */
			if( !empty( $spamshield_options['registration_shield_disable'] ) || !empty( $wpss_reg_form_complete ) ) { return; }

			/* BYPASS - HOOK */
			$reg_form_bypass = apply_filters( 'wpss_registration_form_bypass', FALSE );
			if( !empty( $reg_form_bypass ) ) { return; }
			
			if( TRUE === WPSS_COMPAT_MODE || defined( 'WPSS_SOFT_COMPAT_MODE' ) ) {
				global $wpss_ao_active; $ao_noop_open = $ao_noop_close = '';
				if( empty( $wpss_ao_active ) ) { $wpss_ao_active = rs_wpss_is_plugin_active( 'autoptimize/autoptimize.php' ); }
				if( !empty( $wpss_ao_active ) ) { $ao_noop_open = '<!--noptimize-->'; $ao_noop_close = '<!--/noptimize-->'; }
				$wpss_key_values 		= rs_wpss_get_key_values();
				$wpss_js_key 			= $wpss_key_values['wpss_js_key'];
				$wpss_js_val 			= $wpss_key_values['wpss_js_val'];
				echo WPSS_EOL."\t".$ao_noop_open.'<script type=\'text/javascript\'>'.WPSS_EOL."\t".'/* <![CDATA[ */'.WPSS_EOL."\t".WPSS_REF2XJS.'=escape(document[\'referrer\']);'.WPSS_EOL."\t".'hf3N=\''.$wpss_js_key.'\';'.WPSS_EOL."\t".'hf3V=\''.$wpss_js_val.'\';'.WPSS_EOL."\t".'document.write("<input type=\'hidden\' name=\''.WPSS_REF2XJS.'\' value=\'"+'.WPSS_REF2XJS.'+"\' /><input type=\'hidden\' name=\'"+hf3N+"\' value=\'"+hf3V+"\' />");'.WPSS_EOL."\t".'/* ]]> */'.WPSS_EOL."\t".'</script>'.$ao_noop_close;
			}
			
			$reg_form_append = WPSS_EOL."\t".'<noscript><input type="hidden" name="'.WPSS_JSONST.'" value="NS3" /></noscript>'.WPSS_EOL."\t";
			$wpss_js_disabled_msg 	= __( 'Currently you have JavaScript disabled. In order to register, please make sure JavaScript and Cookies are enabled, and reload the page.', WPSS_PLUGIN_NAME );
			$wpss_js_enable_msg 	= __( 'Click here for instructions on how to enable JavaScript in your browser.', WPSS_PLUGIN_NAME );
			$reg_form_append .= '<noscript><p><strong>'.$wpss_js_disabled_msg.'</strong> <a href="http://enable-javascript.com/" rel="nofollow external" >'.$wpss_js_enable_msg.'</a><br /><br /></p></noscript>'.WPSS_EOL."\t";

			/* If need to add anything else to registration area, start here */


			/* FORM COMPLETE */
			$wpss_reg_form_complete = TRUE;
			
			echo $reg_form_append;
		}
		add_action( 'noo_register_form', 'jm_wpss_register_form_append' );
		add_action( 'noo_register_social_form', 'jm_wpss_register_form_append' );
	}

	if( !function_exists( 'jm_spamshield_registration_errors') ) {
		function jm_spamshield_registration_errors( $errors ) {
			$action = isset( $_POST['action'] ) ? $_POST['action'] : '';
			if ( 'noo_ajax_register' == $action && $errors->get_error_code() ) {
				$error_codes = $errors->get_error_codes();
				$bypass_error_codes = array(
						'empty_first_name',
						'empty_last_name',
						'empty_disp_name',
					);
				$bypass_error_codes = apply_filters( 'jm_spamshield_bypass_codes', $bypass_error_codes );
				foreach ( $bypass_error_codes as $code ) {
					if( in_array( $code, $error_codes ) ) {
						$errors->remove( $code );
					}
				}
			}

			return $errors;
		}

		add_filter( 'registration_errors', 'jm_spamshield_registration_errors', 10000 );
	}

endif;