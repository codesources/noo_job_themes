<?php
/**
 * Add/Remove user fields for NOO Framework.
 *
 * @package    NOO Framework
 * @subpackage NOO Function
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */
// =============================================================================

if ( ! function_exists( 'noo_author_profile_fields' ) ) :
	function noo_author_profile_fields ( $contactmethods ) {
		
		$contactmethods['noo_facebook']  = __( 'Facebook Profile', 'noo');
		$contactmethods['noo_twitter']   = __( 'Twitter Profile', 'noo');
		$contactmethods['noo_google']    = __( 'Google Profile', 'noo');
		$contactmethods['noo_linkedin']  = __( 'LinkedIn Profile', 'noo');
		$contactmethods['noo_pinterest'] = __( 'Pinterest Profile', 'noo');
		
		return $contactmethods;
	}
	add_filter( 'user_contactmethods', 'noo_author_profile_fields', 10, 1);
endif;

/* -------------------------------------------------------
 * Create functions create_new_order
 * ------------------------------------------------------- */

if ( ! function_exists( 'create_new_order' ) ) :
	
	function create_new_order( $title = '', $user_id = '' ) {
		if( empty( $title ) || empty( $user_id ) ) {
			return 0;
		}

		$order = array(
			'post_title'  => $title,
			'post_type'   => 'shop_order',
			'post_status' => 'wc-completed',
			'post_author' => $user_id
		);
		$order_ID = wp_insert_post( $order );
		update_post_meta( $order_ID, '_customer_user', $user_id );

		if( !$order_ID ) {
			return 0;
		}

		return $order_ID;
	}

endif;

/** ====== END create_new_order ====== **/

//Allow html in user description

remove_filter('pre_user_description', 'wp_filter_kses');
add_filter( 'pre_user_description', 'wp_filter_post_kses');