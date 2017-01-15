<?php
if( !function_exists('jm_correct_job_status') ) :
	function jm_correct_job_status( $job_id = null, $job_status = 'pending' ) {
		if ( empty( $job_id ) ) {
			return;
		}
		$corrected_status = '';
		if( $job_status == 'pending' ) {
			$in_review = (bool) noo_get_post_meta( $job_id, '_in_review', '' );
			$waiting_payment = (bool) noo_get_post_meta( $job_id, '_waiting_payment', '' );
			if( !$in_review && !$waiting_payment ) {
				$corrected_status = 'inactive';
			} elseif( $waiting_payment ) {
				delete_post_meta( $job_id, '_waiting_payment' );
				$corrected_status = 'pending_payment';
			}
		}

		// Correct for version 2.10.1 or below
		if( !empty( $corrected_status ) ) {
			wp_update_post(array(
				'ID'=>$job_id,
				'post_status' => $corrected_status,
				)
			);

			return $corrected_status;
		}

		return $job_status;
	}
endif;

if( !function_exists('jm_correct_application_attachment') ) :
	function jm_correct_application_attachment( $application_id = 0 ) {
		if( empty( $application_id ) ) return;
		$attachment = noo_get_post_meta( $application_id, '_attachment', '' );

		if( !empty( $attachment ) && is_numeric( $attachment ) ) {
			$resume = noo_get_post_meta( $application_id, '_resume', '' );
			if( empty( $resume ) ) {
				$maybe_resume = absint($attachment);
				// if( 'noo_resume' === get_post_type( $maybe_resume ) ) {
					update_post_meta( $application_id, '_attachment', '' );
					update_post_meta( $application_id, '_resume', $maybe_resume );
				// }
				return '';
			}
		}
		
		return $attachment;
	}
endif;

if( !function_exists('jm_remove_frontend_editor_pluginss') ) :
	function jm_remove_frontend_editor_pluginss( $plugins ) {
		if( !is_admin() ) {
			$unuse_plugins = array( 'wpeditimage', 'wplink' );
			foreach ($unuse_plugins as $plugin) {
				if( ( $index = array_search($plugin, $plugins) ) !== false ) {
					unset( $plugins[$index] );
				}
			}
		}
		
		return $plugins;
	}
//	add_filter( 'teeny_mce_plugins', 'jm_remove_frontend_editor_pluginss' );
//	add_filter( 'tiny_mce_plugins', 'jm_remove_frontend_editor_pluginss' );
endif;

if( !function_exists('jm_remove_frontend_editor_buttons') ) :
	function jm_remove_frontend_editor_buttons( $buttons ) {
		if( !is_admin() ) {
			$unuse_buttons = array( 'wp_more' );
			foreach ($unuse_buttons as $button) {
				if( ( $index = array_search($button, $buttons) ) !== false ) {
					unset( $buttons[$index] );
				}
			}
		}
		
		return $buttons;
	}
	add_filter( 'mce_buttons', 'jm_remove_frontend_editor_buttons' );
endif;

if( !function_exists('jm_remove_frontend_editor_buttons_2') ) :
	function jm_remove_frontend_editor_buttons_2( $buttons ) {
		if( !is_admin() ) {
			$unuse_buttons = array( 'wp_help' );
			foreach ($unuse_buttons as $button) {
				if( ( $index = array_search($button, $buttons) ) !== false ) {
					unset( $buttons[$index] );
				}
			}
		}
		
		return $buttons;
	}
	add_filter( 'mce_buttons_2', 'jm_remove_frontend_editor_buttons_2' );
endif;


function jm_wp_link_query_args( $query ) {
	$user_id = get_current_user_id();
	if (Noo_Member::get_user_role($user_id) != 'administrator' ) {
		$query['author'] = $user_id;
	}
	return $query;
}

add_filter( 'wp_link_query_args', 'jm_wp_link_query_args' );

// add_action( 'upgrader_process_complete', 'jm_upgrate_theme_ver_300',10, 2);

function jm_upgrate_theme_ver_300( $upgrader_object, $options ) {
	$current_plugin_path_name = plugin_basename( __FILE__ );

	if ($options['action'] == 'update' && $options['type'] == 'theme' ){
		foreach($options['packages'] as $each_theme){
			if ($each_theme=='noo-jobmonster' ) {

				$theme_data = wp_get_theme( 'noo-jobmonster' );
				$theme_version = $theme_data->Version;
				if( version_compare( $theme_version, '3.0.0', '>=' ) ) {
					$jm_data_version = get_option( 'jm_data_version' );
					if ( $jm_data_version == '3.0.0' ) {
						return;
					}

					global $wpdb;

					$job_ids = $wpdb->get_col( "
						SELECT ID FROM {$wpdb->posts}
						WHERE post_status = 'noo_job'
						AND post_type = 'pending'" );

					if ( $job_ids ) {
						foreach ( $job_ids as $job_id ) {
							jm_correct_job_status( $job_id );
						}
					}

					update_option( 'jm_data_version', '3.0.0' );
				}
			}
		}
	}
}
