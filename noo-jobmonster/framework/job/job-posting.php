<?php

if( !function_exists('jm_is_job_posting_page') ) :
	function jm_is_job_posting_page( $page_id = '' ){
		$page_id = empty( $page_id ) ? get_the_ID() : $page_id;
		if( empty( $page_id ) ) return false;

		$page_temp = get_page_template_slug( $page_id );

		return 'page-post-job.php' === $page_temp;
	}
endif;

if( !function_exists('jm_get_job_posting_remain') ) :
	function jm_get_job_posting_remain( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) {
			return 0;
		}

		$package = jm_get_job_posting_info( $user_id );
		$job_limit = empty( $package ) || !is_array( $package ) || !isset( $package['job_limit'] ) ? 0 : $package['job_limit'];
		$job_added = jm_get_job_posting_added( $user_id );

		return absint($job_limit) - absint($job_added);
	}
endif;

if( !function_exists('jm_get_job_posting_added') ) :
	function jm_get_job_posting_added( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) {
			return 0;
		}

		$job_added = get_user_meta($user_id,'_job_added',true);

		return empty( $job_added ) ? 0 : absint( $job_added );
	}
endif;

if( !function_exists('jm_get_job_posting_info') ) :
	function jm_get_job_posting_info( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) {
			return null;
		}

		if( jm_is_woo_job_posting() ) {
			$posting_info = get_user_meta($user_id, '_job_package', true);
		} else {
			$posting_info = array(
				'job_duration' => absint(jm_get_job_setting( 'job_display_duration',30)),
				'job_limit'    => absint(jm_get_job_setting( 'job_posting_limit',5)),
				'job_featured' => absint( jm_get_job_setting( 'job_feature_limit',1)),
				'counter_reset' => absint( jm_get_job_setting( 'job_posting_reset',0))
			);
		}

		return apply_filters( 'jm_job_posting_info', $posting_info, $user_id );
	}
endif;

if( !function_exists('jm_increase_job_posting_count') ) :
	function jm_increase_job_posting_count( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) return false;

		$_count = jm_get_job_posting_added( $user_id );
		update_user_meta($user_id, '_job_added', $_count + 1 );
	}
endif;

if( !function_exists('jm_decrease_job_posting_count') ) :
	function jm_decrease_job_posting_count( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) return false;

		$_count = jm_get_job_posting_added( $user_id );
		update_user_meta($user_id, '_job_added', max( 0, $_count - 1 ) );
	}
endif;

if( !function_exists('jm_get_feature_job_remain') ) :
	function jm_get_feature_job_remain( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) {
			return 0;
		}

		$current_feature_count =  jm_get_feature_job_added($user_id);

		$package = jm_get_job_posting_info($user_id);
		if( empty( $package ) || !isset( $package['job_featured'] ) ) {
			return 0;
		}
		
		return max( absint( $package['job_featured'] ) - absint($current_feature_count), 0 ) ;
	}
endif;

if( !function_exists('jm_get_feature_job_added') ) :
	function jm_get_feature_job_added( $user_id = null ){
		if( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if( empty( $user_id ) ) {
			return 0;
		}

		return absint(get_user_meta( $user_id, '_job_featured', true ));
	}
endif;

if( !function_exists('jm_can_post_job') ) :
	function jm_can_post_job($user_id = null){
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}
		if( !Noo_Member::is_employer( $user_id ) ) return false;

		// Job posting with a package selected
		if( jm_is_job_posting_page() && isset( $_REQUEST['package_id'] ) ) {
			return true;
		}

		// Check the number of job added.
		return jm_get_job_posting_remain( $user_id ) > 0;
	}
endif;

if( !function_exists('jm_can_set_feature_job') ) :
	function jm_can_set_feature_job($user_id = null){
		return jm_get_feature_job_remain($user_id) > 0;
	}
endif;

if( !function_exists('jm_can_edit_job') ) :
	function jm_can_edit_job($job_id = 0, $user_id = 0) {
		if( empty( $job_id ) ) return false;

		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		if( empty($user_id) ) return false;

		$job_status = get_post_status($job_id);

		return ( $user_id == get_post_field( 'post_author', $job_id ) ) && ( $job_status != 'expired' );
	}
endif;

if( !function_exists('jm_can_change_job_state') ) :
	function jm_can_change_job_state($job_id = 0, $user_id = 0) {
		$job_status = get_post_status($job_id);
		return jm_can_edit_job($job_id, $user_id) && ( $job_status == 'publish' || $job_status == 'inactive' );
	}
endif;
