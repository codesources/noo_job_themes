<?php
/**
 * Utilities Functions for NOO Framework.
 * This file contains various functions for getting and preparing data.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if(!function_exists('noo_get_endpoint_url')){
	function noo_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
		if ( ! $permalink )
			$permalink = get_permalink();
	
		
		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
		} else {
			$url = esc_url_raw(add_query_arg( $endpoint, $value, $permalink ));
		}
	
		return apply_filters( 'noo_get_endpoint_url', $url, $endpoint );
	}
}

if (!function_exists('smk_get_all_sidebars')):
	function smk_get_all_sidebars() {
		global $wp_registered_sidebars;
		$sidebars = array();
		$none_sidebars = array();
		for ($i = 1;$i <= 4;$i++) {
			$none_sidebars[] = "noo-top-{$i}";
			$none_sidebars[] = "noo-footer-{$i}";
		}
		if ($wp_registered_sidebars && !is_wp_error($wp_registered_sidebars)) {
			
			foreach ($wp_registered_sidebars as $sidebar) {
				// Don't include Top Bar & Footer Widget Area
				if (in_array($sidebar['id'], $none_sidebars)) continue;
				
				$sidebars[$sidebar['id']] = $sidebar['name'];
			}
		}
		return $sidebars;
	}
endif;

if (!function_exists('get_sidebar_name')):
	function get_sidebar_name($id = '') {
		if (empty($id)) return '';
		
		global $wp_registered_sidebars;
		if ($wp_registered_sidebars && !is_wp_error($wp_registered_sidebars)) {
			foreach ($wp_registered_sidebars as $sidebar) {
				if ($sidebar['id'] == $id) return $sidebar['name'];
			}
		}
		
		return '';
	}
endif;

if (!function_exists('get_sidebar_id')):
	function get_sidebar_id() {
		// Normal Page or Static Front Page
		if ( is_page() || (is_front_page() && get_option('show_on_front') == 'page') ) {
			// Get the sidebar setting from
			$sidebar = noo_get_post_meta(get_the_ID(), '_noo_wp_page_sidebar', 'sidebar-main');
			
			return $sidebar;
		}

		// NOO Resume
		if( is_post_type_archive( 'noo_resume' ) ) {
			$resume_layout = noo_get_option('noo_resumes_layout', 'sidebar');
			if( $resume_layout != 'fullwidth' ) {
				return noo_get_option('noo_resume_list_sidebar', 'sidebar-resume');
			}

			return '';
		}
		if( is_singular( 'noo_resume' ) ) {
			return '';
		}
		
		// NOO Company
		if( is_post_type_archive( 'noo_company' ) || is_singular( 'noo_company' ) ) {
			$companies_layout = noo_get_option('noo_companies_layout', 'fullwidth');
			if( $companies_layout != 'fullwidth' ) {
				return noo_get_option('noo_companies_sidebar', 'sidebar-main');
			}

			return '';
		}
		
		// NOO Job
		if( is_post_type_archive( 'noo_job' )
			|| is_tax( 'job_category' )
			|| is_tax( 'job_type' )
			|| is_tax( 'job_tag' )
			|| is_tax( 'job_location' ) ) {

			$jobs_layout = noo_get_option('noo_jobs_layout', 'sidebar');
			if( $jobs_layout != 'fullwidth' ) {
				return noo_get_option('noo_jobs_sidebar', 'sidebar-job');
			}

			return '';
		}
		
		// Single Job
		if( is_singular( 'noo_job' ) ) {
			return '';
		}

		// WooCommerce Product
		if( NOO_WOOCOMMERCE_EXIST ) {
			if( is_product() ) {
				$product_layout = noo_get_option('noo_woocommerce_product_layout', 'same_as_shop');
				$sidebar = '';
				if ( $product_layout == 'same_as_shop' ) {
					$product_layout = noo_get_option('noo_shop_layout', 'fullwidth');
					$sidebar = noo_get_option('noo_shop_sidebar', '');
				} else {
					$sidebar = noo_get_option('noo_woocommerce_product_sidebar', '');
				}
				
				if ( $product_layout == 'fullwidth' ) {
					return '';
				}
				
				return $sidebar;
			}

			// Shop, Product Category, Product Tag, Cart, Checkout page
			if( is_shop() || is_product_category() || is_product_tag() ) {
				$shop_layout = noo_get_option('noo_shop_layout', 'fullwidth');
				if($shop_layout != 'fullwidth'){
					return noo_get_option('noo_shop_sidebar', '');
				}

				return '';
			}
		}
		
		// Single post page
		if (is_single()) {
			// Check if there's overrode setting in this post.
			$post_id = get_the_ID();
			$override_setting = noo_get_post_meta($post_id, '_noo_wp_post_override_layout', false);
			if ($override_setting) {
				// overrode
				$overrode_layout = noo_get_post_meta($post_id, '_noo_wp_post_layout', 'fullwidth');
				if ($overrode_layout != 'fullwidth') {
					return noo_get_post_meta($post_id, '_noo_wp_post_sidebar', 'sidebar-main');
				}
			} else{

				$post_layout = noo_get_option('noo_blog_post_layout', 'same_as_blog');
				$sidebar = '';
				if ($post_layout == 'same_as_blog') {
					$post_layout = noo_get_option('noo_blog_layout', 'sidebar');
					$sidebar = noo_get_option('noo_blog_sidebar', 'sidebar-main');
				} else {
					$sidebar = noo_get_option('noo_blog_post_sidebar', 'sidebar-main');
				}
				
				if($post_layout == 'fullwidth'){
					return '';
				}
				
				return $sidebar;
			}

			return '';
		}

		// Archive page
		if( is_archive() ) {
			$archive_layout = noo_get_option('noo_blog_archive_layout', 'same_as_blog');
			$sidebar = '';
			if ($archive_layout == 'same_as_blog') {
				$archive_layout = noo_get_option('noo_blog_layout', 'sidebar');
				$sidebar = noo_get_option('noo_blog_sidebar', 'sidebar-main');
			} else {
				$sidebar = noo_get_option('noo_blog_archive_sidebar', 'sidebar-main');
			}
			
			if($archive_layout == 'fullwidth'){
				return '';
			}
			
			return $sidebar;
		}

		// Archive, Index or Home
		if (is_home() || is_archive() || (is_front_page() && get_option('show_on_front') == 'posts')) {
			
			$blog_layout = noo_get_option('noo_blog_layout', 'sidebar');
			if ($blog_layout != 'fullwidth') {
				return noo_get_option('noo_blog_sidebar', 'sidebar-main');
			}
			
			return '';
		}
		
		return '';
	}
endif;

if ( !function_exists('noo_default_primary_color') ) :
	function noo_default_primary_color() {
		return '#e6b706';
	}
endif;
if ( !function_exists('noo_default_font_family') ) :
	function noo_default_font_family() {
		return 'Droid Serif';
	}
endif;
if ( !function_exists('noo_default_text_color') ) :
	function noo_default_text_color() {
		return '#44494b';
	}
endif;
if ( !function_exists('noo_default_headings_font_family') ) {
	function noo_default_headings_font_family() {
		return 'Montserrat';
	}
}
if ( !function_exists('noo_default_headings_color') ) {
	function noo_default_headings_color() {
		return noo_default_text_color();
	}
}
if ( !function_exists('noo_default_header_bg') ) {
	function noo_default_header_bg() {
		if( noo_get_option( 'noo_site_skin', 'light' ) == 'dark' ) {
			return '#000000';
		}

		return '#FFFFFF';
	}
}
if ( !function_exists('noo_default_nav_font_family') ) {
	function noo_default_nav_font_family() {
		return noo_default_headings_font_family();
	}
}
if ( !function_exists('noo_default_logo_font_family') ) {
	function noo_default_logo_font_family() {
		return noo_default_headings_font_family();
	}
}
if ( !function_exists('noo_default_logo_color') ) {
	function noo_default_logo_color() {
		return noo_default_headings_color();
	}
}
if ( !function_exists('noo_default_font_size') ) {
	function noo_default_font_size() {
		return '14';
	}
}
if ( !function_exists('noo_default_font_weight') ) {
	function noo_default_font_weight() {
		return '400';
	}
}

//
// This function help to create the dynamic thumbnail width,
// but we don't use it at the moment.
// 
if (!function_exists('noo_thumbnail_width')) :
	function noo_thumbnail_width() {
		$site_layout	= noo_get_option('noo_site_layout', 'fullwidth');
		$page_layout	= get_page_layout();
		$width			= 1200; // max width

		if($site_layout == 'boxed') {
			$site_width = (int) noo_get_option('noo_layout_site_width', '90');
			$site_max_width = (int) noo_get_option('noo_layout_site_max_width', '1200');
			$width = min($width * $site_width / 100, $site_max_width);
		}

		if($page_layout != 'fullwidth') {
			$width = $width * 75 / 100; // 75% of col-9
		}

		return $width;
	}
endif;

if (!function_exists('get_thumbnail_width')) :
	function get_thumbnail_width() {

		// if( is_admin()) {
		// 	return 'admin-thumb';
		// }

		$site_layout	= noo_get_option('noo_site_layout', 'fullwidth');
		$page_layout	= get_page_layout();

		if($site_layout == 'boxed') {
			if($page_layout == 'fullwidth') {
				return 'boxed-fullwidth';
			} else {
				return 'boxed-sidebar';
			}
		} else {
			if($page_layout == 'fullwidth') {
				return 'fullwidth-fullwidth';
			} else {
				return 'fullwidth-sidebar';
			}
		}

		return 'fullwidth-fullwidth';
	}
endif;

if (!function_exists('get_page_layout')):
	function get_page_layout() {
		
		// Normal Page or Static Front Page
		if (is_page() || (is_front_page() && get_option('show_on_front') == 'page')) {
			// WP page,
			// get the page template setting
			$page_id = get_the_ID();
			$page_template = noo_get_post_meta($page_id, '_wp_page_template', 'default');
			
			if (strpos($page_template, 'sidebar') !== false) {
				if (strpos($page_template, 'left') !== false) {
					return 'left_sidebar';
				}
				
				return 'sidebar';
			}
			
			return 'fullwidth';
		}
		
		// NOO Resume
		if( is_post_type_archive( 'noo_resume' ) ) {
			return noo_get_option('noo_resumes_layout', 'sidebar');
		}
		if( is_singular( 'noo_resume' ) ) {
			return 'fullwidth';
		}
		
		// NOO Company
		if( is_post_type_archive( 'noo_company' ) ) {
			return noo_get_option('noo_companies_layout', 'fullwidth');
		}

		if( is_singular( 'noo_company' ) ) {
			if(noo_get_option('noo_companies_layout', 'fullwidth') == 'fullwidth'){
				return 'sidebar';
			} else{
				return noo_get_option('noo_companies_layout', 'fullwidth');
			}
		}
		
		// NOO Job
		if( is_post_type_archive( 'noo_job' )
			|| is_tax( 'job_category' )
			|| is_tax( 'job_type' )
			|| is_tax( 'job_tag' ) 
			|| is_tax( 'job_location' ) ) {

			return noo_get_option('noo_jobs_layout', 'sidebar');
		}
		
		// Single Job
		if( is_singular( 'noo_job' ) ) {
			return noo_get_option('noo_single_jobs_layout', 'right_company');
		}

		// WooCommerce
		if( NOO_WOOCOMMERCE_EXIST ) {
			if( is_shop() || is_product_category() || is_product_tag() ){
				return noo_get_option('noo_shop_layout', 'fullwidth');
			}

			if( is_product() ) {
				$product_layout = noo_get_option('noo_woocommerce_product_layout', 'same_as_shop');
				if ($product_layout == 'same_as_shop') {
					$product_layout = noo_get_option('noo_shop_layout', 'fullwidth');
				}
				
				return $product_layout;
			}
		}
		
		// Single post page
		if (is_single()) {

			// WP post,
			// check if there's overrode setting in this post.
			$post_id = get_the_ID();
			$override_setting = noo_get_post_meta($post_id, '_noo_wp_post_override_layout', false);
			
			if ( !$override_setting ) {
				$post_layout = noo_get_option('noo_blog_post_layout', 'same_as_blog');
				if ($post_layout == 'same_as_blog') {
					$post_layout = noo_get_option('noo_blog_layout', 'sidebar');
				}
				
				return $post_layout;
			}

			// overrode
			return noo_get_post_meta($post_id, '_noo_wp_post_layout', 'sidebar-main');
		}

		// Archive
		if (is_archive()) {
			$archive_layout = noo_get_option('noo_blog_archive_layout', 'same_as_blog');
			if ($archive_layout == 'same_as_blog') {
				$archive_layout = noo_get_option('noo_blog_layout', 'sidebar');
			}
			
			return $archive_layout;
		}

		// Index or Home
		if (is_home() || (is_front_page() && get_option('show_on_front') == 'posts')) {
			
			return noo_get_option('noo_blog_layout', 'sidebar');
		}
		
		return '';
	}
endif;

if(!function_exists('is_fullwidth')){
	function is_fullwidth(){
		return get_page_layout() == 'fullwidth';
	}
}

if (!function_exists('is_one_page_enabled')):
	function is_one_page_enabled() {
		if( (is_front_page() && get_option('show_on_front' == 'page')) || is_page()) {
			$page_id = get_the_ID();
			return ( noo_get_post_meta( $page_id, '_noo_wp_page_enable_one_page', false ) );
		}

		return false;
	}
endif;

if (!function_exists('get_one_page_menu')):
	function get_one_page_menu() {
		if( is_one_page_enabled() ) {
			if( (is_front_page() && get_option('show_on_front' == 'page')) || is_page()) {
				$page_id = get_the_ID();
				return noo_get_post_meta( $page_id, '_noo_wp_page_one_page_menu', '' );
			}
		}

		return '';
	}
endif;

if (!function_exists('has_home_slider')):
	function has_home_slider() {
		if (class_exists( 'RevSlider' )) {
			if( (is_front_page() && get_option('show_on_front' == 'page')) || is_page()) {
				$page_id = get_the_ID();
				return ( noo_get_post_meta( $page_id, '_noo_wp_page_enable_home_slider', false ) )
					&& ( noo_get_post_meta( $page_id, '_noo_wp_page_slider_rev', '' ) != '' );
			}
		}

		return false;
	}
endif;

if (!function_exists('home_slider_position')):
	function home_slider_position() {
		if (has_home_slider()) {
			return noo_get_post_meta( get_the_ID(), '_noo_wp_page_slider_position', 'below' );
		}

		return '';
	}
endif;

if (!function_exists('get_page_heading')):
	function get_page_heading() {
		$heading = '';
		$sub_heading = '';
		if ( is_home() ) {
			$heading = noo_get_option('noo_blog_heading_title', __( 'Blog', 'noo' ) );
		} elseif ( is_search() ) {
			$heading = __( 'Search Results', 'noo' );
			global $wp_query;
			$search_query = get_search_query();
			$search_query = (isset($_GET['s']) && empty($search_query) ? $_GET['s'] : $search_query);
			// if(!empty($wp_query->found_posts) ) {
			// 	if( !empty($search_query ) ) {
			// 		if($wp_query->found_posts > 1) {
			// 			$heading =  $wp_query->found_posts ." ". __('Search Results for:','noo')." ".esc_attr( $search_query );
			// 		} else {
			// 			$heading =  $wp_query->found_posts ." ". __('Search Results for:','noo')." ".esc_attr( $search_query );
			// 		}
			// 	}
			// } else {
				if(!empty($search_query)) {
					$heading = __('Search Results for:','noo')." ".esc_attr( $search_query );
				}
			// }
		} elseif ( is_post_type_archive( 'noo_job' ) ) {
			$heading = noo_get_option('noo_job_heading_title', __( 'Jobs', 'noo' ) );
		} elseif ( is_post_type_archive( 'noo_company' ) ) {
			$heading = noo_get_option('noo_companies_heading_title', __( 'Companies', 'noo' ) );
		} elseif ( is_post_type_archive( 'noo_resume' ) ) {
			$heading = noo_get_option('noo_resume_heading_title', __( 'Resume Listing', 'noo' ) );
		} elseif ( NOO_WOOCOMMERCE_EXIST && is_shop() ) {
			$heading = noo_get_option( 'noo_shop_heading_title', __( 'Shop', 'noo' ) );
		} elseif ( is_author() ) {
			$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
			$heading = __('Author Archive','noo');

			if(isset($curauth->nickname)) $heading .= ' ' . __('for:','noo')." ".$curauth->nickname;
		}elseif ( is_year() ) {
    		$heading = __( 'Post Archive by Year: ', 'noo' ) . get_the_date( 'Y' );
		} elseif ( is_month() ) {
    		$heading = __( 'Post Archive by Month: ', 'noo' ) . get_the_date( 'F,Y' );
		} elseif ( is_day() ) {
    		$heading = __( 'Post Archive by Day: ', 'noo' ) . get_the_date( 'F j, Y' );
		} elseif ( is_404() ) {
    		$heading = __( 'Oops! We could not find anything to show you.', 'noo' );
    		$sub_heading =  __( 'Would you like to go somewhere else to find your stuff?', 'noo' );
		} elseif ( is_archive() ) {
			$heading        = single_cat_title( '', false );
			$sub_heading   = term_description();
		} elseif( is_page() ) {
			$page_temp = get_page_template_slug();
			if( noo_get_post_meta(get_the_ID(), '_noo_wp_page_hide_page_title', false) ) {
				$heading = '';
			} elseif(get_the_ID() == Noo_Member::get_member_page_id()){
				$heading = get_the_title();
				$current_user = wp_get_current_user();
				if( 'username' == Noo_Member::get_setting('member_title', 'page_title') && 0 != $current_user->ID ) {
					$heading = Noo_Member::get_display_name( $current_user->ID );
				}
				$sub_heading = Noo_Member::get_member_heading_label();
				if(empty($sub_heading) && !is_user_logged_in()){
					$sub_heading = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
				}
			}elseif('page-post-job.php' === $page_temp){
				$heading = __('Post a Job','noo');
				$step = isset($_GET['action']) ? $_GET['action'] : '';
				if($step == 'login'){
					$sub_heading = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
				}elseif ($step == 'job_package'){
					$sub_heading = __('Choose a package','noo');
				}elseif ($step == 'post_job'){
					$sub_heading = __('Describe your company and vacancy','noo');
				}elseif ($step == 'preview_job'){
					$sub_heading = __('Preview and submit your job','noo');
				}else{
					$sub_heading = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
				}
			} elseif('page-post-resume.php' === $page_temp){
				$heading = __('Post a Resume','noo');
				$step = isset($_GET['action']) ? $_GET['action'] : '';
				if($step == 'login'){
					$sub_heading = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
				}elseif ($step == 'resume_general'){
					$sub_heading = __('General Information','noo');
				}elseif ($step == 'resume_detail'){
					$sub_heading = __('Resume Details','noo');
				}elseif ($step == 'resume_preview'){
					$sub_heading = __('Preview and Finish','noo');
				}else{
					$sub_heading = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
				}
			} else {
				$heading = get_the_title();
			}
		} elseif ( is_singular() ) {
			$heading = get_the_title();
		}

		return array($heading, $sub_heading);
	}
endif;

if (!function_exists('get_page_heading_image')):
	function get_page_heading_image() {
		$image = '';
		// if( ! noo_get_option( 'noo_page_heading', true ) ) {
		// 	return $image;
		// }
		if( NOO_WOOCOMMERCE_EXIST && is_shop() ) {
			$image = noo_get_image_option( 'noo_shop_heading_image', '' );
		} elseif ( is_home() ) {
			$image = noo_get_image_option( 'noo_blog_heading_image', '' );
		} elseif( is_category() || is_tag() ) {
			// $queried_object = get_queried_object();
			// $image			= noo_get_term_meta( $queried_object->term_id, 'heading_image', '' );
			// $image			= empty( $image ) ? noo_get_image_option( 'noo_blog_heading_image', '' ) : $image;
		} elseif( NOO_WOOCOMMERCE_EXIST && ( is_product_category() || is_product_tag() ) ) {
			// $queried_object = get_queried_object();
			// $image			= noo_get_term_meta( $queried_object->term_id, 'heading_image', '' );
			// $image			= empty( $image ) ? noo_get_image_option( 'noo_shop_heading_image', '' ) : $image;
		} elseif ( is_singular('noo_job' ) ) {
			$image = '';
			if( jm_get_setting('noo_job_general', 'cover_image','yes') == 'yes' ) {
				$image = noo_get_post_meta(get_the_ID(), '_cover_image', '');
			}
			if ( empty($image) && Noo_Company::get_setting('cover_image', 'yes') == 'yes' ) {
				$company_id = jm_get_job_company( get_the_ID() );
				$image = noo_get_post_meta($company_id, '_cover_image', '');
			}
			if ( empty($image) ) {
				$image = noo_get_image_option( 'noo_job_heading_image', '' );
			}
		} elseif ( is_singular('noo_company' ) ) {
			if( Noo_Company::get_setting('cover_image', 'yes') == 'yes' ) {
				$image = noo_get_post_meta(get_the_ID(), '_cover_image', '');
			}
		} elseif ( is_singular('product' ) ) {
			$image = noo_get_post_meta(get_the_ID(), '_heading_image', '');
		} elseif ( is_page() ) {
			$image = noo_get_post_meta(get_the_ID(), '_heading_image', '');
			$image = wp_get_attachment_image_src( $image, 'full' );
			return $image[0];
		} elseif (is_singular ( 'post' )) {
			$image = noo_get_image_option( 'noo_blog_heading_image', '' );
		} elseif( is_tax('class_category') ) {
			$image = noo_get_image_option( 'noo_class_heading_image', '' );
		} elseif( is_post_type_archive('noo_job') || is_tax('job_location') || is_tax('job_category') ) {
			$image = noo_get_image_option( 'noo_job_heading_image', '' );
		}  elseif( is_post_type_archive('noo_company') ) {
			$image = noo_get_image_option( 'noo_companies_heading_image', '' );
		} elseif( is_post_type_archive('noo_resume') || is_singular( 'noo_resume') ) {
			$image = noo_get_image_option( 'noo_resume_heading_image', '' );
		}
		if (is_numeric( $image )) {
			if( !empty( $image ) ) {
				$image = wp_get_attachment_image_src( $image, 'cover-image' );
				return $image[0];
			}
		}

		if( empty($image) ) {
			$image = NOO_ASSETS_URI . '/images/heading-bg.png';
		}
		return $image;
	}
endif;

if (!function_exists('noo_get_post_format')):
	function noo_get_post_format($post_id = null, $post_type = '') {
		$post_id = (null === $post_id) ? get_the_ID() : $post_id;
		$post_type = ('' === $post_type) ? get_post_type($post_id) : $post_type;

		$post_format = '';
		
		if ($post_type == 'post') {
			$post_format = get_post_format($post_id);
		}
		
		if ($post_type == 'portfolio_project') {
			$post_format = noo_get_post_meta($post_id, '_noo_portfolio_media_type', 'image');
		}

		return $post_format;
	}
endif;

if (!function_exists('has_featured_content')):
	function has_featured_content($post_id = null) {
		$post_id = (null === $post_id) ? get_the_ID() : $post_id;

		$post_type = get_post_type($post_id);
		$prefix = '';
		$post_format = '';
		
		if ($post_type == 'post') {
			$prefix = '_noo_wp_post';
			$post_format = get_post_format($post_id);
		}
		
		if ($post_type == 'portfolio_project') {
			$prefix = '_noo_portfolio';
			$post_format = noo_get_post_meta($post_id, "{$prefix}_media_type", 'image');
		}
		
		switch ($post_format) {
			case 'image':
				$main_image = noo_get_post_meta($post_id, "{$prefix}_main_image", 'featured');
				if( $main_image == 'featured') {
					return has_post_thumbnail($post_id);
				}

				return has_post_thumbnail($post_id) || ( (bool)noo_get_post_meta($post_id, "{$prefix}_image", '') );
			case 'gallery':
				if (!is_singular()) {
					$preview_content = noo_get_post_meta($post_id, "{$prefix}_gallery_preview", 'slideshow');
					if ($preview_content == 'featured') {
						return has_post_thumbnail($post_id);
					}
				}
				
				return (bool)noo_get_post_meta($post_id, "{$prefix}_gallery", '');
			case 'video':
				if (!is_singular()) {
					$preview_content = noo_get_post_meta($post_id, "{$prefix}_preview_video", 'both');
					if ($preview_content == 'featured') {
						return has_post_thumbnail($post_id);
					}
				}
				
				$m4v_video = (bool)noo_get_post_meta($post_id, "{$prefix}_video_m4v", '');
				$ogv_video = (bool)noo_get_post_meta($post_id, "{$prefix}_video_ogv", '');
				$embed_video = (bool)noo_get_post_meta($post_id, "{$prefix}_video_embed", '');
				
				return $m4v_video || $ogv_video || $embed_video;
				
			case 'audio':
				$mp3_audio = (bool)noo_get_post_meta($post_id, "{$prefix}_audio_mp3", '');
				$oga_audio = (bool)noo_get_post_meta($post_id, "{$prefix}_audio_oga", '');
				$embed_audio = (bool)noo_get_post_meta($post_id, "{$prefix}_audio_embed", '');
				return $mp3_audio || $oga_audio || $embed_audio;
			default: // standard post format
				return has_post_thumbnail($post_id);
		}
		
		return false;
	}
endif;

if (!function_exists('noo_get_page_id_by_template')):
	function noo_get_page_id_by_template( $page_template = '' ) {
		global $page_id_by_template;
		if( empty( $page_id_by_template ) || !isset( $page_id_by_template[$page_template] ) ) {
			$pages = get_pages(array(
				'meta_key' => '_wp_page_template',
				'meta_value' => $page_template
			));

			if( $pages ){
				// $page_id = apply_filters( 'wpml_object_id', $pages[0]->ID, 'page', true );
				$page_id = $pages[0]->ID;
				$page_id_by_template[$page_template] = $page_id;
			} else {
				$page_id_by_template[$page_template] = false;	
			}
		}

		return $page_id_by_template[$page_template];
	}
endif;

if (!function_exists('noo_get_page_link_by_template')):
	function noo_get_page_link_by_template( $page_template ) {
		global $page_link_by_template;
		if( empty( $page_link_by_template ) || !isset( $page_link_by_template[$page_template] ) ) {
			$page_id = noo_get_page_id_by_template( $page_template );
			if( !empty( $page_id ) ) {
				$page_link_by_template[$page_template] = get_permalink( $page_id );
			} else {
				$page_link_by_template[$page_template] = home_url();
			}
		}
		
		return $page_link_by_template[$page_template];
	}
endif;

if (!function_exists('noo_current_url')):
	function noo_current_url($encoded = false) {
		global $wp;
		$current_url = esc_url( add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) ) );
		if( $encoded ) {
			return urlencode($current_url);
		}
		return $current_url;
	}
endif;

if (!function_exists('noo_upload_dir_name')):
	function noo_upload_dir_name() {
		return 'noo_jobmonster';
	}
endif;

if (!function_exists('noo_upload_dir')):
	function noo_upload_dir() {
		$upload_dir = wp_upload_dir();

		return $upload_dir['basedir'] . '/' . noo_upload_dir_name();
	}
endif;

if (!function_exists('noo_upload_url')):
	function noo_upload_url() {
		$upload_dir = wp_upload_dir();

		return $upload_dir['baseurl'] . '/' . noo_upload_dir_name();
	}
endif;

if (!function_exists('noo_create_upload_dir')):
	function noo_create_upload_dir( $wp_filesystem = null ) {
		if( empty( $wp_filesystem ) ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		global $wp_filesystem;

		$noo_upload_dir = $wp_filesystem->find_folder( $upload_dir['basedir'] ) . noo_upload_dir_name();
		if ( ! $wp_filesystem->is_dir( $noo_upload_dir ) ) {
			if ( $wp_filesystem->mkdir( $noo_upload_dir, 0777 ) ) {
				return $noo_upload_dir;
			}

			return false;
		}

		return $noo_upload_dir;
	}
endif;

/**
 * This function is original from Visual Composer. Redeclare it here so that it could be used for site without VC.
 */
