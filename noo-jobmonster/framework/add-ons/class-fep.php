<?php
/*
Plugin Name: JobMonster - Font-End PM integration
Plugin URI: https://www.nootheme.com
Description: This plugin help integrates the chat function of FEP to your JobMonster theme
Version: 1.1.0
Author: NooTheme
Author URI: https://www.nootheme.com/
*/

// === << Check class exits

if ( !class_exists( 'Noo_Fep_Integration' ) ) :

	class Noo_Fep_Integration {

		static $cache;

		function __construct() {
            self::$cache = array();

			if(is_admin()){
				add_action('admin_init', array($this,'admin_init'));
				add_filter('noo_job_settings_tabs_array', array( $this, 'add_seting_jobmonster_fep_tab' ));
				add_action('noo_job_setting_jobmonster_fep', array( $this, 'setting_page' ));
			}

			if( self::get_setting('enable_fep', false) && self::is_front_end_pm_activated() ) {

            	add_action( 'init', array( $this, 'unregister_announcement' ), 11 );

				if( is_admin() ) {
					add_filter( 'job_manager_admin_actions', array( $this, 'dashboard_message_action' ), 10, 2);
					add_filter( 'resume_manager_admin_actions', array( $this, 'dashboard_message_action' ), 10, 2);
				}

				// -- Load script
				add_action( 'wp_enqueue_scripts', array( &$this, 'load_enqueue_script' ), 99 );

				// -- Theme's hooks
				add_filter( 'jm_member_heading_label', array( $this, 'member_heading_label' ), 10, 2 );
				add_filter( 'noo-member-page-endpoint', array( $this, 'member_page_endpoint' ) );
				add_filter( 'noo-member-employer-shortcode', array( $this, 'private_message_shortcode' ), 10, 2 );
				add_filter( 'noo-member-candidate-shortcode', array( $this, 'private_message_shortcode' ), 10, 2 );
				add_action( 'noo-member-employer-heading', array( $this, 'member_heading' ) );
				add_action( 'noo-member-candidate-heading', array( $this, 'member_heading' ) );

				add_action( 'noo-member-employer-menu', array( $this, 'member_menu') );
				add_action( 'noo-member-candidate-menu', array( $this, 'member_menu') );
				add_filter( 'noo-user-notifications-number', array( $this, 'user_notifications_number') );

				// Candidate Profile
				add_action( 'noo_resume_candidate_profile_after', array( $this, 'candidate_send_message_btn' ) );

				// Message links
				add_filter( 'noo-manage-application-email-link', array( $this, 'manage_application_email_link' ), 10, 2 );

				add_filter( 'noo-manage-application-message-link', array( $this, 'manage_application_message_link' ), 10, 2 );
				add_filter( 'noo-manage-job-applied-message-link', array( $this, 'manage_job_applied_message_link' ), 10, 2 );

				add_action( 'noo-manage-job-applied-action', array( $this, 'manage_job_applied_action') );

				// Action after apply job
				add_action( 'new_job_application', array( $this, 'new_application_action') );

				// Action after approve/rejected
				add_action( 'manage_application_action_approve', array( $this, 'manage_application_action') );
				add_action( 'manage_application_action_reject', array( $this, 'manage_application_action') );

				// -- FED hooks
				add_filter( 'fep_page_id_filter', array( $this, 'fep_page_id_filter') );
				add_filter( 'fep_using_auth_redirect', '__return_false' );

				add_filter( 'fep_menu_buttons', array( $this, 'fep_menu_buttons' ), 11 );
				add_filter( 'fep_current_user_can', array( $this, 'fep_current_user_can' ), 11, 3 );
				add_filter( 'fep_settings_fields', array( $this, 'fep_settings_fields' ), 11 );

				add_filter( 'fep_autosuggestion_arguments', array( $this, 'chatable_user_query_args') );
				add_filter( 'fep_directory_arguments', array( $this, 'chatable_user_query_args') );
				add_action( 'fep_action_message_after_send', array( $this, 'fed_message_after_send' ), 10, 2 );
			}
		}

		public function admin_init(){
			register_setting( 'jobmonster_fep' , 'jobmonster_fep' );
			
			if( self::get_setting('enable_fep', false) && self::is_front_end_pm_activated() ) {
				remove_submenu_page( 'fep-admin-settings', 'fep-instruction' );
			}
		}

		public function load_enqueue_script() {
			wp_register_style( 'jobmonster-fed', NOO_ASSETS_URI . '/css/fed.css' );
			wp_enqueue_style( 'jobmonster-fed' );
		}

		public function member_page_endpoint( $endpoints = array() ) {
			$endpoints = array_merge( $endpoints, array(
				'private-messages' => 'private-messages',
				'fepaction' => 'fepaction'
			) );

			return $endpoints;
		}

		public function member_heading_label( $label, $endpoint ) {
			if( $endpoint == 'private-messages' || $endpoint == 'fepaction' ) {
				return __('Private Messages','noo');
			}
			
			return $label;
		}

		public function member_heading() {
			?>
			<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class(array( 'private-messages', 'fepaction' )))?>">
				<a href="<?php echo Noo_Member::get_endpoint_url('private-messages') ?>">
					<i class="fa fa-comments-o"></i>
					<?php _e('Messages','noo');
					$numNew = fep_get_new_message_number(); 
					if( !empty( $numNew ) ) : ?>
						<span class="badge"><?php echo fep_get_new_message_number(); ?></span>
					<?php endif; ?>
				</a>
			</li>
			<?php
		}

		public function member_menu() {
			?>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('private-messages')?>"><i class="fa fa-comments-o"></i> <?php _e('Messages','noo')?></a></li>
			<?php
		}

		public function user_notifications_number( $number ) {
			$number += fep_get_new_message_number();

			return $number;
		}

		public function candidate_send_message_btn( $resume_id = 0 ) {
			if( empty( $resume_id ) ) {
				return;
			}

			$candidate_profile_message = self::get_setting('candidate_profile_message', false);
			$is_public_profile = apply_filters( 'jm_resume_show_candidate_contact', true, $resume_id );
			if( $candidate_profile_message == 'no'
				|| ( $candidate_profile_message == 'private_profile' && $is_public_profile )
				|| ( $candidate_profile_message == 'public_profile' && !$is_public_profile )
			) {
				return;
			}

			if( 'noo_resume' == get_post_type( $resume_id ) ) {
				$candidate_id = get_post_field( 'post_author', $resume_id);
			}

			if( empty( $candidate_id ) ) {
				return;
			}

			$user_id = get_current_user_id();
			$target = get_userdata( $candidate_id );
			if( !empty( $target ) && $this->can_start_conversation_with( $user_id, $target->ID ) ) :
				$url = esc_url( add_query_arg( array( 'to' => $target->user_login ), $this->fep_action_url('newmessage') ) );
			?>
				<div class="candidate-message-action">
					<a href="<?php echo $url; ?>" class="btn btn-primary"><i class="fa fa-commenting-o"></i><?php echo esc_attr__('Send Message','noo'); ?></a>
				</div>
			<?php endif;
		}

		public function manage_application_email_link( $link = '', $application_id = 0 ) {
			if( empty( $application_id ) ) {
				return $link;
			}

			$user_id = get_current_user_id();
			$candidate_email = get_post_meta( $application_id, '_candidate_email', true );
			$target = get_user_by( 'email', $candidate_email );
			if( !empty( $target ) && $this->can_start_conversation_with( $user_id, $target->ID ) ) {
				$url = esc_url( add_query_arg( array( 'to' => $target->user_login ), $this->fep_action_url('newmessage') ) );
				$link = '<a href="' . $url . '" class="member-manage-action" data-toggle="tooltip" title="' . esc_attr__('Send Message','noo') . '"><i class="fa fa-commenting-o"></i></a>';
			}

			return $link;
		}

		public function manage_job_applied_action( $application_id = 0 ) {
			if( empty( $application_id ) ) {
				return;
			}

			$option = self::get_setting( 'candidate_message', 'none' );
			$status = get_post_status($application_id );

			// Check option & application status.
			if( ( $option == 'approved_employer' && $status != 'publish' ) 
				|| ( $option == 'approved_rejected_employer' && $status != 'publish' && $status != 'rejected' ) ) {
				return;
			}

			$user_id = get_current_user_id();
			$job_id = get_post_field( 'post_parent', $application_id );
			$target_id = !empty( $job_id ) ? get_post_field( 'post_author', $job_id ) : '';

			if( !empty( $target_id ) && $this->can_start_conversation_with( $user_id, $target_id ) ) {
				$target = get_userdata( $target_id );
				$url = esc_url( add_query_arg( array( 'to' => $target->user_login ), $this->fep_action_url('newmessage') ) );
				echo '<a href="' . $url . '" class="member-manage-action" data-toggle="tooltip" title="' . esc_attr__('Send Message','noo') . '"><i class="fa fa-commenting-o"></i></a>';
			}
		}

		public function manage_application_message_link( $link = '', $application_id = 0 ) {
			if( empty( $application_id ) || !self::get_setting('enable_approve_reject', true) ) {
				return $link;
			}

			$message_id = get_post_meta( $application_id, 'fed_candidate_message_id', true );
			if( !empty( $message_id ) ) {
				$args = array(
					'id' => $message_id,
				);
				$url = esc_url( add_query_arg( $args, $this->fep_action_url('viewmessage') ) );
				$link = '<a href="' . $url . '" class="member-manage-action"><em class="text-primary">' . __('View Message', 'noo') . '&nbsp;<i class="fa fa-long-arrow-right"></i></em></a>';
			}

			return $link;
		}

		public function manage_job_applied_message_link( $link = '', $application_id = 0 ) {
			if( empty( $application_id ) || !self::get_setting('enable_approve_reject', true) ) {
				return $link;
			}

			$message_id = get_post_meta( $application_id, 'fed_employer_message_id', true );
			if( !empty( $message_id ) ) {
				$args = array(
					'id' => $message_id,
				);
				$url = esc_url( add_query_arg( $args, $this->fep_action_url('viewmessage') ) );
				$link = '<a href="' . $url . '" class="member-manage-action"><em class="text-primary">' . __('Continue reading', 'noo') . '&nbsp;<i class="fa fa-long-arrow-right"></i></em></a>';
			}

			return $link;
		}

		public function new_application_action( $application_id = 0 ) {
			if( empty( $application_id ) || !self::get_setting('enable_apply_job', false) ) {
				return;
			}

			$user_id = get_current_user_id();
			if( !empty( $user_id ) ) {
				$application = get_post( $application_id );
				$job = !empty( $application ) ? get_post( $application->post_parent ) : false;
				$target = !empty( $job ) ? get_userdata( $job->post_author ) : false;
				if( !empty( $target ) ) {
					$message = array(
						'message_to_id' => $target->ID,
						'message_title' => sprintf( __('New application for job %s', 'noo'), $job->post_title ),
						'message_content' => empty( $application->post_content ) ? __('No message', 'noo') : $application->post_content,
						'message_from' => $user_id,
						'jm_candidate_application_id' => $application_id, // follow up to save back to application once message is sent. See function fed_message_after_send(),
					);
					$override = array(
						'post_status' => 'publish',
						'post_author' =>$user_id,
					);

					if( function_exists( 'fep_send_message' ) ) {
						fep_send_message( $message, $override );
					}
				}
			}
		}

		public function manage_application_action( $application_id = 0 ) {
			if( empty( $application_id ) || !self::get_setting('enable_approve_reject', true) ) {
				return;
			}

			$user_id = get_current_user_id();
			if( !empty( $user_id ) ) {
				$candidate_email = get_post_meta( $application_id, '_candidate_email', true );
				$target = get_user_by( 'email', $candidate_email );
				if( !empty( $target ) ) {
					$message = array(
						'message_to_id' => $target->ID,
						'message_title' => get_post_meta( $application_id, '_employer_message_title', true ),
						'message_content' => get_post_meta( $application_id, '_employer_message_body', true ),
						'message_from' => $user_id,
						'jm_candidate_application_id' => $application_id, // follow up to save back to application once message is sent. See function fed_message_after_send(),
					);
					$override = array(
						'post_status' => 'publish',
						'post_author' =>$user_id,
					);

					if( function_exists( 'fep_send_message' ) ) {
						fep_send_message( $message, $override );
					}
				}
			}
		}

		public function fed_message_after_send( $message_id = '', $message = array() ) {
			$candidate = false;
			$application_id = 0;
			if( isset( $message['jm_employer_application_id'] ) && !empty( $message['jm_employer_application_id'] ) ) {
				$application_id = absint( $message['jm_employer_application_id'] );
				update_post_meta( $application_id, 'fed_employer_message_id', $message_id );
			}
			if( isset( $message['jm_candidate_application_id'] ) && !empty( $message['jm_candidate_application_id'] ) ) {
				$application_id = absint( $message['jm_candidate_application_id'] );
				update_post_meta( $application_id, 'fed_candidate_message_id', $message_id );
			}

			if( !empty( $application_id ) ) {
				$candidate_email = get_post_meta( $application_id, '_candidate_email', true );
				$candidate = get_user_by( 'email', $candidate_email );

				if( $candidate ) {
					// Set viewed status for the approved/rejected message viewed
					$key_meta = '_check_view_applied';
					$check_view = get_user_meta( $candidate->ID, $key_meta, true ) ? (array) get_user_meta( $candidate->ID, $key_meta, true ) : array();
					
					$arr_value = array_merge($check_view, array( $application_id ) );
					
					if ( !in_array ( $application_id, $check_view ) ):
						update_user_meta( $candidate->ID, $key_meta, $arr_value);
					endif;
				}
			}
		}

		public function private_message_shortcode( $html = '', $query_vars = array() ) {
			if( isset( $query_vars['private-messages']) || isset( $query_vars['fepaction'] ) ){
				$html = $this->_member_messages_shortcode();
			}

			return $html;
		}

		public function unregister_announcement() {
			if( !$this->get_fep_option('enable_announcement', self::get_setting('enable_announcement', false) ) ) {
				global $wp_post_types;
				if ( isset( $wp_post_types[ 'fep_announcement' ] ) ) {
					unset( $wp_post_types[ 'fep_announcement' ] );
					return true;
				}
			}

			return false;
		}

		public function fep_menu_buttons( $menu ) {
			if( isset( $menu['newmessage'] ) && ( $this->can_start_conversation() === false ) ) {
				unset( $menu['newmessage'] );
			}

			if( isset( $menu['announcements'] ) && !$this->get_fep_option('enable_announcement', self::get_setting('enable_announcement', false) ) ) {
				unset( $menu['announcements'] );
			}

			unset( $menu['settings'] );

			return $menu;
		}

		public function fep_current_user_can( $can, $cap, $id ) {
			if( $cap == 'send_new_message' ) {
				$user_id = get_current_user_id();
				if( !empty( $user_id ) && !fep_is_user_blocked() ) {
					$user_role = $this->_get_user_role( $user_id );
					if( $user_role == Noo_Member::EMPLOYER_ROLE || $user_role == Noo_Member::CANDIDATE_ROLE ) {
						$can = $this->can_start_conversation( $user_id, $user_role );
					}
				}
			}

			return $can;
		}

		public function fep_settings_fields( $fields ) {
			unset( $fields['page_id'] );
			$fields['enable_announcement'] = array(
				'type'	=>	'checkbox',
				'value' => $this->get_fep_option('enable_announcement', self::get_setting('enable_announcement', false)),
				'priority'	=> 4,
				'class'	=> '',
				'section'	=> 'announcement',
				'label' => __( 'Enable Announcements', 'noo' ),
				'description' => __( 'All users will receive a notification of announcements sent by admin users. ( This option is added by JobMonster theme )', 'noo' )
				);

			return $fields;
		}

		private function _member_messages_shortcode($atts = array()){
			if( !self::is_front_end_pm_activated() ) {
				return '';
			}

			$out = '<div class="member-manage">';

			$user_ID = get_current_user_id();
			if ($user_ID) {

				$fep = fep_main_class::init();
				$out = $fep->main_shortcode_output();

				//Add footer
				$out .= $fep->Footer();
			} else {
				$out .= "<div id='fep-error'>".__("You must be logged-in to view your message.", 'fep')."</div>";
				$out .= '</div>';
			}

			return $out;
		}

		public function fep_page_id_filter( $id = '' ) {
			$member_page_id = Noo_Member::get_member_page_id();
			if( !empty( $member_page_id ) )
				return $member_page_id;
			else {
				return $id;
			}
		}

		public function chatable_user_query_args( $args = array() ) {
			$args['include'] = $this->get_chatable_user_ids();

			return $args;
		}

		public function get_chatable_user_ids( $user_id = 0, $user_role = '' ) {
			if( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			if( empty( $user_role ) && !empty( $user_id ) ) {
				$user_role = $this->_get_user_role( $user_id );
			}

			$chatable_ids = array(0);
			if( !empty( $user_id ) && ( $user_role == Noo_Member::EMPLOYER_ROLE || $user_role == Noo_Member::CANDIDATE_ROLE ) ) {
				if( isset( self::$cache['chatable_ids'] ) && is_array( self::$cache['chatable_ids'] ) && isset( self::$cache['chatable_ids'][$user_id] ) ) {
					$chatable_ids = self::$cache['chatable_ids'][$user_id];
				} else {
					self::$cache['chatable_ids'] = isset( self::$cache['chatable_ids'] ) && is_array( self::$cache['chatable_ids'] ) ? self::$cache['chatable_ids'] : array();

					if( $user_role == Noo_Member::EMPLOYER_ROLE ) {
						$option = self::get_setting( 'employer_message', 'applied_candidate' );
						if( $option == 'everyone' ) {
							$chatable_ids = array();
						} elseif( $option == 'all_candidate' ) {
							$chatable_ids = jm_get_member_ids( Noo_Member::CANDIDATE_ROLE );
							$chatable_ids = array_merge( $chatable_ids, array(0) ); // prevent select all users.
						} elseif( $option == 'applied_candidate' ) {
							$chatable_ids = $this->_get_applied_candidate_ids( $user_id );
							$chatable_ids = array_merge( $chatable_ids, array(0) ); // prevent select all users.
						}
					} elseif( $user_role == Noo_Member::CANDIDATE_ROLE ) {
						$option = self::get_setting( 'candidate_message', 'none' );
						if( $option == 'everyone' ) {
							$chatable_ids = array();
						} elseif( $option == 'all_employer' ) {
							$chatable_ids = jm_get_member_ids( Noo_Member::EMPLOYER_ROLE );;
							$chatable_ids = array_merge( $chatable_ids, array(0) ); // prevent select all users.
						} elseif( $option == 'approved_employer' || $option == 'approved_rejected_employer' ) {
							$chatable_ids = $this->_get_responsed_employer_ids( $user_id, $option == 'approved_rejected_employer' );
							$chatable_ids = array_merge( $chatable_ids, array(0) ); // prevent select all users.
						}
					}

					self::$cache['chatable_ids'][$user_id] = $chatable_ids;
				}
			}

			return $chatable_ids;
		}

		public function can_start_conversation( $user_id = 0, $user_role = '' ) {
			if( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			if( empty( $user_role ) && !empty( $user_id ) ) {
				$user_role = $this->_get_user_role( $user_id );
			}

			$enable = false;
			if( !empty( $user_id ) && !empty( $user_role ) && ( $user_role == Noo_Member::EMPLOYER_ROLE || $user_role == Noo_Member::CANDIDATE_ROLE ) ) {
				if( isset( self::$cache['can_start_conversation'] ) && is_array( self::$cache['can_start_conversation'] ) && isset( self::$cache['can_start_conversation'][$user_id] ) ) {
					$enable = self::$cache['can_start_conversation'][$user_id];
				} else {
					self::$cache['can_start_conversation'] = isset( self::$cache['can_start_conversation'] ) && is_array( self::$cache['can_start_conversation'] ) ? self::$cache['can_start_conversation'] : array();
					if( $user_role == Noo_Member::EMPLOYER_ROLE ) {
						$option = self::get_setting( 'employer_message', 'applied_candidate' );
						if( !empty( $option ) && $option != 'none' ) {
							if( $option == 'everyone' || $option == 'all_candidate' ) {
								$enable = true;
							} elseif( $option == 'applied_candidate' ) {
								$applied_candidates = $this->_get_applied_candidate_ids( $user_id );

								$enable = (bool) count( $applied_candidates );
							}
						}
					} elseif( $user_role == Noo_Member::CANDIDATE_ROLE ) {
						$option = self::get_setting( 'candidate_message', 'none' );
						if( !empty( $option ) && $option != 'none' ) {
							if( $option == 'everyone' || $option == 'all_employer' ) {
								$enable = true;
							} elseif( $option == 'approved_employer' || $option == 'approved_rejected_employer' ) {
								$responsed_employers = $this->_get_responsed_employer_ids( $user_id, $option == 'approved_rejected_employer' );

								$enable = (bool) count( $responsed_employers );
							}
						}
					}

					self::$cache['can_start_conversation'][$user_id] = $enable;
				}
			} else {
				$enable == 'N/A';
			}

			return $enable;
		}

		public function can_start_conversation_with( $user_id = 0, $target_id = '' ) {
			if( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			if( empty( $user_id ) || empty( $target_id ) ) {
				return false;
			}

			if ( $this->_is_administrator( $user_id ) ) {
				return true;
			}

			$enable = false;
			if( isset( self::$cache['can_start_conversation_with'] )
				&& is_array( self::$cache['can_start_conversation_with'] )
				&& isset( self::$cache['can_start_conversation_with'][$user_id] ) 
				&& is_array( self::$cache['can_start_conversation_with'][$user_id] )
				&& isset( self::$cache['can_start_conversation_with'][$user_id][$target_id] ) ) {
				$enable = self::$cache['can_start_conversation_with'][$user_id][$target_id];
			} else {
				self::$cache['can_start_conversation_with'] = isset( self::$cache['can_start_conversation_with'] ) && is_array( self::$cache['can_start_conversation_with'] ) ? self::$cache['can_start_conversation_with'] : array();
				self::$cache['can_start_conversation_with'][$user_id] = isset( self::$cache['can_start_conversation_with'][$user_id] ) && is_array( self::$cache['can_start_conversation_with'][$user_id] ) ? self::$cache['can_start_conversation_with'][$user_id] : array();
				$user_role = $this->_get_user_role( $user_id );
				if( $user_role == Noo_Member::EMPLOYER_ROLE ) {
					$option = self::get_setting( 'employer_message', 'applied_candidate' );
					if( !empty( $option ) && $option != 'none' ) {
						if( $option == 'everyone' ) {
							$enable = true;
						} elseif( $option == 'all_candidate' ) {
							$enable = Noo_Member::is_candidate( $target_id );
						} elseif( $option == 'applied_candidate' ) {
							$applied_candidates = $this->_get_applied_candidate_ids( $user_id );

							$enable = !empty( $applied_candidates ) && is_array( $applied_candidates ) && in_array($target_id, $applied_candidates);
						}
					}
				} elseif( $user_role == Noo_Member::CANDIDATE_ROLE ) {
					$option = self::get_setting( 'candidate_message', 'none' );
					if( !empty( $option ) && $option != 'none' ) {
						if( $option == 'everyone' ) {
							$enable = true;
						} elseif( $option == 'all_employer' ) {
							$enable = Noo_Member::is_employer( $target_id );
						} elseif( $option == 'approved_employer' || $option == 'approved_rejected_employer' ) {
							$responsed_employers = $this->_get_responsed_employer_ids( $user_id, $option == 'approved_rejected_employer' );

							$enable = in_array($target_id, $responsed_employers);
						}
					}
				}

				self::$cache['can_start_conversation_with'][$user_id][$target_id] = $enable;
			}

			return $enable;
		}

		private function _get_applied_candidate_ids( $employer_id = '' ) {
			if( empty( $employer_id ) ) {
				return array();
			}

			if( isset( self::$cache['applied_candidate_ids'] )
				&& is_array( self::$cache['applied_candidate_ids'] )
				&& isset( self::$cache['applied_candidate_ids'][$employer_id] ) ) {
				return self::$cache['applied_candidate_ids'][$employer_id];
			} else {
				self::$cache['applied_candidate_ids'] = isset( self::$cache['applied_candidate_ids'] ) && is_array( self::$cache['applied_candidate_ids'] ) ? self::$cache['applied_candidate_ids'] : array();
				
				global $wpdb;
				$job_ids = get_posts(
					array(
						'post_type' => 'noo_job',
						'post_status' => array('publish','expired'),
						'author' => $employer_id,
						'posts_per_page' => -1,
						'fields' => 'ids',
						'suppress_filters' => false
					)
				);
				$application_ids = get_posts(
					array(
						'post_type' => 'noo_application',
						'posts_per_page' => -1,
						'post_parent__in' => array_merge($job_ids, array(0)), // make sure return zero application if there's no job.
						'post_status' => array('publish','pending','rejected'),
						'fields' => 'ids',
						'suppress_filters' => false
					)
				);

				if( !empty( $application_ids ) && count( $application_ids ) ) {
					self::$cache['applied_candidate_ids'][$employer_id] = $wpdb->get_col('
						SELECT '.$wpdb->users.'.ID
						FROM '.$wpdb->users.' INNER JOIN '.$wpdb->postmeta.'
						ON '.$wpdb->users.'.user_email = '.$wpdb->postmeta.'.meta_value
						WHERE '.$wpdb->postmeta.'.meta_key = \'_candidate_email\'
						AND '.$wpdb->postmeta.'.post_id IN ('.implode(',', $application_ids).')
					');
				} else {
					self::$cache['applied_candidate_ids'][$employer_id] = array();
				}
			}

			return self::$cache['applied_candidate_ids'][$employer_id];
		}

		private function _get_responsed_employer_ids( $candidate_id = '', $include_rejected = false ) {
			if( empty( $candidate_id ) ) {
				return array();
			}

			if( isset( self::$cache['responsed_employer_ids'] )
				&& is_array( self::$cache['responsed_employer_ids'] )
				&& isset( self::$cache['responsed_employer_ids'][$candidate_id] ) ) {
				$employer_ids = self::$cache['responsed_employer_ids'][$candidate_id];
			} else {
				self::$cache['responsed_employer_ids'] = isset( self::$cache['responsed_employer_ids'] ) && is_array( self::$cache['responsed_employer_ids'] ) ? self::$cache['responsed_employer_ids'] : array();
				
				global $wpdb;
				$user = get_user_by( 'id', $candidate_id );
				$employer_ids = array();

				$sql = 'SELECT '.$wpdb->posts.'.post_parent
					FROM '.$wpdb->posts.' INNER JOIN '.$wpdb->postmeta.'
					ON '.$wpdb->posts.'.ID = '.$wpdb->postmeta.'.post_id
					WHERE '.$wpdb->posts.'.post_type = \'noo_application\'
					AND '.$wpdb->postmeta.'.meta_key = \'_candidate_email\'
					AND '.$wpdb->postmeta.'.meta_value = \''.$user->user_email.'\'
				';
				if( !$include_rejected ) {
					$sql .= 'AND '.$wpdb->posts.'.post_status = \'publish\'';
				} else {
					$sql .= 'AND '.$wpdb->posts.'.post_status IN (\'publish\',\'rejected\')';
				}

				$job_ids = $wpdb->get_col( $sql );
				if( !empty( $job_ids ) && count( $job_ids ) ) {
					$employer_ids = $wpdb->get_col( '
						SELECT '.$wpdb->posts.'.post_author
						FROM '.$wpdb->posts.'
						WHERE '.$wpdb->posts.'.post_type = \'noo_job\'
						AND '.$wpdb->posts.'.post_status IN (\'publish\',\'expired\')
						AND '.$wpdb->posts.'.ID IN ('.implode(',', $job_ids).')
					');
				}

				self::$cache['responsed_employer_ids'][$candidate_id] = $employer_ids;
			}

			return $employer_ids;
		}

		public function add_seting_jobmonster_fep_tab($tabs) {

			$tabs['jobmonster_fep'] = __( 'Messages', 'noo' );
			return $tabs;
		}

		public static function get_setting($id = null ,$default = null){
			$job_package_setting = get_option('jobmonster_fep');
			if(isset($job_package_setting[$id]))
				return $job_package_setting[$id];
			return $default;
		}

		public function dashboard_message_action( $admin_actions = array(), $post ) {
			if( current_user_can( 'edit_posts' ) && ( $post->post_author != get_current_user_id() ) ) {
				$target = get_userdata( $post->post_author );
				if( !empty( $target ) ) {
					$url = esc_url( add_query_arg( array( 'to' => $target->user_login ), $this->fep_action_url('newmessage') ) );
					$admin_actions['message'] = array(
						'action'  => 'message',
						'name'    => __( 'Send Message to author', 'noo' ),
						'url'     => $url,
						'icon'	  => 'admin-comments',
					);
					if( isset( $admin_actions['delete'] ) ) {
						$temp = $admin_actions['delete'];
						unset($admin_actions['delete']);
						$admin_actions['delete'] = $temp;
					}
				}
			}
			return $admin_actions;
		}

		public function setting_page() {
			if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
				flush_rewrite_rules();
			}
			?>
			<?php settings_fields('jobmonster_fep'); ?>
			<h3><?php echo __('Messages Settings','noo')?></h3>
			<?php if( self::is_front_end_pm_activated() ) : ?>
				<table class="form-table" cellspacing="0">
					<tbody>

						<script type="text/javascript">
							jQuery(document).ready(function($) {
								$('#enable_fep').change(function(event) {
									var $input = $( this );
									if ( $input.prop( "checked" ) ) {
										$('.enable_fep').show().find(':input').change();
									} else {
										$('.enable_fep').hide().find(':input').change();
									}
								}).change();

								$( "#select_type" ).change(function () {
								    $( "#select_type option:selected" ).each(function() {
								    	$('#indeed_type').val($( this ).text());
								    });
								}).change();

								$( "#select_show_job" ).change(function () {
									var $this = $( this );
									var opt = $this.find('option:selected').val();
							    	if ( $this.is(':visible') && opt === 'default' ) {
							    		$('.show_job').show();
							    	} else {
							    		$('.show_job').hide();
							    	}
								}).change();

							});
						</script>

						<tr>
							<th>
								<?php esc_html_e('Enable Font-End PM Integration','noo')?>
							</th>
							<td>
								<?php $enable_fep = self::get_setting('enable_fep', false); ?>
								<input type="checkbox" <?php checked( true, $enable_fep ); ?> id="enable_fep" name="jobmonster_fep[enable_fep]" value="1" /> <?php _e( 'Integration with Front-End PM plug-in will allow Employers and Candidates to communicate via private message on Member Page. But you won\'t be able to use Private Message in a separated page anymore.', 'noo' ); ?>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Apply For Job Message','noo')?>
							</th>
							<td>
								<?php $enable_apply_job = self::get_setting('enable_apply_job', false); ?>
								<input type="checkbox" <?php checked( true, $enable_apply_job ); ?> id="enable_apply_job" name="jobmonster_fep[enable_apply_job]" value="1" /> <?php _e( 'Starting a conversation when a candidate applies for a job.', 'noo' ); ?>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Approve/Reject Message','noo')?>
							</th>
							<td>
								<?php $enable_approve_reject = self::get_setting('enable_approve_reject', true); ?>
								<input type="checkbox" <?php checked( true, $enable_approve_reject ); ?> id="enable_approve_reject" name="jobmonster_fep[enable_approve_reject]" value="1" /> <?php _e( 'Starting a conversation when an employer approves/rejects an application.', 'noo' ); ?>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Employers can start conversation with','noo')?>
							</th>
							<td>
								<?php $employer_message = self::get_setting('employer_message', 'applied_candidate'); ?>
								<fieldset>
									<label><input type="radio" <?php checked( $employer_message, 'none' ); ?> name="jobmonster_fep[employer_message]" value="none"><?php _e('None', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $employer_message, 'applied_candidate' ); ?> name="jobmonster_fep[employer_message]" value="applied_candidate"><?php _e('Candidates who applied for their jobs', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $employer_message, 'all_candidate' ); ?> name="jobmonster_fep[employer_message]" value="all_candidate"><?php _e('All Candidates', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $employer_message, 'everyone' ); ?> name="jobmonster_fep[employer_message]" value="everyone"><?php _e('Everyone ( all candidates and other employers )', 'noo'); ?></label><br/>
								</fieldset>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Candiates can start conversation with','noo')?>
							</th>
							<td>
								<?php $candidate_message = self::get_setting('candidate_message', 'none'); ?>
								<fieldset>
									<label><input type="radio" <?php checked( $candidate_message, 'none' ); ?> name="jobmonster_fep[candidate_message]" value="none"><?php _e('None', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_message, 'approved_employer' ); ?> name="jobmonster_fep[candidate_message]" value="approved_employer"><?php _e('Employers who approved their applications.', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_message, 'aproved_rejected_employer' ); ?> name="jobmonster_fep[candidate_message]" value="aproved_rejected_employer"><?php _e('Employers who approved or rejected their applications.', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_message, 'all_employer' ); ?> name="jobmonster_fep[candidate_message]" value="all_employer"><?php _e('All Employers', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_message, 'everyone' ); ?> name="jobmonster_fep[candidate_message]" value="everyone"><?php _e('Everyone ( all candidates and employers )', 'noo'); ?></label><br/>
								</fieldset>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Show Send Message on Candidate profile ( upper part of the Resumes )','noo')?>
								<p><small><?php echo __('Note: you still have to follow the option <em>Employers can start conversation with</em>', 'noo'); ?></small></p>
							</th>
							<td>
								<?php $candidate_profile_message = self::get_setting('candidate_profile_message', 'no'); ?>
								<fieldset>
									<label><input type="radio" <?php checked( $candidate_profile_message, 'no' ); ?> name="jobmonster_fep[candidate_profile_message]" value="no"><?php _e('No', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_profile_message, 'private_profile' ); ?> name="jobmonster_fep[candidate_profile_message]" value="private_profile"><?php _e('For Private Contact', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_profile_message, 'public_profile' ); ?> name="jobmonster_fep[candidate_profile_message]" value="public_profile"><?php _e('For Public Contact', 'noo'); ?></label><br/>
									<label><input type="radio" <?php checked( $candidate_profile_message, 'all_profile' ); ?> name="jobmonster_fep[candidate_profile_message]" value="all_profile"><?php _e('For all Candidate Contact', 'noo'); ?></label><br/>
								</fieldset>
								<p><small><?php echo sprintf( __('The Private/Public Contact depends on the setting <strong><a href="%s">Show Candidate Contact on Resumes for</a></strong>', 'noo'), jm_setting_page_url( 'resume' ) ); ?></small></p>
							</td>
						</tr>

						<tr class="enable_fep">
							<th>
								<?php esc_html_e('Other Settings','noo')?>
							</th>
							<td>
								<label> <?php echo sprintf( __( 'For more settings, please switch to %s.', 'noo' ), '<a href="' . admin_url( 'edit.php?post_type=fep_message&page=fep_settings' ) . '" target="_blank">Front-End PM</a>' ); ?></label>
								<p><small><?php echo __( 'Note: please pay no attention to the setting "<strong>Minimum Capability to use messaging</strong>" because our theme has already handled message permission.', 'noo' ); ?></small></p>
							</td>
						</tr>

					</tbody>
				</table>
			<?php else : ?>
				<div class="update-nag notice">
					<p><strong><?php echo sprintf( __('Please install %s plugin to activate this function', 'noo'), '<a href="https://wordpress.org/plugins/front-end-pm/" target="_blank">Front-End PM</a>' ); ?></strong></p>
				</div>
			<?php endif;
		}

		public function fep_action_url( $action = '' ) {
			$member_page_id = Noo_Member::get_member_page_id();
			if( !empty( $member_page_id ) ) {
				$fep_page = Noo_Member::get_endpoint_url('private-messages');
			} else {
				$fep_page = get_permalink( fep_page_id() );
			}
			
			return esc_url( add_query_arg( array( 'fepaction' => $action ), $fep_page ) );
		}

		public function get_fep_option( $option, $default = '', $section = 'FEP_admin_options' ) {
			if( function_exists( 'fep_get_option' ) ) {
				return fep_get_option( $option, $default, $section );
			} else {
				$options = get_option( $section );
			    if ( isset( $options[$option] ) ) {
			        return $options[$option];
			    }

			    return $default;
			}
		}


		private function _get_user_role( $user_id = 0 ) {
			$user_role = '';
			$user = get_userdata( $user_id );

			if( $user ) {
				if(!function_exists('get_editable_roles'))
					include_once( ABSPATH . 'wp-admin/includes/user.php' );
				$editable_roles = array_keys( get_editable_roles() );
				if ( count( $user->roles ) <= 1 ) {
					$user_role = reset( $user->roles );
				} elseif ( $roles = array_intersect( array_values( $user->roles ), $editable_roles ) ) {
					$user_role = reset( $roles );
				} else {
					$user_role = reset( $user->roles );
				}
			}

			if( $user_role == 'administrator' ) {
				$user_role = 'employer';
			}

			return $user_role;
		}

		private function _is_administrator( $user_id = 0 ) {
			if( empty( $user_id ) ) {
				return current_user_can( 'manage_options' );
			} else {
				return user_can( $user_id, 'manage_options' );
			}
		}

		public static function is_front_end_pm_activated() {
			return is_plugin_active( 'front-end-pm/front-end-pm.php' ) || is_plugin_active( 'front-end-pm-pro/front-end-pm-pro.php' );
		}
	}
	new Noo_Fep_Integration();
endif;