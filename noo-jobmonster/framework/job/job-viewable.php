<?php

if( !function_exists('jm_can_view_job') ) :
	function jm_can_view_job( $job_id = null ) {
		if( empty( $job_id ) ) 
			return false;

		// Job's author can view his/her job
		$employer_id = get_post_field( 'post_author', $job_id );
		if( $employer_id == get_current_user_id() ) {
			return true;
		}
		
		$can_view_job = false;

		// Administrator can view all jobs
		if( 'administrator' == Noo_Member::get_user_role(get_current_user_id()) ) {
			$can_view_job = true;
		}

		if( !$can_view_job ) {
			$view_job_setting = jm_get_action_control('view_job');
			switch( $view_job_setting ) {
				case 'public':
					$can_view_job = true;
					break;
				case 'user':
					$can_view_job = Noo_Member::is_logged_in();
					break;
				case 'candidate':
					$can_view_job = Noo_Member::is_candidate();
					break;
				case 'package': // @TODO: move to specific file for job viewable controlled by job package
					if( Noo_Member::is_candidate() ) {
						$package = jm_get_resume_posting_info();
						$can_view_job = ( isset( $package['can_view_job'] ) && $package['can_view_job'] === '1' ) && ( jm_get_job_view_remain() != 0 );
					}
					break;
			}
		}

		return apply_filters( 'jm_can_view_job', $can_view_job, $job_id );
	};
endif;

if( !function_exists('jm_get_cannot_view_job_message') ) :
	function jm_get_cannot_view_job_message( $job_id = 0 ) {
		$title = '';
		$link = '';

		$view_job_setting = jm_get_action_control('view_job');
		switch( $view_job_setting ) {
			case 'public':
				$title = __( 'There\'s an unknown error. Please retry or contact Administrator.', 'noo' );
				break;
			case 'user':
				$title = __('Only logged in users can view this job.','noo');
				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login', 'noo' ) . '</a>';
				}
				break;
			case 'candidate':
				$title = __('Only candidates can view this job.','noo');
				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'noo' ) . '</a>';
				}
				break;
			case 'package':
				$title = __('Only paid candidates can view this job.','noo');
				$link = Noo_Member::get_endpoint_url('manage-plan');

				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'noo' ) . '</a>';
				} elseif( !Noo_Member::is_candidate() ) {
					$link = Noo_Member::get_logout_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Logout', 'noo' ) . '</a>';
				} else {
					$title = __('Your membership doesn\'t allow you to view this job.','noo');
					$link = Noo_Member::get_endpoint_url('manage-plan');
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Upgrade your membership', 'noo' ) . '</a>';
				}
				break;
		}

		$params = apply_filters( 'jm_cannot_view_job_message', compact( $title, $link ), $job_id );
		extract($params);

		$title = empty( $title ) ? __('You don\'t have permission to view this job.','noo') : $title;

		return array( $title, $link );
	}
endif;