if ( !function_exists('noo_handler_shortcode_content') ):
	function noo_handler_shortcode_content( $content, $autop = false ) {
		if ( $autop ) {
			$content = wpautop( preg_replace( '/<\/?p\>/', "\n", $content ) . "\n" );
		}
		return do_shortcode( shortcode_unautop( $content) );
	}
endif;

if ( ! function_exists( '_wp_render_title_tag' ) ) {
	function noo_theme_slug_render_title() {
?>
<title><?php wp_title( '|', true, 'right' ); ?></title>
<?php
	}
	add_action( 'wp_head', 'noo_theme_slug_render_title' );
}

if (!function_exists('noo_mail')) :
	function noo_mail( $to = '', $subject = '', $body = '', $headers = '', $key = '', $attachments = '' ) {

		if( empty( $headers ) ) {
			$headers = array();
			$from_name = jm_et_get_setting( 'from_name', '' );
			$from_email = jm_et_get_setting( 'from_email', '' );

			if( empty( $from_name ) ) {
				if ( is_multisite() )
					$from_name = $GLOBALS['current_site']->site_name;
				else
					$from_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			}

			if( !empty( $from_name ) && !empty( $from_email ) ) {
				$headers[] = 'From: ' . $from_name . ' <' . strtolower( $from_email ) . '>';
			}
		}

		$headers   = apply_filters( $key . '_header', apply_filters( 'noo_mail_header', $headers ) );

		if( !empty( $key ) ) {
			$subject = apply_filters( $key . '_subject', apply_filters( 'noo_mail_subject', $subject ) );
			$body = apply_filters( $key . '_body', apply_filters( 'noo_mail_body', $body ) );
		}

		// RTL HTML email
		if( is_rtl() ) {
			$body = '<div dir="rtl">' . $body . '</div>';
		}
		
		add_filter( 'wp_mail_content_type', 'noo_mail_set_html_content' );

		$result = wp_mail( $to, $subject, $body, $headers, $attachments );

		// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
		remove_filter( 'wp_mail_content_type', 'noo_mail_set_html_content' );

		return $result;
	}
