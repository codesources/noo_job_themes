<?php

/**
 * Class 
 *
 * @ignore
 */


if( is_plugin_active( 'wordpress-social-login/wp-social-login.php' ) ) :
	if( !function_exists( 'jm_wsl_default_extra_fields' ) ) {
		function jm_wsl_default_extra_fields( $extra_fields = false ) {
			return 1;
		}

		add_filter( 'pre_option_wsl_settings_bouncer_profile_completion_hook_extra_fields', 'jm_wsl_default_extra_fields' );
	}

	if( !function_exists( 'jm_wsl_login_form' ) ) {
		function jm_wsl_login_form() {
			do_action( 'wordpress_social_login' );
		}

		add_action( 'noo_login_form_start', 'jm_wsl_login_form' );
	}
endif;

if( !function_exists('jm_register_form_extra_fields') ) :
	function jm_register_form_extra_fields() {
		$allow_register = Noo_Member::get_setting('allow_register', 'both');
		if( $allow_register == 'both' ) :
		?>
			<strong class="register-text"><?php _e('Who you are', 'noo'); ?></strong>
			<div class="form-group row">
				<div class="col-sm-9">
					<div class="form-control-flat">
						<label class="radio" for="user_role_1" ><input id="user_role_1" type="radio" name="jm_register_user_role" value="employer" checked=""><i></i><?php esc_html_e('I\'m an employer looking to hire','noo')?></label><br/>
						<label class="radio" for="user_role_2" ><input id="user_role_2" type="radio" name="jm_register_user_role" value="candidate" checked=""><i></i><?php esc_html_e('I\'m a candidate looking for a job','noo')?></label>
					</div>
				</div>
			</div>
		<?php elseif( $allow_register == 'employer' || $allow_register == 'candidate' ) : ?>
			<input type="hidden" name="jm_register_user_role" value="<?php echo $allow_register; ?>">
		<?php
		endif;
	}

	add_action( 'register_form', 'jm_register_form_extra_fields' );
endif;

if( !function_exists('jm_register_set_user_role') ) :
	function jm_register_set_user_role( $user_id ) {
		if( isset( $_POST['jm_register_user_role'] ) && !empty( $_POST['jm_register_user_role'] ) ) {
			$role = $_POST['jm_register_user_role'];
			$allowed_roles = apply_filters( 'noo_allowed_register_role', array( Noo_Member::CANDIDATE_ROLE, Noo_Member::EMPLOYER_ROLE ) );
			if( in_array( $role, $allowed_roles ) ){
				$user_id = wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
			}
		}
	}

	add_action( 'register_new_user', 'jm_register_set_user_role' );
	add_action( 'wsl_hook_process_login_after_wp_insert_user', 'jm_register_set_user_role' );
endif;