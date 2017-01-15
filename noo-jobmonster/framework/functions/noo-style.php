<?php
/**
 * Style Functions for NOO Framework.
 * This file contains functions for calculating style (normally it's css class) base on settings from admin side.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if (!function_exists('noo_body_class')):
	function noo_body_class($output) {
		global $wp_customize;
		if (isset($wp_customize)) {
			$output[] = 'is-customize-preview';
		}

		if( isset($_REQUEST['interim-login']) ) {
			$output[] = 'interim-login';
		}

		$page_layout = get_page_layout();
		if ($page_layout == 'fullwidth') {
			$output[] = ' page-fullwidth';
		} elseif ($page_layout == 'left_sidebar') {
			$output[] = ' page-left-sidebar';
		} else {
			$output[] = ' page-right-sidebar';
		}
		
		switch (noo_get_option('noo_site_layout', 'fullwidth')) {
			case 'boxed':
				// if(get_page_template_slug() != 'page-full-width.php')
				$output[] = 'boxed-layout';
			break;
			default:
				$output[] = 'full-width-layout';
			break;
		}

		// SmoothScroll
		if( noo_get_option( 'noo_smooth_scrolling', false ) ) {
			$output[] = 'enable-nice-scroll';
		}

		// Fixed left and/or right Navigation
		$navbar_position = noo_get_option('noo_header_nav_position', 'fixed_top');
		if ($navbar_position == 'fixed_left') {
			$output[] = 'navbar-fixed-left-layout';
		} elseif ($navbar_position == 'fixed_right') {
			$output[] = 'navbar-fixed-right-layout';
		}

		if( is_one_page_enabled() ) {
			$output[] = 'one-page-layout';
		}

		if( is_singular('portfolio_project') || is_singular( 'product' ) || is_single() || is_page() ) {
			$meta_body_class = noo_get_post_meta(get_the_ID(), '_noo_body_css', '');
			if( !empty( $meta_body_class ) )
				$output[] = esc_attr($meta_body_class);
		}
		
		if(is_page() && get_the_ID() == Noo_Member::get_member_page_id()){
			$output[] =' page-member';
		}
		
		if(noo_get_post_meta(get_the_ID(),'_noo_wp_page_menu_transparent')){
			$output[] =' page-menu-transparent';
		}
		
		return $output;
	}
endif;
add_filter('body_class', 'noo_body_class');

if (!function_exists('noo_site_class')):
	function noo_site_class( $class = 'site' ) {
		echo apply_filters( 'noo_site_class', $class );
	}
endif;

if (!function_exists('noo_header_class')):
	function noo_header_class() {
		$class = '';

		$navbar_position = noo_get_option('noo_header_nav_position', 'fixed_top');
		if ($navbar_position == 'fixed_top') {
			$floating = noo_get_option( 'noo_header_nav_floating', false );
			if( $floating ) {
				if( noo_get_option( 'noo_header_nav_floating_bg_color', 'transparent' ) == 'transparent'
					&& noo_get_option( 'noo_header_nav_floating_offset_top', '0' ) == '0' )
				$class .= ' has-border';
			}
		}

		echo $class;
	}
endif;

if (!function_exists('noo_navbar_class')):
	function noo_navbar_class() {
		$class = '';

		$navbar_position = noo_get_option('noo_header_nav_position', 'fixed_top');
		if ($navbar_position == 'static_top') {
			$class .= ' navbar-static-top';
		} elseif ($navbar_position == 'fixed_left' || $navbar_position == 'fixed_right') {
			// noo_header_side_nav_alignment
			if ($navbar_position == 'fixed_left') {
				$class .= ' navbar-fixed-left';
			} else {
				$class .= ' navbar-fixed-right';
			}

			$alignment = noo_get_option( 'noo_header_side_nav_alignment', 'center' );
			$class .= ( $alignment != '' ) ? ' align-' . $alignment : '';
		} elseif ($navbar_position == 'fixed_right') {
			$class = ' navbar-fixed-right';
		} else {
			$class = ' fixed-top';
			$shrinkable = noo_get_option( 'noo_header_nav_shrinkable', true );
			if( $shrinkable ) {
				$class .= ' shrinkable';
			}

			$smart_scroll = noo_get_option( 'noo_header_nav_smart_scroll', false );
			if( $smart_scroll ) {
				$class .= ' smart_scroll';
			}

			$floating = noo_get_option( 'noo_header_nav_floating', false );
			if( $floating ) {
				if( noo_get_option( 'noo_header_nav_floating_bg_color', 'transparent' ) == 'transparent' )
				$class .= ' bg-transparent';
			}
		}

		echo $class;
	}
endif;

if (!function_exists('noo_main_class')):
	function noo_main_class() {
		$class = 'noo-main';
		$page_layout = get_page_layout();
		if ($page_layout == 'fullwidth') {
			$class.= ' col-md-12';
		} elseif ($page_layout == 'left_sidebar' || $page_layout == 'left_company') {
			$class.= ' col-md-8 left-sidebar';
		} else {
			$class.= ' col-md-8';
		}
		
		echo $class;
	}
endif;

if(!function_exists('noo_container_class')){
	function noo_container_class(){
		echo 'container-boxed max offset';
	}
}

if (!function_exists('noo_sidebar_class')):
	function noo_sidebar_class() {
		$class = ' noo-sidebar col-md-4';
		$page_layout = get_page_layout();
		
		if ( $page_layout == 'left_sidebar' || $page_layout == 'left_company' ) {
			$class .= ' noo-sidebar-left pull-left';
		}
		
		echo $class;
	}
endif;

if (!function_exists('noo_blog_class')):
	function noo_blog_class() {
		$class = ' post-area';
		$blog_style = noo_get_option('noo_blog_style', 'standard');
		
		$class.= ' standard-blog';
		
		echo $class;
	}
endif;

if (!function_exists('noo_page_class')):
	function noo_page_class() {
		$class = ' noo-page';
		
		echo $class;
	}
endif;

if (!function_exists('noo_post_class')):
	function noo_post_class($output) {
		if (has_featured_content()) {
			$output[] = 'has-featured';
		} else {
			$output[] = 'no-featured';
		}

		if(!is_single()) {

		}

		$post_id = get_the_id();
		$post_type = get_post_type($post_id);

		$post_format = noo_get_post_format($post_id, $post_type);

		if( noo_get_option('noo_blog_post_author_bio', true) || noo_get_option("noo_blog_social", true ) ) {
			$output[] = 'has-left-col';
		}
		
		return $output;
	}
	
	add_filter('post_class', 'noo_post_class');
endif;
