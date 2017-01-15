<?php
/**
 * Social Login
 */

class JM_SocialLogin {

	public function __construct() {

		if( self::is_social_login_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_enqueue_script' ) );
			add_action( 'noo_user_menu_login_dropdown', array( $this, 'user_menu_login_dropdown' ) );
			add_action( 'noo_collapsed_user_menu_login_after', array( $this, 'collapsed_user_menu_login_after' ) );

			add_action( 'wp_ajax_check_login', array( $this, 'check_login' ) );
			add_action( 'wp_ajax_nopriv_check_login', array( $this, 'check_login' ) );

			add_action( 'wp_ajax_nopriv_create_user', array( $this, 'create_user' ) );

			add_action( 'noo_login_form_start', array( $this, 'login_form' ) );

			add_filter( 'noo_register_user_data', array( $this, 'register_user_data' ), 10, 2 );
			add_action( 'noo_new_user_registered', array( $this, 'new_user_registered' ), 10, 2 );
			
			$setting_linkedin = jm_get_3rd_api_setting('linkedin_app_id', '');
			if( !empty( $setting_linkedin ) ) {
				add_action( 'wp_head', array( 'Noo_Job', 'load_linkedin_script' ) );
			}

			if(!Noo_Member::is_logged_in()){
				add_action('wp_footer', array( $this,'modal_register_social' ), 100 );
			}

			// -- Theme's hooks
			add_filter( 'noo-member-page-endpoint', array( $this, 'linkedin_verify_endpoint' ) );
		}

		if( is_admin() ) {
			add_action( 'noo_setting_member_fields', array( $this, 'setting_fields') );
			add_action( 'jm_setting_3rd_api_fields', array( $this, 'setting_api_fields') );

			add_action( 'admin_init', array( $this, 'check_social_login_settings' ) );
		}
	}

	public function check_social_login_settings() {
		if( self::is_social_login_enabled() ) {
			// Check Facebook setting
			$facebook_app_id = jm_get_3rd_api_setting('facebook_app_id', '');
			if( !self::is_facebook_login_enabled() && !empty( $facebook_app_id) ) {
				add_action('admin_notices', array( $this, 'facebook_login_setting_notice' ) );
			}

			// Check Google setting
			$google_app_id = jm_get_3rd_api_setting('google_client_id', '');
			if( !self::is_google_login_enabled() && !empty( $google_app_id) ) {
				add_action('admin_notices', array( $this, 'google_login_setting_notice' ) );
			}

			// Check LinkedIn setting
			$linkedin_app_id = jm_get_3rd_api_setting('linkedin_app_id', '');
			if( !self::is_linkedin_login_enabled() && !empty( $linkedin_app_id) ) {
				add_action('admin_notices', array( $this, 'linkedin_login_setting_notice' ) );
			}
		}
	}

	public function facebook_login_setting_notice() {
	    ?>
		    <div class="error notice-error">
		    	<p>Please check <strong><a href="<?php echo jm_setting_page_url('member'); ?>#social-login">the settings</a></strong> to continue using Facebook login.</p>
		    </div>
	    <?php
	}

	public function google_login_setting_notice() {
	    ?>
		    <div class="error notice-error">
		    	<p>Please check <strong><a href="<?php echo jm_setting_page_url('member'); ?>#social-login">the settings</a></strong> to continue using Google login.</p>
		    </div>
	    <?php
	}

	public function linkedin_login_setting_notice() {
	    ?>
		    <div class="error notice-error">
		    	<p>Please check <strong><a href="<?php echo jm_setting_page_url('member'); ?>#social-login">the settings</a></strong> to continue using LinkedIn login.</p>
		    </div>
	    <?php
	}

