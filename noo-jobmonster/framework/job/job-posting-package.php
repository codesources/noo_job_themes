<?php

if( !function_exists('jm_is_woo_job_posting') ) :
	function jm_is_woo_job_posting(){
		$job_package_actions = array( 
			jm_get_action_control( 'post_job' ),
			jm_get_action_control( 'view_resume' ),
			jm_get_action_control( 'view_candidate_profile' ),
		);

		return in_array( 'package', $job_package_actions );
	}
endif;
