<?php
if ( ! function_exists( 'noo_save_field' ) ) :
	function noo_save_field( $id = 0, $field_id = '', $value, $field = array(), $post_type = 'post' ) {
		if( empty( $id ) || empty( $field_id ) ) return;

		$value = noo_sanitize_field( $value, $field );

		if( $post_type == 'user' ) {
			update_user_meta( $id, $field_id, $value );
		} else {
			update_post_meta( $id, $field_id, $value );
		}
	}
endif;

if ( ! function_exists( 'noo_sanitize_field' ) ) :
	function noo_sanitize_field( $value, $field = array() ) {
		switch ( $field['type'] ) {
			case "textarea":
				break;
			case "multiple_select":
			case "radio" :
			case "checkbox" :
				if( is_array( $value ) ) {
					foreach ($value as $k => $v) {
						$v = wp_kses( $v, array() );
						$value[$k] = $v;
					}
				} else {
					$value = wp_kses( $value, array() );
				}
				break;
			case "select":
			case "text" :
			case "file_upload" :
				$value = wp_kses( $value, array() );
				break;
			case "number" :
				$value = ( strpos($value, '.') === false && strpos($value, ',') === false ) ? intval( $value ) : floatval( $value );
				break;
			case "email" :
				$value = sanitize_email( $value );
				break;
			case "url" :
			case "embed_video" :
				$value = esc_url( $value );
				break;
			case "datepicker" :
				$value = !is_numeric( $value ) ?  strtotime( $value ) : $value;
				break;
		}

		return apply_filters( 'noo_sanitize_field_' . $field['type'], $value, $field );
	}
endif;

if ( ! function_exists( 'noo_save_hideable_fields' ) ) :
	function noo_save_hideable_fields( $post_id = 0, $id = '', $value, $field = array(), $post_type = 'post' ) {
		// if( empty( $id ) || empty( $field_id ) ) return;

		// $value = noo_sanitize_field( $value, $field );

		// if( $post_type == 'user' ) {
		// 	update_user_meta( $id, $field_id, $value );
		// } else {
		// 	update_post_meta( $id, $field_id, $value );
		// }
	}
endif;