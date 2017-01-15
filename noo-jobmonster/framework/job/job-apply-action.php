<?php

if( !function_exists('jm_can_apply_job') ) :
	function jm_can_apply_job( $job_id = null ) {
		if( empty( $job_id ) ) 
			return false;
		
		$can_apply_job = false;

		if( !$can_apply_job ) {
			$apply_job_setting = jm_get_action_control('apply_job');
			switch( $apply_job_setting ) {
				case 'public':
					$can_apply_job = true;
					break;
				case 'candidate':
					$can_apply_job = Noo_Member::is_candidate();
					break;
				case 'package':
					if( Noo_Member::is_candidate() ) {
						$package = jm_get_resume_posting_info();
						$can_apply_job = ( isset( $package['can_apply_job'] ) && $package['can_apply_job'] === '1' ) && ( jm_get_job_apply_remain() != 0 );
					}
					break;
			}
		}

		return apply_filters( 'jm_can_apply_job', $can_apply_job, $job_id );
	};
endif;

if( !function_exists('jm_get_cannot_apply_job_message') ) :
	function jm_get_cannot_apply_job_message( $job_id = 0 ) {
		$title = '';
		$link = '';

		$apply_job_setting = jm_get_action_control('apply_job');
		switch( $apply_job_setting ) {
			case 'public':
				$title = __( 'There\'s an unknown error. Please retry or contact Administrator.', 'noo' );
				break;
			case 'candidate':
				$title = __('Only candidates can apply for this job.','noo');
				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'noo' ) . '</a>';
				}
				break;
			case 'package':
				$title = __('Only paid candidates can apply for this job.','noo');
				$link = Noo_Member::get_endpoint_url('manage-plan');

				if( !Noo_Member::is_logged_in() ) {
					$link = Noo_Member::get_login_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'noo' ) . '</a>';
				} elseif( !Noo_Member::is_candidate() ) {
					$link = Noo_Member::get_logout_url();
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Logout', 'noo' ) . '</a>';
				} else {
					$title = __('Your membership doesn\'t allow you to apply for this job.','noo');
					$link = Noo_Member::get_endpoint_url('manage-plan');
					$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Upgrade your membership', 'noo' ) . '</a>';
				}
				break;
		}

		$params = apply_filters( 'jm_cannot_apply_job_message', compact( $title, $link ), $job_id );
		extract($params);

		$title = empty( $title ) ? __('You don\'t have permission to apply this job.','noo') : $title;

		return array( $title, $link );
	}
endif;