	public function load_enqueue_script() {

		$setting_google = jm_get_3rd_api_setting('google_client_id', '');
		$setting_linkedin = jm_get_3rd_api_setting('linkedin_app_id','');

		if( !empty( $setting_google ) ) {
			wp_register_script( 'api-google', 'https://apis.google.com/js/api:client.js');
			wp_enqueue_script('api-google');
		}

		if( !SCRIPT_DEBUG ) {
			wp_register_script( 'login-social', NOO_ASSETS_URI . '/js/min/noo.login.social.min.js', array( 'jquery'), null, true );
		} else {
			wp_register_script( 'login-social', NOO_ASSETS_URI . '/js/noo.login.social.js', array( 'jquery'), null, true );
		}
		
		
		$noo_social = array(
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'security'				=> wp_create_nonce( 'jm-social-login' ),
			'allow'                 => Noo_Member::get_setting('allow_register', 'both'),
			'google_client_id'      => jm_get_3rd_api_setting('google_client_id'),
			'google_client_secret'  => jm_get_3rd_api_setting('google_client_secret'),
			'facebook_api'          => jm_get_3rd_api_setting('facebook_app_id'),
			'facebook_secret'       => jm_get_3rd_api_setting('facebook_app_secret'),
			'msgLoginSuccessful'    => '<span class="success-response">' . __( 'Login successful, redirecting...','noo' ) . '</span>',
			'msgFacebookModalTitle' => __( 'Sign Up Via Facebook','noo' ),
			'msgGoogleModalTitle'   => __( 'Sign Up Via Google','noo' ),
			'msgLinkedInModalTitle' => __( 'Sign Up Via LinkedIn','noo' ),
			'msgHi'                 => __( 'Hi, ','noo' ),
			'msgServerError'        => '<span class="error-response">' . __( 'There\'s a problem when processing your data. Please try again or contact Administrator!','noo' ) . '</span>',
			'msgFBMissingEmail'     => '<span class="error-response">' . __( 'You need to provide your email! You can not login if your Facebook doesn\'t share the email.','noo' ) . '</span>',
			'msgMissingAppID'       => '<span class="error-response">' . __( 'The App cannot get user\'s information, please check your App ID installation!','noo' ) . '</span>',
			'msgFBUserCanceledLogin' => __( 'User canceled login or did not fully authorize.','noo' ),
		);
		wp_localize_script( 'login-social', 'nooSocial', $noo_social );

		wp_enqueue_script('login-social');
	}

	public function load_meta_login() {
		?>
		<script type="text/javascript" src="http://platform.linkedin.com/in.js">
			api_key: <?php echo jm_get_3rd_api_setting('linkedin_app_id','') ?>
			authorize: true
		</script>
		<?php
	}
	
	public function modal_register_social(){
		$prefix = uniqid();
		?>
		<div class="memberModalRegisterSocial modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $prefix ?>_memberModalRegisterLabel" aria-hidden="true">
			<div class="modal-dialog modal-member">
		    	<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="<?php echo $prefix ?>_memberModalRegisterLabel"><?php esc_html_e('Sign Up','noo')?></h4>
				     </div>
				      <div class="modal-body">
				        <?php noo_get_layout("register-social-form"); ?>
				      </div>
				</div>
			</div>
		</div>
		<?php
	}

	public function linkedin_verify_endpoint( $endpoints = array() ) {
		$endpoints = array_merge( $endpoints, array(
			'linkedin-verify' => 'linkedin-verify',
		) );

		return $endpoints;
	}

