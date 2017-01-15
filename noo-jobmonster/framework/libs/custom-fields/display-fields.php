<?php
if ( ! function_exists( 'noo_display_field' ) ) :
	function noo_display_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		if( empty( $value ) || !is_array( $field ) ) return;

		$args = array_merge( array(
				'label_tag' => 'span',
				'label_class' => "cf-{$field['type']}-label",
				'value_tag' => 'span',
				'value_class' => "cf-{$field['type']}-value",
				'echo' => true,
			), $args );

		$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
		$html = array();
		if( !empty( $args['label_tag']) ) {
			$html[] = "<{$args['label_tag']} class='label-{$field_id} {$args['label_class']}'>". esc_html( $label ) . "</{$args['label_tag']}>";
		}

		$html[] = noo_display_field_value( $field, $field_id, $value, $args, false );

		$html = implode("\n", $html);
		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_field_value' ) ) :
	function noo_display_field_value( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		if( empty( $value ) || !is_array( $field ) ) return;

		$args = array_merge( array(
				'value_tag' => 'span',
				'value_class' => "cf-{$field['type']}-value",
			), $args );

		switch ( $field['type'] ) {
			case 'textarea':
				$html = noo_display_textarea_field( $field, $field_id, $value, $args, false );
				break;
			case 'select':
				$html = noo_display_select_field( $field, $field_id, $value, $args, false );
				break;
			case 'multiple_select':
				$html = noo_display_multiple_select_field( $field, $field_id, $value, $args, false );
				break;
			case 'radio' :
				$html = noo_display_radio_field( $field, $field_id, $value, $args, false );
				break;
			case 'checkbox' :
				$html = noo_display_checkbox_field( $field, $field_id, $value, $args, false );
				break;
			case 'number' :
				$html = noo_display_number_field( $field, $field_id, $value, $args, false );
				break;
			case 'text' :
				$html = noo_display_text_field( $field, $field_id, $value, $args, false );
				break;
			case 'url' :
				$html = noo_display_url_field( $field, $field_id, $value, $args, false );
				break;
			case 'datepicker' :
				$html = noo_display_datepicker_field( $field, $field_id, $value, $args, false );
				break;
			case 'single_image' :
				$html = noo_display_single_image_field( $field, $field_id, $value, $args, false );
				break;
			case 'image_gallery' :
				$html = noo_display_image_gallery_field( $field, $field_id, $value, $args, false );
				break;
			case 'file_upload' :
				$html = noo_display_file_upload_field( $field, $field_id, $value, $args, false );
				break;
			case 'embed_video' :
				$html = noo_display_embed_video_field( $field, $field_id, $value, $args, false );
				break;
			default :
				$html = apply_filters( 'noo_display_field_' . $field['type'], $value, $field, $field_id, $value, $args, false );
				break;
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_text_field' ) ) :
	function noo_display_text_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		$value = noo_convert_custom_field_value( $field, $value );
		if( is_array( $value ) ) $value = implode(', ', $value);

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>". esc_html( $value ) . "</{$args['value_tag']}>";
		} else {
			$html = esc_html( $value );
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_textarea_field' ) ) :
	function noo_display_textarea_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
		$value = noo_convert_custom_field_value( $field, $value );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>". do_shortcode( $value ) . "</{$args['value_tag']}>";
		} else {
			$html = do_shortcode( $value );
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_select_field' ) ) :
	function noo_display_select_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		return noo_display_text_field( $field, $field_id, $value, $args, $echo );
	}
endif;

if ( ! function_exists( 'noo_display_multiple_select_field' ) ) :
	function noo_display_multiple_select_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		$value = !is_array( $value ) ? noo_json_decode( $value ) : $value;
		$value = noo_convert_custom_field_value( $field, $value );
		$value = implode(', ', $value);

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>". esc_html( $value ) . "</{$args['value_tag']}>";
		} else {
			$html = esc_html( $value );
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_radio_field' ) ) :
	function noo_display_radio_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		return noo_display_text_field( $field, $field_id, $value, $args, $echo );
	}
endif;

if ( ! function_exists( 'noo_display_checkbox_field' ) ) :
	function noo_display_checkbox_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		return noo_display_multiple_select_field( $field, $field_id, $value, $args, $echo );
	}
endif;

if ( ! function_exists( 'noo_display_number_field' ) ) :
	function noo_display_number_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		return noo_display_text_field( $field, $field_id, $value, $args, $echo );
	}
endif;

if ( ! function_exists( 'noo_display_url_field' ) ) :
	function noo_display_url_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		$value = noo_convert_custom_field_value( $field, $value );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'><a href='". esc_url( $value ) . "' target='_blank'>". esc_html( $value ) . "</a></{$args['value_tag']}>";
		} else {
			$html = '<a href="'. esc_url( $value ) . '" target="_blank" class="link-alt">'. esc_html( $value ) . '</a>';
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_datepicker_field' ) ) :
	function noo_display_datepicker_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		return noo_display_text_field( $field, $field_id, $value, $args, $echo );
	}
endif;

if ( ! function_exists( 'noo_display_embed_video_field' ) ) :
	function noo_display_embed_video_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		global $wp_embed;
		$value = noo_convert_custom_field_value( $field, $value );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>";
			$html .= wp_oembed_get( $value, array( 'width' => 800 ) );
			$html .= "</{$args['value_tag']}>";
		} else {
			$html = wp_oembed_get( $value, array( 'width' => 800 ) );
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_single_image_field' ) ) :
	function noo_display_single_image_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		wp_enqueue_script( 'vendor-nivo-lightbox-js' );
		wp_enqueue_style( 'vendor-nivo-lightbox-default-css' );

		$image = noo_convert_custom_field_value( $field, $value );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>{$image}</{$args['value_tag']}>";
		} else {
			$html = $image;
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_image_gallery_field' ) ) :
	function noo_display_image_gallery_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		wp_enqueue_script( 'vendor-nivo-lightbox-js' );
		wp_enqueue_style( 'vendor-nivo-lightbox-default-css' );

		$images = noo_convert_custom_field_value( $field, $value );
		$images = implode( '', $images );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>{$images}</{$args['value_tag']}>";
		} else {
			$html = "<div class='cf-image_gallery-value'>{$images}</div>";
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;

if ( ! function_exists( 'noo_display_file_upload_field' ) ) :
	function noo_display_file_upload_field( $field = array(), $field_id = '', $value = '', $args = array(), $echo = true ) {
		$urls = noo_convert_custom_field_value( $field, $value );
		$urls = implode( ', ', $urls );

		if( !empty( $args['value_tag'] ) ) {
			$html = "<{$args['value_tag']} class='value-{$field_id} {$args['value_class']}'>{$urls}</{$args['value_tag']}>";
		} else {
			$html = $urls;
		}

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
endif;
