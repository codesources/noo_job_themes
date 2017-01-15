<?php

if( !function_exists('jm_is_free_job_posting') ) :
	function jm_is_free_job_posting(){
		return 'employer' == jm_get_action_control( 'post_job' );
	}
endif;

if( !function_exists( 'jm_posting_free_admin_init' ) ) :
	function jm_posting_free_admin_init(){

		if( jm_is_free_job_posting() ) {
			add_action('noo_job_reset_job_counter', 'jm_job_reset_free_posting_counter');
		}
	}
	
	add_filter('admin_init', 'jm_posting_free_admin_init' );
endif;

if( !function_exists('jm_job_reset_free_posting_counter') ) :
	function jm_job_reset_free_posting_counter(){
		update_option( 'noo_time_reset_job_counter', time() );

		// Reset code
		$administrator_list = jm_get_member_ids( 'administrator' );
		$employer_list = jm_get_member_ids( Noo_Member::EMPLOYER_ROLE );
		$employer_list = array_merge( $administrator_list, $employer_list );
		foreach ($employer_list as $user_id) {
			if( defined('WP_DEBUG') && WP_DEBUG ) {
				$job_added = get_user_meta( $user_id, '_job_added', true );
				$job_featured = get_user_meta( $user_id, '_job_featured', true );
				error_log("User_ID: $user_id - Job Added: {$job_added} - Job Featured: {$job_featured}");
			}
			update_user_meta( $user_id, '_job_added', '0' );
			update_user_meta( $user_id, '_job_featured', '0' );
		}

		$job_posting_reset = jm_get_job_setting( 'job_posting_reset',0);
		$job_posting_reset = absint( $job_posting_reset );
		if( !empty( $job_posting_reset ) ) {
			$next_reset_time = strtotime('first day of this month 00:00:00', strtotime( '+' . $job_posting_reset . ' month', time() ) );
			wp_schedule_single_event( $next_reset_time, 'noo_job_reset_job_counter' );
		}
	}
endif;

if( !function_exists('jm_job_posting_free_setting_changed') ) :
	function jm_job_posting_free_setting_changed() {
		$job_posting_reset = jm_get_job_setting('job_posting_reset',0);
		$job_posting_reset = absint( $job_posting_reset );

		if( !jm_is_woo_job_posting() && !empty( $job_posting_reset ) ) {
			$time_reset_job_counter = get_option( 'noo_time_reset_job_counter' );
			$time_reset_job_counter = empty( $time_reset_job_counter ) ? time() : $time_reset_job_counter;
			
			$next_reset_time = strtotime('first day of this month 00:00:00', strtotime( '+' . $job_posting_reset . ' month', $time_reset_job_counter ) );

			wp_clear_scheduled_hook( 'noo_job_reset_job_counter' );
			wp_schedule_single_event( $next_reset_time, 'noo_job_reset_job_counter' );
		} else {
			delete_option( 'noo_time_reset_job_counter' );
			wp_clear_scheduled_hook( 'noo_job_reset_job_counter' );
		}
	}
	add_action( 'jm_job_setting_changed', 'jm_job_posting_free_setting_changed' );
endif;