endif;

if (!function_exists('noo_mail_set_html_content')) :
	function noo_mail_set_html_content() {
		return 'text/html';
	}
endif;

if (!function_exists('noo_mail_do_not_reply')) :
	function noo_mail_do_not_reply(){
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) === 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		return apply_filters( 'noo_mail_do_not_reply', 'noreply@' . $sitename );
	}
endif;

/* -------------------------------------------------------
 * Create functions noo_set_post_views
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_set_post_views' ) ) :
	
	function noo_set_post_views( $id ) {

		$key_meta = '_noo_views_count';
		// echo($id); die;
		$count = noo_get_post_meta( $id, $key_meta );
		// echo $count; die;
		if ( $count == '' ) :
			$count = 1;
		else :
			$count ++;
		endif;
		update_post_meta( $id, $key_meta, $count );
		// return $content;

	}

	// add_action( 'the_content', 'noo_set_post_views' );

endif;

/** ====== END noo_set_post_views ====== **/

/* -------------------------------------------------------
 * Create functions noo_get_post_views
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_get_post_views' ) ) :
	
	function noo_get_post_views( $id ) {
		$key_meta = '_noo_views_count';
		$count = noo_get_post_meta( $id, $key_meta );
		if ( $count == '' ) :
			delete_post_meta( $id, $key_meta );
	        add_post_meta( $id, $key_meta, '0' );
	        return 0;
		endif;
		return $count;
	}

endif;

/** ====== END noo_get_post_views ====== **/