	public function check_login() {
		// ==== Checking library Facebook
			if( !class_exists( 'Facebook') ) {
				include_once dirname( __FILE__ ) . '/facebook/facebook.php';
			}

		// ==== Checking library Linkedin
			if( !class_exists( 'oauth_client_class') ) {
				// include_once dirname( __FILE__ ) . '/LinkedIn/linkedin.php';
				// include_once dirname( __FILE__ ) . '/LinkedIn/oauth_client.php';
			}	

		$response = array();

		if( !isset($_POST['using'] ) || empty( $_POST['using'] ) ) {
			$response['status']  = 'error';
			$response['message'] = esc_html__('There\'s a problem, please reload and retry', 'noo');
			wp_send_json( $response );
		}

		$user_email = '';
		switch ( $_POST['using'] ) {
			case 'fb':
				$appid 		= jm_get_3rd_api_setting('facebook_app_id');
				$appsecret  = jm_get_3rd_api_setting('facebook_app_secret');
				$facebook   = new Facebook(array(
					'appId'  => $appid,
					'secret' => $appsecret,
					'cookie' => TRUE,
				));
				$fbuser = $facebook->getUser();
				if ($fbuser) {
					try {
						$user_profile = $facebook->api('/me?fields=name,email');
					} catch (Exception $e) {
						echo $e->getMessage();
						exit();
					}

					$user_fbid	= $fbuser;
					$user_email = isset( $user_profile["email"] ) ? $user_profile['email'] : '';
				}	
				break;

			case 'linkedin':

				// $linkedin = new LinkedIn('75w7fkt3gey1rb', 'JYN9OebLPJWBDWhB', 'http://wpthemes.noothemes.com' );
			   //  $config['base_url']             =   'http://wpthemes.noothemes.com';
			   //  $config['callback_url']         =   'http://wpthemes.noothemes.com';
			   //  $config['linkedin_access']      =   '75w7fkt3gey1rb';
			   //  $config['linkedin_secret']      =   'JYN9OebLPJWBDWhB';

			   //  include_once "linkedin.php";
			   
			    
			   //  # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
			   //  $linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
			   //  //$linkedin->debug = true;

			   // if (isset($_REQUEST['oauth_verifier'])){
			   //      $_SESSION['oauth_verifier']     = $_REQUEST['oauth_verifier'];

			   //      $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
			   //      $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
			   //      $linkedin->getAccessToken($_REQUEST['oauth_verifier']);

			   //      $_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
			   //      header("Location: " . $config['callback_url']);
			   //      exit;
			   // }
			   // else{
			   //      $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
			   //      $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
			   //      $linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
			   // }


			   //  # You now have a $linkedin->access_token and can make calls on behalf of the current member
			   //  $xml_response = $linkedin->getProfile("~:(id,first-name,last-name,headline,picture-url)");

			   //  echo '<pre>';
			   //  echo 'My Profile Info';
			   //  echo $xml_response;
			   //  echo '<br />';
			   //  echo '</pre>';

			   //  wp_die();
					if( !isset($_POST['id'] ) || empty( $_POST['id'] ) ) {
						$response['status']  = 'error';
						$response['message'] = esc_html__('Can not get email from your social account', 'noo');
						wp_send_json( $response );
					}

					$user = get_user_by( 'email', $_POST['id'] );

					if ( $user ) :

						wp_set_current_user($user->ID, $user->user_login);
						wp_set_auth_cookie($user->ID);
						$response['status']  = 'success';
						$response['message'] = esc_html__('Login successfully.', 'noo');
						wp_send_json( $response );

					else :

						$response['status']  = 'not_user';
						$response['message'] = esc_html__('User not being registered.', 'noo');
						wp_send_json( $response );

					endif;

				break;

				case 'gg':
					
					$id_token = $this->noo_check_token( 'google', $_POST['id_token'] );
					if ( $id_token ) {
						if ( (string)$_POST['id'] != (string)$id_token ) {
							$response['status']  = 'error';
							$response['message'] = esc_html__('The token was not verified. Please check again.', 'noo');
							wp_send_json( $response );
						} else {
							$user_email = $id_token;
						}
					} else {
						$response['status']  = 'error';
						$response['message'] = esc_html__('Token does not exist', 'noo');
						wp_send_json( $response );
					}

					break;
		}

		$user_email = !empty($user_email) ? $user_email : !empty($_POST['id']) ? esc_attr($_POST['id']) : '';
		if( empty( $user_email ) ) :

			$response['status']  = 'not_user';
			$response['message'] = esc_html__('User not being registered.', 'noo');
			wp_send_json( $response );

		endif;

		$user = get_user_by( 'email', $user_email );

		if( $user ) :
			
			$user_id             = $user->ID;
			wp_set_auth_cookie( $user_id, true );
			$response['status']  = 'success';
			$response['message'] = esc_html__('Login successfully.', 'noo');
			wp_send_json( $response );

		else :

			$response['status']  = 'not_user';
			$response['message'] = esc_html__('User not being registered.', 'noo');
			wp_send_json( $response );

		endif;
	}

	public static function noo_check_token( $social, $token ) {

		if ( $social == 'google' ) :
			$ch = curl_init();
			$url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $token;
			curl_setopt($ch, CURLOPT_URL, $url);
			$Headers   = array();
			$Headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0';
			$Headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
			$Headers[] = 'Accept-Language: vi-VN,vi;q=0.8,en-US;q=0.5,en;q=0.3';
			$Headers[] = 'Accept-Encoding: gzip, deflate';
			$Headers[] = 'DNT: 1';
			$Headers[] = 'Connection: keep-alive';
			$Headers[] = 'Cache-Control: max-age=0';
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 400);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$data = curl_exec($ch);
			$HttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			// if ( $HttpCode != 400 ) {
				$response = (array) json_decode($data);
				return $response['email'];
			// } else {
			// 	return false;
			// }

