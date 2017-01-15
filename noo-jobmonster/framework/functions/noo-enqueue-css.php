<?php
/**
 * NOO Framework Site Package.
 *
 * Register Style
 * This file register & enqueue style used in NOO Themes.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */
// =============================================================================

if ( ! function_exists( 'noo_enqueue_site_style' ) ) :
	function noo_enqueue_site_style() {

		if ( ! is_admin() ) {

			// URI variables.
			$get_stylesheet_directory_uri = get_stylesheet_directory_uri();
			$get_template_directory_uri   = get_template_directory_uri();

			// Main style
			wp_register_style( 'noo-style', $get_stylesheet_directory_uri . '/style.css', NULL, NULL, 'all' );
			$main_css = 'noo';
			if( noo_get_option( 'noo_site_skin', 'light' ) == 'dark' ) {
				$main_css .= '-dark';
			}
			wp_register_style( 'noo-main-style', NOO_ASSETS_URI . "/css/{$main_css}.css", NULL, NULL, 'all' );
			if( is_file( noo_upload_dir() . '/custom.css' ) ) {
				wp_register_style( 'noo-custom-style', noo_upload_url() . '/custom.css', NULL, NULL, 'all' );
			}
			wp_enqueue_style( 'noo-main-style' );
			
			//woocommerce
			if(NOO_WOOCOMMERCE_EXIST)
				wp_enqueue_style('noo-woocommerce',NOO_ASSETS_URI."/css/woocommerce.css",null,null,'all');

			if( ! noo_get_option('noo_use_inline_css', false) && wp_style_is( 'noo-custom-style', 'registered' ) ) {
				global $wp_customize;
				if ( !isset( $wp_customize ) ) {
					wp_enqueue_style( 'noo-custom-style' );
				}
			}

			wp_enqueue_style( 'noo-style' ); // place style.css here so that child theme can use custom css inside it.
			
			// Vendors
			// Font Awesome
			wp_register_style( 'vendor-font-awesome-css', NOO_FRAMEWORK_URI . '/vendor/fontawesome/css/font-awesome.min.css',array(),'4.2.0');
			wp_enqueue_style( 'vendor-font-awesome-css' );
			wp_register_style( 'vendor-nivo-lightbox-css', NOO_FRAMEWORK_URI . '/vendor/nivo-lightbox/nivo-lightbox.css', array( ), null );
			wp_register_style( 'vendor-nivo-lightbox-default-css', NOO_FRAMEWORK_URI . '/vendor/nivo-lightbox/themes/default/default.css', array( 'vendor-nivo-lightbox-css' ), null );

			// Carousel Slider
			wp_enqueue_style( 'carousel', NOO_ASSETS_URI . '/css/owl.carousel.css');
			wp_enqueue_style( 'carousel-theme', NOO_ASSETS_URI . '/css/owl.theme.css');

			// Enqueue Fonts.
			$default_font         = noo_default_font_family();

			$protocol             = is_ssl() ? 'https' : 'http';

			$body_font_family     = noo_default_font_family();
			$headings_font_family = noo_default_headings_font_family();
			$nav_font_family      = noo_default_nav_font_family();
			$logo_font_family     = noo_default_logo_font_family();

			$body_font_subset     = '';
			$headings_font_subset = '';
			$nav_font_subset      = '';
			$logo_font_subset     = '';

			$font_in_used		  = array();

			$typo_use_custom_font = noo_get_option( 'noo_typo_use_custom_fonts', false );
			if( $typo_use_custom_font ) {
				$body_font_family		= noo_get_option( 'noo_typo_body_font', '' );
				$body_font_subset		= noo_get_option( 'noo_typo_body_font_subset', 'latin' );

				$headings_font_family   = noo_get_option( 'noo_typo_headings_font', '' );
				$headings_font_subset   = noo_get_option( 'noo_typo_headings_font_subset', 'latin' );
			}
			
			$nav_custom_font        = noo_get_option( 'noo_header_custom_nav_font', false );
			if( $nav_custom_font ) {
				$nav_font_family    = noo_get_option( 'noo_header_nav_font', '' );
				$nav_font_subset    = noo_get_option( 'noo_header_nav_font_subset', 'latin' );
			}

			$use_image_logo         = noo_get_option( 'noo_header_use_image_logo', false );
			if( ! $use_image_logo ) {
				$logo_font_family   = noo_get_option( 'noo_header_logo_font', '' );
				$logo_font_subset   = noo_get_option( 'noo_header_logo_font_subset', 'latin' );
			}

			if ( ! empty( $body_font_family ) ) {
				$font_in_used[]	 = $body_font_family;

				$font      = str_replace( ' ', '+', $body_font_family ) . ':100,300,400,700,900,300italic,400italic,700italic,900italic';
				$subset    = !empty( $body_font_subset ) ? '&subset=' . $body_font_subset : '';

				wp_enqueue_style( 'noo-google-fonts-body', "{$protocol}://fonts.googleapis.com/css?family={$font}{$subset}", false, null, 'all' );
			}

			if ( ! empty( $headings_font_family ) && !in_array($headings_font_family, $font_in_used) ) {
				$font_in_used[]	 = $headings_font_family;

				$font      = str_replace( ' ', '+', $headings_font_family ) . ':100,300,400,700,900,300italic,400italic,700italic,900italic';
				$subset    = !empty( $headings_font_subset ) ? '&subset=' . $headings_font_subset : '';

				wp_enqueue_style( 'noo-google-fonts-headings', "{$protocol}://fonts.googleapis.com/css?family={$font}{$subset}", false, null, 'all' );
			}

			if ( ! empty( $nav_font_family ) && !in_array($nav_font_family, $font_in_used) ) {
				$font_in_used[]	 = $nav_font_family;

				$font      = str_replace( ' ', '+', $nav_font_family ) . ':100,300,400,700,900,300italic,400italic,700italic,900italic';
				$subset    = !empty( $nav_font_subset ) ? '&subset=' . $nav_font_subset : '';

				wp_enqueue_style( 'noo-google-fonts-nav', "{$protocol}://fonts.googleapis.com/css?family={$font}{$subset}", false, null, 'all' );
			}

			if ( !empty( $logo_font_family ) && !in_array($logo_font_family, $font_in_used) ) {
				// $font_in_used[]	 = $logo_font_family;

				$font      = str_replace( ' ', '+', $logo_font_family ) . ':100,300,400,700,900,300italic,400italic,700italic,900italic';
				$subset    = !empty( $logo_font_subset ) ? '&subset=' . $logo_font_subset : '';

				wp_enqueue_style( 'noo-google-fonts-logo', "{$protocol}://fonts.googleapis.com/css?family={$font}{$subset}", false, null, 'all' );
			}

			//
			// Unused style
			//
			// De-register Contact Form 7 Styles
			if ( class_exists( 'WPCF7_ContactForm' ) ) :
			    wp_deregister_style( 'contact-form-7' );
			endif;
		}
		/*
		 * RTL Support
		 */

		// -- Checking enable RTL Support

			if ( is_rtl() ) :

			   	wp_enqueue_style( 'style-name', NOO_ASSETS_URI .'/css/rtl.css' );

			endif;
	}
add_action( 'wp_enqueue_scripts', 'noo_enqueue_site_style' );
endif;