/* -------------------------------------------------------
 * Create functions track_post_views
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_track_post_views' ) ) :
	
	function noo_track_post_views( $post_id = '' ) {
		
		if ( !is_single() ) return;

    	if ( empty ( $post_id) ) {
	        global $post;
	        $post_id = $post->ID;
	    }
	    if( get_post_status( $post_id ) !== 'publish' ) {
	    	return;
	    }

	    if ( is_singular( 'noo_job' ) ) $name_cookie = 'noo_jobs_' . $post_id;
	    if ( is_singular( 'noo_resume' ) ) $name_cookie = 'noo_resume_' . $post_id;
	    if ( is_singular( 'noo_company' ) ) $name_cookie = 'noo_company_' . $post_id;
	    if ( isset( $name_cookie ) ) {
		    if ( !isset ( $_COOKIE[$name_cookie] ) ) {
		    	noo_set_post_views($post_id);
		    }
		    setcookie( $name_cookie, $post_id, time() + (86400 * 3), "/");
		}
	}

	add_action( 'wp_head', 'noo_track_post_views');

endif;

/** ====== END track_post_views ====== **/

/* -------------------------------------------------------
 * Create functions noo_get_job_applications_count
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_get_job_applications_count' ) ) :
	
	function noo_get_job_applications_count( $job_id ) {
		$key_meta = '_noo_job_applications_count';
		$count = noo_get_post_meta( $job_id, $key_meta );
		if ( $count === '' || $count === null ) :
			global $wpdb;
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'noo_application' AND post_parent = {$job_id}" );
			update_post_meta( $job_id, $key_meta, absint( $count ) );
	        return $count;
		endif;

		return $count;
	}

endif;

/** ====== END noo_get_job_applications_count ====== **/

