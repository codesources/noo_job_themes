<?php

if( !function_exists('jm_is_resume_posting_page') ) :
	function jm_is_resume_posting_page( $page_id = '' ){
		$page_id = empty( $page_id ) ? get_the_ID() : $page_id;
		if( empty( $page_id ) ) return false;

		$page_temp = get_page_template_slug( $page_id );

		return 'page-post-resume.php' === $page_temp;
	}
endif;

if( !function_exists('jm_get_resume_posting_remain') ) :
	function jm_get_resume_posting_remain( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$package = jm_get_resume_posting_info( $user_id );
		$resume_limit = empty( $package ) || !is_array( $package ) || !isset( $package['resume_limit'] ) ? 0 : $package['resume_limit'];
		$resume_added = jm_get_resume_posting_added( $user_id );

		return absint($resume_limit) - absint($resume_added);
	}
endif;

if( !function_exists('jm_get_resume_posting_added') ) :
	function jm_get_resume_posting_added( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$resume_added = get_user_meta($user_id,'_resume_added',true);

		return empty( $resume_added ) ? 0 : absint( $resume_added );
	}
endif;

if( !function_exists('jm_get_resume_posting_info') ) :
	function jm_get_resume_posting_info($user_id=''){
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		if( jm_is_woo_resume_posting() ) {
			// delete_user_meta($user_id, '_resume_package'); // This code is for debuging
			$posting_info = get_user_meta($user_id, '_resume_package', true);
		} else {
			$posting_info = array(
				'resume_limit'    => absint(jm_get_resume_setting( 'resume_posting_limit',5)),
			);
		}

		return apply_filters( 'jm_resume_posting_info', $posting_info, $user_id );
	}
endif;

if( !function_exists('jm_increase_resume_posting_count') ) :
	function jm_increase_resume_posting_count($user_id='') {
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		if( empty( $user_id ) ) return false;

		$_count = jm_get_resume_posting_added( $user_id );
		update_user_meta($user_id, '_resume_added', $_count + 1 );
	}
endif;

if( !function_exists('jm_decrease_resume_posting_count') ) :
	function jm_decrease_resume_posting_count($user_id='') {
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		if( empty( $user_id ) ) return false;

		$_count = jm_get_resume_posting_added( $user_id );
		update_user_meta($user_id, '_resume_added', max( 0, $_count - 1 ) );
	}
endif;

if( !function_exists('jm_can_post_resume') ) :
	function jm_can_post_resume($user_id = ''){
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}
		if( !Noo_Member::is_candidate( $user_id ) ) return false;

		if( jm_is_woo_resume_posting() ) {
			// Resume posting with a package selected
			if( jm_is_resume_posting_page() && isset( $_REQUEST['package_id'] ) ) {
				return true;
			}

			// Check the number of resume added.
			return jm_get_resume_posting_remain( $user_id ) > 0;
		}

		return true;		
	}
endif;

if( !function_exists('jm_can_edit_resume') ) :
	function jm_can_edit_resume($resume_id = 0, $user_id = 0) {
		if( empty( $resume_id ) ) return false;

		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		if( empty($user_id) ) return false;

		return ( $user_id == get_post_field( 'post_author', $resume_id ) );
	}
endif;
