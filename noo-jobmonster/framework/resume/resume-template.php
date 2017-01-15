<?php

if( !function_exists('jm_resume_template_loader') ) :
	function jm_resume_template_loader( $template ) {
		global $wp_query;
		if( is_post_type_archive( 'noo_resume' ) ){
			$template       = locate_template( 'archive-noo_resume.php' );
		}
		return $template;
	}

	add_action( 'template_include', 'jm_resume_template_loader' );
endif;

if( !function_exists('jm_resume_loop') ) :
	function jm_resume_loop( $args = '' ) {
		$defaults = array( 
			'query'          => '', 
			'title'          => '', 
			'pagination'     => 1,
			'paginate'       => 'normal',
			'ajax_item'		 => false,
			'excerpt_length' => 30,
			'posts_per_page'  => 3,
			'is_shortcode'   => false,
			'job_category'    => 'all',
			'job_location'    => 'all',
			'orderby'         => 'date',
			'order'           => 'desc',
			'live_search'     => false
		);
		$p = wp_parse_args($args,$defaults);
		extract($p);
		global $wp_query;
		if(!empty($query))
			$wp_query = $query;

		ob_start();
		include(locate_template("layouts/noo_resume-loop.php"));
		echo ob_get_clean();

		wp_reset_postdata();
		wp_reset_query();

	}
endif;

if( !function_exists('jm_resume_detail') ) :
	function jm_resume_detail( $query = null, $hide_profile = false ) {
		if(empty($query)){
			global $wp_query;
			$query = $wp_query;
		}

		while ($query->have_posts()): $query->the_post(); global $post;

			$resume_id			= $post->ID;

			ob_start();
			if( jm_can_view_single_resume( $resume_id ) ) {
				include(locate_template("layouts/noo_resume-detail.php"));
			} else {
				include(locate_template("layouts/cannot-view-resume.php"));
			}
			echo ob_get_clean();
		
		endwhile;
		wp_reset_query();
	}
endif;