/* -------------------------------------------------------
 * Create functions track_applications_post
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_track_applications_post' ) ) :
	
	function noo_track_applications_post( $post_id = '', $post = null, $update = true ) {
		
		if ( $update || 'noo_application' !== $post->post_type ) {
			return;
		}

	    $job_id = $post->post_parent;
	    if( !empty( $job_id ) ) {
			global $wpdb;
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'noo_application' AND post_parent = {$job_id}" );
			update_post_meta( $job_id, '_noo_job_applications_count', absint( $count ) );
	    }
	}

	add_action( 'wp_insert_post', 'noo_track_applications_post', 10, 3 );

endif;

/** ====== END track_applications_post ====== **/

/* -------------------------------------------------------
 * Create functions noo_caroufredsel_slider
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_caroufredsel_slider' ) ) :
	
	// function noo_caroufredsel_slider( $r, $id, $show = 'company', $max = 6 ) {
	function noo_caroufredsel_slider( $r, $options = array() ) {
		// Default config options.
			$defaults = array(
				'id'                => uniqid() . '_show_slider',
				'show'              => 'company',
				'style'             => 1,
				'min'               => 1,
				'max'               => 6,
				'autoplay'          => 'false',
				'slider_speed'      => '800',
				'width'             => 180,
				'height'            => 'variable',
				'hidden_pagination' => 'false',
				'owl' => ''
			);

			$options = wp_parse_args( $options, $defaults );

			if( $options['show'] == 'testimonial' ) {
				$options['width'] = 767;
			}
		// -- Check query
		if( $r->have_posts() ):

			if($options['owl'] == 'yes'){
				wp_enqueue_script( 'vendor-carousel' );
			} else{
				wp_enqueue_script( 'vendor-carouFredSel' );
			}

			echo '
			<div class="featured_slider">
				<div id="slider_' . $options['id'] . '">';
				if( $options['style'] == 1 ) :
		 			while ( $r->have_posts() ): $r->the_post(); global $post;
		 				if ( $options['show'] == 'company' ) :
							$logo_company = Noo_Company::get_company_logo( $post->ID );

			 				echo "<div class='bg_images'><a href='" . get_permalink( $post->ID ) . "' target='_blank'>{$logo_company}</a></div>";
			 			elseif ( $options['show'] == 'testimonial' ) :
			 				$name     = get_post_meta(get_the_ID(),'_noo_wp_post_name', true);
							$position = get_post_meta(get_the_ID(),'_noo_wp_post_position', true);
							$url = get_post_meta(get_the_ID(),'_noo_wp_post_image', true);
							?>
								<div class="box_testimonial">
									<div class="box-content">
										<?php the_content(); ?>
									</div>
									<div class="icon"></div>
									<div class="box-info">
										<div class="box-info-image">
											<img src="<?php echo wp_get_attachment_url(esc_attr($url)); ?>" alt="<?php the_title(); ?>" />
										</div>
										<div class="box-info-entry">
											<h4><?php echo $name; ?></h4>
											<h5><?php echo $position ?></h5>
										</div>
									</div>
								</div>
							<?php
			 			endif;

		 			endwhile;
		 		elseif ( $options['style'] == 2 ) :
					while ( $r->have_posts() ): $r->the_post(); global $post;
						if ( $options['show'] == 'testimonial' ) :
			 				$name     = get_post_meta(get_the_ID(),'_noo_wp_post_name', true);
							$position = get_post_meta(get_the_ID(),'_noo_wp_post_position', true);
							$url = get_post_meta(get_the_ID(),'_noo_wp_post_image', true);
							?>
								<div class="box_testimonial_single">
									<div class="box-info">
										<div class="box-info-image">
											<img src="<?php echo wp_get_attachment_url(esc_attr($url)); ?>" alt="<?php the_title(); ?>" />
										</div>
										<div class="box-info-entry">
											<h4><?php echo $name; ?></h4>
											<h5><?php echo $position ?></h5>
										</div>
									</div>
									<div class="box-content">
										<?php the_content(); ?>
									</div>
								</div>
							<?php
			 			endif;
					endwhile;
		 		endif;
				wp_reset_query();
		 	$pagination = ( $options['hidden_pagination'] == 'true' ? '' : 'pagination: { container : ".pag_slider_' . $options['id'] . '", keys	: true }' );
	 		echo '</div>
	 			<div class="clearfix"></div>
				<div class="page pag_slider_' . $options['id'] . '"></div>
	 		</div>';
			if($options['owl'] == 'yes'):
				echo '<script type="text/javascript">
				jQuery(\'document\').ready(function ($) {
					$("#slider_' . $options['id'] . '").each(function() {
						var $this = $(this);
						imagesLoaded($this, function() {
							$this.owlCarousel({
								items : 5,
								itemsDesktopSmall : [900,3], // betweem 900px and 601px
								itemsTablet: [600,2],
								autoPlay 		: ' . $options['autoplay'] .',
								slideSpeed : ' . $options['slider_speed'] .'
							});
						});
					});
				});
				</script>';
			else:
				echo '<script type="text/javascript">
				jQuery(\'document\').ready(function ($) {
					$("#slider_' . $options['id'] . '").each(function() {
						var $this = $(this);
						imagesLoaded($this, function() {
							$this.carouFredSel({
								responsive	: true,
								auto 		: ' . $options['autoplay'] .',
								items		: {
									width		: ' . $options['width'] .',
									height		: "' . $options['height'] .'",
									visible		: {
										min			: ' . $options['min'] .',
										max			: ' . $options['max'] .'
									}
								},
								scroll : {
									duration        : ' . $options['slider_speed'] . ',
									pauseOnHover    : true
								},
								' . $pagination . '
							});
						});
					});
				});
				</script>';
			endif;
		endif;

	}

endif;

/** ====== END noo_caroufredsel_slider ====== **/

