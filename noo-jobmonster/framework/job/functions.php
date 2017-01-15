<?php
if( !function_exists( 'jm_get_job_setting' ) ) :
	function jm_get_job_setting($id = null ,$default = null){
		return jm_get_setting('noo_job_general', $id, $default);
	}
endif;

if( !function_exists( 'jm_get_application_setting' ) ) :
	function jm_get_application_setting($id = null ,$default = null){
		return jm_get_setting('noo_job_linkedin', $id, $default);
	}
endif;

if( !function_exists( 'jm_get_email_setting' ) ) :
	function jm_get_email_setting($id = null ,$default = null){
		return jm_get_setting('noo_email', $id, $default);
	}
endif;

if( !function_exists( 'jm_get_employer_company' ) ) :
	function jm_get_employer_company($employer_id=''){
		if(empty($employer_id)){
			$employer_id = get_current_user_id();
		}
		return get_user_meta($employer_id,'employer_company',true);
	}
endif;

if( !function_exists( 'jm_get_job_company' ) ) :
	function jm_get_job_company($job='') {
		$job_id = 0;
		if( is_object( $job ) ) {
			$job_id = $job->ID;
		} elseif( is_numeric( $job ) ) {
			$job_id = $job;
		}

		if( empty( $job_id ) ) {
			$job_id = get_the_ID();
		}

		if( 'noo_job' != get_post_type( $job_id ) ) {
			return 0;
		}

		$company_id = noo_get_post_meta( $job_id, '_company_id', '' );
		if( empty( $company_id ) ) {
			$company_id = jm_get_employer_company( get_post_field( 'post_author', $job_id ) );
		}

		return $company_id;
	}
endif;

if( !function_exists( 'jm_get_job_type' ) ) :
	function jm_get_job_type($job = null ) {
		global $noo_job_type;

		if( is_int( $job ) ) {
			$job = get_post( $job );
		}

		if( empty( $job->post_type ) || !is_object( $job ) || $job->post_type !== 'noo_job' ) {
			return;
		}

		if( empty( $noo_job_type ) ) {
			$noo_job_type = array();
		}
		
		if( !isset( $noo_job_type[$job->ID] ) ) {
			$types = get_the_terms( $job->ID, 'job_type' );
			$type = false;

			if ( !is_wp_error( $types ) && !empty( $types ) ) {
				$type = current( $types );

				$type->color = jm_get_job_type_color( $type->term_id );
			}

			$noo_job_type[$job->ID] = $type;
		}
		
		return apply_filters( 'noo_get_job_type', $noo_job_type[$job->ID], $job );
	}
endif;

if( !function_exists( 'jm_get_job_status' ) ) :
	function jm_get_job_status() {
		return apply_filters('noo_job_status', array(
			'publish'         => _x( 'Active', 'Job status', 'noo' ),
			'inactive'        => _x( 'Inactive', 'Job status', 'noo' ),
			'pending'         => _x( 'Pending Approval', 'Job status', 'noo' ),
			'pending_payment' => _x( 'Pending Payment', 'Job status', 'noo' ),
			'expired'         => _x( 'Expired', 'Job status', 'noo' ),
			'draft'           => _x( 'Draft', 'Job status', 'noo' ),
			// 'preview'         => _x( 'Preview', 'Job status', 'noo' ),
		));
	}
endif;

if( !function_exists( 'jm_job_default_data' ) ) :
	function jm_job_default_data($post_ID = 0, $post = null, $update = false) {

		if( !$update && !empty( $post_ID ) && $post->post_type == 'noo_job' ) {
			$featured = noo_get_post_meta( $post_ID, '_featured' );
			if( empty( $featured ) ) {
				update_post_meta( $post_ID, '_featured', 'no' );
			}
		}
	}
	add_filter('wp_insert_post', 'jm_job_default_data', 10, 3);
endif;
