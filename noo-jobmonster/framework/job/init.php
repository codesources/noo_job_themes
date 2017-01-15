<?php
if( !function_exists('jm_register_job_post_type') ) :
	function jm_register_job_post_type() {
		if ( post_type_exists( 'noo_job' ) )
			return;
		
		$job_slug = jm_get_job_setting('archive_slug','jobs');
		$job_rewrite = $job_slug ? array( 'slug' => sanitize_title( $job_slug ), 'with_front' => true, 'feeds' => true ) : false;

		register_post_type( 
			'noo_job', 
			array( 
				'labels' => array( 
					'name' => __( 'Jobs', 'noo' ), 
					'singular_name' => __( 'Job', 'noo' ), 
					'add_new' => __( 'Add New Job', 'noo' ), 
					'add_new_item' => __( 'Add Job', 'noo' ), 
					'edit' => __( 'Edit', 'noo' ), 
					'edit_item' => __( 'Edit Job', 'noo' ), 
					'new_item' => __( 'New Job', 'noo' ), 
					'view' => __( 'View', 'noo' ), 
					'view_item' => __( 'View Job', 'noo' ), 
					'search_items' => __( 'Search Job', 'noo' ), 
					'not_found' => __( 'No Jobs found', 'noo' ), 
					'not_found_in_trash' => __( 'No Jobs found in Trash', 'noo' ), 
					'parent' => __( 'Parent Job', 'noo' ) ),
				 
				'description'         => __( 'This is a place where you can add new job.', 'noo' ),
				'public'              => true,
				'menu_icon' 		  => 'dashicons-portfolio',
				'show_ui'             => true,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false, // Hierarchical jobs memory issues - WP loads all records!
				'rewrite'             => apply_filters( 'jm_job_rewrite', $job_rewrite ),
				'query_var'           => true,
				'supports' 			  => noo_get_option( 'noo_job_comment', false ) ? array( 'title', 'editor', 'comments' ) : array( 'title', 'editor' ), 
				'has_archive'         => true,
				'show_in_nav_menus'   => true,
				'delete_with_user'	  => true,
				'can_export' 		  => true) );
		register_taxonomy( 
			'job_category', 
			'noo_job', 
			array( 
				'labels' => array( 
					'name' => __( 'Job Category', 'noo' ), 
					'add_new_item' => __( 'Add New Job Category', 'noo' ), 
					'new_item_name' => __( 'New Job Category', 'noo' ) ), 
				'hierarchical' => true, 
				'query_var' => true, 
				// 'capabilities'          => array(
				// 	'manage_terms' => 'manage_noo_job_terms',
				// 	'edit_terms'   => 'edit_noo_job_terms',
				// 	'delete_terms' => 'delete_noo_job_terms',
				// 	'assign_terms' => 'assign_noo_job_terms',
				// ),
				'rewrite' => array( 'slug' => _x('job-category', 'slug', 'noo') ) ) );
		register_taxonomy( 
			'job_type', 
			'noo_job', 
			array( 
				'labels' => array( 
					'name' => __( 'Job Type', 'noo' ), 
					'add_new_item' => __( 'Add New Job Type', 'noo' ), 
					'new_item_name' => __( 'New Job Type', 'noo' ) ), 
				'hierarchical' => true, 
				'query_var' => true,
				// 'capabilities'          => array(
				// 	'manage_terms' => 'manage_noo_job_terms',
				// 	'edit_terms'   => 'edit_noo_job_terms',
				// 	'delete_terms' => 'delete_noo_job_terms',
				// 	'assign_terms' => 'assign_noo_job_terms',
				// ),
				'rewrite' => array( 'slug' => _x('job-type', 'slug', 'noo') ) ) );
		register_taxonomy( 
			'job_tag', 
			'noo_job', 
			array( 
				'labels' => array( 
					'name' => __( 'Job Tag', 'noo' ), 
					'add_new_item' => __( 'Add New Job Tag', 'noo' ), 
					'new_item_name' => __( 'New Job Tag', 'noo' ) ), 
				'hierarchical' => false, 
				'query_var' => true, 
				// 'capabilities'          => array(
				// 	'manage_terms' => 'manage_noo_job_terms',
				// 	'edit_terms'   => 'edit_noo_job_terms',
				// 	'delete_terms' => 'delete_noo_job_terms',
				// 	'assign_terms' => 'assign_noo_job_terms',
				// ),
				'rewrite' => array( 'slug' => _x('job-tag', 'slug', 'noo') ) ) );
		register_taxonomy( 
			'job_location', 
			'noo_job', 
			array( 
				'labels' => array( 
					'name' => __( 'Job Location', 'noo' ), 
					'add_new_item' => __( 'Add New Job Location', 'noo' ), 
					'new_item_name' => __( 'New Job Location', 'noo' ) ), 
				'hierarchical' => true, 
				'query_var' => true, 
				// 'capabilities'          => array(
				// 	'manage_terms' => 'manage_noo_job_terms',
				// 	'edit_terms'   => 'edit_noo_job_terms',
				// 	'delete_terms' => 'delete_noo_job_terms',
				// 	'assign_terms' => 'assign_noo_job_terms',
				// ),
				'rewrite' => array( 'slug' => _x('job-location', 'slug', 'noo') ) ) );
		
		register_post_status( 'expired',array( 
				'label' => _x( 'Expired', 'Job status', 'noo' ), 
				'public' => false, 
				'exclude_from_search' => true, 
				'show_in_admin_all_list' => false, 
				'show_in_admin_status_list' => true, 
				'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'noo' ) 
		));
		register_post_status( 'pending_payment',array( 
				'label' => _x( 'Pending Payment', 'Job status', 'noo' ), 
				'public' => false, 
				'exclude_from_search' => true, 
				'show_in_admin_all_list' => false, 
				'show_in_admin_status_list' => true, 
				'label_count' => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'noo' ) 
		));

		// register_post_status('preview',array(
		// 		'label' => _x( 'Preview', 'Job status', 'noo' ),
		// 		'public' => false,
		// 		'exclude_from_search' => false,
		// 		'show_in_admin_all_list' => true,
		// 		'show_in_admin_status_list' => true,
		// 		'label_count' => _n_noop('Preview <span class="count">(%s)</span>','Preview <span class="count">(%s)</span>','noo' ) 
		// ));

		register_post_status( 'inactive', array(
			'label'                     => __( 'Inactive', 'noo' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'noo' ),
		) );
	}
	add_action( 'init', 'jm_register_job_post_type', 0 );