/* -------------------------------------------------------
 * Create functions check_view_application
 * ------------------------------------------------------- */

if ( ! function_exists( 'check_view_applied' ) ) :
	
	function check_view_applied() {
		if ( Noo_Member::is_employer() ) :
			// -- get id candidate
				$user = wp_get_current_user();
			// -- default meta
				$key_meta = '_check_view_applied';
			// get value in meta -> array
				$check_view = get_user_meta( $user, $key_meta, true ) ? (array) get_user_meta( $user, $key_meta, true ) : array();
				
				$id_applications = array($_POST['application_id']);
				$arr_value = array_merge($check_view, $id_applications);
				
				if ( !in_array ( $_POST['application_id'], $check_view ) ):
					update_user_meta( $user, $key_meta, $arr_value);
				endif;
				
		endif;

	}
	add_action( 'wp_ajax_nopriv_check_view_applied', 'check_view_applied' );
	add_action( 'wp_ajax_check_view_applied', 'check_view_applied' );
endif;

/** ====== END check_view_application ====== **/

/* -------------------------------------------------------
 * Create functions unseen_applications_number
 * ------------------------------------------------------- */

if ( ! function_exists( 'unseen_applications_number' ) ) :
	
	function unseen_applications_number( $html = true ) {
		global $wpdb;
		$count_view = 0;

		if ( Noo_Member::is_employer() ) {
			$count_view = noo_employer_unseen_application_count();
		} elseif( Noo_Member::is_candidate() ) {
			$user = wp_get_current_user();
			$total_applied = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} 
				INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
				WHERE post_type = 'noo_application' AND (post_status = 'publish' OR post_status = 'rejected')
					AND {$wpdb->postmeta}.meta_key = '_candidate_email'
					AND {$wpdb->postmeta}.meta_value = '{$user->user_email}'" );
			$view_applications = count(get_user_meta( $user->ID, '_check_view_applied', true ));

			$count_view = $total_applied - $view_applications;
		}

		$count_view = apply_filters( 'noo-unseen-applications-number', $count_view );

		if ( $count_view > 0 ) {
			return $html ? '<span class="badge">' . $count_view .'</span>' : absint( $count_view );
		} else {
			return $html ? '' : 0;
		}
	}

