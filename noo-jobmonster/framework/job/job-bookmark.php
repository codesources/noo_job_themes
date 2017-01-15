<?php
if( !function_exists('jm_is_enabled_job_bookmark') ) :
	function jm_is_enabled_job_bookmark() {
		return (bool) apply_filters( 'jm_enable_job_bookmark', jm_get_action_control( 'bookmark_job' ) );
	}
endif;

if( !function_exists('jm_member_page_endpoint_bookmark') ) :
	function jm_member_page_endpoint_bookmark( $endpoints ) {
		if( jm_is_enabled_job_bookmark() ) {
			$endpoints = array_merge( $endpoints, array(
				'bookmark-job' => 'bookmark-job',
			) );
		}

		return $endpoints;
	}
	add_filter( 'noo-member-page-endpoint', 'jm_member_page_endpoint_bookmark' );
endif;

if( !function_exists( 'jm_job_detail_bookmark_button' ) ) :
	function jm_job_detail_bookmark_button( $job_id = 0 ) {
		if( !jm_is_enabled_job_bookmark() ) {
			return false;
		}

		if( empty($job_id) ) {
			$job_id = get_the_ID();
		}

		$job_id = absint( $job_id );

		// if( empty($job_id) || 'noo_job' != get_post_type( $job_id ) ) {
		// 	return false;
		// }

		if( Noo_Member::is_candidate() ) : ?>
			<a class="bookmark-job-link bookmark-job <?php echo ( jm_is_job_bookmarked(0, $job_id) ? 'bookmarked' : '' ); ?> pull-right" href="javascript:void(0);" data-toggle="tooltip" data-job-id="<?php echo esc_attr($job_id); ?>" data-action="noo_bookmark_job" data-security="<?php echo wp_create_nonce( 'noo-bookmark-job' );?>" title="<?php _e('Bookmark Job', 'noo'); ?>"><i class="fa fa-heart"></i></a>
		<?php elseif( !Noo_Member::is_logged_in() ) : ?>
			<a class="bookmark-job-link member-login-link pull-right" href="<?php echo Noo_Member::get_login_url(); ?>" data-toggle="tooltip" data-login-message="<?php _e('Please login to Bookmark', 'noo'); ?>" title="<?php _e('Bookmark Job', 'noo'); ?>"><i class="fa fa-heart"></i></a>
		<?php endif;
	}
	add_action( 'jm_job_detail_actions', 'jm_job_detail_bookmark_button' );
endif;

if( !function_exists( 'jm_member_heading_label_job_bookmark' ) ) :
	function jm_member_heading_label_job_bookmark( $label, $endpoint ) {
		if( $endpoint == 'bookmark-job' ) {
			return __('Bookmarked Jobs','noo');
		}
		
		return $label;
	}
	add_action( 'jm_member_heading_label', 'jm_member_heading_label_job_bookmark', 10, 2 );
endif;

if( !function_exists( 'jm_member_heading_job_bookmark' ) ) :
	function jm_member_heading_job_bookmark() {
		?>
		<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('bookmark-job'))?>"><a href="<?php echo Noo_Member::get_endpoint_url('bookmark-job')?>"><i class="fa fa-heart"></i> <?php _e('Bookmarked Jobs','noo')?></a></li>
		<?php
	}
	add_action( 'noo-member-candidate-heading', 'jm_member_heading_job_bookmark' );
endif;

if( !function_exists( 'jm_member_menu_job_bookmark' ) ) :
	function jm_member_menu_job_bookmark() {
		?>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('bookmark-job')?>"><i class="fa fa-heart"></i> <?php _e('Bookmarked Jobs','noo')?></a></li>
		<?php
	}
	add_action( 'noo-member-candidate-menu', 'jm_member_menu_job_bookmark' );
endif;