endif;

if( !function_exists('jm_job_switch_theme_hook') ) :
	function jm_job_switch_theme_hook($newname = '', $newtheme = ''){
		_job_insert_default_data();
	}
	add_action( 'after_switch_theme', 'jm_job_switch_theme_hook' );

	if( !function_exists('_job_insert_default_data') ) :
		function _job_insert_default_data() {
			if ( get_option( 'noo_job_insert_default_data' ) == '1' ) {
				return;
			}	
			$taxonomies = array(
				'job_type' => array(
					'Full Time',
					'Part Time',
					'Freelance',
					'Contract'
				)
			);
			$default_colors = array('#f14e3b', '#458cce', '#e6b707', '#578523');

			foreach ( $taxonomies as $taxonomy => $terms ) {
				foreach ( $terms as $index => $term ) {
					if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
						$result = wp_insert_term( $term, $taxonomy );
						if( !is_wp_error( $result ) && $taxonomy == 'job_type' ) {
							if( function_exists( 'update_term_meta' ) ) {
								update_term_meta( $result['term_id'], '_color', $default_colors[$index] );
							}
						}
					}
				}
			}

			delete_option( 'noo_job_insert_default_data' );
			update_option( 'noo_job_insert_default_data', '1' );
		}
	endif;
endif;
