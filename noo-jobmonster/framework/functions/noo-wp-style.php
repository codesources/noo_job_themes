<?php
/**
 * WP Element Functions.
 * This file contains functions related to Wordpress base elements.
 * It mostly contains functions for improving trivial issue on Wordpress.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */


// Remove Recent Comments Style
// --------------------------------------------------------

if ( ! function_exists( 'remove_wp_widget_recent_comments_style' ) ) :
	function remove_wp_widget_recent_comments_style() {
		global $wp_widget_factory;
		remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
	}
	add_filter( 'wp_head', 'remove_wp_widget_recent_comments_style', 1 );
endif;


// Excerpt Length
// --------------------------------------------------------

if ( ! function_exists( 'noo_excerpt_length' ) ) :
	function noo_excerpt_length( $length ) {
		$excerpt_length = noo_get_option('noo_blog_excerpt_length', 60);

		return (empty($excerpt_length) ? 60 : $excerpt_length); 
	}
	add_filter( 'excerpt_length', 'noo_excerpt_length' );
endif;


if(!function_exists('noo_the_excerpt')){
	function noo_the_excerpt($excerpt=''){
		return str_replace('&nbsp;', '', $excerpt);
	}
	add_filter('the_excerpt', 'noo_the_excerpt');
}


// Excerpt Read More
// --------------------------------------------------------

if ( ! function_exists( 'noo_excerpt_read_more' ) ) :
	function noo_excerpt_read_more( $more ) {

		return '...<div>' . noo_get_readmore_link() . '</div>';
	}

	add_filter( 'excerpt_more', 'noo_excerpt_read_more' );
endif;


// Content Read More
// --------------------------------------------------------

if ( ! function_exists( 'noo_content_read_more' ) ) :
	function noo_content_read_more( $more ) {

		return noo_get_readmore_link();
	}

	add_filter( 'the_content_more_link', 'noo_content_read_more' );
endif;

// Navbar post button
if( !function_exists('noo_navbar_btn') ) :
	function noo_navbar_btn ( $items, $args ) {
		if( !noo_get_option('noo_header_nav_post_btn', true) ) return $items;
		if ($args->theme_location == 'primary') {
			$default_button = noo_get_option('noo_header_nav_post_btn_type', 'post_job');
			if( Noo_Member::is_employer() || ( !Noo_Member::is_candidate() && $default_button == 'post_job' ) || !jm_resume_enabled() ) {
				$link = Noo_Member::get_post_job_url();
				$items .= apply_filters('noo_post_job_btn', '<li id="nav-menu-item-post-btn" class="menu-item-post-btn"><a href="' . $link . '">' . __('Post a Job', 'noo') . '</a></li>' );
			} else {
				$link = Noo_Member::get_post_resume_url();
	        	$items .= apply_filters('noo_post_resume_btn', '<li id="nav-menu-item-post-btn" class="menu-item-post-btn"><a href="' . $link . '">' . __('Post a Resume', 'noo') . '</a></li>' );
			}
	    }

	    return $items;
	}
	add_filter( 'wp_nav_menu_items', 'noo_navbar_btn', 11, 2 );
endif;