endif;

//Process Unseen Application

function noo_employer_unseen_application_count(){

	$key_meta = '_noo_applications_unseen_count';
	$user_id = get_current_user_id();
	$count = get_user_meta( $user_id,$key_meta, true );
	if ( $count === '' || $count === null ) :
		$pending_applications = noo_employer_unseen_application_updating_count();
		return $pending_applications;
	endif;
	return $count;
}

function noo_employer_unseen_application_updating_count(){
	global $wpdb;
	$key_meta = '_noo_applications_unseen_count';
	$user_id = get_current_user_id();
	$job_ids = get_posts(array(
		'post_type'=>'noo_job',
		'post_status'=>array('publish','expired','inactive'),
		'author'=>get_current_user_id(),
		'posts_per_page'=>-1,
		'fields' => 'ids',
		'suppress_filters' => false
	));
	$pending_applications = 0;
	if( !empty( $job_ids ) ) {
		$job_ids = array_merge($job_ids, array(0));
		$job_ids_where = implode( ', ', $job_ids );
		$pending_applications = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'noo_application' AND post_parent IN ( {$job_ids_where} ) AND post_status = 'pending'" );
	}
	update_user_meta( $user_id, $key_meta, absint( $pending_applications ) );
	return $pending_applications;
}

add_action( 'transition_post_status', 'noo_employer_unseen_application_updating_count');