if( !function_exists( 'jm_member_job_bookmark_shortcode' ) ) :
	function jm_member_job_bookmark_shortcode( $html = '', $query_vars = array() ) {
		if( isset( $query_vars['bookmark-job'] ) ) {
			if( !Noo_Member::is_candidate() ) {
				return '<h3>' . __('Only candidates can access this page', 'noo') . '</h3>';
			}
			ob_start();
	        
	        include(locate_template("layouts/manage-job_bookmark.php"));

			return ob_get_clean();
		}
	}
	add_filter( 'noo-member-candidate-shortcode', 'jm_member_job_bookmark_shortcode', 10, 2 );
endif;

if( !function_exists( 'jm_job_set_bookmarked' ) ) :
	function jm_job_set_bookmarked( $user_id = 0, $job_id = 0 ) {
		if( empty($user_id) ) {
			$user_id = get_current_user_id();
		}

		if( empty($user_id) ) {
			return false;
		}

		if( empty($job_id) ) {
			$job_id = get_the_ID();
		}

		$job_id = absint( $job_id );

		// if( empty($job_id) || 'noo_job' != get_post_type( $job_id ) ) {
		// 	return false;
		// }

		$bookmarks = get_option("noo_bookmark_job_{$user_id}");
		if( empty( $bookmarks ) || !is_array( $bookmarks ) ) {
			$bookmarks = array();
		}

		if( isset( $bookmarks[$job_id] ) && $bookmarks[$job_id] == 1 ) {
			return true;
		} else {
			$bookmarks[$job_id] = 1;
		}
		return update_option("noo_bookmark_job_{$user_id}", $bookmarks);
	}
endif;

if( !function_exists( 'jm_job_clear_bookmarked' ) ) :
	function jm_job_clear_bookmarked( $user_id = 0, $job_id = 0 ) {
		if( empty($user_id) ) {
			$user_id = get_current_user_id();
		}

		if( empty($user_id) ) {
			return false;
		}

		if( empty($job_id) ) {
			$job_id = get_the_ID();
		}

		$job_id = absint( $job_id );

		// if( empty($job_id) || 'noo_job' != get_post_type( $job_id ) ) {
		// 	return false;
		// }

		$bookmarks = get_option("noo_bookmark_job_{$user_id}", array());
		if( empty( $bookmarks ) || !is_array( $bookmarks ) ) {
			$bookmarks = array();
		}

		if( !isset($bookmarks[$job_id]) ) {
			return true;
		}

		unset($bookmarks[$job_id] );
		return update_option("noo_bookmark_job_{$user_id}", $bookmarks);
	}
endif;

if( !function_exists( 'jm_is_job_bookmarked' ) ) :
	function jm_is_job_bookmarked( $user_id = 0, $job_id = 0 ) {
		if( empty($user_id) ) {
			$user_id = get_current_user_id();
		}

		if( empty($user_id) ) {
			return false;
		}

		if( empty($job_id) ) {
			$job_id = get_the_ID();
		}

		if( empty($job_id) || 'noo_job' != get_post_type( $job_id ) ) {
			return false;
		}

		$job_id = absint( $job_id );

		$bookmarks = get_option("noo_bookmark_job_{$user_id}", array());

		if( empty( $bookmarks ) || !is_array( $bookmarks ) ) {
			return false;
		}

		return ( isset($bookmarks[$job_id]) && !empty($bookmarks[$job_id]) );
	}
endif;

if( !function_exists( 'jm_get_candidate_bookmarked_job' ) ) :
	function jm_get_candidate_bookmarked_job( $user_id = 0 ) {
		if( empty($user_id) ) {
			$user_id = get_current_user_id();
		}

		if( empty($user_id) ) {
			return array();
		}

		$bookmarks = get_option("noo_bookmark_job_{$user_id}", array());
		if( empty( $bookmarks ) || !is_array( $bookmarks ) ) {
			return array();
		}

		return $bookmarks;
	}
endif;

if( function_exists('noo_message_print') ) {
	add_action('noo_member_manage_bookmark_job_before', 'noo_message_print',10);
}

