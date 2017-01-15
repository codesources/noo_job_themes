<?php

if( !function_exists('jm_noo_resumes_shortcode') ) :
	function jm_noo_resumes_shortcode( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'title'           => '',
			'show_pagination' => 'no',
			'posts_per_page'  => 3,
			'no_content'      => 'text',
			'job_category'    => 'all',
			'job_location'    => 'all',
			'orderby'         => 'date',
			'order'           => 'desc'
		), $atts));
		$paged = 1;
		if(defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'noo_resume_nextajax'){
			$paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
			$posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : 3;
			$job_category = isset($_POST['job_category']) ? $_POST['job_category'] : $job_category;
			$job_location = isset($_POST['job_location']) ? $_POST['job_location'] : $job_location;
			$orderby = isset($_POST['orderby']) ? $_POST['orderby'] : $orderby;
			$order = isset($_POST['order']) ? $_POST['order'] : $order;
		} else {
			if( is_front_page() || is_home()) {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
			} else {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			}
		}
		$args = array(
			'post_type'			  => 'noo_resume',
			'post_status'         => 'publish',
			'paged'			  	  => $paged,
			'posts_per_page'	  => $posts_per_page,
			// 'ignore_sticky_posts' => true,
		);
		//  -- tax_query
	
			$args['meta_query'] = array( 'relation' => 'AND' );
			if ( $job_category != 'all' ) {
				$args['meta_query'][] = array(
					'key'     => '_job_category',
					'value'   => '"' . $job_category . '"',
					'compare' => 'LIKE'
				);
			}

			if ( $job_location != 'all' ) {
				$args['meta_query'][] = array(
					'key'     => '_job_location',
					'value'   => '"' . $job_location . '"',
					'compare' => 'LIKE'
				);
			}

		//  -- Check order by......
		
			if ( $orderby == 'view' ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_noo_views_count';
	 		} else {
	 			$args['orderby'] = 'date';
	 		}

	 	//  -- Check order
	 	
	 		if ( $order == 'asc' ) {
	 			$args['order'] = 'ASC';
	 		} else {
	 			$args['order'] = 'DESC';
	 		}

	 	$r = new WP_Query( $args );
	 	ob_start();
		$pagination = $show_pagination == 'yes' ? 1 : 0;
		jm_resume_loop(array(
			'query'          =>$r,
			'title'          =>$title,
			'paginate'       =>'resume_nextajax',
			'ajax_item'      => (defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'noo_resume_nextajax'),
			'item_class'     =>'nextajax-item',
			'pagination'     =>$pagination,
			'posts_per_page' =>$posts_per_page,
			'job_category'	 =>$job_category,
			'job_location'   =>$job_location,
			'orderby'        =>$orderby,
			'order'          =>$order,
			'is_shortcode'   =>true
		));
		$output = ob_get_clean();
		wp_reset_query();
		if(defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'noo_resume_nextajax'){
			echo $output;
			die;
		}
		return $output;
	}

	add_shortcode( 'noo_resume', 'jm_noo_resumes_shortcode' );

	// ajax action
	add_action( 'wp_ajax_nopriv_noo_resume_nextajax', 'jm_noo_resumes_shortcode' );
	add_action( 'wp_ajax_noo_resume_nextajax', 'jm_noo_resumes_shortcode' );
endif;
