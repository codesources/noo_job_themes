<?php
if( !function_exists('jm_register_resume_post_type') ) :
	function jm_register_resume_post_type() {
		if ( post_type_exists( 'noo_resume' ) || !jm_resume_enabled() )
			return;

		// Sample register post type
		$archive_slug = jm_get_resume_setting('archive_slug', 'resumes');
		$archive_rewrite = $archive_slug ? array( 'slug' => sanitize_title( $archive_slug ), 'with_front' => true, 'feeds' => true ) : false;

		register_post_type( 
			'noo_resume', 
			array( 
				'labels' => array( 
					'name' => __( 'Resumes', 'noo' ), 
					'singular_name' => __( 'Resume', 'noo' ), 
					'add_new' => __( 'Add New Resume', 'noo' ), 
					'add_new_item' => __( 'Add Resume', 'noo' ), 
					'edit' => __( 'Edit', 'noo' ), 
					'edit_item' => __( 'Edit Resume', 'noo' ), 
					'new_item' => __( 'New Resume', 'noo' ), 
					'view' => __( 'View', 'noo' ), 
					'view_item' => __( 'View Resume', 'noo' ), 
					'search_items' => __( 'Search Resume', 'noo' ), 
					'not_found' => __( 'No Resumes found', 'noo' ), 
					'not_found_in_trash' => __( 'No Resumes found in Trash', 'noo' )
				), 
				'public' => true, 
				'has_archive' => true, 
				'menu_icon' => 'dashicons-id-alt', 
				'rewrite' => apply_filters( 'jm_resume_rewrite', $archive_rewrite ), 
				'supports' => array( 'title', 'editor' ), 
				'can_export' => true,
				'delete_with_user' => true,
			)
		);
	}
	add_action( 'init', 'jm_register_resume_post_type', 0 );
endif;
