<?php
if(!class_exists('Noo_Resume')):
	if( !class_exists('Noo_CPT') ) {
		require_once dirname(__FILE__) . '/noo_cpt.php';
	}
	class Noo_Resume extends Noo_CPT {

		static $instance = false;
		
		public static function get_setting( $id, $default = null ) {
			return jm_get_resume_setting( $id, $default );
		}

		public static function enable_resume_detail() {
			$education = jm_get_resume_setting('enable_education', '1');
			$experience = jm_get_resume_setting('enable_experience', '1');
			$skill = jm_get_resume_setting('enable_skill', '1');

			return $education || $experience || $skill;
		}

		public static function notify_candidate( $resume_id = null, $user_id = 0 ) {
			if( !Noo_Member::is_logged_in() || empty( $resume_id ) ) {
				return false;
			}

			$user = false;
			if( !empty( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = wp_get_current_user();
			}

			if( !$user ) {
				return false;
			}

			if( !Noo_Member::is_resume_owner( $user->ID, $resume_id ) ) {
				return false;
			}

			$emailed = noo_get_post_meta( $resume_id, '_new_resume_emailed', 0 );
			if( $emailed ) {
				return false;
			}

			$candidate_email = $user->user_email;
			$resume = get_post($resume_id);
			$job_location = noo_get_post_meta($resume_id, '_job_location');
			if( !empty( $job_location ) ) {
				$job_location = noo_json_decode($job_location);
				$job_location_terms = empty( $job_location ) ? array() : get_terms( 'job_location', array('include' => array_merge( $job_location, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );
				$job_location = implode(', ', $job_location_terms);
			}
			$job_category = noo_get_post_meta($resume_id, '_job_category');
			if( !empty( $job_category ) ) {
				$job_category = noo_json_decode($job_category);
				$job_category_terms = empty( $job_category ) ? array() : get_terms( 'job_category', array('include' => array_merge( $job_category, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );
				$job_category = implode(', ', $job_category_terms);
			}

			$resume_need_approve = jm_get_resume_setting( 'resume_approve','yes' ) == 'yes';
			if( $resume_need_approve ) {
				$resume_link = esc_url( add_query_arg( 'resume_id', $resume_id, Noo_Member::get_endpoint_url('preview-resume') ) );
			} else {
				$resume_link = get_permalink($resume_id);
			}

			// admin email
			if( jm_et_get_setting('admin_resume_activated') ) {
				$subject = jm_et_get_setting('admin_resume_subject');

				$array_subject = array(
					'[resume_title]' => get_the_title($resume_id),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);
				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$to = get_option('admin_email');

				$array_message = array(
					'[resume_title]' => get_the_title($resume_id),
					'[resume_url]' => $resume_link,
					'[resume_category]' => $job_category,
					'[resume_location]' => $job_location,
					'[candidate_name]' => $user->display_name,
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('admin_resume_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				$subject = jm_et_custom_field('resume',$resume_id, $subject);
				$message = jm_et_custom_field('resume',$resume_id, $message);

				noo_mail($to, $subject, $message ,array(),'noo_notify_resume_submitted_admin');
			}

			//candidate email
			if( jm_et_get_setting('candidate_resume_activated') ) {
				$blogname = get_bloginfo( 'name' );
				$to = $user->user_email;

				$subject = jm_et_get_setting('candidate_resume_subject');

				$array_subject = array(
					'[resume_title]' => get_the_title($resume_id),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$array_message = array(
					'[resume_title]' => get_the_title($resume_id),
					'[resume_url]' => $resume_link,
					'[resume_category]' => $job_category,
					'[resume_location]' => $job_location,
					'[candidate_name]' => $user->display_name,
					'[resume_manage_url]' => Noo_Member::get_endpoint_url('manage-resume'),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('candidate_resume_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				$subject = jm_et_custom_field('resume',$resume_id, $subject);
				$message = jm_et_custom_field('resume',$resume_id, $message);

				noo_mail($to, $subject, $message,array(),'noo_notify_resume_submitted_candidate');
			}

			update_post_meta( $resume_id, '_new_resume_emailed', 1 );
		}

		public function __construct(){

			$this->post_type = 'noo_resume';
			$this->slug = 'resumes';
			$this->prefix = 'resume';
			$this->option_key = 'noo_resume';

			$this->setting_title = __('Resume Settings', 'noo');


			// add_shortcode('noo_resume', array(&$this,'noo_resume_shortcode'));

			// add_action('wp_ajax_nopriv_noo_resume_nextajax', array(&$this,'noo_resume_shortcode'));
			// add_action('wp_ajax_noo_resume_nextajax', array(&$this,'noo_resume_shortcode'));
			add_action( 'save_post', array( $this, 'save_data_attachment_file' ) );
		}

		public function save_data_attachment_file( $post_id ) {

			// Check if our nonce is set.
				if ( ! isset( $_POST['attachment_file_nonce'] ) ) {
					return;
				}

			// Verify that the nonce is valid.
				if ( ! wp_verify_nonce( $_POST['attachment_file_nonce'], 'save_attachment_file' ) ) {
					return;
				}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}

			// Sanitize user input.
				$my_data = sanitize_text_field( $_POST['file_cv'] );

			// Update the meta field in the database.
				update_post_meta( $post_id, '_noo_file_cv', $my_data );

		}

		public static function is_page_post_resume(){
			return jm_is_resume_posting_page();
		}
		
		public static function need_login(){
			return !Noo_Member::is_candidate();
		}

		public static function login_handler(){
			if(!self::need_login()){
				wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'postresume')));
			}
			return;
		}

		public static function get_default_fields() {
			return jm_get_resume_default_fields();
		}

		public static function count_viewable_resumes( $candidate_id = 0, $count_all = false ) {
			if( empty( $candidate_id ) && !$count_all ) return 0;

			$args = array(
				'post_type'=>'noo_resume',
				'post_per_page'=>-1,
				'post_status'=>array('publish'),
				'author'=>$candidate_id,
				'meta_query'=>array(
					array(
						'key' => '_viewable',
						'value' => 'yes',
					),
				)
			);

			if( !$count_all ) {
				$args['author'] = $candidate_id;
			}

			$query = new WP_Query( $args );

			return $query->found_posts;
		}
		public static function can_view_resume( $resume_id = null,$is_loop = false) {
			return jm_can_view_resume( $resume_id, $is_loop );
		}

		public static function get_resume_permission_message( $viewable = true ) {
			$title = __('You don\'t have permission to view resumes.','noo');
			$link = '';
			$can_view_resume_setting = jm_get_resume_setting('can_view_resume','employer');
			if( !$viewable ) {
				$title = __('This resume is private.','noo');
			} elseif( $can_view_resume_setting == 'employer' ) {
				$title = __('Only employers can view resumes.','noo');
				$link = Noo_Member::get_logout_url();

				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '"><i class="fa fa-long-arrow-right"></i>&nbsp;' . __( 'Login as Employer', 'noo' ) . '</a>';
				} elseif( !Noo_Member::is_employer() ) {
					$link = Noo_Member::get_logout_url();
					$link = '<a href="' . esc_url( $link ) . '"><i class="fa fa-long-arrow-right"></i>&nbsp;' . __( 'Logout then Login as Employer', 'noo' ) . '</a>';
				}
			} elseif( $can_view_resume_setting == 'package' ) {
				$title = __('Only paid employers can view resumes.','noo');
				$link = Noo_Member::get_endpoint_url('manage-plan');

				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '"><i class="fa fa-long-arrow-right"></i>&nbsp;' . __( 'Login as Employer', 'noo' ) . '</a>';
				} elseif( !Noo_Member::is_employer() ) {
					$link = Noo_Member::get_logout_url();
					$link = '<a href="' . esc_url( $link ) . '"><i class="fa fa-long-arrow-right"></i>&nbsp;' . __( 'Logout then Login as Employer', 'noo' ) . '</a>';
				} else {
					$title = __('Your membership doesn\'t allow you to view resumes.','noo');
					$link = Noo_Member::get_endpoint_url('manage-plan');
					$link = '<a href="' . esc_url( $link ) . '"><i class="fa fa-long-arrow-right"></i>&nbsp;' . __( 'Upgrade your membership', 'noo' ) . '</a>';
				}
			}

			return array( $title, $link );
		}

		public static function display_detail($query=null, $hide_profile=false) {
			jm_resume_detail( $query, $hide_profile );
		}

		public static function noo_resume_shortcode( $atts, $content = null ) {
			return jm_noo_resumes_shortcode( $atts, $content );
		}

		public static function loop_display( $args = '' ) {
			jm_resume_loop( $args );
		}
	}

	new Noo_Resume();
endif;
