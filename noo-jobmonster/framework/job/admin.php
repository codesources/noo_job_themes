<?php

if( !function_exists( 'jm_job_admin_init' ) ) :
	function jm_job_admin_init(){
		register_setting('noo_job_general','noo_job_general');
		register_setting('noo_job_custom_field', 'noo_job_custom_field');
		register_setting('noo_job_custom_field_display', 'noo_job_custom_field_display');
		register_setting('noo_job_linkedin','noo_job_linkedin');
		add_action('noo_job_setting_general', 'jm_job_settings_form');
		add_action('noo_job_setting_email', 'jm_email_settings_form');
		if( class_exists('TablePress') ) {
			add_action( "load-noo_job_page_manage_noo_job", array( TablePress::$controller, 'add_editor_buttons' ) );
		}
	}
	
	add_filter('admin_init', 'jm_job_admin_init' );
endif;

if( !function_exists( 'jm_job_admin_enqueue_scripts' ) ) :
	function jm_job_admin_enqueue_scripts(){
		if(get_post_type() === 'noo_job' || get_post_type() === 'noo_application' || get_post_type() === 'noo_resume' ){
			wp_enqueue_style( 'noo-job', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo_job.css');
			
			wp_register_script( 'noo-job', NOO_FRAMEWORK_ADMIN_URI . '/assets/js/noo_job.js', array( 'jquery'), null, true );
			wp_enqueue_script('noo-job');
		}
	}
	add_filter( 'admin_enqueue_scripts', 'jm_job_admin_enqueue_scripts', 10, 2 );
endif;

if( !function_exists( 'jm_admin_jobs_page_state' ) ) :
	function jm_admin_jobs_page_state( $states = array(), $post = null ) {
		if( !empty( $post ) && is_object( $post ) ) {
			$archive_slug = jm_get_job_setting('archive_slug');
			if( !empty( $archive_slug ) && $archive_slug == $post->post_name ) {
				$states['job_page'] = __('Jobs Page', 'noo');
			}
		}

		return $states;
	}
	add_filter( 'display_post_states', 'jm_admin_jobs_page_state', 10, 2 );
endif;

if( !function_exists( 'jm_admin_jobs_page_notice' ) ) :
	function jm_admin_jobs_page_notice( $post_type = '', $post = null ) {
		if( !empty( $post ) && is_object( $post ) ) {
			$archive_slug = jm_get_job_setting('archive_slug');
			if ( !empty( $archive_slug ) && $archive_slug == $post->post_name && empty( $post->post_content ) ) {
				add_action( 'edit_form_after_title', '_jm_admin_jobs_page_notice' );
				remove_post_type_support( $post_type, 'editor' );
			}
		}
	}
	add_action( 'add_meta_boxes', 'jm_admin_jobs_page_notice', 10, 2 );

	function _jm_admin_jobs_page_notice() {
		echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the page that shows all your jobs.', 'noo' ) . '</p></div>';
	}
endif;