		endif;
	}

	public static function get_setting($id = null ,$default = null){
		$noo_member_setting = get_option('noo_member');
		if(isset($noo_member_setting[$id]))
			return $noo_member_setting[$id];
		return $default;
	}

	public function create_user() {

		// ===== Verify Ajax Request
			check_ajax_referer( 'jm-social-login', 'security' );

		// ===== VAR
			$response   = array();
			$user_email = $_POST['id'];
			$user_id    = username_exists( $user_email );

		// ===== END VAR

		// ===== Process
			if ( !$user_id and email_exists($user_email) == false ) :

				/**
				 * Check user rights
				 */
				if ( empty( $_POST['capabilities'] ) || $_POST['capabilities'] != 'employer' && $_POST['capabilities'] != 'candidate' ) :

					/**
					 * [$response description]
					 * @var array
					 */
					$response['status']  = 'error';
					$response['message'] = esc_html__( 'Please choose your account type as Employer or Candidate','noo' );

					wp_send_json( $response );

				endif;

				/**
				 * [$random_password create auto random password]
				 * @var [md5]
				 */
				$random_password = wp_generate_password( 12, false );

				/**
				 * [$userdata list data user]
				 * @var array
				 */
				$userdata = array(
					'user_login'    =>  sanitize_user( $user_email ),
					'user_email'    =>  sanitize_user( $user_email ),
					'display_name'  =>  $_POST['name'],
					'user_pass'     =>  $random_password,
					'role'			=>  $_POST['capabilities']
				);

				/**
				 * [$user_id Create new user]
				 * @var [type]
				 */
				$user_id = wp_insert_user( $userdata ) ;

				/**
				 * [$user Get info user by id]
				 * @var [type]
				 */
				$user    = get_user_by( 'id', $user_id ); 

				/**
				 * Checking info user in database
				 */
				if( !is_wp_error( $user ) ) :
					// -- update user meta
						// if ( isset($_POST['birthday'] ) ) update_user_meta( $user_id, 'birthday', $_POST['birthday'] );
						// if ( isset($_POST['address'] ) ) update_user_meta( $user_id, 'address', $_POST['address'] );
						if ( !empty( $_POST['using'] ) ) :

							if ( $_POST['using'] == 'google' ) :

								update_user_meta( $user_id, 'id_google', $_POST['userid'] );

							elseif ( $_POST['using'] == 'facebook' ) :

								update_user_meta( $user_id, 'id_facebook', $_POST['userid'] );

							elseif ( $_POST['using'] == 'linkedin' ) :

								update_user_meta( $user_id, 'id_linkedin', $_POST['userid'] );

							endif;

							$response['using_login'] = $_POST['using'];

						endif;

					// -- Set autologin
						wp_set_current_user($user_id, $user->user_login);
						wp_set_auth_cookie($user_id);
						do_action( 'wp_login', $user->user_login );
					
					$response['status']  = 'success';
					$response['message'] = esc_html__( 'Create user successfully.', 'noo' );
				
				else :

					$response['status']  = 'error';
					$response['message'] = $user->get_error_message();

				endif;
			
			else :

				$response['status']  = 'error';
				$response['message'] = esc_html__( 'Email already exists.', 'noo' );

			endif;

		// ===== END Process
		wp_send_json( $response );
	}

	public function user_menu_login_dropdown() {
		$facebook = self::is_facebook_login_enabled();
		$google = self::is_google_login_enabled();
		$linkedin = self::is_linkedin_login_enabled();

		if ( self::is_social_login_enabled() && ( $facebook || $google || $linkedin ) ) : ?>
			<ul class="sub-menu login-socical" style="display: none;">
				<?php 
					$id_facebook = uniqid();
					$id_google = uniqid();
					$id_linkedin = uniqid();
				?>
				<?php if( $facebook ) : ?>
					<li class="button_socical fb">
						<i data-id="<?php echo $id_facebook; ?>" id="<?php echo $id_facebook; ?>" class="fa fa-facebook-square"></i>
						<em data-id="<?php echo $id_facebook; ?>" class="fa-facebook-square"><?php _e('Login with Facebook', 'noo'); ?></em>
					</li>

				<?php endif; ?>
				<?php if( $google ) : ?>
					<li class="button_socical gg">
						<i data-id="<?php echo $id_google; ?>" id="i_<?php echo $id_google; ?>" class="fa fa-google-plus"></i>
						<em data-id="<?php echo $id_google; ?>" id="<?php echo $id_google; ?>" class="fa-google-plus"><?php _e('Login with Google', 'noo'); ?></em>
					</li>
				<?php endif; ?>
				<?php if( $linkedin ) : ?>
					<li class="button_socical linkedin">
						<i data-id="<?php echo $id_linkedin; ?>" id="<?php echo $id_linkedin; ?>" class="fa fa-linkedin-square"></i>
						<em data-id="<?php echo $id_linkedin; ?>" class="fa-linkedin-square"><?php _e('Login with LinkedIn', 'noo'); ?></em>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif;
	}

	public function collapsed_user_menu_login_after() {
		$facebook = self::is_facebook_login_enabled();
		$google = self::is_google_login_enabled();
		$linkedin = self::is_linkedin_login_enabled();

		$id_facebook = uniqid();
		$id_google = uniqid();
		$id_linkedin = uniqid();
		if ( self::is_social_login_enabled() && ( $facebook || $google || $linkedin ) ) : ?>
			<?php if( $facebook ) : ?>
				<li class="button_socical fb">
					<i data-id="<?php echo $id_facebook; ?>" id="<?php echo $id_facebook; ?>" class="fa fa-facebook-square"></i>
					<em data-id="<?php echo $id_facebook; ?>" class="fa-facebook-square"><?php _e('Login with Facebook', 'noo'); ?></em>
				</li>

			<?php endif; ?>
			<?php if( $google ) : ?>
				<li class="button_socical gg">
					<i data-id="<?php echo $id_google; ?>" id="i_<?php echo $id_google; ?>" class="fa fa-google-plus"></i>
					<em data-id="<?php echo $id_google; ?>" id="<?php echo $id_google; ?>" class="fa-google-plus"><?php _e('Login with Google', 'noo'); ?></em>
				</li>
			<?php endif; ?>
			<?php if( $linkedin ) : ?>
				<li class="button_socical linkedin">
					<i data-id="<?php echo $id_linkedin; ?>" id="<?php echo $id_linkedin; ?>" class="fa fa-linkedin-square"></i>
					<em data-id="<?php echo $id_linkedin; ?>" class="fa-linkedin-square"><?php _e('Login with LinkedIn', 'noo'); ?></em>
				</li>
			<?php endif; ?>
		<?php endif;
	}

	public function login_form() {
		$facebook = self::is_facebook_login_enabled();
		$google = self::is_google_login_enabled();
		$linkedin = self::is_linkedin_login_enabled();
		if ( self::is_social_login_enabled() && ( $facebook || $google || $linkedin ) ) :
			?>
			<div class="form-group row login-socical">
			    <div class="col-sm-9 col-xs-12">
			    	<?php 
						$id_facebook      = uniqid();
						$id_google        = uniqid();
						$id_linkedin         = uniqid();
			    	?>
			    	<?php if( $facebook ) : ?>
			    		
			    		<div class="button_socical fb">
			    			<i data-id="<?php echo $id_facebook; ?>" id="<?php echo $id_facebook; ?>" class="fa fa-facebook-square"></i>
				    		<em data-id="<?php echo $id_facebook; ?>" class="fa-facebook-square"><?php _e('Login with Facebook', 'noo'); ?></em>
			    		</div>
			    		
			    	<?php endif; ?>
			    	<?php if( $google ) : ?>
			    		<div class="button_socical gg">
			    			<i data-id="<?php echo $id_google; ?>" id="i_<?php echo $id_google; ?>" class="fa fa-google-plus"></i>
				    		<em data-id="<?php echo $id_google; ?>" id="<?php echo $id_google; ?>" class="fa-google-plus"><?php _e('Login with Google', 'noo'); ?></em>
			    		</div>
			    	<?php endif; ?>
			    	<?php if( $linkedin ) : ?>
			    		<div class="button_socical linkedin">
			    			<i data-id="<?php echo $id_linkedin; ?>" id="<?php echo $id_linkedin; ?>" class="fa fa-linkedin-square"></i>
				    		<em data-id="<?php echo $id_linkedin; ?>" class="fa-linkedin-square"><?php _e('Login with LinkedIn', 'noo'); ?></em>
			    		</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif;
	}

	public function register_user_data( $user_args, $POST ) {
		$user_args['using']     = isset($POST['using']) ? stripslashes( esc_html( $POST['using'] ) ) : '';
		$user_args['user_name'] = isset($POST['user_name']) ? stripslashes( esc_html( $POST['user_name'] ) ) : '';
		$user_args['userid']    = isset($POST['userid']) ? stripslashes( esc_html(  $POST['userid'] ) ) : '';
		$user_args['using_id']  = isset($POST['using_id']) ? stripslashes( esc_html(  $POST['using_id'] ) ) : '';

		return $user_args;
	}

	public function new_user_registered( $user_id, $user_args ) {
		
		if ( isset( $user_args['using'] ) && !empty( $user_args['using'] ) && isset( $user_args['using_id'] ) ) :
			
			// -- checking user meta
				// $get_info_login = get_user_meta( $user_id, 'info_login', true );
			// -- Get list user
				// $list_user = is_array($get_info_login) ? $get_info_login : array();

			if ( $user_args['using'] == 'fb' ) {
				
				// -- Add id facebook in meta user
					update_user_meta( $user_id, 'id_facebook', $user_args['using_id'] );
			
			} elseif ( $user_args['using'] == 'gg' ) {
				
				// -- Add id facebook in meta user
					update_user_meta( $user_id, 'id_google', $user_args['using_id'] );
			
			} elseif ( $user_args['using'] == 'linkedin' ) {
				
				// -- Add id linkedin in meta user
					update_user_meta( $user_id, 'id_linkedin', $user_args['using_id'] );

			}
			
			// Update and replace user new to list
				// $list_user_new = array_merge( $list_user, $user_new );
				// update_user_meta( $user_id, 'info_login', $user_new );

		endif; // -- / Check $using
	}

	public static function is_social_login_enabled() {
		return (bool) Noo_Member::get_setting('login_using_social', false);
	}

	public static function is_facebook_login_enabled() {
		$social_login = self::is_social_login_enabled();
		$facebook_login = Noo_Member::get_setting('facebook_login', false);
		$facebook_app_id = jm_get_3rd_api_setting('facebook_app_id', '');
		$facebook_app_secret = jm_get_3rd_api_setting('facebook_app_secret');

		return $social_login && $facebook_login && !empty( $facebook_app_id ) && !empty( $facebook_app_secret );
	}

	public static function is_google_login_enabled() {
		$social_login = self::is_social_login_enabled();
		$google_login = Noo_Member::get_setting('google_login', false);
		$google_app_id = jm_get_3rd_api_setting('google_client_id', '');
		$google_app_secret = jm_get_3rd_api_setting('google_client_secret');

		return $social_login && $google_login && !empty( $google_app_id ) && !empty( $google_app_secret );
	}

	public static function is_linkedin_login_enabled() {
		$social_login = self::is_social_login_enabled();
		$linkedin_login = Noo_Member::get_setting('linkedin_login', false);
		$linkedin_app_id = jm_get_3rd_api_setting('linkedin_app_id', '');
		$linkedin_app_secret = jm_get_3rd_api_setting('linkedin_app_secret', '');

		return $social_login && $linkedin_login && !empty( $linkedin_app_id ) && !empty( $linkedin_app_secret );
	}

	public static function get_user_by_meta_data( $meta_key, $meta_value, $count = false ) {

		$user_query = new WP_User_Query(
			array(
				'meta_key'	  =>	$meta_key,
				'meta_value'	=>	$meta_value
			)
		);

		$users = $user_query->get_results();
		if ( $count ) return count( $users );
		else return $users[0]->user_login;
	} // end get_user_by_meta_data

	public function setting_fields() {
		?>
				<!-- Add custom login using social -->
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							if ( $('#login_using_social').is(':checked') ) {
								$('.item_api').show();
							}else {
								$('.item_api').hide();
							}
							$('#login_using_social').change(function(event) {
								if ( $('#login_using_social').is(':checked') ) {
									$('.item_api').show();
								} else {
									$('.item_api').hide();
								}
							});
						});
					</script>
					<tr id="social-login">
						<th>
							<?php _e('Enable Social Login','noo')?>
						</th>
						<td>
							<input id="login_using_social" type="checkbox" name="noo_member[login_using_social]" value="1" <?php checked( Noo_Member::get_setting('login_using_social', false) );?> />
						</td>
					</tr>
					<tr class="item_api">
						<th>
							<label for="facebook_api"><?php _e( 'Facebook Login', 'noo' ); ?></label>
						</th>
						<td>
							<input id="facebook_login" type="checkbox" name="noo_member[facebook_login]" value="1" <?php checked( Noo_Member::get_setting('facebook_login', false) );?> />
							<p><small><a href="<?php echo jm_setting_page_url('3rd_api') . '#facebook-app-api'; ?>"><?php _e('Go to 3rd APIs setting', 'noo'); ?></a> <?php _e('to finish configuration with the <b>Facebook App API</b>', 'noo'); ?></small></p>
						</td>
					</tr>
					<tr class="item_api">
						<th>
							<label for="google_api"><?php _e( 'Google Login', 'noo' ); ?></label>
						</th>
						<td>
							<input id="google_login" type="checkbox" name="noo_member[google_login]" value="1" <?php checked( Noo_Member::get_setting('google_login', false) );?> />
							<p><small><a href="<?php echo jm_setting_page_url('3rd_api') . '#google-app-api'; ?>"><?php _e('Go to 3rd APIs setting', 'noo'); ?></a> <?php _e('to finish configuration with the <b>Google App API</b>', 'noo'); ?></small></p>
						</td>
					</tr>
					<tr class="item_api">
						<th>
							<label for="linkedin_api"><?php _e( 'LinkedIn Login', 'noo' ); ?></label>
						</th>
						<td>
							<input id="linkedin_login" type="checkbox" name="noo_member[linkedin_login]" value="1" <?php checked( Noo_Member::get_setting('linkedin_login', false) );?> />
							<p><small><a href="<?php echo jm_setting_page_url('3rd_api') . '#linkedin-app-api'; ?>"><?php _e('Go to 3rd APIs setting', 'noo'); ?></a> <?php _e('to finish configuration with the <b>LinkedIn App API</b>', 'noo'); ?></small></p>
						</td>
					</tr>

				<!-- / Add custom login using social -->

		<?php
	}

	public function setting_api_fields() {
		?>
		<tr id="facebook-app-api">
			<th>
				<label for="facebook_app_api"><?php _e( 'Facebook App API', 'noo' ); ?></label>
			</th>
			<td>
				<input id="facebook_app_api" type="text" name="jm_3rd_api[facebook_app_id]" value="<?php echo jm_get_3rd_api_setting('facebook_app_id') ?>" placeholder="<?php _e( 'Application API', 'noo' ); ?>" size="60" />
				<input id="facebook_app_secret" type="text" name="jm_3rd_api[facebook_app_secret]" value="<?php echo jm_get_3rd_api_setting('facebook_app_secret') ?>" placeholder="<?php _e( 'Application Secret', 'noo' ); ?>" size="50" />
				<p>
					<?php echo sprintf( __('<b>%s</b> requires that you create an application inside its framework to allow access from your website to their API.<br/> To know how to create this application, ', 'noo' ), 'Facebook' ); ?>
					<a href="javascript:void(0)" onClick="jQuery('#facebook-help').toggle();return false;"><?php _e('click here and follow the steps.', 'noo'); ?></a>
				</p>
				<div id="facebook-help" class="noo-setting-help" style="display: none; max-width: 1200px;" >
					<hr/>
					<br/>
					<?php _e('<em>Application ID</em> and <em>Secret</em> (also sometimes referred as <em>Consumer Key</em> and <em>Secret</em> or <em>Client ID</em> and <em>Secret</em>) are what we call an application credential', 'noo') ?>. 
					<?php echo sprintf( __( 'This application will link your website <code>%s</code> to <code>%s API</code> and these credentials are needed in order for <b>%s</b> users to access your website', 'noo'), $_SERVER["SERVER_NAME"], 'Facebook', 'Facebook' ) ?>. 
					<br/>
					<br/>
					<?php echo sprintf( __('To register a new <b>%s API Application</b> and enable authentication, follow the steps', 'noo'), 'Facebook' ) ?>
					<br/>
					<?php $setupsteps = 0; ?>
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e( 'Go to', 'noo'); ?>&nbsp;<a href="https://developers.facebook.com/apps" target ="_blank">https://developers.facebook.com/apps</a></p>
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Select <b>Add a New App</b> from the <b>Apps</b> menu at the top", 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Choose Website as your app platform", 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Fill out Name and click <b>Create New Facebook App ID</b>", 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Choose a Category for your app and click <b>Create App ID</b>", 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Input your website URL. It should match the current site", 'noo') ?> <em><?php echo get_option('siteurl'); ?></em></p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Skip the rest of the setup and Go to the Developer Dashboard to see your Application", 'noo') ?>.</p>  
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Go to the App <b>Settings</b> and add an email to your app. It's required before publishing your app", 'noo') ?>.</p>  
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Go to the <b>Status & Review</b> and publish your application.", 'noo') ?>.</p>  
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e("Go back to the App <b>Dashboard</b> and copy the <em>Application ID</em> and <em>Application Secret</em> then paste to the settings above.", 'noo') ?>.</p>  
					<p>
						<b><?php _e("And that's it!", 'noo') ?></b> 
						<br />
						<?php echo __( 'For more reference, you can see: ', 'noo' ); ?><a href="https://developers.facebook.com/docs/apps/register", target="_blank"><?php _e('Facebook Document', 'noo'); ?></a>, <a href="https://www.google.com/search?q=Facebook API create application" target="_blank"><?php _e('Google', 'noo'); ?></a>, <a href="http://www.youtube.com/results?search_query=Facebook API create application " target="_blank"><?php _e('Youtube', 'noo'); ?></a>
					</p>
					<div style="margin-bottom:12px;" class="noo-thumb-wrapper">
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_1.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_1.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_2.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_2.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_3.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_3.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_4.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_4.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_5.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_5.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_6.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_6.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_7.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/facebook_api_7.png"></a>
					</div> 
					<br/>
					<hr/>
				</div>
			</td>
		</tr>
		<tr class="item_api">
			<th>
				<label for="google_app_id"><?php _e( 'Google App API', 'noo' ); ?></label>
			</th>
			<td>
				<input id="google_app_id" type="text" name="jm_3rd_api[google_client_id]" value="<?php echo jm_get_3rd_api_setting('google_client_id') ?>" placeholder="<?php _e( 'Client ID', 'noo' ); ?>" size="60"/>
				<input id="google_app_secret" type="text" name="jm_3rd_api[google_client_secret]" value="<?php echo jm_get_3rd_api_setting('google_client_secret') ?>" placeholder="<?php _e( 'Client Secret', 'noo' ); ?>" size="50" />
				<p><?php echo sprintf( __('<b>%s</b> requires that you create an application inside its framework to allow access from your website to their API.<br/> To know how to create this application, ', 'noo' ), 'Google' ); ?>
					<a href="javascript:void(0)" onClick="jQuery('#google-help').toggle();return false;"><?php _e('click here and follow the steps.', 'noo'); ?></a>
				</p>
				<div id="google-help" class="noo-setting-help" style="display: none; max-width: 1200px;" >
					<hr/>
					<br/>
					<?php _e('<em>Application ID</em> and <em>Secret</em> (also sometimes referred as <em>Consumer Key</em> and <em>Secret</em> or <em>Client ID</em> and <em>Secret</em>) are what we call an application credential', 'noo') ?>. 
					<?php echo sprintf( __( 'This application will link your website <code>%s</code> to <code>%s API</code> and these credentials are needed in order for <b>%s</b> users to access your website', 'noo'), $_SERVER["SERVER_NAME"], 'Google', 'Google' ) ?>. 
					<br/>
					<br/>
					<?php echo sprintf( __('To register a new <b>%s API Application</b> and enable authentication, follow the steps', 'noo'), 'Google' ) ?>
					<br/>
					<?php $setupsteps = 0; ?>
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e( 'Go to', 'noo'); ?>&nbsp;<a href="https://console.developers.google.com/project" target ="_blank">https://console.developers.google.com/project</a></p>
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Select <b>Create Project</b> button', 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Fill in <b>Project name</b> then click <b>Create</b> button', 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('In the sidebar, under <b>APIs & auth</b>, select <b>Credentials</b> then switch to <b>OAuth consent screen</b> tab', 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Choose an Email Address and specify a <b>Product Name</b>', 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Switch back to <b>Credentials</b> tab and add a new <b>OAuth 2.0 client ID</b>', 'noo') ?>
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('In the <b>Application type</b> section, select <b>Web application</b> then input your site URL to the <b>Authorized JavaScript origins</b>. It should match the current site', 'noo') ?> <em><?php echo get_option('siteurl'); ?></em></p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('In the resulting section, you should see the <em>Client ID</em> and <em>Client Secret</em>', 'noo') ?>.</p> 
					<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Go back to this setting page and paste the created Client ID and Client Secret into the settings above', 'noo') ?>.</p> 
					<p>
						<b><?php _e("And that's it!", 'noo') ?></b> 
						<br />
						<?php echo __( 'For more reference, you can see: ', 'noo' ); ?><a href="https://developers.google.com/identity/sign-in/web/devconsole-project", target="_blank"><?php _e('Google Document', 'noo'); ?></a>, <a href="https://www.google.com/search?q=Google API create application" target="_blank"><?php _e('Google', 'noo'); ?></a>, <a href="http://www.youtube.com/results?search_query=Google API create application " target="_blank"><?php _e('Youtube', 'noo'); ?></a>
					</p> 
					<div style="margin-bottom:12px;" class="noo-thumb-wrapper">
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_1.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_1.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_2.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_2.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_3.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_3.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_4.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_4.png"></a>
						<a href="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_5.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/google_api_5.png"></a>
					</div> 
					<br/>
					<hr/>
				</div>
			</td>
		</tr>

		<?php
	}

}

new JM_SocialLogin();