/** ====== END unseen_applications_number ====== **/

/* -------------------------------------------------------
 * Create functions user_notifications_number
 * ------------------------------------------------------- */

if ( ! function_exists( 'user_notifications_number' ) ) :
	
	function user_notifications_number( $html = true ) {
		$count_view = unseen_applications_number( false );
		$count_view = apply_filters( 'noo-user-notifications-number', $count_view );

		if ( $count_view > 0 ) {
			return $html ? '<span class="badge">' . $count_view .'</span>' : $count_view;
		} else {
			return $html ? '' : 0;
		}
	}

endif;

/** ====== END user_notifications_number ====== **/


/* -------------------------------------------------------
 * Create functions noo_auto_create_order_free_package
 * ------------------------------------------------------- */

if ( ! function_exists( 'noo_auto_create_order_free_package' ) ) :
	
	function noo_auto_create_order_free_package() {
		check_ajax_referer('noo-free-package','security');

		if( !is_user_logged_in() ) {
			wp_die();
		}

		$product_id = absint( $_POST['package_id'] );
		$user_id = absint( $_POST['user_id'] );
		$login_user_id = get_current_user_id();
		if( $user_id != $login_user_id ) {
			wp_die();
		}

		$user_info = get_userdata($user_id);
		$new_order_ID = create_new_order( $user_info->display_name, $user_id );
		$order = new WC_Order( $new_order_ID );

		if( $user_id != $order->customer_user ) {
			wp_die();
		}

		$order->update_status( 'completed' );

		$product = wc_get_product( $product_id  );

		if ($product && $product->get_price() == 0 && is_user_logged_in() ) {

			if( $product->is_type( 'job_package' ) ) {
				$package_interval = absint($product->get_package_interval());
				$package_interval_unit = $product->get_package_interval_unit();

				$package_data = array(
					'product_id'   => $product->id,
					'created'      => current_time('mysql'),
					'package_interval' => $package_interval,
					'package_interval_unit' => $package_interval_unit,
					'job_duration' => absint($product->get_job_display_duration()),
					'job_limit'    => absint($product->get_post_job_limit()),
					'job_featured' => absint($product->get_job_feature_limit())
				);

				$package_data = apply_filters( 'jm_job_package_user_data', $package_data, $product );

				if( !empty( $package_interval ) ) {
					$expired = strtotime( "+{$package_interval} {$package_interval_unit}" );
					$package_data['expired'] = $expired;
					Noo_Job_Package::set_expired_package_schedule( $user_id, $package_data );
				}

				update_user_meta( $user_id, '_free_package_bought', 1 );
				update_user_meta( $user_id, '_job_package', $package_data );
				update_user_meta( $user_id, '_job_added', 0 );
				update_user_meta( $user_id, '_job_featured', 0 );

				do_action( 'jm_job_package_order_completed', $product, $user_id );
			} elseif( $product->is_type( 'resume_package' ) ) {
				$package_interval = absint($product->get_package_interval());
				$package_interval_unit = $product->get_package_interval_unit();

				$package_data = array(
					'product_id'   => $product->id,
					'created'      => current_time('mysql'),
					'package_interval' => $package_interval,
					'package_interval_unit' => $package_interval_unit,
					'resume_limit'    => absint($product->get_post_resume_limit()),
				);

				$package_data = apply_filters( 'jm_resume_package_user_data', $package_data, $product );

				if( !empty( $package_interval ) ) {
					$expired = strtotime( "+{$package_interval} {$package_interval_unit}" );
					$package_data['expired'] = $expired;
					Noo_Resume_Package::set_expired_package_schedule( $user_id, $package_data );
				}

				update_user_meta( $user_id, '_free_resume_package_bought', 1 );
				update_user_meta( $user_id, '_resume_package', $package_data );
				update_user_meta( $user_id, '_resume_added', 0 );

				do_action( 'jm_resume_package_order_completed', $product, $user_id );
			}
		}

		wp_die();
	}

	add_action( 'wp_ajax_auto_create_order', 'noo_auto_create_order_free_package' );

endif;

/** ====== END noo_auto_create_order_free_package ====== **/

 /**
  * Get Location Long Lat from Addresss.
  */
if(!function_exists('noo_address_to_lng_lat')):
  function noo_address_to_lng_lat($address){

	  $geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false');
	  $geo = json_decode($geo, true);

	  if ($geo['status'] == 'OK') {
		  $location['lat'] = $geo['results'][0]['geometry']['location']['lat'];
		  $location['long'] = $geo['results'][0]['geometry']['location']['lng'];
		  return $location;
	  }
	  return '';
	  
  }
endif;


if (!function_exists('noo_wp_editor')):
	function noo_wp_editor($content, $editor_id, $editor_name = '')
	{
		$configs = array(
			'editor_class' => 'noo-editor',
			'media_buttons' => false
		);
		if (!empty($editor_name)){
			$configs['textarea_name'] = $editor_name;
		}

		$configs = apply_filters('noo_editor_config', $configs);
		return wp_editor($content, $editor_id, $configs);
	}
endif;

if (!function_exists('noo_form_nonce')):
	function noo_form_nonce($action)
	{
		$nonce = wp_create_nonce($action);
		echo '<input type="hidden" id="_wpnonce" name="_wpnonce" value="'.$nonce.'">';
	}
endif;


function noo_company_job_count($company_id){
	$key_meta = '_noo_job_count';
	$count = noo_get_post_meta( $company_id,$key_meta, '' );
	if ( empty($count) && !is_numeric( $count ) ) {
		$count = Noo_Company::count_jobs( $company_id);
		update_post_meta( $company_id, $key_meta, $count );
	}
	return $count;
}

function noo_update_company_job_count( $new_status, $old_status, $post ){
	$company_id = '';
	if( $post->post_type == 'noo_job' ) {
		$company_id = jm_get_job_company( $post->ID );
	} elseif( $post->post_type == 'noo_company' ) {
		$company_id = $post->ID;
	}
	if(!empty($company_id)){
		$key_meta = '_noo_job_count';
		$count	= Noo_Company::count_jobs( $company_id );
		update_post_meta( $company_id, $key_meta, $count );
	}
}

add_action( 'transition_post_status', 'noo_update_company_job_count', 10, 3);