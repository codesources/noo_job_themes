<?php
if(!class_exists('Noo_Member')):
class Noo_Member {

	const EMPLOYER_ROLE = 'employer';
	const CANDIDATE_ROLE = 'candidate';
	
	protected $query_vars;
	
	protected static $_instance = null;
	
	protected function __construct(){
		$this->query_vars =  array(
			// 'my-profile'   			=> 'my-profile',
			'lost-password'		    => 'lost-password',
			'reset-password'		=> 'reset-password',
			'edit-job'     			=> 'edit-job',
			'preview-job'     		=> 'preview-job',
			'manage-application' 	=> 'manage-application',
			'manage-plan'        	=> 'manage-plan',
			'manage-job'        	=> 'manage-job',
			// 'post-job'     			=> 'post-job',
			'company-profile'      	=> 'company-profile',
			'package-checkout'		=> 'package-checkout', // processing payment.
			// Candidate:
			'candidate-profile'     => 'candidate-profile',
			'edit-resume'		    => 'edit-resume',
			// 'post-resume'     		=> 'post-resume',
			'preview-resume'     	=> 'preview-resume',
			'manage-resume'    		=> 'manage-resume',
			'manage-job-applied'    => 'manage-job-applied',
			'viewed-resume'    		=> 'viewed-resume',
			// 'bookmark-job'    		=> 'bookmark-job',
			'job-alert'    			=> 'job-alert',
			'add-job-alert'    		=> 'add-job-alert',
			'edit-job-alert'    	=> 'edit-job-alert'
		);

		add_action( 'init', array( &$this, 'add_endpoints' ) );
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'switch_theme', array(&$this,'theme_deactivated'), 10 , 2);
		add_action( 'after_switch_theme', array(&$this,'theme_activated') );

		add_shortcode('noo_member_account', array(&$this,'noo_member_account_shortcode'));
		add_filter( 'wp_nav_menu_items', array(&$this,'profile_menu'), 50, 2 );
		add_filter( 'get_avatar', array(&$this,'get_avatar'), 100000, 6 );
		
		if( self::get_setting('use_custom_login', true) ) {
			add_filter('login_url', array(&$this,'login_url'),99999);
		}
		add_filter('logout_url', array(&$this,'logout_url'),99999, 2);
		add_filter('register_url', array(&$this,'register_url'),99999);
		add_filter('lostpassword_url', array(&$this,'lostpassword_url'),99999);
			
		if(!self::is_logged_in()){
			add_action('wp_footer', array(&$this,'modal_login'),100);
			add_action('wp_footer', array(&$this,'modal_register'),100);
		}
		
		if( is_admin() ) {
			add_action('admin_init', array(&$this,'admin_init'));
			
			add_action( 'user_new_form', array(&$this,'user_profile') );
			add_action( 'show_user_profile', array(&$this,'user_profile') );
			add_action( 'edit_user_profile', array(&$this,'user_profile') );

			add_action( 'user_register', array(__CLASS__,'save_user_profile' ));
			add_action( 'personal_options_update', array(__CLASS__,'save_user_profile' ));
			add_action( 'edit_user_profile_update', array(__CLASS__,'save_user_profile' ));
			
			add_filter('noo_job_settings_tabs_array', array(&$this,'add_seting_member_tab'));
			add_action('noo_job_setting_member', array(&$this,'setting_page'));
			
			add_action( 'admin_enqueue_scripts', array (&$this,'enqueue_style_script') );
		} else {
			add_filter( 'query_vars', array( &$this, 'add_query_vars'), 0 );
			add_action( 'parse_request', array( $this, 'parse_request'), 0 );
		}

		// Remove admin bar and redirect profile page to site interface 
		if( self::get_setting('hide_admin_bar', true) ) {
			add_action( 'admin_init', array( $this, 'prevent_admin_access') );
			add_action( 'user_register', array( $this, 'hide_admin_bar_front') );
			add_action( 'wp_before_admin_bar_render', array( $this, 'stop_admin_bar_render') );
			add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );

			// Stop WooCommerce redirect to My Account page.
			add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
		}
	}
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	
	public function init(){
		$employer_role = get_role(self::EMPLOYER_ROLE);
		$candidate_role = get_role(self::CANDIDATE_ROLE);
		if( empty( $employer_role ) || empty( $candidate_role ) ) {
			$this->create_roles();
		}
		if(defined('WOOCOMMERCE_VERSION'))
			add_filter('woocommerce_disable_admin_bar','__return_false');
	}
	
	public function add_endpoints(){
		foreach ( $this->get_query_vars() as $key => $var )
			add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
	}
	
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var )
			$vars[] = $key;
	
		return $vars;
	}
	
	public function get_query_vars() {
		return apply_filters( 'noo-member-page-endpoint', $this->query_vars );
	}
	
	public function parse_request() {
		global $wp;
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			}elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$q = $wp->query_vars[ $var ];
				if(strstr( $q, 'page' ) ){
					$wp->query_vars[ $key ] = '';
					$p = explode('/', $q);
					$wp->query_vars['paged'] = absint($p[1]);
				}else{
					$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
				}
				
			}
		}
	}
	
	public function admin_init(){
		register_setting('noo_member','noo_member');
	}
	

	public static function get_setting($id = null ,$default = null){
		global $noo_member_setting;
		if(!isset($noo_member_setting) || empty($noo_member_setting)){
			$noo_member_setting = get_option('noo_member');
		}
		if (isset($noo_member_setting[$id])) {
			return $noo_member_setting[$id];
		}
		return $default;
	}

	public static function can_register(){
		$can_register = get_option('users_can_register');
		$allow_register = Noo_Member::get_setting('allow_register', 'both');
		if( $allow_register == 'none' ) {
			$can_register = false;
		} elseif( $allow_register == 'employer' ) {
			$can_register = $can_register && !jm_is_resume_posting_page();
		} elseif( $allow_register == 'candidate' ) {
			$can_register = $can_register && !jm_is_job_posting_page();
		}
		return $can_register;
	}

	public static function get_member_page_id() {
		$page_id = self::get_setting('manage_page_id');
		$page_id = apply_filters( 'wpml_object_id', $page_id, 'page', true );
		return $page_id;
	}

	public static function get_member_page_url() {
		global $member_page_url;
		if( empty( $member_page_url ) ) {
			$member_page_url = get_permalink(self::get_member_page_id());
		}
		return $member_page_url;
	}
	
	public static function get_checkout_url( $product_id ){
		if( empty( $product_id ) ) return self::get_member_page_url();

		$checkout_url = self::get_endpoint_url('package-checkout');
		return esc_url_raw( add_query_arg( 'product_id', $product_id, $checkout_url ) );
	}
	
	public static function get_login_url(){
		if($manage_page_url = self::get_member_page_url()){
			$login_url = esc_url_raw( add_query_arg('action','login',$manage_page_url) );
		} else {
			$login_url = wp_login_url();
		}
		return $login_url;
	}
	
	public static function get_register_url(){
		if($manage_page_url = self::get_member_page_url()){
			$register_url = esc_url_raw( add_query_arg(array('action'=>'login','mode'=>'register'),$manage_page_url) );
		} else {
			$register_url = wp_registration_url();
		}
		return $register_url;
	}
	
	public static function get_logout_url(){
		$logout_url = wp_logout_url();
		return $logout_url;
	}
	
	public function login_url($login_url){
		$basename = basename($_SERVER['REQUEST_URI']);
		$user_login = self::get_member_page_id();
		if($user_login && strpos($basename, 'wp-login.php') === false) {
			$new_login_url = get_permalink($user_login);
			if ( $new_login_url ) {
				// retain the redirect url
				if( $var_pos = strpos($login_url, '?') ) {
					$login_args = wp_parse_args( substr($login_url, $var_pos + 1), array() );
					if( isset( $login_args['redirect_to'] ) ) {
						$new_login_url = esc_url_raw( add_query_arg(array( 'action' => 'login', 'redirect_to' => urlencode($login_args['redirect_to'])), $new_login_url ) );
					}
				}
				return $new_login_url;
			}
		}

		return $login_url;
	}
	
	public function register_url($register_url){
		$basename = basename($_SERVER['REQUEST_URI']);
		$user_regiter = self::get_member_page_id();
		if($user_regiter && $basename != 'wp-login.php')
			$register_url = get_permalink($user_regiter);
		return $register_url;
	}
	
	public function logout_url($logout_url,$redirect=''){
		$basename = basename($_SERVER['REQUEST_URI']);
		if(strpos($basename, 'wp-login.php') === false){
			$args = array();
			$redirect_to = !empty( $redirect ) ? $redirect : home_url( '/' );
			$args['redirect_to'] = urlencode( $redirect_to );
			return  esc_url_raw( add_query_arg($args, $logout_url) );
		}
		return $logout_url;
	}
	
	public function lostpassword_url($lostpassword_url){
		$user_forgotten = self::get_member_page_id();
		if($user_forgotten)
			$lostpassword_url = self::get_endpoint_url('lost-password');
		return $lostpassword_url;
	}
	
	public static function resetpassword_url(){
		$user_forgotten = self::get_member_page_id();
		if($user_forgotten) {
			$resetpassword_url = self::get_endpoint_url('reset-password');
		} else {
			$resetpassword_url = network_site_url("wp-login.php?action=rp", 'login');
		}

		return $resetpassword_url;
	}
	
	
	public function profile_menu( $items, $args ) {
		if( !noo_get_option('noo_header_nav_user_menu', true) ) {
			return $items;
		}
		if ( $args->theme_location == 'primary' ) {
			ob_start();
			if( function_exists( 'noo_menu_get_option' ) && noo_menu_get_option( 'enable', 1  ) > 0 ) {
				include(locate_template("layouts/noo-menu-user-menu.php"));
			} else {
				include(locate_template("layouts/user-menu.php"));
			}
			
			$items .= ob_get_clean();
		}
		return $items;
	}

	public function get_avatar( $avatar = '', $user_id = null, $size = 40, $default = '', $alt = '', $args = array() ) {
		$user_id = empty( $user_id ) ? get_current_user_id() : ( is_object( $user_id ) ? $user_id->ID : $user_id );

		if( !is_numeric( $user_id  ) ) {
			$maybe_user = get_user_by( 'email', $user_id );
			if( !empty( $maybe_user ) ) {
				$user_id = $maybe_user->ID;
			}
		}

		if( empty( $user_id ) || !is_numeric( $user_id ) ) {
			return '';
		}

		if( isset( $args['force_default'] ) && $args['force_default'] ) {
			return $avatar;
		}

		$new_avatar = '';
		$args['alt'] = $alt;
		$args['class'] = isset( $args['class'] ) ? $args['class'] : 'avatar avatar-'.$size;
		if( self::is_candidate( $user_id ) ) {
			$profile_image = get_user_meta( $user_id, 'profile_image', true );
			if( !empty( $profile_image) ) {
				if( is_numeric( $profile_image ) ) {
					$new_avatar = wp_get_attachment_image($profile_image, array( $size, $size ), false, $args );
				} else {
					$new_avatar = '<img alt="'.$alt.'" src="' . esc_url( $profile_image ) . '" class="'.$args['class'].'" height="'.$size.'" width="'.$size.'">';
				}
			}
		} elseif( self::is_employer( $user_id ) ) {
			$company_id = jm_get_employer_company( $user_id );
			$new_avatar = !empty( $company_id ) ? Noo_Company::get_company_logo( $company_id, $size, $alt, $args ) : $new_avatar;
		}
		if ( empty( $new_avatar ) ) {
			$id_facebook = get_user_meta( $user_id, 'id_facebook', true );
			$id_google = get_user_meta( $user_id, 'id_google', true );
			if ( !empty( $id_facebook ) ) {
				$new_avatar = '<img src="//graph.facebook.com/' . $id_facebook . '/picture?type=large" alt="'.$alt.'" class="'.$args['class'].'" height="'.$size.'" width="'.$size.'"/>';
			} elseif ( !empty( $id_google ) ) {
				$json_google = 'http://picasaweb.google.com/data/entry/api/user/' . $id_google . '?alt=json';
				$get_info = file_get_contents( $json_google );
				preg_match( '/\"gphoto\$thumbnail\"\:\{\"\$t\"\:\"(.*?)\"\}/is', $get_info, $thumbnail);
				$new_avatar = '<img src="' . $thumbnail[1] . '" alt="'.$alt.'" class="'.$args['class'].'" height="'.$size.'" width="'.$size.'"/>';
			}
		}

		return ( empty( $new_avatar ) ? $avatar : $new_avatar );
	}

	public static function get_display_name( $user_id = '' ) {
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

		if( empty( $user_id ) ) return '';
		$user = get_userdata( $user_id );
		$display_name = $user->display_name;
		if( Noo_Member::is_employer( $user->ID ) ) {
			$company_id = get_user_meta( $user->ID,'employer_company' ,true );
			if( $company_id ) {
				$display_name = get_the_title( $company_id );

				// @TODO: Update the employer's display name. Should be removed after some version
				if( $user->display_name != $display_name ) {
					wp_update_user( array( 'ID' => $user->ID, 'display_name' => $display_name ) );
				}
			}
		}

		return apply_filters( 'jm_member_display_name', $display_name, $user_id );
	}
	
	public function theme_activated($newname = '', $newtheme = '') {
		$this->create_roles();
		$this->_create_cron_jobs();
	}
	
	public function theme_deactivated($newname, $newtheme) {
		remove_role( self::EMPLOYER_ROLE );
		remove_role( self::CANDIDATE_ROLE );
	}
	
	public function get_core_capabilities(){
		$capabilities = array();
		
		$capabilities['core'] = array(
			'manage_noo_job',
			'manage_noo_resume'
		);
		$capability_types = array( 'noo_job', 'noo_resume');
		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
		
				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}
		
		return $capabilities;
	}
	
	public function create_roles(){
		global $wp_roles;
		
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}
		
		if ( is_object( $wp_roles ) ) {
			
			add_role( self::EMPLOYER_ROLE, __( 'Employer', 'noo' ), array(
				'read' 						=> true,
				'edit_posts' 				=> false,
				'delete_posts' 				=> false
			) );
			
			// $capabilities = $this->get_core_capabilities();
			// foreach ( $capabilities as $cap_group ) {
			// 	foreach ( $cap_group as $cap ) {
			// 		$wp_roles->add_cap( self::EMPLOYER_ROLE, $cap );
			// 	}
			// }
			
			add_role( self::CANDIDATE_ROLE, __( 'Candidate', 'noo' ), array(
				'read' 						=> true,
				'edit_posts' 				=> false,
				'delete_posts' 				=> false
			) );
			
			// $capabilities = $this->get_core_capabilities();
			// foreach ( $capabilities as $cap_group ) {
			// 	foreach ( $cap_group as $cap ) {
			// 		$wp_roles->add_cap( self::CANDIDATE_ROLE, $cap );
			// 	}
			// }
		}
	}

	public function enqueue_style_script( $hook ) {
		global $post;

		if ( $hook == 'user-new.php' || $hook == 'user-edit.php' || $hook == 'profile.php' ) {
			wp_enqueue_media();

			// wp_enqueue_script( 'vendor-datetimepicker' );
			// wp_enqueue_style( 'vendor-datetimepicker' );

			wp_enqueue_script( 'noo-user-admin', NOO_FRAMEWORK_ADMIN_URI . '/assets/js/noo-user-admin.js', null, null, true );

			// wp_register_style( 'noo-user-admin', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo-user-admin.css' );
			// wp_enqueue_style( 'noo-user-admin' );
		}
	}

	public function user_profile( $user ) {
		$user_id = is_object( $user ) && isset( $user->ID ) ? $user->ID : 0;

		?>
		<?php if ( ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) || is_network_admin() ) :
			$user_roles = array_intersect( array_values( $user->roles ), array_keys( get_editable_roles() ) );
			$user_role  = reset( $user_roles );
		?>
			<input id="role" type="hidden" name="role" value="<?php echo $user_role; ?>" />
		<?php endif; ?>
		<div id="candidate_profile" class="candidate_profile">
			<h3 class=""><?php _e( 'Candidate Basic Info', 'noo' ); ?></h3>

			<table class="form-table">
				<tr>
					<th><label for="profile_image"><?php _e( 'Profile Image', 'noo' ); ?></label></th>
					<td>
						<!-- Outputs the image after save -->
						<?php
							$profile_image = empty( $user_id ) ? '' : get_the_author_meta( 'profile_image', $user_id );
							if( is_numeric( $profile_image ) ) {
								$profile_image = wp_get_attachment_url( $profile_image );
							}
						?>
						<img src="<?php echo esc_url($profile_image); ?>" style="width:150px;"><br />
						<!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
						<input type="hidden" name="profile_image" id="profile_image" value="<?php echo esc_url($profile_image); ?>" class="regular-text" />
						<!-- Outputs the save button -->
						<input type='button' class="additional-user-image button-primary" value="<?php _e( 'Upload Image', 'noo' ); ?>" id="uploadimage"/><br />
						<span class="description"><?php _e( 'Upload an additional image for your user profile.', 'noo' ); ?></span>
					</td>
				</tr>
				<?php 
					$fields = jm_get_candidate_custom_fields();
					if( !empty( $fields ) ) :
						foreach ($fields as $field) : 
							if( isset( $field['is_default'] ) ) {
								if( in_array( $field['name'], array( 'first_name', 'last_name', 'full_name', 'email' ) ) )
									continue; // don't display WordPress default user fields
							}

							$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
							$field_id = jm_candidate_custom_fields_name( $field['name'], $field );
 							?>
							<tr>
								<th><label for="<?php echo $field_id; ?>"><?php echo $label; ?></label></th>
								<td>
									<?php
									noo_render_setting_field(
											array(
												'id' => $field_id,
												'type' => $field['type'],
												'options' => noo_convert_custom_field_setting_value( $field ),
												'value' => get_user_meta( $user_id, $field_id, true ),
												'class' => 'regular-text',
												'echo' => true
											)
										);
									?>
								</td>
							</tr>
						<?php endforeach;
					endif;
				?>
			</table>
			<h3 id="candidate_social" class="candidate_social"><?php _e( 'Candidate Social Profile', 'noo' ); ?></h3>

			<table class="form-table">
				<?php 
					$socials = jm_get_candidate_socials();
					$all_socials = noo_get_social_fields();
					if( !empty( $socials ) ) :
						foreach ($socials as $social) :
							if( empty( $social ) || !isset( $all_socials[$social] ) ) continue;
 							?>
							<tr>
								<th><label for="<?php echo $social; ?>"><?php echo $all_socials[$social]['label']; ?></label></th>
								<td>
									<?php
									noo_render_setting_field(
											array(
												'id' => $social,
												'type' => 'text',
												'value' => get_user_meta( $user_id, $social, true ),
												'class' => 'regular-text',
												'echo' => true
											)
										);
									?>
								</td>
							</tr>
						<?php endforeach;
					endif;
				?>
			</table>
		</div>
		<div id="employer_profile" class="employer_profile">
			<h3 class=""><?php _e( 'Employer Information', 'noo' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="employer_company"><?php _e( 'Representative for Company', 'noo' ); ?></label></th>
					<td>
						<?php 
						$companies = get_posts(array(
							'post_type'=>'noo_company',
							'posts_per_page'=>'-1',
							'suppress_filters' => false
						));
						$current_company = ( !empty( $user ) && is_object( $user ) ) ? get_user_meta($user_id,'employer_company',true) : '';
						?>
						<select id="employer_company" name="employer_company">
							<option value=""><?php _e('- Select -', 'noo') ?></option>
						<?php if($companies):?>
							<?php foreach ($companies as $company):?>
								<option <?php selected($current_company,$company->ID)?> value="<?php echo esc_attr($company->ID)?>"><?php echo esc_html($company->post_title); ?></option>
							<?php endforeach;?>
						<?php endif;?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<script>
			jQuery(document).ready(function($) {
				var setOption = function() {
					var $roleEl = $("#role");
					var $selectedRole = $roleEl.prop('nodeName') === 'select' ? $("#role option:selected").val() : $("#role").val();

					if( $selectedRole !== '<?php echo self::CANDIDATE_ROLE; ?>' ) {
						$("#candidate_profile").hide();
					} else {
						$("#candidate_profile").show();
					}
					if( ( $selectedRole !== '<?php echo self::EMPLOYER_ROLE; ?>' ) && ( $selectedRole !== 'administrator' ) ) {
						$("#employer_profile").hide();
					} else {
						$("#employer_profile").show();
					}
				};
				setOption();

				$("#role").change( setOption );

				// Uploading files
				var file_frame;

				$('.additional-user-image').on('click', function( event ){

					event.preventDefault();

					$this = $(this);

				    // If the media frame already exists, reopen it.
				    if ( file_frame ) {
				    	file_frame.open();
				    	return;
				    }

				    // Create the media frame.
				    file_frame = wp.media.frames.file_frame = wp.media({
				    	title: $this.data( 'uploader_title' ),
				    	button: {
				    		text: $this.data( 'uploader_button_text' ),
				    	},
						multiple: false  // Set to true to allow multiple files to be selected
				  	});

				    // When an image is selected, run a callback.
				    file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();

						// Do something with attachment.id and/or attachment.url here
						$this.siblings('img').attr('src', attachment.url);
						$this.siblings('input#profile_image').val(attachment.id);
				  	});

				    // Finally, open the modal
				    file_frame.open();
				});

				});
		</script>
		<?php
	}

	public static function save_user_profile( $user_id ) {
		if( empty( $user_id ) ) {
			return;
		}

		if( isset( $_POST['profile_image'] ) ) {
			$old_profile_image = get_user_meta( $user_id, 'profile_image', true );
			if( $old_profile_image != $_POST['profile_image'] ) {
				update_user_meta( $user_id,'profile_image', sanitize_text_field( $_POST['profile_image'] ) );
				if( is_numeric( $old_profile_image ) ) {
					wp_delete_attachment($old_profile_image, true);
				}
			}
		}

		jm_candidate_save_custom_fields( $user_id, $_POST );
		
		if( isset( $_POST['employer_company'] ) ) {
			update_user_meta($user_id, 'employer_company', sanitize_text_field($_POST['employer_company']));
		}

		do_action( 'jm_saved_user_profile', $user_id );
	}

	public static function can_go_to_admin( $user_id = '') {
		$can_go_to_admin = false;

		if( empty( $user_id) ) {
			$can_go_to_admin = current_user_can( 'edit_posts' ) || current_user_can( 'activate_plugins' );
		} else {
			$can_go_to_admin = user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'activate_plugins' );
		}

		return apply_filters( 'noo_can_go_to_admin', $can_go_to_admin );
	}

	public function hide_admin_bar_front($user_ID) {
		if( !self::can_go_to_admin( $user_ID ) ) {
			update_user_meta( $user_ID, 'show_admin_bar_front', 'false' );
		}
	}

	public function stop_admin_bar_render() {
		if( !self::can_go_to_admin() ) {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('site-name');
			$wp_admin_bar->remove_menu('dashboard');
			$wp_admin_bar->remove_menu('edit-profile');
			$wp_admin_bar->remove_menu('user-actions');
		}
	}

	public function remove_admin_bar() {
		if (!self::can_go_to_admin() && !is_admin()) {
			show_admin_bar(false);
		}
	}

	public function prevent_admin_access() {
		if ( !(defined('DOING_AJAX') && DOING_AJAX) && basename( $_SERVER["SCRIPT_FILENAME"] ) !== 'admin-post.php' && !self::can_go_to_admin() ) {
			wp_safe_redirect( self::get_member_page_url() );
			exit;
		}
	}
	
	public static function get_endpoint_url($endpoint, $value = ''){
		$url = noo_get_endpoint_url( $endpoint , $value, self::get_member_page_url() );
		return $url;
	}
	
	public static function get_company_profile_url(){
		$url              = self::get_endpoint_url('company-profile');
		$company_id       = get_user_meta(get_current_user_id(),'employer_company',true);
		if ( 'noo_company' == get_post_type( $company_id ) ) {
			$url = esc_url_raw( add_query_arg('company_id',$company_id,$url) );
		}
		return $url;
	}
	
	public static function get_candidate_profile_url(){
		$url = self::get_endpoint_url('candidate-profile');
		if($candidate_id = get_current_user_id()){
			$url = esc_url_raw( add_query_arg('candidate_id',$candidate_id,$url) );
		}
		return $url;
	}
	
	public static function get_edit_job_url($job_id){
		return esc_url_raw( add_query_arg('job_id',$job_id,self::get_endpoint_url('edit-job')) );
	}
	
	public static function get_edit_resume_url($resume_id){
		return esc_url_raw( add_query_arg('resume_id',$resume_id,self::get_endpoint_url('edit-resume')) );
	}

	public static function get_edit_job_alert_url($alert_id){
		return esc_url_raw( add_query_arg('job_alert_id',$alert_id,self::get_endpoint_url('edit-job-alert')) );
	}

	public function noo_member_account_shortcode($atts, $content = null){
		global $wp;
		if ( isset( $wp->query_vars['lost-password']) ){
			return $this->_lost_password($atts);
		}
		if ( isset( $wp->query_vars['reset-password']) ){
			return $this->_reset_password($atts);
		}
		if(!self::is_logged_in()) {
			$html = apply_filters( 'noo-member-not-login-shortcode', '', $wp->query_vars );
			if( empty( $html ) ) :
				ob_start();
				$form = '';
				if( self::can_register() 
					&& isset($_GET['action'])
					&& ( $_GET['action'] === 'register'
						|| ( $_GET['action'] === 'login' && isset( $_GET['mode'] ) && $_GET['mode'] === 'register' ) ) ) {
					$form = 'register';
				}else{
					$form = 'login';
				}
				?>
				<div class="account-form show-login-form-links">
					<?php do_action('noo_member_manage_not_login_before'); ?>
					<?php if( isset( $wp->query_vars['package-checkout'] ) ) : ?>
						<div class="noo-messages noo-message-error">
							<ul>
								<li><?php _e('Please login before buying a Job Package', 'noo'); ?></li>
							</ul>
						</div>
					<?php endif; ?>
					<?php if('login' == $form):?>
						<div class="account-log-form">
							<?php Noo_Member::ajax_login_form(__('Login','noo'));?>
						</div>
					<?php endif;?>
					<?php if('register' == $form):?>
						<div class="account-reg-form">
							<?php Noo_Member::ajax_register_form(__('Register','noo'))?>
						</div>
					<?php endif;?>
					<?php do_action('noo_member_manage_not_login_after'); ?>
				</div>
				<?php
				$html = ob_get_clean();
			endif;

			return $html;
		}

		$html = '';
		if( self::is_employer() ) {
			if ( isset( $wp->query_vars['edit-job']) ){
				$html = $this->_edit_job_shortcode($atts);	
			}elseif ( isset( $wp->query_vars['preview-job']) ){
				$html = $this->_preview_job_shortcode($atts);	
			}elseif ( isset( $wp->query_vars['manage-application']) ){
				$html = $this->_manage_application_shortcode($atts);	
			}elseif ( isset( $wp->query_vars['manage-plan']) ){
				$html = $this->_manage_plans_shortcode($atts);
			}elseif ( isset( $wp->query_vars['manage-job']) ){
				$html = $this->_manage_job_shortcode($atts);
			}elseif ( isset( $wp->query_vars['viewed-resume']) ){
				$html = $this->_viewed_resume_shortcode($atts);
			}elseif( isset( $wp->query_vars['post-job'] ) ){
				if(jm_can_post_job()){
					$html = $this->_post_job_shortcode($atts);	
				}else{
					noo_message_add(__('You used up your Job Limit.','noo'),'error');
					if(jm_is_woo_job_posting()){
						wp_safe_redirect(self::get_endpoint_url('manage-plan'));
					}else{
						wp_safe_redirect(self::get_member_page_url());
					}
					$html = '';
				}
			}elseif( isset( $wp->query_vars['company-profile']) ){
				$html = $this->_company_profile_shortcode($atts);
			}elseif( isset( $wp->query_vars['package-checkout']) ){
				$html = $this->_checkout($atts);
			} else {
				$html = apply_filters( 'noo-member-employer-shortcode', $html, $wp->query_vars );
			}
			
			if( empty( $html ) ) {
				wp_safe_redirect( self::get_endpoint_url('manage-job') );
				exit;
			}
		} else if( self::is_candidate() ) {
			if( isset( $wp->query_vars['edit-resume'] ) ){
				$html = $this->_edit_resume_shortcode($atts);	
			}elseif ( isset( $wp->query_vars['preview-resume']) ){
				$html = $this->_preview_resume_shortcode($atts);	
			}elseif( isset( $wp->query_vars['post-resume'] ) ){
				jm_force_redirect( self::get_post_resume_url() );	
			}elseif( isset( $wp->query_vars['manage-resume']) ){
				$html = $this->_manage_resume_shortcode($atts);
			}elseif( isset( $wp->query_vars['manage-job-applied']) ){
				$html = $this->_manage_job_applied_shortcode($atts);
			}elseif ( isset( $wp->query_vars['manage-plan']) ){
				$html = $this->_manage_plans_shortcode($atts);
			}elseif( isset( $wp->query_vars['job-alert']) ){
				$html = $this->_job_alert_shortcode($atts);
			}elseif( isset( $wp->query_vars['add-job-alert']) ){
				$html = $this->_add_job_alert_shortcode($atts);
			}elseif( isset( $wp->query_vars['edit-job-alert']) ){
				$html = $this->_edit_job_alert_shortcode($atts);
			}elseif( isset( $wp->query_vars['candidate-profile']) ){
				$html = $this->_candidate_profile_shortcode($atts);
			}elseif( isset( $wp->query_vars['package-checkout']) ){
				$html = $this->_checkout($atts);
			} else {
				$html = apply_filters( 'noo-member-candidate-shortcode', $html, $wp->query_vars);
			}

			if( empty( $html ) ) {
				if( jm_resume_enabled() ) {
					wp_safe_redirect( self::get_endpoint_url('manage-resume') );
				} else {
					wp_safe_redirect( self::get_endpoint_url('manage-job-applied') );
				}
				exit;
			}
		}

		return $html;
	}

	public static function get_actice_enpoint_class($endpoint=''){
		global $wp;
		if( !is_array( $endpoint ) ) $endpoint = array( $endpoint );
		foreach ($endpoint as $ep) {
			if(array_key_exists($ep,(array)$wp->query_vars)){
				return 'active';	
			}
		}

		return '';
	}
	
	public static function get_member_heading_label(){
		global $wp;
		if(get_the_ID() == Noo_Member::get_setting('manage_page_id')):
			foreach (self::instance()->get_query_vars() as $endpoint){
				if(isset($wp->query_vars[$endpoint])){
					switch ($endpoint){
						case 'lost-password':
							return __('Lost Password','noo');
						case 'edit-job':
							return __('Edit Job','noo');
						case 'manage-plan':
							return __('Manage Plan','noo');
						case 'manage-application':
							return __('Manage Applications','noo');
						case 'manage-job':
							return __('Manage Jobs','noo');
						case 'company-profile':
							return __('Company Profile','noo');
						case 'post-resume':
							return __('Post a Resume','noo');
						case 'manage-resume':
							return __('Manage Resumes','noo');
						case 'manage-job-applied':
							return __('Manage Applications','noo');
						case 'job-alert':
							return __('Job Alerts','noo');
						case 'candidate-profile':
							return __('My Profile','noo');
						default:
							return apply_filters( 'jm_member_heading_label', '', $endpoint );
					}
				}
			}
		endif;

		return '';
	}
	
	private function _my_profile_shortcode($atts){
		
	}
	
	private function _lost_password($atts){
		ob_start();
		include(locate_template("layouts/lost-password-form.php"));
		return ob_get_clean();
	}
	
	private function _reset_password($atts){
		$rp_key = isset( $_GET['key'] ) ? $_GET['key'] : '';
    	$rp_login = isset( $_GET['login'] ) ? $_GET['login'] : '';

		if ( empty( $rp_key ) || empty( $rp_login ) ) {
			noo_message_add(__('Reset link misses key or username.','noo'), 'error');
			wp_redirect( wp_lostpassword_url() );
			exit;
		}

		$user = check_password_reset_key( $rp_key, $rp_login );
		if ( ! $user || is_wp_error( $user ) ) {
			if ( $user && $user->get_error_code() === 'expired_key' ) {
				noo_message_add(__('Your reset key is expired.','noo'), 'error');
				wp_redirect( wp_lostpassword_url() );
			} else {
				noo_message_add(__('Invalid reset key.','noo'), 'error');
				wp_redirect( wp_lostpassword_url() );
			}
			exit;
		}

		ob_start();
		include(locate_template("layouts/reset-password-form.php"));
		return ob_get_clean();
	}
	
	private function _edit_job_shortcode($atts){
		if( !isset( $_GET['job_id'] ) || empty( $_GET['job_id'] ) ) {
			noo_message_add( __('Mising Job ID.', 'noo' ), 'error' );
			wp_safe_redirect(self::get_endpoint_url('manage-job'));
			exit;
		}

		if( !self::can_edit_job( $_GET['job_id'] ) ) {
			noo_message_add( __('You can\'t edit this job.', 'noo' ), 'error' );
			wp_safe_redirect(self::get_endpoint_url('manage-job'));
			exit;
		}

		$defaults = array(
			'is_edit'=>true,
		);
		
		return $this->_post_job_shortcode(wp_parse_args($atts,$defaults));
	}
	
	private function _preview_job_shortcode($atts){
		$job_id = isset($_GET['job_id']) ? absint($_GET['job_id']) : 0;
		$query = new WP_Query(array(
			'post__in' => array($job_id),
			'post_type'=>'noo_job',
			'post_status'=>array( 'pending', 'pending_payment' ),
			'post_author'=>get_current_user_id()
		));

		ob_start();
        jm_job_detail($query,true);
		return ob_get_clean();
	}

	private function _manage_application_shortcode($atts){
		if( ! self::is_employer() ) {
			return '<h3>' . __('Only employers can access this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-application.php"));
		return ob_get_clean();
	}

	private function _manage_plans_shortcode($atts){
		if( ! self::is_logged_in() ) {
			return '<h3>' . __('Only logged-in users can see this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-plan.php"));
		return ob_get_clean();
	}

	private function _post_job_shortcode($atts){
		extract(shortcode_atts(array(
			'is_edit'				=>false,
		), $atts));
		ob_start();
		do_action('noo_edit_job_before');
		?>
		<form id="post_job_form" class="form-horizontal" autocomplete="on" method="post" novalidate="novalidate">
			<div style="display: none">
				<input type="hidden" name="action" value="edit_job"> 
				<input type="hidden" name="job_id" value="<?php echo isset($_GET['job_id']) ? absint($_GET['job_id']) : 0?>">
				<?php if($is_edit):?>
				<input type="hidden" name="is_edit" value="1"> 
				<?php endif;?>
				<?php wp_nonce_field('edit-job')?>
			</div>
			<?php noo_get_layout('job_form')?>
			<div class="form-actions form-group text-center">
			 	<button type="submit" class="btn btn-primary"><?php echo esc_html__('Save','noo')?></button>
			 	
			 </div>
		</form>
		<?php
		do_action('noo_edit_job_after');
		return ob_get_clean();
	}

	private function _company_profile_shortcode($atts){
		return self::get_company_profile_form(true);
	}

	private function _checkout($atts){
		if( !self::is_logged_in() ) {
			noo_message_add( __('Please login before buying a Package', 'noo'), 'error' );
			wp_safe_redirect(self::get_member_page_url());
			exit();
		}
		
		do_action('before_package_checkout');

		$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
		if( empty( $product_id ) ) {
			noo_message_add( __('Missing Package ID.', 'noo' ), 'error' );
			wp_safe_redirect(self::get_member_page_url());
			exit();
		}

		$product = wc_get_product($product_id);
		if( !$product ) {
			noo_message_add( __('The Package you selected is removed or unavailable. Please choose another package.', 'noo' ), 'error' );
			wp_safe_redirect(self::get_member_page_url());
			exit();
		}

		wp_safe_redirect( $product->add_to_cart_url() );
		exit();
	}
	
	public static function get_company_profile_form($form=true,$submit_label=''){
		$company_id = isset( $_GET['company_id'] ) ? $_GET['company_id'] : 0;
		if( ! self::can_edit_company( $company_id ) ) {
			return '<p>' . __('You can\'t edit this company', 'noo') . '</p>';
		}

		ob_start();
		if($form):
		do_action('noo_edit_company_before');
		?>
		<div class="form-title">
			<h3><?php _e('Edit Company Info', 'noo'); ?></h3>
		</div>
		<form method="post" id="company_profile_form"  class="form-horizontal jform-validate" autocomplete="on" novalidate="novalidate">
		<?php	
		endif;
		noo_get_layout('company_profile');
		if($form):
		$submit_label = empty($submit_label) ? __('Save','noo') : $submit_label;
		?>
		 <div class="form-group">
		 	<button type="submit" class="btn btn-primary"><?php echo esc_html($submit_label)?></button>
		 	<?php wp_nonce_field('edit-company')?>
		 	<input type="hidden" name="action" value="edit_company">
		 	<input type="hidden" name="company_id" value="<?php echo !empty($company_id) ? absint($company_id) : 0 ?>">
		 </div>
		</form>
		<?php
		echo self::get_update_email_form();
		echo self::get_update_password_form();
		do_action('noo_edit_company_after');
		endif;
		return ob_get_clean();
	}
	
	private function _manage_job_shortcode($atts){
		if( ! self::is_employer() ) {
			return '<h3>' . __('Only employers can access this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-job.php"));
		return ob_get_clean();
	}

	private function _viewed_resume_shortcode($atts){
		if( ! self::is_employer() ) {
			return '<h3>' . __('Only employers can access this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-viewed_resume.php"));
		return ob_get_clean();
	}
	
	private function _edit_resume_shortcode($atts){
		return $this->_post_resume_shortcode($atts);
	}

	private function _post_resume_shortcode($atts){
		ob_start();
		do_action('noo_edit_resume_before');
		?>
		<form id="post_resume_form" class="form-horizontal" autocomplete="on" method="post" novalidate="novalidate">
			<div style="display: none">
				<input type="hidden" name="action" value="edit_resume"> 
				<input type="hidden" name="resume_id" value="<?php echo isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0?>">
				<input type="hidden" name="candidate_id" value="<?php echo get_current_user_id();?>">
				<?php wp_nonce_field('edit-resume')?>
			</div>
			<?php noo_get_layout( 'resume_form' ); ?>
			<div class="form-actions form-group text-center">
			 	<button type="submit" class="btn btn-primary"><?php echo esc_html__('Save','noo')?></button>
			 </div>
		</form>
		<?php
		do_action('noo_edit_resume_after');
		return ob_get_clean();
	}
	
	private function _preview_resume_shortcode($atts){
		$resume_id = isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0;
		$query = new WP_Query(array(
			'post__in' => array($resume_id),
			'post_type'=>'noo_resume',
			'post_status'=>array( 'pending', 'pending_payment' ),
			'post_author'=>get_current_user_id()
		));

		ob_start();
        jm_resume_detail($query);
		return ob_get_clean();
	}

	private function _manage_resume_shortcode($atts){
		if( ! self::is_candidate() ) {
			return '<h3>' . __('Only candidates can access this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-resume.php"));
		return ob_get_clean();
	}

	private function _manage_job_applied_shortcode($atts){
		if( ! self::is_candidate() ) {
			return '<h3>' . __('Only candidates can access this page', 'noo') . '</h3>';
		}
		ob_start();
        include(locate_template("layouts/manage-job_applied.php"));
		return ob_get_clean();
	}
	
	private function _job_alert_shortcode($atts){
		if( !Noo_Job_Alert::enable_job_alert() ) {
			return;
		}

		if( ! self::is_candidate() ) {
			return '<h3>' . __('Only candidates can access this page', 'noo') . '</h3>';
		}

		ob_start();
        include(locate_template("layouts/manage-job_alert.php"));
		return ob_get_clean();
	}

	private function _edit_job_alert_shortcode($atts){
		return $this->_add_job_alert_shortcode($atts);
	}

	private function _add_job_alert_shortcode($atts){
		if( !Noo_Job_Alert::enable_job_alert() ) {
			return;
		}

		ob_start();
		do_action('noo_edit_job_alert_before');
		$is_edit = isset($_GET['job_alert_id']) && is_numeric( $_GET['job_alert_id'] );
		?>
		<form id="add_job_alert_form" class="form-horizontal" autocomplete="on" method="post" novalidate="novalidate">
			<div style="display: none">
				<input type="hidden" name="action" value="edit_job_alert"> 
				<input type="hidden" name="job_alert_id" value="<?php echo ( $is_edit ? absint($_GET['job_alert_id']) : 0 );?>">
				<input type="hidden" name="candidate_id" value="<?php echo get_current_user_id();?>">
				<?php wp_nonce_field('edit-job-alert')?>
			</div>
			<div class="form-title">
				<?php if( $is_edit ) : ?>
				<h3><?php _e('Edit Job alert', 'noo'); ?></h3>
				<?php else : ?>
				<h3><?php _e('Create a new Job alert', 'noo'); ?></h3>
				<?php endif; ?>
			</div>
			<?php noo_get_layout('job_alert_form')?>
			<div class="form-actions form-group text-center">
			 	<button type="submit" class="btn btn-primary"><?php echo esc_html__('Save','noo')?></button>
			 </div>
		</form>
		<?php
		do_action('noo_edit_job_alert_after');
		return ob_get_clean();
	}
	
	private function _candidate_profile_shortcode($atts){
		if( ! self::is_candidate() ) {
			return '<h3>' . __('Only candidates can access this page', 'noo') . '</h3>';
		}
		$candidate_id = isset( $_GET['candidate_id'] ) ? $_GET['candidate_id'] : get_current_user_id();
		if( ! self::can_edit_profile( $candidate_id ) ) {
			return '<p>' . __('You can\'t edit this profile', 'noo') . '</p>';
		}
		ob_start();
		do_action('noo_edit_candidate_before');
		?>
		<div class="form-title">
			<h3><?php _e('Change Profile', 'noo'); ?></h3>
		</div>
		<form method="post" id="candidate_profile_form"  class="form-horizontal" autocomplete="on" novalidate="novalidate">
		<?php	
		noo_get_layout('candidate_profile');
		$submit_label = __('Save My Profile','noo');
		?>
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><?php echo esc_html($submit_label)?></button>
				<?php wp_nonce_field('edit-candidate')?>
				<input type="hidden" name="action" value="edit_candidate">
				<input type="hidden" name="candidate_id" value="<?php echo absint( $candidate_id ); ?>">
			</div>
		</form>
		<?php
		echo self::get_update_password_form();
		do_action('noo_edit_candidate_after');
		return ob_get_clean();
	}

	public static function get_update_email_form( $user_id = 0 ) {
		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		ob_start();
		do_action('noo_update_email_before');
		?>
		<hr/>
		<?php
		noo_get_layout('update_email');
		?>
		<?php
		do_action('noo_update_email_after');
		return ob_get_clean();
	}

	public static function get_update_password_form( $user_id = 0 ) {
		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		ob_start();
		do_action('noo_update_password_before');
		?>
		<hr/>
		<?php
		noo_get_layout('update_password');
		?>
		<?php
		do_action('noo_update_password_after');
		return ob_get_clean();
	}
	
	public static function is_logged_in() {
		return is_user_logged_in();
	}

	public static function is_candidate( $user_id = null ) {
		return self::CANDIDATE_ROLE == self::get_user_role($user_id);
	}

	public static function is_employer( $user_id = null ) {
		// @TODO: Remove administrator when finished.
		$user_role = self::get_user_role($user_id);
		return self::EMPLOYER_ROLE == $user_role || 'administrator' == $user_role;
	}

	public static function get_user_role( $user_id = null ) {
		$role = '';

// 		if( empty( $user_id ) ) {
// 			// Check current user
// 			if( ! self::is_logged_in() ) {
// 				return '';
// 			}

// 			$current_user = wp_get_current_user();

// 			$user_roles = $current_user->roles;
// 			$user_role = array_shift($user_roles);
// 		} else {
// 			$user = get_userdata( $user_id );

// 			$user_roles = $user->roles;
// 			$user_role = array_shift($user_roles);
// 		}
		
		if(empty($user_id)){
			if( ! self::is_logged_in() ) {
				return '';
			}
			$user = wp_get_current_user();
		}else{
			$user = get_userdata( $user_id );
		}

		if( !$user ) {
			return '';
		}
		
		if(!function_exists('get_editable_roles'))
			include_once( ABSPATH . 'wp-admin/includes/user.php' );
		$editable_roles = array_keys( get_editable_roles() );
		if ( count( $user->roles ) <= 1 ) {
			$role = reset( $user->roles );
		} elseif ( $roles = array_intersect( array_values( $user->roles ), $editable_roles ) ) {
			$role = reset( $roles );
		} else {
			$role = reset( $user->roles );
		}
		return $role;
	}

	public static function get_post_resume_url() {
		return noo_get_page_link_by_template('page-post-resume.php') . '#jform';
	}

	public static function get_post_job_url() {
		return noo_get_page_link_by_template('page-post-job.php') . '#jform';
	}

	/*** Permission checking ***/
	/* ======================= */

	public static function can_edit_profile($candidate_id = 0, $user_id = 0) {
		if( !self::is_logged_in() ) return false;
		if( empty( $candidate_id ) ) return true;

		$user_id = empty( $user_id ) ? get_current_user_id() : 0;
		if( empty($user_id) ) return false;

		return $candidate_id == $user_id;
	}

	public static function can_edit_job($job_id = 0, $user_id = 0) {
		return jm_can_edit_job( $job_id, $user_id );
	}

	public static function can_change_job_state($job_id = 0, $user_id = 0) {
		return jm_can_change_job_state( $job_id, $user_id );
	}

	public static function can_edit_company($company_id = 0, $user_id = 0) {
		if( !self::is_employer() ) return false;
		if( empty( $company_id ) ) return true;

		$user_id = empty( $user_id ) ? get_current_user_id() : 0;
		if( empty($user_id) ) return false;

		return $company_id == jm_get_employer_company( $user_id );
	}

	public static function can_post_resume($user_id = 0) {
		return jm_can_post_resume($user_id);
	}

	public static function is_resume_owner($user_id = 0, $resume_id = 0) {
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

		if( empty($user_id) || empty($resume_id) ) return false;

		$candidate_id = get_post_field( 'post_author', $resume_id );

		return $candidate_id == $user_id;
	}

	public static function is_job_alert_owner($user_id = 0, $job_alert_id = 0) {
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

		if( empty($user_id) || empty($job_alert_id) ) return false;

		$candidate_id = get_post_field( 'post_author', $job_alert_id );

		return $candidate_id == $user_id;
	}
	
	public static function modal_application($application_id,$type=''){
		$display = '';
		$avatar = '<img class="avatar avatar-40 photo" width="40" height="40" src="'.get_template_directory_uri().'/assets/images/avatar-40.png" alt="">';
		$ids = array();
		if( strpos($application_id, ',') === false ) {
			$application = get_post($application_id);
			if($application->post_type != 'noo_application' || empty($type)){
				return '';
			}
			
			if($_candidate_user_id = noo_get_post_meta($application->ID,'_candidate_user_id')){
				$_candidate_userdata = get_userdata($_candidate_user_id);
				$display = !empty( $_candidate_userdata ) ? $_candidate_userdata->display_name : '';
				$avatar = noo_get_avatar($_candidate_user_id, 40);
			}else{
				$display = $application->post_title;
			}
		} else {
			$display = __('your candidates', 'noo');
		}
		ob_start();
        include(locate_template("layouts/manage-approve-reject.php"));
		echo ob_get_clean();
	}

	public static function modal_employer_message($application_id,$mode=0){
		$application = get_post($application_id);
		if($application->post_type != 'noo_application' ){
			return '';
		}

		// -- get id candidate
			$user = wp_get_current_user();
		// -- default meta
			$key_meta = '_check_view_applied';
		// get value in meta -> array
			$check_view = get_user_meta( $user->ID, $key_meta, true ) ? (array) get_user_meta( $user->ID, $key_meta, true ) : array();
			
			$arr_value = array_merge($check_view, array( $application_id ) );
			
			if ( !in_array ( $application_id, $check_view ) ):
				update_user_meta( $user->ID, $key_meta, $arr_value);
			endif;
		
		$job = get_post( $application->post_parent );
		$logo_company = '';
		$company_id = jm_get_job_company( $job );
		if( !empty( $company_id ) ) {
			$logo_company 	= Noo_Company::get_company_logo( $company_id );
		}
		ob_start();
        include(locate_template("layouts/manage-application-message.php"));
		echo ob_get_clean();
	}

	public function modal_login(){
		$perfix = uniqid(); 

		?>
		<div class="memberModalLogin modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $perfix ?>_memberModalLoginLabel" aria-hidden="true">
			<div class="modal-dialog modal-member">
		    	<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="<?php echo $perfix ?>_memberModalLoginLabel"><?php esc_html_e('Login','noo')?></h4>
				     </div>
				      <div class="modal-body">
				        <?php self::ajax_login_form()?>
				      </div>
				</div>
			</div>
		</div>
		<?php
	}
	
	public function modal_register(){
		$prefix = uniqid();
		?>
		<div class="memberModalRegister modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $prefix ?>_memberModalRegisterLabel" aria-hidden="true">
			<div class="modal-dialog modal-member">
		    	<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				        <h4 class="modal-title" id="<?php echo $prefix ?>_memberModalRegisterLabel"><?php esc_html_e('Sign Up','noo')?></h4>
				     </div>
				      <div class="modal-body">
				        <?php self::ajax_register_form()?>
				      </div>
				</div>
			</div>
		</div>
		<?php
	}
	
	public static function ajax_login_form($submit_label=''){
		ob_start();
        include(locate_template("layouts/login-form.php"));
		echo ob_get_clean();	
	}
	
	public static function ajax_register_form($submit_label=''){
		ob_start();
        include(locate_template("layouts/register-form.php"));
		echo ob_get_clean();
	}
	
	private function _create_cron_jobs(){
		if ( get_option( 'noo_member_cron_job' ) == '1' ) {
			return;
		}
		
		$this->add_endpoints();
		flush_rewrite_rules();
		
		delete_option('noo_member_cron_job');
		update_option('noo_member_cron_job', '1');
	}

	public function add_seting_member_tab($tabs){
		$temp1 = array_slice($tabs, 0, 4);
		$temp2 = array_slice($tabs, 4);

		$member_tab = array( 'member' => __('Member','noo') );
		return array_merge($temp1, $member_tab, $temp2);
	}
	
	public function setting_page(){
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'])
		{
			flush_rewrite_rules();
		}
		?>
		<?php settings_fields('noo_member'); ?>
		<h3><?php echo __('Member Options','noo')?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr>
					<th>
						<?php _e('Member Manage Page','noo')?>
					</th>
					<td>
						<?php 
						$args = array(
							'name'             => 'noo_member[manage_page_id]',
							'id'               => 'manage_page_id',
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => 'noo-admin-chosen',
							'echo'             => false,
							'selected'         => self::get_member_page_id()
						);
						?>
						<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'noo' ) .  "' id=", wp_dropdown_pages( $args ) ); ?>
						<p><small><?php _e('Select a page with shortcode [noo_member_account]', 'noo'); ?></small></p>
					</td>
				</tr>	
				<tr>
					<th>
						<?php _e('Member Page Title show','noo')?>
					</th>
					<td>
						<?php $member_title = self::get_setting('member_title', 'page_title'); ?>
						<fieldset>
							<label><input type="radio" <?php checked( $member_title, 'page_title' ); ?> name="noo_member[member_title]" value="page_title"><?php _e('Page Title', 'noo'); ?></label><br/>
							<label><input type="radio" <?php checked( $member_title, 'username' ); ?> name="noo_member[member_title]" value="username"><?php _e('Current user\'s name', 'noo'); ?></label><br/>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Use Member page as WordPress Login/Register', 'noo')?>
					</th>
					<td>
						<input type="hidden" name="noo_member[use_custom_login]" value="0" />
						<input type="checkbox" name="noo_member[use_custom_login]" value="1" <?php checked( self::get_setting('use_custom_login', true) );?> />
						<small><?php _e('If this option is enabled, all login links ( /wp-admin included ) will be redirect to the Member page', 'noo'); ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Who can Register?','noo')?>
					</th>
					<td>
						<?php if( get_option('users_can_register', true) ) : ?>
							<?php $allow_register = self::get_setting('allow_register', 'both');
							?>
							<select name="noo_member[allow_register]" >
								<option value="both" <?php selected( $allow_register, 'both' );?> ><?php _e('Both Employer & Candidate', 'noo'); ?></option>
								<option value="employer" <?php selected( $allow_register, 'employer' );?> ><?php _e('Only Employer', 'noo'); ?></option>
								<option value="candidate" <?php selected( $allow_register, 'candidate' );?> ><?php _e('Only Candidate', 'noo'); ?></option>
								<option value="none" <?php selected( $allow_register, 'none' );?> ><?php _e('Disable Register', 'noo'); ?></option>
							</select>
						<?php else: ?>
							<h4><?php echo sprintf( __( 'Registration is not enabled on this site. To enable it please go to %s and allow Anyone can register.', 'noo' ), '<a href="' . admin_url('options-general.php') . '">' . __( 'General Setting', 'noo' ) . '</a>' ); ?></h4>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Enable Captcha on Registration','noo')?>
					</th>
					<td>
						<input type="checkbox" name="noo_member[register_using_captcha]" value="1" <?php checked( self::get_setting('register_using_captcha', false) );?> />
						<p><small><?php echo sprintf( __('By default, simple Captcha is enabled. To use Google re-Captcha, please go to <a href="%s">APIs setting</a> to add your re-Captcha key', 'noo'), jm_setting_page_url('3rd_api') . '#google-recaptcha-key' ); ?></small></p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Hide Admin Bar','noo')?>
					</th>
					<td>
						<input type="hidden" name="noo_member[hide_admin_bar]" value="0" />
						<input type="checkbox" name="noo_member[hide_admin_bar]" value="1" <?php checked( self::get_setting('hide_admin_bar', true) );?> />
						<small><?php _e('Hide Admin bar for users ( Candidates and Employeres ) registered from now.', 'noo'); ?></small>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Terms and Conditions Page','noo')?>
					</th>
					<td>
						<?php 
						$args = array(
							'name'             => 'noo_member[term_page_id]',
							'id'               => 'term_page_id',
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => 'noo-admin-chosen',
							'echo'             => false,
							'selected'         => self::get_setting('term_page_id')
						);
						?>
						<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'noo' ) .  "' id=", wp_dropdown_pages( $args ) ); ?>
						<small><?php _e('This page used for "I agree with the Terms of use" on Registration form', 'noo'); ?></small>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Enable Login Using Email','noo')?>
					</th>
					<td>
						<input type="checkbox" name="noo_member[register_using_email]" value="1" <?php checked( self::get_setting('register_using_email', false) );?> />
						<small><?php _e('Enabling this option will remove username and only use email for login/registration.', 'noo'); ?></small>
					</td>
				</tr>

				<?php do_action( 'noo_setting_member_fields' ); ?>

			</tbody>
		</table>
		<?php
	}
}
Noo_Member::instance();

endif;

if( !function_exists( 'noo_get_avatar' ) ) :
	if( get_option('show_avatars') && function_exists( 'get_avatar' ) ) :
		function noo_get_avatar( $id_or_email = '', $size = '' ) {
			return get_avatar( $id_or_email, $size );
		}
	else :
		function noo_get_avatar( $id_or_email = '', $size = '' ) {
			return Noo_Member::instance()->get_avatar( '', $id_or_email, $size );
		}
	endif;
endif;