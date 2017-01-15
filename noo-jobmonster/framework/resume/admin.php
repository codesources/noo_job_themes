<?php

if( !function_exists( 'jm_resume_admin_init' ) ) :
	function jm_resume_admin_init(){
		register_setting('noo_resume', 'noo_resume');
		register_setting('noo_resume_general', 'noo_resume_general');
		register_setting('noo_resume_custom_field', 'noo_resume_custom_field');
	}
	
	add_filter('admin_init', 'jm_resume_admin_init' );
endif;

if( !function_exists( 'jm_resume_admin_menu' ) ) :
	function jm_resume_admin_menu() {
		global $submenu;
		$permalink = jm_setting_page_url('resume');
		
		$submenu['edit.php?post_type=noo_resume'][] = array( 'Settings', 'edit_theme_options', $permalink );
	}
	
	// No more settings link
	// add_action( 'admin_menu', 'jm_resume_admin_menu', 11 );
endif;

if( !function_exists( 'jm_resume_admin_enqueue_scripts' ) ) :
	function jm_resume_admin_enqueue_scripts( $hook ) {
		if ( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'edit.php' ) {
			$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : get_post_type();

			if ( 'noo_resume' === $post_type ) {

				wp_register_style( 'noo_resume', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo_resume.css' );
				wp_enqueue_style( 'noo_resume' );
			}
		}
	}
	add_filter( 'admin_enqueue_scripts', 'jm_resume_admin_enqueue_scripts', 10 );
endif;

if( !function_exists( 'jm_admin_resumes_page_state' ) ) :
	function jm_admin_resumes_page_state( $states = array(), $post = null ) {
		if( !empty( $post ) && is_object( $post ) ) {
			$archive_slug = jm_get_resume_setting('archive_slug');
			if( !empty( $archive_slug ) && $archive_slug == $post->post_name ) {
				$states['resume_page'] = __('Resumes Page', 'noo');
			}
		}

		return $states;
	}
	add_filter( 'display_post_states', 'jm_admin_resumes_page_state', 10, 2 );
endif;

if( !function_exists( 'jm_admin_resumes_page_notice' ) ) :
	function jm_admin_resumes_page_notice( $post_type = '', $post = null ) {
		if( !empty( $post ) && is_object( $post ) ) {
			$archive_slug = jm_get_resume_setting('archive_slug');
			if ( !empty( $archive_slug ) && $archive_slug == $post->post_name && empty( $post->post_content ) ) {
				add_action( 'edit_form_after_title', '_jm_admin_resumes_page_notice' );
				remove_post_type_support( $post_type, 'editor' );
			}
		}
	}
	add_action( 'add_meta_boxes', 'jm_admin_resumes_page_notice', 10, 2 );

	function _jm_admin_resumes_page_notice() {
		echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the page that shows all your resumes.', 'noo' ) . '</p></div>';
	}
endif;
