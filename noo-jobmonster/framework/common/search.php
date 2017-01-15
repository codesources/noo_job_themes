<?php

/* -------------------------------------------------------
 * Create functions jm_request_live_search
 * ------------------------------------------------------- */

if ( ! function_exists( 'jm_request_live_search' ) ) :
	
	function jm_request_live_search() {

		check_ajax_referer('noo-advanced-live-search', 'live-search-nonce');

		$post_type = esc_html( $_GET['post_type'] );
		if( !in_array( $post_type, array( 'noo_job', 'noo_resume' ) ) ) {
			wp_die();
		} elseif( $post_type == 'noo_resume' && !jm_can_view_resume(null,true) ) {
			wp_die();
		}

		unset( $_GET['post_type'] );

		$args = array(
			'post_type'   => $post_type,
			'post_status' => 'publish',
			's' => esc_html( $_GET['s'] ),
			'paged' => isset( $_GET['paged'] ) ? $_GET['paged'] : 1
		);

		unset( $_GET['s'] );
		unset( $_GET['action'] );
		unset( $_GET['live-search-nonce'] );

		$query = new WP_Query( $args );

		add_filter( 'paginate_links', 'jm_ajax_live_search_paginate' );

		if ( $args['post_type'] == 'noo_resume' ) :
			$args = jm_resume_query_from_request( $args, $_GET );
			$query = new WP_Query( $args );

			$loop_args = apply_filters( 'noo_resume_search_args', array(
					'query'    => $query,
					'live_search' => true
				), $args );
			jm_resume_loop( $loop_args );

		elseif ( $args['post_type'] == 'noo_job' ) :
			$args = jm_job_query_from_request( $args, $_GET );
			$query = new WP_Query( $args );

			$loop_args = apply_filters( 'noo_job_search_args', array(
					'query'    => $query,
					'paginate' =>'loadmore',
					'title'    =>''
				), $args );
			jm_job_loop( $loop_args );

		endif;

		remove_filter( 'paginate_links', 'jm_ajax_live_search_paginate' );

		wp_die();

	}

	add_action( 'wp_ajax_nopriv_live_search', 'jm_request_live_search' );
	add_action( 'wp_ajax_live_search', 'jm_request_live_search' );

endif;

/** ====== END jm_request_live_search ====== **/

if ( ! function_exists( 'jm_ajax_live_search_paginate' ) ) :
	
	function jm_ajax_live_search_paginate( $link ) {

		if (defined('DOING_AJAX') && DOING_AJAX ) {
			$post_type = get_query_var( 'post_type' );
			if( $post_type == 'noo_job' || $post_type == 'noo_resume' ) {
				$link_request = explode( '?', $link );
				$link_query = isset( $link_request[1] ) ? $link_request[1] : '';
				wp_parse_str( $link_query, $link_args );

				$link = isset( $link_args['_wp_http_referer'] ) ? $link_args['_wp_http_referer'] : home_url('/');
				unset( $link_args['action'] );
				unset( $link_args['live-search-nonce'] );
				unset( $link_args['_wp_http_referer'] );
				foreach ($link_args as $key => $value) {
					if( $question_mark = strpos($key, '?') ) {
						$key = substr($key, $question_mark);
					}
					$link = esc_url_raw( add_query_arg( $key, $value, $link ) );
				}
			}
		}

		return $link;
	}

endif;
