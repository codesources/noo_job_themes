<?php
/*
 * RTL Support
 */

// -- Checking enable RTL Support

if ( is_rtl() ) :

	/* -------------------------------------------------------
	 * Create functions noo_set_derection
	 * ------------------------------------------------------- */
	
	if ( ! function_exists( 'noo_set_derection' ) ) :
		
		function noo_set_derection() {
	
			global $wp_locale, $wp_styles;
			$direction = 'rtl';
			$wp_locale->text_direction = $direction;
			if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
				$wp_styles = new WP_Styles();
			}
			$wp_styles->text_direction = $direction;
	
		}
	
	endif;

	// add_action( 'init', 'noo_set_derection');
	
	/** ====== END noo_set_derection ====== **/

	/* -------------------------------------------------------
	 * Create functions noo_rtl_body
	 * ------------------------------------------------------- */

	if ( ! function_exists( 'noo_rtl_body' ) ) :
		
		function noo_rtl_body( $classes ) {

			$classes[] = 'noo-rtl';
	        return $classes;

		}

		add_filter( 'body_class', 'noo_rtl_body' );

	endif;

	/** ====== END noo_rtl_body ====== **/

endif;