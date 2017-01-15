<?php
/**
 * Plugin Name: Email Confirmation
 * Description: Require email confirmation for user registration.
 * Part of the code is base on class EmailConfirmation from Cedric Ruiz
 * ( https://gist.github.com/elclanrs/6516451 )
 */

if ( !class_exists( 'JM_EmailConfirmation' ) ) :
	class JM_EmailConfirmation {
		const PREFIX = 'email-confirmation-';

		public function __construct() {

			if( self::get_setting('email_confirmation', false) ) {
				add_filter( 'noo_register_user_data', array( $this, 'add_redirect_link' ), 10, 2 );
				add_filter( 'noo_registration_errors', array( $this, 'require_confirmation' ), 10, 2 );
				add_filter( 'noo_registration_errors', array( $this, 'bypass_verifying_error' ), 10, 2 );

				add_filter( 'noo_register_error_result', array( $this, 'add_redirect_to_member' ), 99, 2 );

				add_filter( 'noo-member-page-endpoint', array( $this, 'member_page_endpoint' ) );
				add_filter( 'noo-member-not-login-shortcode', array( $this, 'verify_email_shortcode' ), 10, 2 );

				add_filter( 'jm_email_template_field', array( $this, 'email_template_field') );
				add_action( 'jm_setting_email_template_other', array( $this, 'email_template_settings') );
			}

			if( is_admin() ) {
				add_action( 'noo_setting_member_fields', array( $this, 'setting_fields') );
			}
		}

		public function add_redirect_to_member( $result = array(), $errors = null ) {
			if( self::get_setting('email_confirmation', false) ) {
				if ( is_wp_error( $errors ) && $errors->get_error_code() == 'email_confirmation' ) {
					$result['redirecturl'] = Noo_Member::get_member_page_url();
				}
			}

			return $result;
		}

		public function add_redirect_link( $user_args = array(), $POST = array() ) {
			if( self::get_setting('email_confirmation', false) ) {
				$redirect_to = '';
				if( $user_args['role'] == Noo_Member::CANDIDATE_ROLE )
					$redirect_to = Noo_Member::get_candidate_profile_url();
				elseif( $user_args['role'] == Noo_Member::EMPLOYER_ROLE )
					$redirect_to = Noo_Member::get_company_profile_url();

				$redirect_to = isset($_POST['redirect_to']) && !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : $redirect_to;
				$filter_tag = 'noo_register_redirect' . ( !empty($user_args['role']) ? '_'.$user_args['role'] : '' );

				$user_args['redirect_to'] = apply_filters($filter_tag, $redirect_to);
			}

			return $user_args;
		}

		public function require_confirmation( $errors = null, $new_user = array() ) {
			if ( is_wp_error( $errors ) && $errors->get_error_code() ) {
				return $errors;
			}

			if( self::get_setting('email_confirmation', false) && !empty( $new_user['user_email'] ) ) {

				// No confirm for social login
				if( isset( $new_user['using'] ) && !empty( $new_user['using'] ) ) {
					return $errors;
				}

				$token = $this->_get_token( $new_user );

				if ( is_multisite() )
					$blogname = $GLOBALS['current_site']->site_name;
				else
					$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				$site_url = esc_url(home_url(''));

				// user email
				$to = $new_user['user_email'];
				$verify_link = esc_url( add_query_arg( array( 'token' => $token ), Noo_Member::get_endpoint_url('verify-email') ) );


				$subject = jm_et_get_setting('verify_email_subject');

				$array_subject = array(
					'[site_name]' => $blogname,
					'[site_url]' => $site_url,
					'[user_name]' => isset( $new_user['display_name'] ) ? $new_user['display_name'] : $new_user['user_login'],
					'[user_email]' => $new_user['user_email'],
				);
				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$array_message = array(
					'[verify_link]' => $verify_link,
					'[user_name]' => isset( $new_user['display_name'] ) ? $new_user['display_name'] : $new_user['user_login'],
					'[user_email]' => $new_user['user_email'],
					'[site_name]' => $blogname,
					'[site_url]' => $site_url,
				);

				$message = jm_et_get_setting('verify_email_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);


				$result = noo_mail($to, $subject, $message,array(),'noo_register_confirm_email');
				if( $result ) {
					noo_message_add( __( 'Please confirm your email address to complete your registration.', 'noo' ), 'error' );
					$errors->add( 'email_confirmation', __( 'Please confirm your email address to complete your registration.', 'noo' ) );
				}
			}

			return $errors;
		}

		public function bypass_verifying_error( $errors = array(), $new_user = array() ) {
			if( isset( $new_user['email-verifying'] ) && !empty( $new_user['email-verifying'] ) ) {

				// Bypass error code form WP-SpamShield plugin
				if ( defined( 'WPSS_VERSION' ) && is_wp_error( $errors ) && $errors->get_error_code() ) {
					$error_codes = $errors->get_error_codes();
					$bypass_error_codes = array(
							'empty_first_name',
							'empty_last_name',
							'empty_disp_name',
							'jsck_error'
						);
					$bypass_error_codes = apply_filters( 'jm_spamshield_bypass_codes', $bypass_error_codes );
					foreach ( $bypass_error_codes as $code ) {
						if( in_array( $code, $error_codes ) ) {
							$errors->remove( $code );
						}
					}
				}
			}
			
			return $errors;
		}

		public function member_page_endpoint( $endpoints = array() ) {
			$endpoints = array_merge( $endpoints, array(
				'verify-email' => 'verify-email'
			) );

			return $endpoints;
		}

		public function verify_email_shortcode( $html = '', $query_vars = array() ) {
			if( isset( $query_vars['verify-email']) ){
				$user_args = false;
				if( isset( $_GET['token'] ) ) {
					$user_args = $this->_check_token( $_GET['token'] );
					if( !empty( $user_args ) && is_array( $user_args ) ) {
						$user_args['email-verifying'] = true;
					} else {
						noo_message_add( __('There was a problem verifying your email address. Please try registering again.', 'noo'), 'error' );
						wp_safe_redirect(Noo_Member::get_member_page_url());
						exit();
					}
				} else {
					noo_message_add( __('Something went wrong with your verification. Please retry with the correct, complete verification link, or try registering again.', 'noo'), 'error' );
					wp_safe_redirect(Noo_Member::get_member_page_url());
					exit();
				}

				if( !empty( $user_args ) ) {
					remove_filter( 'noo_registration_errors', array( $this, 'require_confirmation' ), 10, 2 );
					$errors = Noo_Form_Handler::_register_new_user( $user_args );
					add_filter( 'noo_registration_errors', array( $this, 'require_confirmation' ), 10, 2 );

					if ( is_wp_error( $errors ) && $errors->get_error_code() ) {
						noo_message_add( $errors->get_error_message(), 'error' );
						wp_safe_redirect(Noo_Member::get_member_page_url());
						exit();
					} else {
						$redirect_to = isset($user_args['redirect_to']) && !empty($user_args['redirect_to']) ? $user_args['redirect_to'] : $redirect_to;
						$filter_tag = 'noo_register_redirect' . ( !empty($user_args['role']) ? '_'.$user_args['role'] : '' );
						
						noo_message_add( __('Thank you for verifying your email! Your registration is now complete.', 'noo') );
						wp_safe_redirect( esc_url( apply_filters($filter_tag, $redirect_to) ) );
						exit();
					}
				} else {
					noo_message_add( __('There was a problem verifying your email address. Please try registering again.', 'noo'), 'error' );
					wp_safe_redirect(Noo_Member::get_member_page_url());
					exit();
				}
			}

			return $html;
		}

		private function _verify_email() {
			if( isset( $_GET['token'] ) ) {
				$data = $this->_check_token( $_GET['token'] );

				return $data;
			}
		}

		private function _get_token( $userData = array() ) {
			$token = sha1(uniqid());

			$oldData = get_option(self::PREFIX .'data');
			$oldData = !empty( $oldData ) ? $oldData : array();
			$data = array();
			$data[$token] = $userData;
			update_option(self::PREFIX .'data', array_merge($oldData, $data));

			return $token;
		}

		private function _check_token($token = '') {
			if( empty( $token ) ) return false;

			$data = get_option(self::PREFIX .'data');
			$userData = false;

			if (isset($data[$token])) {
				$userData = $data[$token];
				unset($data[$token]);
				update_option(self::PREFIX .'data', $data);
			}

			return $userData;
		}

		public static function get_setting($id = null ,$default = null){
			$noo_member_setting = get_option('noo_member');
			if(isset($noo_member_setting[$id]))
				return $noo_member_setting[$id];
			return $default;
		}

		public function setting_fields() {
			?>
			<tr>
				<th>
					<?php _e('Require Email Confirmation','noo')?>
				</th>
				<td>
					<input id="email_confirmation" type="checkbox" name="noo_member[email_confirmation]" value="1" <?php checked( self::get_setting('email_confirmation', false) );?> />
					<small><?php echo __('Newly registered users will be required to confirm their email addresses before accessing their account. Please note that this will delay registrations.', 'noo' ); ?></small>
				</td>
			</tr>

			<?php
		}

		public function email_template_field( $email_template ) {
			$verify_email_template = array(
	            'verify_email_subject_default' => __('[[site_name]] Verify your email address', 'noo'),
	            'verify_email_subject' => array(
	            	'[user_name]' => __('inserting username', 'noo'),
	                '[user_email]' => __('inserting user email', 'noo'),
	            ),
	            'verify_email_content_default' => __('Dear [user_name],<br/>
Thank you for registering an account on [site_name]. Please <a href="[verify_link]">click here</a> or or use the following copy paste link to confirm this email address.<br/>
[verify_link]
<br/><br/>
Best regards,<br/>
[site_name]','noo'),
	            'verify_email_content' => array(
	            	'[user_name]' => __('inserting username', 'noo'),
	                '[user_email]' => __('inserting user email', 'noo'),
	                '[verify_link]' => __('inserting verify link', 'noo'),
	            ),
			);
			$email_template = array_merge( $email_template, $verify_email_template );

			return $email_template;
		}

		public function email_template_settings() {
	        $verify_email_activated = jm_et_get_setting('verify_email_activated', 1);
	        $verify_email_subject = jm_et_get_setting('verify_email_subject');
	        $verify_email_content = jm_et_get_setting('verify_email_content');
			?>
			<hr/>
            <h3><?php _e('Verify user register email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[verify_email_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($verify_email_subject) ? $verify_email_subject : ''; ?>">
                        <?php jm_et_render_field('verify_email_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($verify_email_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[verify_email_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('verify_email_content', true, false); ?>
                    </td>
                </tr>
                </tbody>
            </table>
			<?php
		}
	}

	new JM_EmailConfirmation();
endif;