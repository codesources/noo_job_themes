<?php
/**
 * NOO Visual Composer Add-ons
 *
 * Customize Visual Composer to suite NOO Framework
 *
 * @package    NOO Framework
 * @subpackage NOO Visual Composer Add-ons
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

// Set as theme - http://kb.wpbakery.com/index.php?title=Vc_set_as_theme
if ( function_exists( 'vc_set_as_theme' ) ) :
	vc_set_as_theme( true );

endif;

// Disable Frontend Editor
// http://kb.wpbakery.com/index.php?title=Vc_disable_frontend

// if ( function_exists( 'vc_disable_frontend' ) ) :
// 	vc_disable_frontend();

// endif;

if ( defined( 'WPB_VC_VERSION' ) ) :

	function noo_dropdown_group_param( $param, $param_value ) {
		$css_option = vc_get_dropdown_option( $param, $param_value );
		$param_line = '';
		$param_line .= '<select name="' . $param['param_name'] .
			 '" class="dh-chosen-select wpb_vc_param_value wpb-input wpb-select ' . $param['param_name'] . ' ' .
			 $param['type'] . ' ' . $css_option . '" data-option="' . $css_option . '">';
		foreach ( $param['optgroup'] as $text_opt => $opt ) {
			if ( is_array( $opt ) ) {
				$param_line .= '<optgroup label="' . $text_opt . '">';
				foreach ( $opt as $text_val => $val ) {
					if ( is_numeric( $text_val ) && ( is_string( $val ) || is_numeric( $val ) ) ) {
						$text_val = $val;
					}
					$selected = '';
					if ( $param_value !== '' && (string) $val === (string) $param_value ) {
						$selected = ' selected="selected"';
					}
					$param_line .= '<option class="' . $val . '" value="' . $val . '"' . $selected . '>' .
						 htmlspecialchars( $text_val ) . '</option>';
				}
				$param_line .= '</optgroup>';
			} elseif ( is_string( $opt ) ) {
				if ( is_numeric( $text_opt ) && ( is_string( $opt ) || is_numeric( $opt ) ) ) {
					$text_opt = $opt;
				}
				$selected = '';
				if ( $param_value !== '' && (string) $opt === (string) $param_value ) {
					$selected = ' selected="selected"';
				}
				$param_line .= '<option class="' . $opt . '" value="' . $opt . '"' . $selected . '>' .
					 htmlspecialchars( $text_opt ) . '</option>';
			}
		}
		$param_line .= '</select>';
		return $param_line;
	}
	vc_add_shortcode_param( 'noo_dropdown_group', 'noo_dropdown_group_param' );
	
	// Categories select field type
	if ( ! function_exists( 'noo_vc_field_type_post_categories' ) ) :

		function noo_vc_custom_param_post_categories( $settings, $value ) {
			$dependency = vc_generate_dependencies_attributes( $settings );
			$categories = get_categories( array( 'orderby' => 'NAME', 'order' => 'ASC' ) );
			$class = 'wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] . '_field';
			$selected_values = explode( ',', $value );
			$html = array( '<div class="noo_vc_custom_param post_categories">' );
			$html[] = '  <input type="hidden" name="' . $settings['param_name'] . '" value="' . $value .
				 '" class="wpb_vc_param_value" />';
			$html[] = '  <select name="' . $settings['param_name'] . '-select" multiple="true" class="' . $class . '" ' .
				 $dependency . '>';
			$html[] = '    <option value="all" ' . ( in_array( 'all', $selected_values ) ? 'selected="true"' : '' ) . '>' .
				 __( 'All', 'noo' ) . '</option>';
			foreach ( $categories as $category ) {
				$html[] = '    <option value="' . $category->term_id . '" ' .
					 ( in_array( $category->term_id, $selected_values ) ? 'selected="true"' : '' ) . '>';
				$html[] = '      ' . $category->name;
				$html[] = '    </option>';
			}
			
			$html[] = '  </select>';
			$html[] = '</div>';
			$html[] = '<script>';
			$html[] = '  jQuery("document").ready( function() {';
			$html[] = '	   jQuery( "select[name=\'' . $settings['param_name'] . '-select\']" ).click( function() {';
			$html[] = '      var selected_values = jQuery(this).find("option:selected").map(function(){ return this.value; }).get().join(",");';
			$html[] = '      jQuery( "input[name=\'' . $settings['param_name'] . '\']" ).val( selected_values );';
			$html[] = '	   } );';
			$html[] = '  } );';
			$html[] = '</script>';
			
			return implode( "\n", $html );
		}
		vc_add_shortcode_param( 'post_categories', 'noo_vc_custom_param_post_categories' );
	

	endif;
	
	// Product Category
	if ( ! function_exists( 'noo_vc_field_type_product_categories' ) ) :

		function noo_vc_custom_param_product_categories( $settings, $value ) {
			$dependency = vc_generate_dependencies_attributes( $settings );
			$categories = get_terms( 'product_cat', array( 'orderby' => 'NAME', 'order' => 'ASC' ) );
			$class = 'wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] . '_field';
			$selected_values = explode( ',', $value );
			$html = array( '<div class="noo_vc_custom_param product_categories">' );
			$html[] = '  <input type="hidden" name="' . $settings['param_name'] . '" value="' . $value .
				 '" class="wpb_vc_param_value" />';
			$html[] = '  <select name="' . $settings['param_name'] . '-select" multiple="true" class="' . $class . '" ' .
				 $dependency . '>';
			$html[] = '    <option value="">' . __( 'All', 'noo' ) . '</option>';
			foreach ( $categories as $category ) {
				$html[] = '    <option value="' . $category->slug . '" ' .
					 ( in_array( $category->slug, $selected_values ) ? 'selected="true"' : '' ) . '>';
				$html[] = '      ' . $category->name;
				$html[] = '    </option>';
			}
			
			$html[] = '  </select>';
			$html[] = '</div>';
			$html[] = '<script>';
			$html[] = '  jQuery("document").ready( function() {';
			$html[] = '	   jQuery( "select[name=\'' . $settings['param_name'] . '-select\']" ).click( function() {';
			$html[] = '      var selected_values = jQuery(this).find("option:selected").map(function(){ return this.value; }).get().join(",");';
			$html[] = '      jQuery( "input[name=\'' . $settings['param_name'] . '\']" ).val( selected_values );';
			$html[] = '	   } );';
			$html[] = '  } );';
			$html[] = '</script>';
			
			return implode( "\n", $html );
		}
		vc_add_shortcode_param( 'product_categories', 'noo_vc_custom_param_product_categories' );
	
	endif; // Categories select field type
	
	if ( ! function_exists( 'noo_vc_custom_param_user_list' ) ) :

		function noo_vc_custom_param_user_list( $settings, $value ) {
			$dependency = vc_generate_dependencies_attributes( $settings );
			$users = get_users( array( 'orderby' => 'NAME', 'order' => 'ASC' ) );
			$class = 'wpb_vc_param_value wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] .
				 '_field';
			$html = array( '<div class="noo_vc_custom_param user_list">' );
			// $html[] = ' <input type="hidden" name="'. $settings['param_name'] . '" value="'. $value . '"
			// class="wpb_vc_param_value" />';
			$html[] = '  <select name="' . $settings['param_name'] . '" class="' . $class . '" ' . $dependency . '>';
			foreach ( $users as $user ) {
				$html[] = '    <option value="' . $user->ID . '" ' . ( selected( $value, $user->ID, false ) ) . '>';
				$html[] = '      ' . $user->display_name;
				$html[] = '    </option>';
			}
			
			$html[] = '  </select>';
			$html[] = '</div>';
			
			return implode( "\n", $html );
		}
		vc_add_shortcode_param( 'user_list', 'noo_vc_custom_param_user_list' );
	

	endif;
	
	if ( class_exists( 'RevSlider' ) ) {
		if ( ! function_exists( 'noo_vc_rev_slider' ) ) :

			function noo_vc_rev_slider( $settings, $value ) {
				$dependency = vc_generate_dependencies_attributes( $settings );
				$rev_slider = new RevSlider();
				$sliders = $rev_slider->getArrSliders();
				$class = 'wpb_vc_param_value wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] .
					 '_field';
				$html = array( '<div class="noo_vc_custom_param noo_rev_slider">' );
				$html[] = '  <select name="' . $settings['param_name'] . '" class="' . $class . '" ' . $dependency . '>';
				foreach ( $sliders as $slider ) {
					$html[] = '    <option value="' . $slider->getAlias() . '"' .
						 ( selected( $value, $slider->getAlias() ) ) . '>' . $slider->getTitle() . '</option>';
				}
				$html[] = '  </select>';
				$html[] = '</div>';
				
				return implode( "\n", $html );
			}
			
			vc_add_shortcode_param( 'noo_rev_slider', 'noo_vc_rev_slider' );
		

		endif;
	}
	
	if ( ! function_exists( 'noo_vc_custom_param_ui_slider' ) ) :

		function noo_vc_custom_param_ui_slider( $settings, $value ) {
			$dependency = vc_generate_dependencies_attributes( $settings );
			$class = 'noo-slider wpb_vc_param_value wpb-input wpb-select ' . $settings['param_name'] . ' ' .
				 $settings['type'] . '_field';
			$data_min = ( isset( $settings['data_min'] ) && ! empty( $settings['data_min'] ) ) ? 'data-min="' .
				 $settings['data_min'] . '"' : 'data-min="0"';
			$data_max = ( isset( $settings['data_max'] ) && ! empty( $settings['data_max'] ) ) ? 'data-max="' .
				 $settings['data_max'] . '"' : 'data-max="100"';
			$data_step = ( isset( $settings['data_step'] ) && ! empty( $settings['data_step'] ) ) ? 'data-step="' .
				 $settings['data_step'] . '"' : 'data-step="1"';
			$html = array();
			
			$html[] = '	<div class="noo-control">';
			$html[] = '		<input type="text" id="' . $settings['param_name'] . '" name="' . $settings['param_name'] .
				 '" class="' . $class . '" value="' . $value . '" ' . $data_min . ' ' . $data_max . ' ' . $data_step .
				 '/>';
			$html[] = '	</div>';
			$html[] = '<script>';
			$html[] = 'jQuery("#' . $settings['param_name'] . '").each(function() {';
			$html[] = '	var $this = jQuery(this);';
			$html[] = '	var $slider = jQuery("<div>", {id: $this.attr("id") + "-slider"}).insertAfter($this);';
			$html[] = '	$slider.slider(';
			$html[] = '	{';
			$html[] = '		range: "min",';
			$html[] = '		value: $this.val() || $this.data("min") || 0,';
			$html[] = '		min: $this.data("min") || 0,';
			$html[] = '		max: $this.data("max") || 100,';
			$html[] = '		step: $this.data("step") || 1,';
			$html[] = '		slide: function(event, ui) {';
			$html[] = '			$this.val(ui.value).attr("value", ui.value);';
			$html[] = '		}';
			$html[] = '	}';
			$html[] = '	);';
			$html[] = '	$this.change(function() {';
			$html[] = '		$slider.slider( "option", "value", $this.val() );';
			$html[] = '	});';
			$html[] = '});';
			$html[] = '</script>';
			
			return implode( "\n", $html );
		}
		
		vc_add_shortcode_param( 'ui_slider', 'noo_vc_custom_param_ui_slider' );
	
	endif;

	if ( ! function_exists( 'noo_vc_custom_param_job_taxonomy' ) ) :

		function noo_vc_custom_param_job_taxonomy( $settings, $value ) {
			if( taxonomy_exists('job_category') ):
				$categories = get_terms( 'job_category', array( 'orderby' => 'NAME', 'order' => 'ASC' ) );
				$class = 'wpb-input wpb-select ' . $settings['param_name'] . ' ' . $settings['type'] . '_field';
				$selected_values = explode( ',', $value );
				$html = array( '<div class="noo_vc_custom_param project_categories">' );
				$html[] = '  <input type="hidden" name="' . $settings['param_name'] . '" value="' . $value .
					'" class="wpb_vc_param_value" />';
				$html[] = '  <select name="' . $settings['param_name'] . '-select" multiple="true" class="' . $class . '">';
				$html[] = '    <option value="all" ' . ( in_array( 'all', $selected_values ) ? 'selected="true"' : '' ) . '>' .
					esc_html__( 'All', 'noo' ) . '</option>';
				foreach ( $categories as $category ) {
					$html[] = '    <option value="' . $category->term_id . '" ' .
						( in_array( $category->term_id, $selected_values ) ? 'selected="true"' : '' ) . '>';
					$html[] = '      ' . $category->name;
					$html[] = '    </option>';
				}

				$html[] = '  </select>';
				$html[] = '</div>';
				$html[] = '<script>';
				$html[] = '  jQuery("document").ready( function() {';
				$html[] = '	   jQuery( "select[name=\'' . $settings['param_name'] . '-select\']" ).click( function() {';
				$html[] = '      var selected_values = jQuery(this).find("option:selected").map(function(){ return this.value; }).get().join(",");';
				$html[] = '      jQuery( "input[name=\'' . $settings['param_name'] . '\']" ).val( selected_values );';
				$html[] = '	   } );';
				$html[] = '  } );';
				$html[] = '</script>';

				return implode( "\n", $html );
			endif;
		}
		vc_add_shortcode_param( 'job_category', 'noo_vc_custom_param_job_taxonomy' );

	endif;

endif;

if ( defined( 'WPB_VC_VERSION' ) ) :
	if ( ! function_exists( 'noo_vc_admin_enqueue_assets' ) ) :

		function noo_vc_admin_enqueue_assets( $hook ) {
			if ( $hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php' ) {
				return;
			}
			// Enqueue style for VC admin
			wp_register_style( 'noo-vc-admin-css', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo-vc-admin.css', array( 'noo-jquery-ui-slider' ) );
			wp_enqueue_style( 'noo-vc-admin-css' );
			
			// Enqueue script for VC admin
			wp_register_script( 
				'noo-vc-admin-js', 
				NOO_FRAMEWORK_ADMIN_URI . '/assets/js/noo-vc-admin.js', 
				null, 
				null, 
				false );
			wp_enqueue_script( 'noo-vc-admin-js' );
		}
	
	endif;
	add_action( 'admin_enqueue_scripts', 'noo_vc_admin_enqueue_assets' );

endif;

// Remove unused VC Metabox: Teaser Metabox
if ( defined( 'WPB_VC_VERSION' ) ) :
	if ( ! function_exists( 'noo_vs_remove_unused_metabox' ) ) :

		function noo_vs_remove_unused_metabox() {
			if ( is_admin() ) {
				$post_types = get_post_types( '', 'names' );
				foreach ( $post_types as $post_type ) {
					remove_meta_box( 'vc_teaser', $post_type, 'side' );
				}
			}
		}
		
		add_action( 'do_meta_boxes', 'noo_vs_remove_unused_metabox' );
	
	endif;

endif;

// Remove unused VC Shortcodes
if ( defined( 'WPB_VC_VERSION' ) ) :
	
	if ( ! function_exists( 'noo_vc_remove_unused_elements' ) ) :

		function noo_vc_remove_unused_elements() {
			vc_remove_element( 'vc_text_separator' );
			vc_remove_element( 'vc_facebook' );
			vc_remove_element( 'vc_tweetmeme' );
			vc_remove_element( 'vc_googleplus' );
			vc_remove_element( 'vc_pinterest' );
			vc_remove_element( 'vc_toggle' );
			vc_remove_element( 'rev_slider_vc' );
//			vc_remove_element( 'vc_gallery' );
			vc_remove_element( 'vc_images_carousel' );
			vc_remove_element( 'vc_posts_grid' );
//			vc_remove_element( 'vc_carousel' );
			vc_remove_element( 'vc_posts_slider' );
			vc_remove_element( 'vc_video' );
			vc_remove_element( 'vc_flickr' );
			vc_remove_element( 'vc_progress_bar' );
			vc_remove_element( 'vc_wp_search' );
			vc_remove_element( 'vc_wp_meta' );
			vc_remove_element( 'vc_wp_recentcomments' );
			vc_remove_element( 'vc_wp_calendar' );
			vc_remove_element( 'vc_wp_pages' );
			vc_remove_element( 'vc_wp_tagcloud' );
			vc_remove_element( 'vc_wp_custommenu' );
			vc_remove_element( 'vc_wp_text' );
			vc_remove_element( 'vc_wp_posts' );
			vc_remove_element( 'vc_wp_links' );
			vc_remove_element( 'vc_wp_categories' );
			vc_remove_element( 'vc_wp_archives' );
			vc_remove_element( 'vc_wp_rss' );
			vc_remove_element( 'vc_button2' );
			vc_remove_element( 'vc_cta_button2' );
			vc_remove_element( 'vc_empty_space' );
			vc_remove_element( 'vc_custom_heading' );
		}
		
		add_action( 'admin_init', 'noo_vc_remove_unused_elements' );
	

	endif;

endif;

// NOO VC Shortcodes Base Element
// =============================================================================
if ( defined( 'WPB_VC_VERSION' ) ) :
	if ( ! function_exists( 'noo_vc_base_element' ) ) :

		function noo_vc_base_element() {
			
			//
			// Variables.
			//
			$category_base_element = __( 'Base Elements', 'noo' );
			$category_typography = __( 'Typography', 'noo' );
			$category_content = __( 'Content', 'noo' );
			$category_wp_content = __( 'WordPress Content', 'noo' );
			$category_media = __( 'Media', 'noo' );
			$category_custom = __( 'Custom', 'noo' );
			
			$param_content_name = 'content';
			$param_content_heading = __( 'Text', 'noo' );
			$param_content_description = __( 'Enter your text.', 'noo' );
			$param_content_type = 'textarea_html';
			$param_content_holder = 'div';
			$param_content_value = '';
			
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array( 
				__( 'All Devices', 'noo' ) => "all", 
				__( 'Hidden Phone', 'noo' ) => "hidden-phone", 
				__( 'Hidden Tablet', 'noo' ) => "hidden-tablet", 
				__( 'Hidden PC', 'noo' ) => "hidden-pc", 
				__( 'Visible Phone', 'noo' ) => "visible-phone", 
				__( 'Visible Tablet', 'noo' ) => "visible-tablet", 
				__( 'Visible PC', 'noo' ) => "visible-pc" );
			
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
			
			$param_id_name = 'id';
			$param_id_heading = __( 'Row ID', 'noo' );
			$param_id_description = __( 
				'(Optional) Enter a unique ID', 
				'noo' );
			$param_id_type = 'textfield';
			$param_id_holder = 'div';
			
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			
			$param_holder = 'div';

			
			$param_animation_value = array( 
				"None" => "", 
				"Bounce In" => "bounceIn", 
				"Bounce In Right" => "bounceInRight", 
				"Bounce In Left" => "bounceInLeft", 
				"Bounce In Up" => "bounceInUp", 
				"Bounce In Down" => "bounceInDown", 
				"Fade In" => "fadeIn", 
				"Grow In" => "growIn", 
				"Shake" => "shake", 
				"Shake Up" => "shakeUp", 
				"Fade In Left" => "fadeInLeft", 
				"Fade In Right" => "fadeInRight", 
				"Fade In Up" => "fadeInUp", 
				"Fade InDown" => "fadeInDown", 
				"Rotate In" => "rotateIn", 
				"Rotate In Up Left" => "rotateInUpLeft", 
				"Rotate In Down Left" => "rotateInDownLeft", 
				"Rotate In Up Right" => "rotateInUpRight", 
				"Rotate In Down Right" => "rotateInDownRight", 
				"Roll In" => "rollIn", 
				"Wiggle" => "wiggle", 
				"Swing" => "swing", 
				"Tada" => "tada", 
				"Wobble" => "wobble", 
				"Pulse" => "pulse", 
				"Light Speed In Right" => "lightSpeedInRight", 
				"Light Speed In Left" => "lightSpeedInLeft", 
				"Flip" => "flip", 
				"Flip In X" => "flipInX", 
				"Flip In Y" => "flipInY", 
				
				// Out animation
				"Bounce Out" => "bounceOut", 
				"Bounce Out Up" => "bounceOutUp", 
				"Bounce Out Down" => "bounceOutDown", 
				"Bounce Out Left" => "bounceOutLeft", 
				"Bounce Out Right" => "bounceOutRight", 
				"Fade Out" => "fadeOut", 
				"Fade Out Up" => "fadeOutUp", 
				"Fade Out Down" => "fadeOutDown", 
				"Fade Out Left" => "fadeOutLeft", 
				"Fade Out Right" => "fadeOutRight", 
				"Flip Out X" => "flipOutX", 
				"Flip Out Y" => "flipOutY", 
				"Light Speed Out Right" => "lightSpeedOutLeft", 
				"Rotate Out" => "rotateOut", 
				"Rotate Out Up Left" => "rotateOutUpLeft", 
				"Rotate Out Down Left" => "rotateOutDownLeft", 
				"Rotate Out Up Right" => "rotateOutUpRight", 
				"Roll Out" => "rollOut" );
			
			// [vc_row]
			// ============================
			vc_map_update( 
				'vc_row', 
				array( 
					'category' => $category_base_element, 
					'weight' => 990, 
					'class' => 'noo-vc-element noo-vc-element-row', 
					'icon' => 'noo-vc-icon-row' ) );
			
			vc_remove_param( 'vc_row', 'full_width' );
			vc_remove_param( 'vc_row', 'gap' );
			vc_remove_param( 'vc_row', 'full_height' );
			vc_remove_param( 'vc_row', 'equal_height' );
			vc_remove_param( 'vc_row', 'content_placement' );
			vc_remove_param( 'vc_row', 'columns_placement' );
			vc_remove_param( 'vc_row', 'video_bg' );
			vc_remove_param( 'vc_row', 'video_bg_url' );
			vc_remove_param( 'vc_row', 'video_bg_parallax' );
			vc_remove_param( 'vc_row', 'bg_color' );
			vc_remove_param( 'vc_row', 'font_color' );
			vc_remove_param( 'vc_row', 'padding' );
			vc_remove_param( 'vc_row', 'margin_bottom' );
			vc_remove_param( 'vc_row', 'bg_image' );
			vc_remove_param( 'vc_row', 'bg_image_repeat' );
			vc_remove_param( 'vc_row', 'el_class' );
			vc_remove_param( 'vc_row', 'el_id' );
			vc_remove_param( 'vc_row', 'css' );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_color', 
					'heading' => __( 'Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_image', 
					'heading' => __( 'Background Image', 'noo' ), 
					'type' => 'attach_image', 
					'holder' => $param_holder ) );
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_color_overlay', 
					'heading' => __( 'Background Color Overlay', 'noo' ), 
					'type' => 'colorpicker', 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ), 
					'holder' => $param_holder ) );
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_image_repeat', 
					'heading' => __( 'Background Image Repeat', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'false' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'parallax', 
					'heading' => __( 'Parallax Background', 'noo' ), 
					'description' => __( 'Enable Parallax Background', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'parallax_no_mobile', 
					'heading' => __( 'Disable Parallax on Mobile', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'parallax_velocity', 
					'heading' => __( 'Parallax Velocity', 'noo' ), 
					'description' => __( 'The movement speed, value should be between -1.0 and 1.0', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder, 
					'value' => '0.1', 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_video', 
					'heading' => __( 'Background Video', 'noo' ), 
					'description' => __( 
						'Enable Background Video, it will override Background Color and Background Image', 
						'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( __( 'Yes', 'noo' ) => 'true' ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_video_url', 
					'heading' => __( 'Video URL', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder, 
					'dependency' => array( 'element' => "bg_video", 'value' => array( 'true' ) ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'bg_video_poster', 
					'heading' => __( 'Video Poster Image', 'noo' ), 
					'type' => 'attach_image', 
					'holder' => $param_holder, 
					'dependency' => array( 'element' => 'bg_video', 'value' => array( 'true' ) ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'inner_container', 
					'heading' => __( 'Has Container', 'noo' ), 
					'description' => __( 'If enabled, this row will be placed inside a container.', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( __( 'Yes', 'noo' ) => 'true' ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'border', 
					'heading' => __( 'Border', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'None', 'noo' ) => '', 
						__( 'Top', 'noo' ) => 'top', 
						__( 'Right', 'noo' ) => 'right', 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Bottom', 'noo' ) => 'bottom', 
						__( 'Vertical', 'noo' ) => 'vertical', 
						__( 'Horizontal', 'noo' ) => 'horizontal', 
						__( 'All', 'noo' ) => 'all' ) ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'padding_top', 
					'heading' => __( 'Padding Top (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100' ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => 'padding_bottom', 
					'heading' => __( 'Padding Bottom (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100' ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => $param_id_name, 
					'heading' => $param_id_heading, 
					'description' => $param_id_description, 
					'type' => $param_id_type, 
					'holder' => $param_id_holder ) );
			
			vc_add_param( 
				'vc_row', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_row_inner]
			// ============================
			vc_map_update( 
				'vc_row_inner', 
				array( 
					'category' => $category_base_element, 
					'class' => 'noo-vc-element noo-vc-element-row', 
					'icon' => 'noo-vc-icon-row' ) );
			
			vc_remove_param( 'vc_row_inner', 'gap' );
			vc_remove_param( 'vc_row_inner', 'equal_height' );
			vc_remove_param( 'vc_row_inner', 'content_placement' );
			vc_remove_param( 'vc_row_inner', 'columns_placement' );
			vc_remove_param( 'vc_row_inner', 'el_class' );
			vc_remove_param( 'vc_row_inner', 'el_id' );
			vc_remove_param( 'vc_row_inner', 'css' );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_color', 
					'heading' => __( 'Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_image', 
					'heading' => __( 'Background Image', 'noo' ), 
					'type' => 'attach_image', 
					'holder' => $param_holder ) );
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_color_overlay', 
					'heading' => __( 'Background Color Overlay', 'noo' ), 
					'type' => 'colorpicker', 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ), 
					'holder' => $param_holder ) );
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_image_repeat', 
					'heading' => __( 'Background Image Repeat', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'false' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'parallax', 
					'heading' => __( 'Parallax Background', 'noo' ), 
					'description' => __( 'Enable Parallax Background', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'parallax_no_mobile', 
					'heading' => __( 'Disable Parallax on Mobile', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'parallax_velocity', 
					'heading' => __( 'Parallax Velocity', 'noo' ), 
					'description' => __( 'The movement speed, value should be between 0.1 and 1.0', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder, 
					'value' => '0.1', 
					'dependency' => array( 'element' => "bg_image", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_video', 
					'heading' => __( 'Background Video', 'noo' ), 
					'description' => __( 
						'Enable Background Video, it will override Background Color and Background Image', 
						'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( __( 'Yes', 'noo' ) => 'true' ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_video_url', 
					'heading' => __( 'Video URL', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder, 
					'dependency' => array( 'element' => "bg_video", 'value' => array( 'true' ) ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'bg_video_poster', 
					'heading' => __( 'Video Poster Image', 'noo' ), 
					'type' => 'attach_image', 
					'holder' => $param_holder, 
					'dependency' => array( 'element' => "bg_video", 'value' => array( 'true' ) ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'inner_container', 
					'heading' => __( 'Has Container', 'noo' ), 
					'description' => __( 'If enabled, this row will be placed inside a container.', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'border', 
					'heading' => __( 'Border', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'None', 'noo' ) => '', 
						__( 'Top', 'noo' ) => 'top', 
						__( 'Right', 'noo' ) => 'right', 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Bottom', 'noo' ) => 'bottom', 
						__( 'Vertical', 'noo' ) => 'vertical', 
						__( 'Horizontal', 'noo' ) => 'horizontal', 
						__( 'All', 'noo' ) => 'all' ) ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'padding_top', 
					'heading' => __( 'Padding Top (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100' ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => 'padding_bottom', 
					'heading' => __( 'Padding Bottom (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100' ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => $param_id_name, 
					'heading' => $param_id_heading, 
					'description' => $param_id_description, 
					'type' => $param_id_type, 
					'holder' => $param_id_holder ) );
			
			vc_add_param( 
				'vc_row_inner', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_column]
			// ============================
			vc_remove_param( 'vc_column', 'el_class' );
			vc_remove_param( 'vc_column', 'css' );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'alignment', 
					'heading' => __( 'Text Alignment', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Center', 'noo' ) => 'center', 
						__( 'Right', 'noo' ) => 'right' ) ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'animation', 
					'heading' => __( 'Select Animation', 'noo' ), 
					'description' => __( 'Choose animation effect for this column.', 'noo' ), 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => $param_animation_value ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'animation_offset', 
					'heading' => __( 'Animation Offset', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '40', 
					'data_min' => '0', 
					'data_max' => '200', 
					'data_step' => '10', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'animation_delay', 
					'heading' => __( 'Animation Delay (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '0', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'animation_duration', 
					'heading' => __( 'Animation Duration (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '1000', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_column_inner]
			// ============================
			vc_remove_param( 'vc_column_inner', 'el_class' );
			vc_remove_param( 'vc_column_inner', 'css' );
			
			vc_add_param( 
				'vc_column', 
				array( 
					'param_name' => 'alignment', 
					'heading' => __( 'Text Alignment', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Center', 'noo' ) => 'center', 
						__( 'Right', 'noo' ) => 'right' ) ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => 'animation', 
					'heading' => __( 'Select Animation', 'noo' ), 
					'description' => __( 'Choose animation effect for this column.', 'noo' ), 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => $param_animation_value ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => 'animation_offset', 
					'heading' => __( 'Animation Offset', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '40', 
					'data_min' => '0', 
					'data_max' => '200', 
					'data_step' => '10', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => 'animation_delay', 
					'heading' => __( 'Animation Delay (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '0', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => 'animation_duration', 
					'heading' => __( 'Animation Duration (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '1000', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_column_inner', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_separator]
			// ============================
			vc_map_update( 
				'vc_separator', 
				array( 
					'category' => $category_base_element, 
					'weight' => 980, 
					'class' => 'noo-vc-element noo-vc-element-separator', 
					'icon' => 'noo-vc-icon-separator' ) );
			
			vc_remove_param( 'vc_separator', 'color' );
			vc_remove_param( 'vc_separator', 'accent_color' );
			vc_remove_param( 'vc_separator', 'style' );
			vc_remove_param( 'vc_separator', 'el_width' );
			vc_remove_param( 'vc_separator', 'el_class' );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'type', 
					'heading' => __( 'Type', 'noo' ), 
					'description' => __( 'Choose type of this seperator.', 'noo' ), 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Line', 'noo' ) => 'line', 
						__( 'Line with Text', 'noo' ) => 'line-with-text' ) ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'title', 
					'heading' => __( 'Title', 'noo' ), 
					'description' => '', 
					'type' => 'textfield', 
					'holder' => $param_holder, 
					'dependency' => array( 'element' => "type", 'value' => array( 'line-with-text' ) ) ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'size', 
					'heading' => __( 'Size', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Full-Width', 'noo' ) => 'fullwidth', 
						__( 'Half', 'noo' ) => 'half' ) ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'position', 
					'heading' => __( 'Position', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Center', 'noo' ) => 'center', 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Right', 'noo' ) => 'right' ) ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'color', 
					'heading' => __( 'Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '2' ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'thickness', 
					'heading' => __( 'LIne Thickness (px)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '2', 
					'data_min' => '0', 
					'data_max' => '10' ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'space_before', 
					'heading' => __( 'Space Before (px)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100', 
					'data_step' => '5' ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => 'space_after', 
					'heading' => __( 'Space After (px)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '20', 
					'data_min' => '0', 
					'data_max' => '100', 
					'data_step' => '5' ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_separator', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			//
			// Animation.
			//
			vc_map( 
				array( 
					'base' => 'animation', 
					'name' => __( 'Animation Block', 'noo' ), 
					'weight' => 985, 
					'class' => 'noo-vc-element noo-vc-element-animation', 
					'icon' => 'noo-vc-icon-animation', 
					'category' => $category_base_element, 
					'description' => __( 'Enable animation for serveral elements.', 'noo' ), 
					'as_parent' => array( 
						'only' => 'vc_column_text,icon,label,code,vc_button,vc_pie,vc_message,vc_widget_sidebar,vc_single_image,vc_gmaps,gap' ), 
					'content_element' => true, 
					'js_view' => 'VcColumnView', 
					'show_settings_on_create' => false, 
					'params' => array() ) );
			
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => 'animation', 
					'heading' => __( 'Select Animation', 'noo' ), 
					'description' => __( 'Choose animation effect for this column.', 'noo' ), 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => $param_animation_value ) );
			
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => 'animation_offset', 
					'heading' => __( 'Animation Offset', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '40', 
					'data_min' => '0', 
					'data_max' => '200', 
					'data_step' => '10', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => 'animation_delay', 
					'heading' => __( 'Animation Delay (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '0', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => 'animation_duration', 
					'heading' => __( 'Animation Duration (ms)', 'noo' ), 
					'description' => '', 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '1000', 
					'data_min' => '0', 
					'data_max' => '3000', 
					'data_step' => '50', 
					'dependency' => array( 'element' => "animation", 'not_empty' => true ) ) );
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			vc_add_param( 
				'animation', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [gap]
			// ============================
			vc_map( 
				array( 
					'base' => 'gap', 
					'name' => __( 'Gap', 'noo' ), 
					'weight' => 960, 
					'class' => 'noo-vc-element noo-vc-element-gap', 
					'icon' => 'noo-vc-icon-gap', 
					'category' => $category_base_element, 
					'description' => __( 'Insert a vertical gap in your content', 'noo' ), 
					'params' => array( 
						array( 
							'param_name' => 'size', 
							'heading' => __( 'Size (px)', 'noo' ), 
							'description' => __( 'Enter in the size of your gap.', 'noo' ), 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '50', 
							'data_min' => '20', 
							'data_max' => '200' ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [clear]
			// ============================
			vc_map( 
				array( 
					'base' => 'clear', 
					'name' => __( 'Clear', 'noo' ), 
					'weight' => 950, 
					'class' => 'noo-vc-element noo-vc-element-clear', 
					'icon' => 'noo-vc-icon-clear', 
					'category' => $category_base_element, 
					'description' => __( 'Clear helps you fix the normal break style', 'noo' ), 
					'params' => array( 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
		}
		
		add_action( 'admin_init', 'noo_vc_base_element' );

		//
		// Extend container class (parents).
		//
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {

			class WPBakeryShortCode_Animation extends WPBakeryShortCodesContainer {
			}
		}
	

	endif;
	// Noo JobMonster
	if ( ! function_exists( 'noo_vc_jobmonster' ) ) {
	
		function noo_vc_jobmonster() {

			// --- Job Category

				$job_category = array( __( 'All Categories', 'noo' ) => 'all' );
				$job_resume_category = array( __( 'All Categories', 'noo' ) => 'all' );
				$job_category_list = get_terms( 'job_category', array( 'hide_empty' => false ) );

			    if ( is_array( $job_category_list ) && ! empty( $job_category_list ) ) {
			        foreach ( $job_category_list as $category_details ) {
			            $job_category[ $category_details->name ] = $category_details->slug;  
			            $job_resume_category[ $category_details->name ] = $category_details->term_id;  
			        }
			    }
		    	
		    // --- Job Type

				$job_type = array( __( 'All types', 'noo' ) => 'all' );
				$job_type_list = get_terms( 'job_type', array( 'hide_empty' => false ) );

			    if ( is_array( $job_type_list ) && ! empty( $job_type_list ) ) {
			        foreach ( $job_type_list as $type_details ) {
			            $job_type[ $type_details->name ] = $type_details->slug;  
			        }
			    }

			// --- Job Location

				$job_location = array( __( 'All locations', 'noo' ) => 'all' );
				$job_resume_location = array( __( 'All locations', 'noo' ) => 'all' );
				$job_location_list = get_terms( 'job_location', array( 'hide_empty' => false ) );

			    if ( is_array( $job_location_list ) && ! empty( $job_location_list ) ) {
			        foreach ( $job_location_list as $location_details ) {
			            $job_location[ $location_details->name ] = $location_details->slug; 
			            $job_resume_location[ $location_details->name ] = $location_details->term_id;   
			        }
			    }



			$category_name = __('JobMonster','noo');
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array(
					__( 'All Devices', 'noo' ) => "all",
					__( 'Hidden Phone', 'noo' ) => "hidden-phone",
					__( 'Hidden Tablet', 'noo' ) => "hidden-tablet",
					__( 'Hidden PC', 'noo' ) => "hidden-pc",
					__( 'Visible Phone', 'noo' ) => "visible-phone",
					__( 'Visible Tablet', 'noo' ) => "visible-tablet",
					__( 'Visible PC', 'noo' ) => "visible-pc" );
		
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
					
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			$param_holder = 'div';
			vc_map( 
				array( 
					'base' => 'noo_recent_news', 
					'name' => __( 'Noo Recent News', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_recent_news',
					'icon' => 'noo-icon fa fa-bullhorn',
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'title', 
							'heading' => __( 'Title', 'noo' ), 
							'description' => __( 
								'Enter text which will be used as element title. Leave blank if no title is needed.', 
								'noo' ), 
							'type' => 'textfield', 
							'admin_label' => true, 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'style', 
							'heading' => __( 'Style', 'noo' ), 
							'type' => 'dropdown', 
							'admin_label' => true, 
							'value' => array( 
								__( 'Grid', 'noo' ) => 'grid', 
								__( 'Standard', 'noo' ) => 'standard', 
							), 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'columns', 
							'heading' => __( 'Columns', 'noo' ), 
							'type' => 'dropdown', 
							'admin_label' => true, 
							'value' => array( 
								__( '2', 'noo' ) => '2', 
								__( '3', 'noo' ) => '3', 
								__( '4', 'noo' ) => '4' ), 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'posts_per_page', 
							'heading' => __( 'Number of Posts', 'noo' ), 
							'type' => 'textfield', 
							'admin_label' => true, 
							'value' => '3', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'excerpt_length', 
							'heading' => __( 'Excerpt Length', 'noo' ), 
							'type' => 'textfield', 
							'std' => 20, 
							'admin_label' => true, 
							'holder' => $param_holder ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			vc_map( 
				array( 
					'base' => 'noo_step_icon', 
					'name' => __( 'Noo Step Icon', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_step_icon',
					'icon' => 'noo-icon fa fa-step-forward',
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						// array(
						// 	'type' => 'exploded_textarea',
						// 	'heading' => __( 'Step values', 'js_composer' ),
						// 	'param_name' => 'values',
						// 	'description' => __( 'Input step values, icon class, titles and link here. Divide values with linebreaks (Enter). Example: 90|Development|#e75956', 'noo' ),
						// 	'value' => "fa fa-key|1. Register an account to start|#,fa fa-search-plus|2. Specify & search your desired job|#,fa fa-file-text-o|3. Send your resume to employers|#"
						// ),
						array(
							'param_name' => 'new_values',
							'type' => 'param_group',
							'heading' => __('Step Icons', 'noo'),
							'value' => '',
							'params' => array(
								array(
									'param_name' => 'icon',
									'type' => 'iconpicker',
									'value' => 'fa fa-home',
									'heading' => __('Icon', 'noo'),
								),
								array(
									'param_name' => 'text',
									'type' => 'textfield',
									'value' => '1. Register an account to start',
									'heading' => __('Text', 'noo'),
								),
								array(
									'param_name' => 'link',
									'type' => 'textfield',
									'value' => '#',
									'heading' => __('Link', 'noo'),
								),
							)
						),
						array(
							'param_name' => 'style',
							'heading' => __('Style', 'noo'),
							'description' => '',
							'type' => 'dropdown',
							'admin_label' => true,
							'holder' => $param_holder,
							'value'       => array(
								__( 'Simple', 'noo' ) => '',
								__( 'Advanced', 'noo' )   => 'advanced',
							),
						),
						array(
							'param_name' => 'content',
							'heading' => __('Text', 'noo'),
							'dependency' => array( 'element' => 'style', 'value' => 'advanced' ),
							'description' => '',
							'type' => 'textarea_html',
							'holder' => $param_holder
						),
						array(
							'param_name' => 'btns',
							'type' => 'param_group',
							'heading' => __('List button', 'noo'),
							'dependency' => array( 'element' => 'style', 'value' => 'advanced' ),
							'value' => '',
							'params' => array(
								array(
									'param_name' => 'btn',
									'type' => 'vc_link',
									'value' => '#',
									'heading' => __('Link', 'noo'),
								),
								array(
									'param_name' => 'btn_color',
									'type' => 'colorpicker',
									'value' => '',
									'heading' => __('Button color', 'noo'),
								)
							)
						),
						array(
							'param_name' => 'background',
							'heading' => __('Background Image', 'noo'),
							'dependency' => array( 'element' => 'style', 'value' => 'advanced' ),
							'description' => '',
							'type' => 'attach_image',
							'holder' => $param_holder
						),

						array(
							'param_name' => $param_visibility_name,
							'heading' => $param_visibility_heading,
							'description' => $param_visibility_description,
							'type' => $param_visibility_type,
							'holder' => $param_visibility_holder,
							'value' => $param_visibility_value
						),
						array(
							'param_name' => $param_class_name,
							'heading' => $param_class_heading,
							'description' => $param_class_description,
							'type' => $param_class_type,
							'holder' => $param_class_holder
						),
						array(
							'param_name' => $param_custom_style_name,
							'heading' => $param_custom_style_heading,
							'description' => $param_custom_style_description,
							'type' => $param_custom_style_type,
							'holder' => $param_custom_style_holder
						)
					) 
				) 
			);
			$noo_jobs_params = array(
				array(
					'param_name'  => 'show',
					'heading'     => __( 'Show', 'noo' ),
					'type'        => 'dropdown',
					'admin_label' => true,
					'value'       => array(
						__( 'Featured', 'noo' ) => 'featured',
						__( 'Recent', 'noo' )   => 'recent',
					),
					'holder' 	  => $param_holder 
				),
				array(
					'param_name' => 'display_style',
					'heading'    => __( 'Display Style', 'noo' ),
//					'dependency' => array( 'element' => 'show', 'value' => array( 'featured' ) ),
					'type'       => 'dropdown',
					'value'      => array(
						__( 'Job List Full', 'noo' )   => 'list',
						__( 'Job List Column', 'noo' )   => 'list2',
						__( 'Job Slider', 'noo' ) => 'slider'
					) 
				),
				array(
					'param_name'  => 'list_column',
					'heading'     => __( 'List Column', 'noo' ),
					'dependency' => array( 'element' => 'display_style', 'value' => array( 'list2' ) ),
					'type'        => 'dropdown',
					'value'       => array(
						__( '3 Columns', 'noo' )      => '3',
						__( '4 Columns', 'noo' ) => '4'
					),
					'admin_label' => true,
					'holder'      => $param_holder
				),
				array(
					'param_name'  => 'btn_link',
					'heading'     => __( 'Button Link', 'noo' ),
					'dependency' => array( 'element' => 'display_style', 'value' => array( 'list2' ) ),
					'description' => __( 'Setup button will be show below list job.', 'noo' ),
					'type'        => 'vc_link',
					'holder'      => $param_holder
				),
				array(
					'param_name'  => 'title_type',
					'heading'     => __( 'Title Type', 'noo' ),
					'description' => __( 'You can choose preset title with Job count or add a new Text for title.', 'noo' ),
					'type'        => 'dropdown',
					'value'       => array(
						__( 'Text', 'noo' )      => 'text',
						__( 'Job count', 'noo' ) => 'job_count' 
					) 
				),
				array(
					'param_name'  => 'title',
					'heading'     => __( 'Title', 'noo' ),
					'dependency'  => array( 'element' => 'title_type', 'value' => array( 'text' ) ),
					'description' => __( 'Enter text which will be used as element title. Leave blank if no title is needed.', 'noo' ),
					'type'        => 'textfield',
					'admin_label' => true,
					'holder'      => $param_holder 
				),
				array(
					'param_name'  => 'posts_per_page',
					'heading'     => __( 'Posts per page', 'noo' ),
					'description' => __( 'Enter number item to show on the page.', 'noo' ),
					'type'        => 'textfield',
					'admin_label' => true,
					'value'       => '3',
					'holder'      => $param_holder 
				),
				array(
					'param_name' => 'job_category',
					'heading'    => __( 'Job Category', 'noo' ),
					'type'       => 'dropdown',
					'value'      => $job_category
				),
				array(
					'param_name' => 'job_type',
					'heading'    => __( 'Job Type', 'noo' ),
					'type'       => 'dropdown',
					'value'      => $job_type
				),
				array(
					'param_name' => 'job_location',
					'heading'    => __( 'Job Location', 'noo' ),
					'type'       => 'dropdown',
					'value'      => $job_location
				),
				array(
					'param_name'  => 'show_autoplay',
					'heading'     => __( 'Show Auto Play', 'noo' ),
					'dependency'  => array( 'element' => 'display_style', 'value' => array( 'slider' ) ),
					'description' => __( 'Turn on/off auto play.', 'noo' ),
					'type'        => 'dropdown',
					'value'       => array(
						__( 'On', 'noo' )  => 'on',
						__( 'Off', 'noo' ) => 'off' 
					)
				),
				array(
					'param_name' => 'slider_time',
					'heading'    => __( 'Slide Time (ms)', 'noo' ),
					'dependency' => array( 'element' => 'show_autoplay', 'value' => array( 'on' ) ),
					'type'       => 'ui_slider',
					'holder'     => $param_holder,
					'value'      => '3000',
					'data_min'   => '500',
					'data_max'   => '8000',
					'data_step'  => '100' 
				),
				array(
					'param_name' => 'slider_speed',
					'heading'    => __( 'Slide Speed (ms)', 'noo' ),
					'dependency' => array( 'element' => 'show_autoplay', 'value' => array( 'on' ) ),
					'type'       => 'ui_slider',
					'holder'     => $param_holder,
					'value'      => '600',
					'data_min'   => '100',
					'data_max'   => '3000',
					'data_step'  => '100' 
				),
				array(
					'param_name' => 'orderby',
					'heading' => __( 'Order by', 'noo' ),
					'type' => 'dropdown',
					'value' => array(
						__( 'Date', 'noo' )    => 'date',
						__( 'Popular', 'noo' ) => 'view',
						__( 'Featured', 'noo' ) => 'featured',
						__( 'Random', 'noo' )  => 'rand'
					)
				),
				array(
					'param_name' => 'order',
					'heading'    => __( 'Sort by', 'noo' ),
					'type'       => 'dropdown',
					'dependency' => array( 'element' => 'orderby', 'value' => array( 'date', 'view' ) ),
					'value'      => array(
						__( 'Recent', 'noo' ) => 'desc',
						__( 'Older', 'noo' )  => 'asc'
					)
				),
				array(
					'param_name'  => 'show_pagination',
					'heading'     => __( 'Show Pagination', 'noo' ),
					'dependency'  => array( 'element' => 'display_style', 'value' => array( 'list' ) ),
					'description' => __( 'Show/hide Pagination.', 'noo' ),
					'type'        => 'dropdown',
					'value'       => array(
						__( 'Hide', 'noo' ) => 'no',
						__( 'Show', 'noo' ) => 'yes' 
					)
				),
				array(

					'param_name'    => 'show_view_more',
					'heading'       => __( 'Show View More', 'noo' ),
					'dependency' => array( 'element' => 'display_style', 'value' => array( 'list', 'slider' ) ),
					'description'   => __( 'Show/hide read more.', 'noo' ),
					'type'          => 'dropdown',
					'value'         => array(
						__( 'Show', 'noo' ) => 'yes',
						__( 'Hide', 'noo' ) => 'no'
					)
				),
				array( 
					'param_name'  => $param_visibility_name, 
					'heading'     => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type'        => $param_visibility_type, 
					'holder'      => $param_visibility_holder, 
					'value'       => $param_visibility_value 
				), 
				array( 
					'param_name'  => $param_class_name, 
					'heading'     => $param_class_heading, 
					'description' => $param_class_description, 
					'type'        => $param_class_type, 
					'holder'      => $param_class_holder 
				), 
				array( 
					'param_name'  => $param_custom_style_name, 
					'heading'     => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type'        => $param_custom_style_type, 
					'holder'      => $param_custom_style_holder 
				) 
			) ;
			$noo_jobs_params = apply_filters( 'noo-jobs-shortcode-params', $noo_jobs_params );
			vc_map( 
				array( 
					'base' => 'noo_jobs', 
					'name' => __( 'Noo Jobs', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_jobs',
					'icon' => 'noo-icon fa fa-briefcase',
					'category' => $category_name, 
					'description' => '', 
					'params' => $noo_jobs_params
				) 
			);

			vc_map( 
				array( 
					'base' => 'noo_resume', 
					'name' => __( 'Noo Resume', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_resume', 
					'icon' => 'noo-icon fa fa-certificate',
					'category' => $category_name, 
					'description' => '', 
					'params' => array(
						// array(
						// 	'param_name' => 'display_style',
						// 	'heading' => __( 'Display Style', 'noo' ),
						// 	'dependency' => array( 'element' => 'show', 'value' => array( 'featured' ) ),
						// 	'type' => 'dropdown',
						// 	'value' => array(
						// 		__( 'Resume List', 'noo' ) => 'list',
						// 		__( 'Resume Slider', 'noo' ) => 'slider' 
						// 	) 
						// ),
						array(
							'param_name' => 'title',
							'heading' => __( 'Title', 'noo' ),
							'description' => __(
								'Enter text which will be used as element title. Leave blank if no title is needed.',
								'noo' ),
							'type' => 'textfield',
							'admin_label' => true,
							'holder' => $param_holder ),
						array(
							'param_name' => 'posts_per_page',
							'heading' => __( 'Posts per page', 'noo' ),
							'description' => __(
								'If you select display style Job Slider please enter value 1.',
								'noo' ),
							'type' => 'textfield',
							'admin_label' => true,
							'value' => '3',
							'holder' => $param_holder 
						),
						array(
							'param_name' => 'job_category',
							'heading' => __( 'Job Category', 'noo' ),
							'type' => 'dropdown',
							'value' => $job_resume_category
						),
						// array(
						// 	'param_name' => 'job_type',
						// 	'heading' => __( 'Job Type', 'noo' ),
						// 	'type' => 'dropdown',
						// 	'value' => $job_resume_type
						// ),
						array(
							'param_name' => 'job_location',
							'heading' => __( 'Job Location', 'noo' ),
							'type' => 'dropdown',
							'value' => $job_resume_location
						),
						array(
							'param_name' => 'orderby',
							'heading' => __( 'Order by', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Date', 'noo' ) => 'date',
								__( 'Popular', 'noo' ) => 'view' 
							)
						),
						array(
							'param_name' => 'order',
							'heading' => __( 'Sort by', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Recent', 'noo' ) => 'desc',
								__( 'Older', 'noo' ) => 'asc'
							)
						),
						array(
							'param_name' => 'show_pagination',
							'heading' => __( 'Show Pagination', 'noo' ),
							'description' => __( 'Show/hide Pagination.', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Hide', 'noo' ) => 'no',
								__( 'Show', 'noo' ) => 'yes' ) ),
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value 
						), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder 
						), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder 
						) 
					) 
				) 
			);

			vc_map(
				array(
					'base' => 'noo_counter',
					'name' => __( 'Noo Counter', 'noo' ),
					'class' => 'noo-vc-element noo-vc-element-noo_step_icon',
					'icon' => 'noo-icon fa fa-sort-numeric-asc',
					'category' => $category_name,
					'description' => '',
					'params' => array(
						array(
							'param_name'  => 'data',
							'heading'     => __( 'Data', 'noo' ),
							'type'        => 'dropdown',
							'admin_label' => true,
							'value'       => 'public_job',
							'value' => array(
								__( 'Public Jobs', 'noo' ) => 'public_job',
								__( 'Public Resumes', 'noo' ) => 'public_resume',
								__( 'All Jobs', 'noo' ) => 'all_job',
								__( 'All Resumes', 'noo' ) => 'all_resume',
								__( 'All Companies', 'noo' ) => 'all_company',
								__( 'All Applications', 'noo' ) => 'all_application',
								__( 'All Users', 'noo' ) => 'user_all',
								__( 'Candidate User', 'noo' ) => 'user_candidate',
								__( 'Employer User', 'noo' ) => 'user_employer',
								__( 'Custom Number', 'noo' ) => 'custom',
							),
							'holder'      => $param_holder
						),
						array(
							'param_name'  => 'number',
							'heading'     => __( 'Number', 'noo' ),
							'type'        => 'textfield',
							'value'       => '',
							'dependency' => array('element'=>'data','value'=>array('custom')),
							'holder'      => $param_holder
						),
						array(
							'param_name'  => 'text',
							'heading'     => __( 'Text', 'noo' ),
							'type'        => 'textfield',
							'value'       => '',
//							'dependency' => array('element'=>'data','value'=>array('custom')),
							'holder'      => $param_holder
						),
						array(
							'param_name'  => 'icon',
							'heading'     => __( 'Icon', 'noo' ),
							'type'        => 'iconpicker',
							'value'       => '',
							'holder'      => $param_holder
						),
						array(
							'param_name' => $param_visibility_name,
							'heading' => $param_visibility_heading,
							'description' => $param_visibility_description,
							'type' => $param_visibility_type,
							'holder' => $param_visibility_holder,
							'value' => $param_visibility_value
						),
						array(
							'param_name' => $param_class_name,
							'heading' => $param_class_heading,
							'description' => $param_class_description,
							'type' => $param_class_type,
							'holder' => $param_class_holder
						),
						array(
							'param_name' => $param_custom_style_name,
							'heading' => $param_custom_style_heading,
							'description' => $param_custom_style_description,
							'type' => $param_custom_style_type,
							'holder' => $param_custom_style_holder
						)
					)
				)
			);

			$search_field = array(
				__('None', 'noo')=>'no',
			);
			$custom_fields = jm_get_job_search_custom_fields();
			if($custom_fields){
				foreach ($custom_fields as $k=>$field){
					if( isset( $field['is_default'] ) ) {
						$label = isset($field['label']) ? $field['label'] : $k;
						$id = $field['name'];
						$search_field[$label] = $id;
					} else {
						$label = __('Custom Field: ','noo').( isset( $field['label_translated'] ) ? $field['label_translated'] : (isset($field['label']) ? $field['label'] : $k));
						$id = jm_job_custom_fields_name($field['name'],$field);
						$search_field[$label] = $id;
					}
				}
			}
			vc_map( 
				array( 
					'base' => 'noo_job_map', 
					'name' => __( 'Job Map', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_job_map',
					'icon' => 'noo-icon fa fa-map-marker',
					'category' => $category_name, 
					'description' => '',
					'show_settings_on_create' => true,
					'params' => array(
						array (
							'param_name' => 'map_height',
							'heading' => __( 'Map Height (px)', 'noo' ),
							'description' => __( 'The maximum height of the map', 'noo' ),
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '700',
							'data_min' => '400',
							'data_max' => '1200',
							'data_step' => '20'
						),
						array( 
							'param_name' => 'map_style', 
							'heading' => __( 'Map Style', 'noo' ), 
							'description' => __( 'Select your map style.', 'noo' ),
							'type' => 'dropdown', 
							'holder' => $param_holder,
							'std'=>'dark', 
							'value' => array(
								__( 'Dark', 'noo' ) => 'dark',
								__( 'Light', 'noo' ) => 'light',
								__( 'Nature', 'noo' ) => 'nature',
								__( 'Apple Map', 'noo' ) => 'apple',
								__( 'Default', 'noo' ) => 'none',
							),
						),
						array (
							'param_name' => 'center_latitude',
							'heading' => __( 'Center Latitude', 'noo' ),
							'description' => __( 'The Latitude of the Map\'s center point', 'noo' ),
							'type' => 'textfield',
							'holder' => $param_holder,
							'value' => '40.714398',
						),
						array (
							'param_name' => 'center_longitude',
							'heading' => __( 'Center Longitude', 'noo' ),
							'description' => __( 'The Longitude of the Map\'s center point', 'noo' ),
							'type' => 'textfield',
							'holder' => $param_holder,
							'value' => '-74.005279',
						),
						array (
							'param_name' => 'zoom',
							'heading' => __( 'Zoom level', 'noo' ),
							'description' => __( 'The default zoom level', 'noo' ),
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '12',
							'data_min' => '3',
							'data_max' => '20',
							'data_step' => '1', 
						),
						array( 
							'param_name' => 'fit_bounds', 
							'heading' => __( 'Fit all jobs', 'noo' ), 
							'description' => __( 'If enable, the map will ignore the above settings and try to show all your jobs.', 'noo' ),
							'type' => 'dropdown', 
							'holder' => $param_holder,
							'std'=>'yes', 
							'value' => array(
								__( 'Enable', 'noo' ) => 'yes',
								__( 'Disable', 'noo' ) => 'no',
							),
						),
						array(
							'param_name' => 'search_form',
							'heading' => __( 'Show Job Search', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Show', 'noo' ) => 'yes',
								__( 'Hide', 'noo' ) => 'no' ),
							),
						array(
							'param_name' => 'show_keyword',
							'heading' => __( 'Enable Keyword Search', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Show', 'noo' ) => 'yes',
								__( 'Hide', 'noo' ) => 'no' ),
							'dependency' => array('element'=>'search_form','value'=>array('yes'))
							),
						array(
							'param_name' => 'pos2',
							'heading' => __( 'Search Position 2', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_location',
							'value' => $search_field,
							'dependency' => array('element'=>'search_form','value'=>array('yes'))
							),
						array(
							'param_name' => 'pos3',
							'heading' => __( 'Search Position 3', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_category',
							'value' => $search_field,
							'dependency' => array('element'=>'search_form','value'=>array('yes'))
						),
						array(
							'param_name' => 'pos4',
							'heading' => __( 'Search Position 4', 'noo' ),
							'type' => 'dropdown',
							'std'=>'no',
							'value' => $search_field,
							'dependency' => array('element'=>'search_form','value'=>array('yes'))
						),
						// array(
						// 	'param_name' => 'show_location',
						// 	'heading' => __( 'Enable Location Search', 'noo' ),
						// 	'type' => 'dropdown',
						// 	'value' => array(
						// 		__( 'Show', 'noo' ) => 'yes',
						// 		__( 'Hide', 'noo' ) => 'no' ),
						// 	'dependency' => array('element'=>'search_form','value'=>array('yes'))
						// 	),
						// array(
						// 	'param_name' => 'show_category',
						// 	'heading' => __( 'Enable Category Search', 'noo' ),
						// 	'type' => 'dropdown',
						// 	'value' => array(
						// 		__( 'Show', 'noo' ) => 'yes',
						// 		__( 'Hide', 'noo' ) => 'no' ),
						// 	'dependency' => array('element'=>'search_form','value'=>array('yes'))
						// 	),
						// array(
						// 	'param_name' => 'show_type',
						// 	'heading' => __( 'Enable Type Search', 'noo' ),
						// 	'type' => 'dropdown',
						// 	'value' => array(
						// 		__( 'Hide', 'noo' ) => 'no',
						// 		__( 'Show', 'noo' ) => 'yes' ),
						// 	'dependency' => array('element'=>'search_form','value'=>array('yes'))
						// 	),
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value 
						), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder 
						), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder 
						)   )
				)
			);
			$resume_custom_fields = jm_get_resume_search_custom_fields();
			$resume_search_field = array(
				__('None', 'noo')=>'no',
			);
			if( $resume_custom_fields ) {
				foreach ($resume_custom_fields as $k=>$field){
					if( isset( $field['is_default'] ) ) {
						$label = ( isset( $field['label_translated'] ) ? $field['label_translated'] : (isset($field['label']) ? $field['label'] : $k));
						$id = $field['name'];
						$resume_search_field[$label] = $id;
					} else {
						$label = __('Custom Field: ','noo').( isset( $field['label_translated'] ) ? $field['label_translated'] : (isset($field['label']) ? $field['label'] : $k));
						
						$id = jm_resume_custom_fields_name($field['name'],$field);
						$resume_search_field[$label] = $id;
					}
				}
			}
			vc_map(
				array(
					'base' => 'noo_job_search',
					'name' => __( 'Advanced Search', 'noo' ),
					'weight' => 809,
					'class' => 'noo-vc-element noo-vc-element-noo_job_search',
					'icon' => 'noo-icon fa fa-search',
					'category' => $category_name,
					'description' => '',
					'show_settings_on_create' => true,
					'params' => array(
						array(
							'param_name' => 'title',
							'heading' => __( 'Main Title', 'noo' ),
							'type' => 'textfield',
							'holder' => $param_holder,
							'value' => __( 'Join us & Explore thousands of Jobs', 'noo' ) ),
						array(
							'param_name' => 'top_title',
							'heading' => __( 'Top Title', 'noo' ),
							'type' => 'textfield',
							'holder' => $param_holder,
							'value' => __( 'JobMonster WordPress Theme', 'noo' ) ),
						array(
							'param_name' => 'search_mode',
							'heading' => __( 'Search Form Layout', 'noo' ),
							'type' => 'dropdown',
							'admin_label'=>true,
							'value' => array(
								__( 'Horizontal', 'noo' ) => 'noo_horizontal',
								__( 'Vertical', 'noo' ) => 'noo_vertical' ),
						),
						array(
							'param_name' => 'background_type',
							'heading' => __( 'Background Type', 'noo' ),
							'type' => 'dropdown',
							'holder' => $param_holder,
							'std' => 'no_background',
							'value' => array(
								__( 'Revolution Slider', 'noo' ) => 'revo_slider',
								__( 'Image Slider', 'noo' ) => 'slider',
								__( 'One Image', 'noo' ) => 'image',
								__( 'None', 'noo' ) => 'no_background' ) ),
						array(
							'param_name' => 'revo_slider_id',
							'heading' => __( 'Revolution Slider', 'noo' ),
							'type' => 'noo_rev_slider',
							'admin_label'=>true,
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'revo_slider' ) )
						),
						array(
							'param_name' => 'slider_images',
							'heading' => __( 'Slider Images', 'noo' ),
							'type' => 'attach_images',
							'holder' => $param_holder,
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider' ) )
						),
						array (
							'param_name' => 'slider_animation',
							'heading' => __( 'Slider Animation', 'noo' ),
							'description' => '',
							'type' => 'dropdown',
							'holder' => $param_holder,
							'value' => array (
								__( 'Fade', 'noo' ) => 'fade',
								__( 'Slide', 'noo' ) => 'slide'
							),
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider' ) )
						),
						array (
							'param_name' => 'slider_time',
							'heading' => __( 'Slider Time (ms)', 'noo' ),
							'description' => '',
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '3000',
							'data_min' => '500',
							'data_max' => '8000',
							'data_step' => '100',
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider' ) )
						),
						array (
							'param_name' => 'slider_speed',
							'heading' => __( 'Slider Speed (ms)', 'noo' ),
							'description' => '',
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '600',
							'data_min' => '100',
							'data_max' => '3000',
							'data_step' => '100',
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider' ) )
						),
						array (
							'param_name' => 'slider_height',
							'heading' => __( 'Slider Max Height (px)', 'noo' ),
							'description' => __( 'The maximum height of the slider', 'noo' ),
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '600',
							'data_min' => '400',
							'data_max' => '1200',
							'data_step' => '20',
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider' ) )
						),
						array(
							'param_name' => 'background_image',
							'heading' => __( 'Image', 'noo' ),
							'type' => 'attach_image',
							'holder' => $param_holder,
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'image' ) ) ),
						array(
							'param_name'  => 'image_height_type',
							'heading'     => __( 'Image Height Type', 'noo' ),
							'description' => __( 'Select custom height or full-screen image', 'noo' ),
							'type'        => 'dropdown',
							'holder'      => $param_holder,
							'admin_label' =>true,
							'value' => array(
								__( 'Custom Height', 'noo' ) => 'noo_custom_height',
								__( 'Fullscreen', 'noo' )    => 'noo_fullscreen' ),
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'image' ) )
						),
						array(
							'param_name'  => 'image_height_custom',
							'heading'     => __( 'Image Max Height (px)', 'noo' ),
							'description' => __( 'Choose the height of the image, work only when Image Height type is custom.', 'noo' ),
							'type'        => 'ui_slider',
							'holder'      => $param_holder,
							'value'       => '500',
							'data_min'    => '400',
							'data_max'    => '1200',
							'data_step'   => '20',
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'image' ) )
						),
						array (
							'param_name' => 'search_position',
							'heading' => __( 'Search Form Top Position', 'noo' ),
							'description' => __( 'The top position of the search form base on the image backgroud.', 'noo' ),
							'type' => 'ui_slider',
							'holder' => $param_holder,
							'value' => '120',
							'data_min' => '50',
							'data_max' => '1000',
							'data_step' => '10',
							'dependency' => array( 'element' => 'background_type', 'value' => array( 'slider', 'image', 'revo_slider' ) )
						),
						array(
							'param_name' => 'search_type',
							'heading' => __( 'Search Post type', 'noo' ),
							'type' => 'dropdown',
							'admin_label'=>true,
							'value' => array(
								__( 'Job', 'noo' ) => 'noo_job',
								__( 'Resume', 'noo' ) => 'noo_resume' ),
						),
						array(
							'param_name' => 'show_keyword',
							'heading' => __( 'Enable Keyword Search', 'noo' ),
							'type' => 'dropdown',
							'value' => array(
								__( 'Show', 'noo' ) => 'yes',
								__( 'Hide', 'noo' ) => 'no' ),
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_resume')),
							'param_name' => 'r_pos2',
							'heading' => __( 'Search Position 2', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_location',
							'value' => $resume_search_field
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_resume')),
							'param_name' => 'r_pos3',
							'heading' => __( 'Search Position 3', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_category',
							'value' => $resume_search_field
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_resume')),
							'param_name' => 'r_pos4',
							'heading' => __( 'Search Position 4', 'noo' ),
							'type' => 'dropdown',
							'std'=>'no',
							'value' => $resume_search_field
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_job')),
							'param_name' => 'pos2',
							'heading' => __( 'Search Position 2', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_location',
							'value' => $search_field
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_job')),
							'param_name' => 'pos3',
							'heading' => __( 'Search Position 3', 'noo' ),
							'type' => 'dropdown',
							'std'=>'job_category',
							'value' => $search_field
						),
						array(
							'dependency' => array('element'=>'search_type','value'=>array('noo_job')),
							'param_name' => 'pos4',
							'heading' => __( 'Search Position 4', 'noo' ),
							'type' => 'dropdown',
							'std'=>'no',
							'value' => $search_field
						),
						array(
							'param_name' => $param_visibility_name,
							'heading' => $param_visibility_heading,
							'description' => $param_visibility_description,
							'type' => $param_visibility_type,
							'holder' => $param_visibility_holder,
							'value' => $param_visibility_value
						),
						array(
							'param_name' => $param_class_name,
							'heading' => $param_class_heading,
							'description' => $param_class_description,
							'type' => $param_class_type,
							'holder' => $param_class_holder
						),
						array(
							'param_name' => $param_custom_style_name,
							'heading' => $param_custom_style_heading,
							'description' => $param_custom_style_description,
							'type' => $param_custom_style_type,
							'holder' => $param_custom_style_holder
						)   )
				)
			);

			vc_map(
				array(
					'base' => 'noo_companies',
					'name' => __('Companies Listing', 'noo'),
					'weight' => 809,
					'class' => 'noo-vc-element noo-vc-element-noo_companies',
					'icon' => 'noo-icon fa fa-building-o',
					'category' => $category_name,
					'description' => '',
					'show_settings_on_create' => false,
					'params' => array(
						array(
							'param_name' => 'title',
							'heading' => __('Title', 'noo'),
							'type' => 'textfield',
							'holder' => $param_holder,
							'admin_label' => true,
							'value' => __('Companies', 'noo')
						),
						array(
							'param_name' => 'style',
							'heading' => __('Display Style', 'noo'),
							'description' => '',
							'type' => 'dropdown',
							'holder' => $param_holder,
							'value' => array(
								__('Alphabet', 'noo') => '',
								__('Grid', 'noo') => 'style2'
							)
						),
						array(
							'param_name' => 'number',
							'heading' => __('Number Company', 'noo'),
							'type' => 'textfield',
							'holder' => $param_holder,
							'admin_label' => true,
							'value' => 12,
							'dependency' => array( 'element' => 'style', 'value' => array( 'style2' ) ),
						),
					)
				)
			);

			vc_map( 
				array( 
					'base' => 'noo_company_feature', 
					'name' => __( 'Companies Featured', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_companies',
					'icon' => 'noo-icon fa fa-building-o',
					'category' => $category_name, 
					'description' => '',
//					'show_settings_on_create' => false,
					'params' => array(
						array( 
							'param_name' => 'title', 
							'heading'    => __( 'Title', 'noo' ), 
							'type'       => 'textfield', 
							'holder'     => $param_holder, 
							'value'      => __( 'Featured Employer', 'noo' ) 
						),
						array( 
							'param_name'  => 'posts_per_page', 
							'heading'     => __( 'Posts per page', 'noo' ), 
							'type'        => 'textfield', 
							'holder'      => $param_holder, 
							'value'       => __( '-1', 'noo' ),
							'description' => __( 'Default value -1 show all company featured', 'noo' ),
						),
						array( 
							'param_name'  => 'autoplay', 
							'heading'     => __( 'Auto Play', 'noo' ), 
							'description' => '', 
							'type'        => 'dropdown', 
							'holder'      => $param_holder, 
							'value'       => array(
								__( 'No', 'noo' )  => 'false',
								__( 'Yes', 'noo' ) => 'true'
							) 
						),
						array(
							'param_name' => 'slider_speed',
							'heading'    => __( 'Slide Speed (ms)', 'noo' ),
							'dependency' => array( 'element' => 'autoplay', 'value' => array( 'true' ) ),
							'type'       => 'ui_slider',
							'holder'     => $param_holder,
							'value'      => '800',
							'data_min'   => '300',
							'data_max'   => '3000',
							'data_step'  => '100' 
						),
						array( 
							'param_name' => 'content', 
							'heading'    => __( 'Content', 'noo' ), 
							'type'       => 'textarea_html', 
							'holder'     => $param_holder, 
							'value'      => ''
						),
					)
				)
			);

			vc_map( 
				array( 
					'base' => 'noo_job_package_list', 
					'name' => __( 'Job Packages', 'noo' ), 
					'weight' => 810, 
					'class' => 'noo-vc-element noo-vc-element-pricing_table', 
					'icon' => 'noo-icon fa fa-money',
					'category' => $category_name, 
					'description' => '',
					'show_settings_on_create' => false,
					'params' => array(
						array( 
							'param_name'  => 'product_cat', 
							'heading'     => __( 'Product Categories', 'noo' ), 
							'type'        => 'product_categories', 
							'holder'      => $param_holder, 
							'description' => __( 'Leave it blank to show all categories', 'noo' ),
						),
						array( 
							'param_name'  => 'columns', 
							'heading' => __( 'Number of Columns', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder,
							'admin_label' => true,
							'value' => array( 
								__( 'Automatically', 'noo' ) => '',
								__( 'One', 'noo' ) => '1', 
								__( 'Two', 'noo' ) => '2', 
								__( 'Three', 'noo' ) => '3', 
								__( 'Four', 'noo' ) => '4', 
								__( 'Five', 'noo' ) => '5', 
								__( 'Six', 'noo' ) => '6'
							)
						), 
					)
				)
			);

			vc_map( 
				array( 
					'base' => 'noo_resume_package_list', 
					'name' => __( 'Resume Packages', 'noo' ), 
					'weight' => 810, 
					'class' => 'noo-vc-element noo-vc-element-pricing_table', 
					'icon' => 'noo-icon fa fa-money',
					'category' => $category_name, 
					'description' => '',
					'show_settings_on_create' => false,
					'params' => array(
						array( 
							'param_name'  => 'product_cat', 
							'heading'     => __( 'Product Categories', 'noo' ), 
							'type'        => 'product_categories', 
							'holder'      => $param_holder, 
							'description' => __( 'Leave it blank to show all categories', 'noo' ),
						),
						array( 
							'param_name'  => 'columns', 
							'heading' => __( 'Number of Columns', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder,
							'admin_label' => true,
							'value' => array(
								__( 'Automatically', 'noo' ) => '',
								__( 'One', 'noo' ) => '1', 
								__( 'Two', 'noo' ) => '2', 
								__( 'Three', 'noo' ) => '3', 
								__( 'Four', 'noo' ) => '4', 
								__( 'Five', 'noo' ) => '5', 
								__( 'Six', 'noo' ) => '6'
							)
						), 
					)
				)
			);

			// Noo List Job Category
			vc_map(
				array(
					'base' => 'noo_list_job_category',
					'name' => __('Job Category List', 'noo'),
					'weight' => 810,
					'class' => 'noo-vc-element noo-vc-element-noo_icon_box',
					'icon' => 'noo-icon fa fa-th-list',
					'category' => $category_name,
					'description' => '',
					'show_settings_on_create' => false,
					'params' => array(
						array(
							'param_name' => 'title',
							'heading' => __('Heading Title', 'noo'),
							'type' => 'textfield',
							'admin_label' => true,
							'holder' => $param_holder,
						),
						array(
							'param_name' => 'list_job_category',
							'heading' => __('List Job Category', 'noo'),
							'type' => 'job_category',
							'admin_label' => true,
							'holder' => $param_holder,
						),
						array(
							'param_name'  => 'list_column',
							'heading'     => __( 'List Column', 'noo' ),
							'type'        => 'dropdown',
							'value'       => array(
								__( '4 Columns', 'noo' ) => '4',
								__( '3 Columns', 'noo' )      => '3'
							),
							'admin_label' => true,
							'holder'      => $param_holder
						),
						array(
							'param_name' => $param_visibility_name,
							'heading' => $param_visibility_heading,
							'description' => $param_visibility_description,
							'type' => $param_visibility_type,
							'holder' => $param_visibility_holder,
							'value' => $param_visibility_value
						),
						array(
							'param_name' => $param_class_name,
							'heading' => $param_class_heading,
							'description' => $param_class_description,
							'type' => $param_class_type,
							'holder' => $param_class_holder
						),
						array(
							'param_name' => $param_custom_style_name,
							'heading' => $param_custom_style_heading,
							'description' => $param_custom_style_description,
							'type' => $param_custom_style_type,
							'holder' => $param_custom_style_holder
						)
					)
				)
			);

			// [noo_testimonial]
			// ============================
			vc_map( 
				array( 
					'base' => 'noo_testimonial', 
					'name' => __( 'Noo Testimonial', 'noo' ), 
					'weight' => 809, 
					'class' => 'noo-vc-element noo-vc-element-noo_testimonial',
					'icon' => 'noo-icon fa fa-users',
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						// array( 
						// 	'param_name' => 'title', 
						// 	'heading' => __( 'Testimonial Title', 'noo' ), 
						// 	'type' => 'textfield', 
						// 	'holder' => $param_holder, 
						// 	'value' => '' 
						// ), 
						
						array( 
							'param_name'  => 'style', 
							'heading'     => __( 'Display Style', 'noo' ), 
							'description' => '', 
							'type'        => 'dropdown', 
							'holder'      => $param_holder, 
							'value'       => array( 
								__( 'Style 1', 'noo' ) => '1', 
								__( 'Style 2', 'noo' ) => '2' 
							) 
						),
						array( 
							'param_name'  => 'image_per_page', 
							'heading'     => __( 'Testimonial Image Per Page', 'noo' ), 
							'type'        => 'textfield', 
							'description' => 'Number images of customers displayed per page', 
							'holder'      => $param_holder, 
							'value'       => '3',
							'dependency'  => array( 
								'element' => 'style', 
								'value'   => array( '1' ) 
							)
						), 
						array( 
							'param_name'  => 'autoplay', 
							'heading'     => __( 'Auto Play', 'noo' ), 
							'description' => '', 
							'type'        => 'dropdown', 
							'holder'      => $param_holder, 
							'value'       => array( 
								__( 'Yes', 'noo' ) => 'true', 
								__( 'No', 'noo' )  => 'false' 
							) 
						),
						array(
							'param_name' => 'slider_speed',
							'heading'    => __( 'Slide Speed (ms)', 'noo' ),
							'dependency' => array( 'element' => 'autoplay', 'value' => array( 'true' ) ),
							'type'       => 'ui_slider',
							'holder'     => $param_holder,
							'value'      => '800',
							'data_min'   => '300',
							'data_max'   => '3000',
							'data_step'  => '100' 
						),
						array( 
							'param_name'  => 'hidden_pagination', 
							'heading'     => __( 'Hidden pagination', 'noo' ), 
							'description' => '', 
							'type'        => 'dropdown', 
							'holder'      => $param_holder, 
							'value'       => array( 
								__( 'Yes', 'noo' ) => 'true', 
								__( 'No', 'noo' ) => 'false' 
							) 
						)
					)
				) 
			);
			
			// === << action shortcode
			// do_action( 'add_noo_vc_map' );
		}
		add_action( 'admin_init', 'noo_vc_jobmonster' );
	}
	//
	// Extend container class (parents).
	//
	
	if ( ! function_exists( 'noo_vc_typography' ) ) :

		function noo_vc_typography() {
			
			//
			// Variables.
			//
			$category_base_element = __( 'Base Elements', 'noo' );
			$category_typography = __( 'Typography', 'noo' );
			$category_content = __( 'Content', 'noo' );
			$category_wp_content = __( 'WordPress Content', 'noo' );
			$category_media = __( 'Media', 'noo' );
			$category_custom = __( 'Custom', 'noo' );
			
			$param_content_name = 'content';
			$param_content_heading = __( 'Text', 'noo' );
			$param_content_description = __( 'Enter your text.', 'noo' );
			$param_content_type = 'textarea_html';
			$param_content_holder = 'div';
			$param_content_value = '';
			
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array( 
				__( 'All Devices', 'noo' ) => "all", 
				__( 'Hidden Phone', 'noo' ) => "hidden-phone", 
				__( 'Hidden Tablet', 'noo' ) => "hidden-tablet", 
				__( 'Hidden PC', 'noo' ) => "hidden-pc", 
				__( 'Visible Phone', 'noo' ) => "visible-phone", 
				__( 'Visible Tablet', 'noo' ) => "visible-tablet", 
				__( 'Visible PC', 'noo' ) => "visible-pc" );
			
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
			
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			
			$param_icon_value = array();
			
			$param_social_icon_value = array( 
				'' => '', 
				__( 'ADN', 'noo' ) => 'fa-adn', 
				__( 'Android', 'noo' ) => 'fa-android', 
				__( 'Apple', 'noo' ) => 'fa-apple', 
				__( 'Bitbucket', 'noo' ) => 'fa-bitbucket', 
				__( 'Bitbucket-Sign', 'noo' ) => 'fa-bitbucket-sign', 
				__( 'Bitcoin', 'noo' ) => 'fa-bitcoin', 
				__( 'BTC', 'noo' ) => 'fa-btc', 
				__( 'CSS3', 'noo' ) => 'fa-css3', 
				__( 'Dribbble', 'noo' ) => 'fa-dribbble', 
				__( 'Dropbox', 'noo' ) => 'fa-dropbox', 
				__( 'Facebook', 'noo' ) => 'fa-facebook', 
				__( 'Facebook-Sign', 'noo' ) => 'fa-facebook-sign', 
				__( 'Flickr', 'noo' ) => 'fa-flickr', 
				__( 'Foursquare', 'noo' ) => 'fa-foursquare', 
				__( 'GitHub', 'noo' ) => 'fa-github', 
				__( 'GitHub-Alt', 'noo' ) => 'fa-github-alt', 
				__( 'GitHub-Sign', 'noo' ) => 'fa-github-sign', 
				__( 'Gittip', 'noo' ) => 'fa-gittip', 
				__( 'Google Plus', 'noo' ) => 'fa-google-plus', 
				__( 'Google Plus-Sign', 'noo' ) => 'fa-google-plus-sign', 
				__( 'HTML5', 'noo' ) => 'fa-html5', 
				__( 'Instagram', 'noo' ) => 'fa-instagram', 
				__( 'LinkedIn', 'noo' ) => 'fa-linkedin', 
				__( 'LinkedIn-Sign', 'noo' ) => 'fa-linkedin-sign', 
				__( 'Linux', 'noo' ) => 'fa-linux', 
				__( 'MaxCDN', 'noo' ) => 'fa-maxcdn', 
				__( 'Pinterest', 'noo' ) => 'fa-pinterest', 
				__( 'Pinterest-Sign', 'noo' ) => 'fa-pinterest-sign', 
				__( 'Renren', 'noo' ) => 'fa-renren', 
				__( 'Skype', 'noo' ) => 'fa-skype', 
				__( 'StackExchange', 'noo' ) => 'fa-stackexchange', 
				__( 'Trello', 'noo' ) => 'fa-trello', 
				__( 'Tumblr', 'noo' ) => 'fa-tumblr', 
				__( 'Tumblr-Sign', 'noo' ) => 'fa-tumblr-sign', 
				__( 'Twitter', 'noo' ) => 'fa-twitter', 
				__( 'Twitter-Sign', 'noo' ) => 'fa-twitter-sign', 
				__( 'VK', 'noo' ) => 'fa-vk', 
				__( 'Weibo', 'noo' ) => 'fa-weibo', 
				__( 'Windows', 'noo' ) => 'fa-windows', 
				__( 'Xing', 'noo' ) => 'fa-xing', 
				__( 'Xing-Sign', 'noo' ) => 'fa-xing-sign', 
				__( 'YouTube', 'noo' ) => 'fa-youtube', 
				__( 'YouTube Play', 'noo' ) => 'fa-youtube-play', 
				__( 'YouTube-Sign', 'noo' ) => 'fa-youtube-sign' );
			
			$param_holder = 'div';
			
			// [vc_column_text] ( Text Block )
			// ============================
			vc_map_update( 
				'vc_column_text', 
				array( 
					'category' => $category_typography, 
					'class' => 'noo-vc-element noo-vc-element-text_block', 
					'icon' => 'noo-vc-icon-text_block', 
					'weight' => 890 ) );
			
			vc_remove_param( 'vc_column_text', 'css_animation' );
			vc_remove_param( 'vc_column_text', 'el_class' );
			vc_remove_param( 'vc_column_text', 'css' );
			
			vc_add_param( 
				'vc_column_text', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_column_text', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_column_text', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_button]
			// ============================
			vc_map_update( 
				'vc_button', 
				array( 
					'category' => $category_typography, 
					'weight' => 880, 
					'class' => 'noo-vc-element noo-vc-element-button', 
					'icon' => 'noo-vc-icon-button' ) );
			
			vc_remove_param( 'vc_button', 'color' );
			vc_remove_param( 'vc_button', 'icon' );
			vc_remove_param( 'vc_button', 'size' );
			vc_remove_param( 'vc_button', 'el_class' );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'target', 
					'heading' => __( 'Open in new tab', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'size', 
					'heading' => __( 'Size', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Extra Small', 'noo' ) => 'x_small', 
						__( 'Small', 'noo' ) => 'small', 
						__( 'Medium', 'noo' ) => 'medium', 
						__( 'Large', 'noo' ) => 'large', 
						__( 'Custom', 'noo' ) => 'custom' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'fullwidth', 
					'heading' => __( 'Forge Full-Width', 'noo' ), 
					'description' => '', 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'vertical_padding', 
					'heading' => __( 'Vertical Padding (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '10', 
					'data_min' => '0', 
					'data_max' => '50', 
					'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'horizontal_padding', 
					'heading' => __( 'Horizontal Padding (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '10', 
					'data_min' => '0', 
					'data_max' => '50', 
					'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'icon', 
					'heading' => __( 'Icon', 'noo' ), 
					'type' => 'iconpicker', 
					'holder' => $param_holder, 
					'value' => '' ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'icon_right', 
					'heading' => __( 'Right Icon', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'icon_only', 
					'heading' => __( 'Show only Icon', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'icon_color', 
					'heading' => __( 'Icon Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'shape', 
					'heading' => __( 'Shape', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Square', 'noo' ) => 'square', 
						__( 'Rounded', 'noo' ) => 'rounded', 
						__( 'Pill', 'noo' ) => 'pill' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'style', 
					'heading' => __( 'Style', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( '3D Pressable', 'noo' ) => 'pressable', 
						__( 'Metro', 'noo' ) => 'metro', 
						__( 'Blank', 'noo' ) => '' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'skin', 
					'heading' => __( 'Skin', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Default', 'noo' ) => 'default', 
						__( 'Primary', 'noo' ) => 'primary', 
						__( 'Custom', 'noo' ) => 'custom', 
						__( 'White', 'noo' ) => 'white', 
						__( 'Black', 'noo' ) => 'black', 
						__( 'Success', 'noo' ) => 'success', 
						__( 'Info', 'noo' ) => 'info', 
						__( 'Warning', 'noo' ) => 'warning', 
						__( 'Danger', 'noo' ) => 'danger', 
						__( 'Link', 'noo' ) => 'link' ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'text_color', 
					'heading' => __( 'Text Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'hover_text_color', 
					'heading' => __( 'Hover Text Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'bg_color', 
					'heading' => __( 'Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'hover_bg_color', 
					'heading' => __( 'Hover Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'border_color', 
					'heading' => __( 'Border Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => 'hover_border_color', 
					'heading' => __( 'Hover Border Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_button', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [dropcap]
			// ============================
			vc_map( 
				array( 
					'base' => 'dropcap', 
					'name' => __( 'Dropcap', 'noo' ), 
					'weight' => 860, 
					'class' => 'noo-vc-element noo-vc-element-dropcap', 
					'icon' => 'noo-vc-icon-dropcap', 
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'letter', 
							'heading' => __( 'Letter', 'noo' ), 
							'description' => '', 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'color', 
							'heading' => __( 'Letter Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'style', 
							'heading' => __( 'Style', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Transparent', 'noo' ) => 'transparent', 
								__( 'Filled Square', 'noo' ) => 'square', 
								__( 'Filled Circle', 'noo' ) => 'circle' ) ), 
						array( 
							'param_name' => 'bg_color', 
							'heading' => __( 'Background Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'square', 'circle' ) ) ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			


			// [quote]
			// ============================
			vc_map( 
				array( 
					'base' => 'quote', 
					'name' => __( 'Quote', 'noo' ), 
					'weight' => 850, 
					'class' => 'noo-vc-element noo-vc-element-quote', 
					'icon' => 'noo-vc-icon-quote', 
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'Quote', 'noo' ), 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => 'cite', 
							'heading' => __( 'Cite', 'noo' ), 
							'description' => __( 'Who originally said this.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'type', 
							'heading' => __( 'Type', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Block Quote', 'noo' ) => 'block', 
								__( 'Block Comma', 'noo' ) => 'comma',
								__( 'Pull Quote', 'noo' ) => 'pull' ) ),
						array( 
							'param_name' => 'alignment', 
							'heading' => __( 'Alignment', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Left', 'noo' ) => 'left', 
								__( 'Center', 'noo' ) => 'center', 
								__( 'Right', 'noo' ) => 'right' ) ), 
						array( 
							'param_name' => 'position', 
							'heading' => __( 'Position', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Left', 'noo' ) => 'left', 
								__( 'Right', 'noo' ) => 'right' ), 
							'dependency' => array( 'element' => 'type', 'value' => array( 'pull' ) ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [icon]
			// ============================
			vc_map( 
				array( 
					'base' => 'icon', 
					'name' => __( 'Icon', 'noo' ), 
					'weight' => 840, 
					'class' => 'noo-vc-element noo-vc-element-icon', 
					'icon' => 'noo-vc-icon-icon', 
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'icon', 
							'heading' => __( 'Icon', 'noo' ), 
							'description' => '', 
							'type' => 'iconpicker',
							'admin_label' => true,
							'holder' => $param_holder, 
							'value' => '' ), 
						array( 
							'param_name' => 'href', 
							'heading' => __( 'Icon URL', 'noo' ), 
							'description' => '', 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'size', 
							'heading' => __( 'Size', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Normal', 'noo' ) => '', 
								__( 'Large', 'noo' ) => 'lg', 
								__( 'Double', 'noo' ) => '2x', 
								__( 'Triple', 'noo' ) => '3x', 
								__( 'Quadruple', 'noo' ) => '4x', 
								__( 'Quintuple', 'noo' ) => '5x', 
								__( 'Custom', 'noo' ) => 'custom' ) ),
						array(
							'param_name' => 'position',
							'heading' => __( 'Position', 'noo' ),
							'description' => '',
							'type' => 'dropdown',
							'admin_label' => true,
							'holder' => $param_holder,
							'value' => array(
								__( 'Default (Left)', 'noo' ) => 'left',
								__( 'Center', 'noo' ) => 'center'
							)),
						array( 
							'param_name' => 'custom_size', 
							'heading' => __( 'Custom Size', 'noo' ), 
							'description' => '', 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '50', 
							'data_min' => '10', 
							'data_max' => '200', 
							'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'icon_color', 
							'heading' => __( 'Icon Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'hover_icon_color', 
							'heading' => __( 'Hover Icon Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'shape', 
							'heading' => __( 'Icon Shape', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Circle', 'noo' ) => 'circle', 
								__( 'Square', 'noo' ) => 'square' ) ), 
						array( 
							'param_name' => 'style', 
							'heading' => __( 'Style', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Simple', 'noo' ) => 'simple', 
								__( 'Filled Stack', 'noo' ) => 'stack_filled', 
								__( 'Bordered Stack', 'noo' ) => 'stack_bordered', 
								__( 'Custom', 'noo' ) => 'custom' ) ), 
						array( 
							'param_name' => 'bg_color', 
							'heading' => __( 'Background Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'hover_bg_color', 
							'heading' => __( 'Hover Background Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'border_color', 
							'heading' => __( 'Border Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'hover_border_color', 
							'heading' => __( 'Hover Border Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [social_icon]
			// ============================
			vc_map( 
				array( 
					'base' => 'social_icon', 
					'name' => __( 'Social Icon', 'noo' ), 
					'weight' => 835, 
					'class' => 'noo-vc-element noo-vc-element-social_icon', 
					'icon' => 'noo-vc-icon-social_icon', 
					'deprecated' => '4.9',
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'icon', 
							'heading' => __( 'Select Icon', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => $param_social_icon_value ), 
						array( 
							'param_name' => 'href', 
							'heading' => __( 'Social Profile URL', 'noo' ), 
							'description' => '', 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'target', 
							'heading' => __( 'Open in New Tab', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'size', 
							'heading' => __( 'Size', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Normal', 'noo' ) => '', 
								__( 'Large', 'noo' ) => 'large', 
								__( 'Double', 'noo' ) => '2x', 
								__( 'Triple', 'noo' ) => '3x', 
								__( 'Quadruple', 'noo' ) => '4x', 
								__( 'Quintuple', 'noo' ) => '5x', 
								__( 'Custom', 'noo' ) => 'custom' ) ), 
						array( 
							'param_name' => 'custom_size', 
							'heading' => __( 'Custom Size', 'noo' ), 
							'description' => '', 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '50', 
							'data_min' => '10', 
							'data_max' => '200', 
							'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'icon_color', 
							'heading' => __( 'Icon Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'hover_icon_color', 
							'heading' => __( 'Hover Icon Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'shape', 
							'heading' => __( 'Icon Shape', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Circle', 'noo' ) => 'circle', 
								__( 'Square', 'noo' ) => 'square' ) ), 
						array( 
							'param_name' => 'style', 
							'heading' => __( 'Style', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Simple', 'noo' ) => 'simple', 
								__( 'Filled Stack', 'noo' ) => 'stack_filled', 
								__( 'Bordered Stack', 'noo' ) => 'stack_bordered', 
								__( 'Custom', 'noo' ) => 'custom' ) ), 
						array( 
							'param_name' => 'bg_color', 
							'heading' => __( 'Background Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'hover_bg_color', 
							'heading' => __( 'Hover Background Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'border_color', 
							'heading' => __( 'Border Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'hover_border_color', 
							'heading' => __( 'Hover Border Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [icon_list]
			// ============================
			vc_map( 
				array( 
					'base' => 'icon_list', 
					'name' => __( 'Icon List', 'noo' ), 
					'weight' => 830, 
					'class' => 'noo-vc-element noo-vc-element-icon_list', 
					'icon' => 'noo-vc-icon-icon_list',
					'deprecated' => '4.9',
					'category' => $category_typography, 
					'description' => '', 
					'show_settings_on_create' => false, 
					'as_parent' => array( 'only' => 'icon_list_item' ), 
					'content_element' => true, 
					'js_view' => 'VcColumnView', 
					'params' => array( 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [icon_list_item]
			// ============================
			vc_map( 
				array( 
					'base' => 'icon_list_item', 
					'name' => __( 'Icon List Item', 'noo' ), 
					'weight' => 825, 
					'class' => 'noo-vc-element noo-vc-element-icon_list_item', 
					'icon' => 'noo-vc-icon-icon_list_item', 
					'deprecated' => '4.9',
					'category' => $category_typography, 
					'description' => '', 
					'as_child' => array( 'only' => 'icon_list' ), 
					'content_element' => true, 
					'params' => array( 
						array( 
							'param_name' => 'icon', 
							'heading' => __( 'Icon', 'noo' ), 
							'description' => '', 
							'type' => 'iconpicker', 
							'holder' => $param_holder, 
							'value' => '' ), 
						array( 
							'param_name' => 'icon_size', 
							'heading' => __( 'Icon Size (px)', 'noo' ), 
							'description' => __( 
								'Leave it empty or 0 to use the base size of your theme.', 
								'noo' ), 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '', 
							'data_min' => '0', 
							'data_max' => '60' ), 
						array( 
							'param_name' => 'icon_color', 
							'heading' => __( 'Icon Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'text_same_size', 
							'heading' => __( 'Text has Same Size as Icon', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Yes', 'noo' ) => 'true', 
								__( 'No', 'noo' ) => 'false' ) ), 
						array( 
							'param_name' => 'text_size', 
							'heading' => __( 'Text Size (px)', 'noo' ), 
							'description' => __( 
								'Leave it empty or 0 to use the base size of your theme.', 
								'noo' ), 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '', 
							'data_min' => '0', 
							'data_max' => '60', 
							'dependency' => array( 'element' => 'text_same_size', 'value' => array( 'false' ) ) ), 
						array( 
							'param_name' => 'text_same_color', 
							'heading' => __( 'Text has Same Color as Icon', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Yes', 'noo' ) => 'true', 
								__( 'No', 'noo' ) => 'false' ) ), 
						array( 
							'param_name' => 'text_color', 
							'heading' => __( 'Text Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'text_same_color', 'value' => array( 'false' ) ) ), 
						array( 
							'param_name' => $param_content_name, 
							'heading' => $param_content_heading, 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [label]
			// ============================
			vc_map( 
				array( 
					'base' => 'label', 
					'name' => __( 'Label', 'noo' ), 
					'weight' => 820, 
					'class' => 'noo-vc-element noo-vc-element-label', 
					'icon' => 'noo-vc-icon-label', 
					'deprecated' => '4.9',
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'word', 
							'heading' => __( 'Word', 'noo' ), 
							'description' => '', 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'color', 
							'heading' => __( 'Color', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Default', 'noo' ) => 'default', 
								__( 'Custom', 'noo' ) => 'custom', 
								__( 'Primary', 'noo' ) => 'primary', 
								__( 'Success', 'noo' ) => 'success', 
								__( 'Info', 'noo' ) => 'info', 
								__( 'Warning', 'noo' ) => 'warning', 
								__( 'Danger', 'noo' ) => 'danger' ) ), 
						array( 
							'param_name' => 'custom_color', 
							'heading' => __( 'Custom Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'color', 'value' => array( 'custom' ) ) ), 
						array( 
							'param_name' => 'rounded', 
							'heading' => __( 'Rounded Corner', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [code]
			// ============================
			vc_map( 
				array( 
					'base' => 'code', 
					'name' => __( 'Code Block', 'noo' ), 
					'weight' => 810, 
					'class' => 'noo-vc-element noo-vc-element-code', 
					'icon' => 'noo-vc-icon-code', 
					'category' => $category_typography, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'Put your code here', 'noo' ), 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
		}
		
		add_action( 'admin_init', 'noo_vc_typography' );
		
		//
		// Extend container class (parents).
		//
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {

			class WPBakeryShortCode_Icon_List extends WPBakeryShortCodesContainer {
			}
		}
		
		//
		// Extend item class (children).
		//
		if ( class_exists( 'WPBakeryShortCode' ) ) {

			class WPBakeryShortCode_Icon_List_Item extends WPBakeryShortCode {
			}
		}
	

	endif;
	
	if ( ! function_exists( 'noo_vc_content' ) ) :

		function noo_vc_content() {
			
			//
			// Variables.
			//
			$category_base_element = __( 'Base Elements', 'noo' );
			$category_typography = __( 'Typography', 'noo' );
			$category_content = __( 'Content', 'noo' );
			$category_wp_content = __( 'WordPress Content', 'noo' );
			$category_media = __( 'Media', 'noo' );
			$category_custom = __( 'Custom', 'noo' );
			
			$param_content_name = 'content';
			$param_content_heading = __( 'Text', 'noo' );
			$param_content_description = __( 'Enter your text.', 'noo' );
			$param_content_type = 'textarea_html';
			$param_content_holder = 'div';
			$param_content_value = '';
			
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array( 
				__( 'All Devices', 'noo' ) => "all", 
				__( 'Hidden Phone', 'noo' ) => "hidden-phone", 
				__( 'Hidden Tablet', 'noo' ) => "hidden-tablet", 
				__( 'Hidden PC', 'noo' ) => "hidden-pc", 
				__( 'Visible Phone', 'noo' ) => "visible-phone", 
				__( 'Visible Tablet', 'noo' ) => "visible-tablet", 
				__( 'Visible PC', 'noo' ) => "visible-pc" );
			
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
			
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			
			$param_icon_value = array();
			
			$param_holder = 'div';
			
			// [vc_accordion]
			// ============================
			vc_map_update( 
				'vc_accordion', 
				array( 'category' => $category_content, 'weight' => 790 ) );
			
			vc_remove_param( 'vc_accordion', 'collapsible' );
			vc_remove_param( 'vc_accordion', 'el_class' );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => 'title', 
					'heading' => __( 'Title (optional)', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => 'active_tab', 
					'heading' => __( 'Active Tab', 'noo' ), 
					'description' => __( 
						'The tab number to be active on load, default is 1. Enter -1 to collapse all tabs.', 
						'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => 'icon_style', 
					'heading' => __( 'Icon Style', 'noo' ), 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Dark Circle', 'noo' ) => 'dark_circle', 
						__( 'Light Circle', 'noo' ) => 'light_circle', 
						__( 'Dark Square', 'noo' ) => 'dark_square', 
						__( 'Light Square', 'noo' ) => 'light_square', 
						__( 'Simple Icon', 'noo' ) => 'simple', 
						__( 'Left Arrow', 'noo' ) => 'left_arrow', 
						__( 'Right Arrow', 'noo' ) => 'right_arrow' ) ) );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_accordion', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [vc_tabs]
			// ============================
			vc_remove_element('vc_tabs');
			// vc_map_update( 'vc_tabs', array( 'category' => $category_content, 'weight' => 780 ) );
			
			// vc_remove_param( 'vc_tabs', 'interval' );
			// vc_remove_param( 'vc_tabs', 'el_class' );
			
			// vc_add_param( 
			// 	'vc_tabs', 
			// 	array( 
			// 		'param_name' => 'title', 
			// 		'heading' => __( 'Title (optional)', 'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder ) );
			// vc_add_param(
			// 	'vc_tabs',
			// 	array(
			// 	'param_name' => 'control_position',
			// 	'heading' => __( 'Control Position', 'noo' ),
			// 	'type' => 'dropdown',
			// 	'holder' => $param_holder,
			// 	'value' => array(
			// 		__( 'Left', 'noo' ) => 'left',
			// 		__( 'Right', 'noo' ) => 'right',
			// 		)
			// 	) 
			// );
			// vc_add_param( 
			// 	'vc_tabs', 
			// 	array( 
			// 		'param_name' => 'active_tab', 
			// 		'heading' => __( 'Active Tab', 'noo' ), 
			// 		'description' => __( 
			// 			'The tab number to be active on load, default is 1. Enter -1 to collapse all tabs.', 
			// 			'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_tabs', 
			// 	array( 
			// 		'param_name' => $param_visibility_name, 
			// 		'heading' => $param_visibility_heading, 
			// 		'description' => $param_visibility_description, 
			// 		'type' => $param_visibility_type, 
			// 		'holder' => $param_visibility_holder, 
			// 		'value' => $param_visibility_value ) );
			
			// vc_add_param( 
			// 	'vc_tabs', 
			// 	array( 
			// 		'param_name' => $param_class_name, 
			// 		'heading' => $param_class_heading, 
			// 		'description' => $param_class_description, 
			// 		'type' => $param_class_type, 
			// 		'holder' => $param_class_holder ) );
			
			// vc_add_param( 
			// 	'vc_tabs', 
			// 	array( 
			// 		'param_name' => $param_custom_style_name, 
			// 		'heading' => $param_custom_style_heading, 
			// 		'description' => $param_custom_style_description, 
			// 		'type' => $param_custom_style_type, 
			// 		'holder' => $param_custom_style_holder ) );
			
			// vc_add_param( 
			// 	'vc_tab', 
			// 	array( 
			// 		'param_name' => 'icon', 
			// 		'heading' => __( 'Icon', 'noo' ), 
			// 		'type' => 'iconpicker', 
			// 		'holder' => $param_holder, 
			// 		'value' => '' ) );
			
			// [vc_tour]
			// ============================
			vc_map_update( 'vc_tour', array( 'category' => $category_content, 'weight' => 770 ) );
			
			vc_remove_param( 'vc_tour', 'interval' );
			vc_remove_param( 'vc_tour', 'el_class' );
			
			vc_add_param( 
				'vc_tour', 
				array( 
					'param_name' => 'title', 
					'heading' => __( 'Title (optional)', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_tour', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_tour', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_tour', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [block_grid]
			// ============================
			vc_map( 
				array( 
					'base' => 'block_grid', 
					'name' => __( 'Block Grid', 'noo' ), 
					'weight' => 760, 
					'class' => 'noo-vc-element noo-vc-element-block_grid', 
					'icon' => 'noo-vc-icon-block_grid', 
					'deprecated' => '4.9',
					'category' => $category_content, 
					'description' => '', 
					'as_parent' => array( 'only' => 'block_grid_item' ), 
					'content_element' => true, 
					'js_view' => 'VcColumnView', 
					'params' => array( 
						array( 
							'param_name' => 'title', 
							'heading' => __( 'Title (optional)', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'columns', 
							'heading' => __( 'Number of Columns', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'One', 'noo' ) => '1', 
								__( 'Two', 'noo' ) => '2', 
								__( 'Three', 'noo' ) => '3', 
								__( 'Four', 'noo' ) => '4', 
								__( 'Five', 'noo' ) => '5', 
								__( 'Six', 'noo' ) => '6' ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [block_grid_item]
			// ============================
			vc_map( 
				array( 
					'base' => 'block_grid_item', 
					'name' => __( 'Block Grid Item', 'noo' ), 
					'weight' => 755, 
					'class' => 'noo-vc-element noo-vc-element-block_grid_item', 
					'icon' => 'noo-vc-icon-block_grid_item', 
					'deprecated' => '4.9',
					'category' => $category_content, 
					'description' => '', 
					'as_child' => array( 'only' => 'block_grid' ), 
					'content_element' => true, 
					'show_settings_on_create' => false, 
					'params' => array( 
						array( 
							'param_name' => $param_content_name, 
							'heading' => $param_content_heading, 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [progress_bar]
			// ============================
			vc_map( 
				array( 
					'base' => 'progress_bar', 
					'name' => __( 'Progress Bar', 'noo' ), 
					'weight' => 750, 
					'class' => 'noo-vc-element noo-vc-element-progress_bar', 
					'icon' => 'noo-vc-icon-progress_bar', 
					'category' => $category_content, 
					'description' => '', 
					'as_parent' => array( 'only' => 'progress_bar_item' ), 
					'content_element' => true, 
					'js_view' => 'VcColumnView', 
					'params' => array( 
						array( 
							'param_name' => 'title', 
							'heading' => __( 'Title (optional)', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'style', 
							'heading' => __( 'Bar Style', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Lean', 'noo' ) => 'lean', 
								__( 'Thick', 'noo' ) => 'thick' ) ), 
						array( 
							'param_name' => 'rounded', 
							'heading' => __( 'Rounded Bar', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [progress_bar_item]
			// ============================
			vc_map( 
				array( 
					'base' => 'progress_bar_item', 
					'name' => __( 'Progress Bar Item', 'noo' ), 
					'weight' => 745, 
					'class' => 'noo-vc-element noo-vc-element-progress_bar_item', 
					'icon' => 'noo-vc-icon-progress_bar_item', 
					'category' => $category_content, 
					'description' => '', 
					'as_child' => array( 'only' => 'progress_bar' ), 
					'content_element' => true, 
					'params' => array( 
						array( 
							'param_name' => 'title', 
							'heading' => __( 'Bar Title', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'progress', 
							'heading' => __( 'Progress ( out of 100 )', 'noo' ), 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '50', 
							'data_min' => '1', 
							'data_max' => '100' ), 
						array( 
							'param_name' => 'color', 
							'heading' => __( 'Color', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Primary', 'noo' ) => 'primary', 
								__( 'Success', 'noo' ) => 'success', 
								__( 'Info', 'noo' ) => 'info', 
								__( 'Warning', 'noo' ) => 'warning', 
								__( 'Danger', 'noo' ) => 'danger' ) ), 
						array( 
							'param_name' => 'color_effect', 
							'heading' => __( 'Color Effect', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'None', 'noo' ) => '', 
								__( 'Striped', 'noo' ) => 'striped', 
								__( 'Striped with Animation', 'noo' ) => 'striped_animation' ) ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// // [pricing_table]
			// // ============================
			// vc_map( 
			// 	array( 
			// 		'base' => 'pricing_table', 
			// 		'name' => __( 'Pricing Table', 'noo' ), 
			// 		'weight' => 740, 
			// 		'class' => 'noo-vc-element noo-vc-element-pricing_table', 
			// 		'icon' => 'noo-vc-icon-pricing_table', 
			// 		'category' => $category_content, 
			// 		'description' => '', 
			// 		'as_parent' => array( 'only' => 'pricing_table_column' ), 
			// 		'content_element' => true, 
			// 		'js_view' => 'VcColumnView', 
			// 		'params' => array( 
			// 			array( 
			// 				'param_name' => 'title', 
			// 				'heading' => __( 'Title (optional)', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder ), 
			// 			array( 
			// 				'param_name' => 'columns', 
			// 				'heading' => __( 'Number of Columns', 'noo' ), 
			// 				'description' => '', 
			// 				'type' => 'dropdown', 
			// 				'holder' => $param_holder, 
			// 				'value' => array( 
			// 					__( 'One', 'noo' ) => '1', 
			// 					__( 'Two', 'noo' ) => '2', 
			// 					__( 'Three', 'noo' ) => '3', 
			// 					__( 'Four', 'noo' ) => '4', 
			// 					__( 'Five', 'noo' ) => '5', 
			// 					__( 'Six', 'noo' ) => '6' ) ), 
			// 			array( 
			// 				'param_name' => 'style', 
			// 				'heading' => __( 'Style', 'noo' ), 
			// 				'type' => 'dropdown', 
			// 				'holder' => $param_holder, 
			// 				'value' => array( 
			// 					__( 'Ascending', 'noo' ) => 'ascending', 
			// 					__( 'Classic', 'noo' ) => 'classic' ) ), 
			// 			array( 
			// 				'param_name' => $param_visibility_name, 
			// 				'heading' => $param_visibility_heading, 
			// 				'description' => $param_visibility_description, 
			// 				'type' => $param_visibility_type, 
			// 				'holder' => $param_visibility_holder, 
			// 				'value' => $param_visibility_value ), 
			// 			array( 
			// 				'param_name' => $param_class_name, 
			// 				'heading' => $param_class_heading, 
			// 				'description' => $param_class_description, 
			// 				'type' => $param_class_type, 
			// 				'holder' => $param_class_holder ), 
			// 			array( 
			// 				'param_name' => $param_custom_style_name, 
			// 				'heading' => $param_custom_style_heading, 
			// 				'description' => $param_custom_style_description, 
			// 				'type' => $param_custom_style_type, 
			// 				'holder' => $param_custom_style_holder ) ) ) );
			
			// // [pricing_table_column]
			// // ============================
			// vc_map( 
			// 	array( 
			// 		'base' => 'pricing_table_column', 
			// 		'name' => __( 'Pricing Table Column', 'noo' ), 
			// 		'weight' => 735, 
			// 		'class' => 'noo-vc-element noo-vc-element-pricing_table_column', 
			// 		'icon' => 'noo-vc-icon-pricing_table_column', 
			// 		'category' => $category_content, 
			// 		'description' => '', 
			// 		'as_child' => array( 'only' => 'pricing_table' ), 
			// 		'content_element' => true, 
			// 		'params' => array( 
			// 			array( 
			// 				'param_name' => 'title', 
			// 				'heading' => __( 'Title', 'noo' ), 
			// 				'description' => __( 'Column Title', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder ), 
			// 			array( 
			// 				'param_name' => 'featured', 
			// 				'heading' => __( 'Featured Column', 'noo' ), 
			// 				'description' => '', 
			// 				'type' => 'checkbox', 
			// 				'holder' => $param_holder, 
			// 				'value' => array( '' => 'true' ) ), 
			// 			array( 
			// 				'param_name' => 'price', 
			// 				'heading' => __( 'Price', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder, 
			// 				'value' => '' ), 
			// 			array( 
			// 				'param_name' => 'symbol', 
			// 				'heading' => __( 'Currency Symbol', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder, 
			// 				'value' => '$' ), 
			// 			array( 
			// 				'param_name' => 'before_price', 
			// 				'heading' => __( 'Text Before Price', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder, 
			// 				'value' => 'From' ), 
			// 			array( 
			// 				'param_name' => 'after_price', 
			// 				'heading' => __( 'Text After Price', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder, 
			// 				'value' => 'per Month' ), 
			// 			array( 
			// 				'param_name' => $param_content_name, 
			// 				'heading' => $param_content_heading, 
			// 				'description' => $param_content_description, 
			// 				'type' => $param_content_type, 
			// 				'holder' => $param_content_holder, 
			// 				'value' => '[icon_list][icon_list_item icon="fa fa-check"]Etiam rhoncus[/icon_list_item][icon_list_item icon="fa fa-times"]Donec mi[/icon_list_item][icon_list_item icon="fa fa-times"]Nam ipsum[/icon_list_item][/icon_list]' ), 
			// 			array( 
			// 				'param_name' => 'button_text', 
			// 				'heading' => __( 'Button Text', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder, 
			// 				'value' => __( 'Purchase', 'noo' ) ), 
			// 			array( 
			// 				'param_name' => 'href', 
			// 				'heading' => __( 'URL (Link)', 'noo' ), 
			// 				'type' => 'textfield', 
			// 				'holder' => $param_holder ), 
			// 			array( 
			// 				'param_name' => 'target', 
			// 				'heading' => __( 'Open in new tab', 'noo' ), 
			// 				'type' => 'checkbox', 
			// 				'holder' => $param_holder, 
			// 				'value' => array( '' => 'true' ) ), 
			// 			array( 
			// 				'param_name' => $param_visibility_name, 
			// 				'heading' => $param_visibility_heading, 
			// 				'description' => $param_visibility_description, 
			// 				'type' => $param_visibility_type, 
			// 				'holder' => $param_visibility_holder, 
			// 				'value' => $param_visibility_value ), 
			// 			array( 
			// 				'param_name' => $param_class_name, 
			// 				'heading' => $param_class_heading, 
			// 				'description' => $param_class_description, 
			// 				'type' => $param_class_type, 
			// 				'holder' => $param_class_holder ), 
			// 			array( 
			// 				'param_name' => $param_custom_style_name, 
			// 				'heading' => $param_custom_style_heading, 
			// 				'description' => $param_custom_style_description, 
			// 				'type' => $param_custom_style_type, 
			// 				'holder' => $param_custom_style_holder ) ) ) );
			
			// [vc_pie]
			// ============================
			// vc_map_update( 
			// 	'vc_pie', 
			// 	array( 
			// 		'category' => $category_content, 
			// 		'weight' => 730, 
			// 		'class' => 'noo-vc-element noo-vc-element-pie', 
			// 		'icon' => 'noo-vc-icon-pie' ) );
			
			// vc_remove_param( 'vc_pie', 'color' );
			// vc_remove_param( 'vc_pie', 'el_class' );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => 'style', 
			// 		'heading' => __( 'Style', 'noo' ), 
			// 		'type' => 'dropdown', 
			// 		'holder' => $param_holder, 
			// 		'value' => array( 
			// 			__( 'Filled', 'noo' ) => 'filled', 
			// 			__( 'Bordered', 'noo' ) => 'bordered' ) ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => 'color', 
			// 		'heading' => __( 'Bar Color', 'noo' ), 
			// 		'type' => 'colorpicker', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => 'width', 
			// 		'heading' => __( 'Bar Width (px)', 'noo' ), 
			// 		'type' => 'ui_slider', 
			// 		'holder' => $param_holder, 
			// 		'value' => '1', 
			// 		'data_min' => '1', 
			// 		'data_max' => '20' ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => 'value_color', 
			// 		'heading' => __( 'Value Label Color', 'noo' ), 
			// 		'type' => 'colorpicker', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => $param_visibility_name, 
			// 		'heading' => $param_visibility_heading, 
			// 		'description' => $param_visibility_description, 
			// 		'type' => $param_visibility_type, 
			// 		'holder' => $param_visibility_holder, 
			// 		'value' => $param_visibility_value ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => $param_class_name, 
			// 		'heading' => $param_class_heading, 
			// 		'description' => $param_class_description, 
			// 		'type' => $param_class_type, 
			// 		'holder' => $param_class_holder ) );
			
			// vc_add_param( 
			// 	'vc_pie', 
			// 	array( 
			// 		'param_name' => $param_custom_style_name, 
			// 		'heading' => $param_custom_style_heading, 
			// 		'description' => $param_custom_style_description, 
			// 		'type' => $param_custom_style_type, 
			// 		'holder' => $param_custom_style_holder ) );
			
			// [vc_cta_button]
			// ============================
			vc_map_update( 
				'vc_cta_button', 
				array( 
					'category' => $category_content, 
					'weight' => 720, 
					'class' => 'noo-vc-element noo-vc-element-cta', 
					'icon' => 'noo-vc-icon-cta' ) );
			
			vc_remove_param( 'vc_cta_button', 'call_text' );
			vc_remove_param( 'vc_cta_button', 'title' );
			vc_remove_param( 'vc_cta_button', 'href' );
			vc_remove_param( 'vc_cta_button', 'target' );
			vc_remove_param( 'vc_cta_button', 'color' );
			vc_remove_param( 'vc_cta_button', 'icon' );
			vc_remove_param( 'vc_cta_button', 'size' );
			vc_remove_param( 'vc_cta_button', 'position' );
			vc_remove_param( 'vc_cta_button', 'css_animation' );
			vc_remove_param( 'vc_cta_button', 'position' );
			vc_remove_param( 'vc_cta_button', 'el_class' );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'title', 
					'heading' => __( 'Title (Heading)', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'message', 
					'heading' => __( 'Message', 'noo' ), 
					'type' => 'textarea', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'alignment', 
					'heading' => __( 'Alignment', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Center', 'noo' ) => 'center', 
						__( 'Left', 'noo' ) => 'left', 
						__( 'Right', 'noo' ) => 'right' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'button_text', 
					'heading' => __( 'Button Text', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'href', 
					'heading' => __( 'URL (Link)', 'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'target', 
					'heading' => __( 'Open in new tab', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'size', 
					'heading' => __( 'Size', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Extra Small', 'noo' ) => 'x_small', 
						__( 'Small', 'noo' ) => 'small', 
						__( 'Medium', 'noo' ) => 'medium', 
						__( 'Large', 'noo' ) => 'large', 
						__( 'Custom', 'noo' ) => 'custom' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'fullwidth', 
					'heading' => __( 'Forge Full-Width', 'noo' ), 
					'description' => '', 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'false' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'vertical_padding', 
					'heading' => __( 'Vertical Padding (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '10', 
					'data_min' => '0', 
					'data_max' => '50', 
					'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'horizontal_padding', 
					'heading' => __( 'Horizontal Padding (px)', 'noo' ), 
					'type' => 'ui_slider', 
					'holder' => $param_holder, 
					'value' => '10', 
					'data_min' => '0', 
					'data_max' => '50', 
					'dependency' => array( 'element' => 'size', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'icon', 
					'heading' => __( 'Icon', 'noo' ), 
					'type' => 'iconpicker', 
					'holder' => $param_holder, 
					'value' => '' ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'icon_right', 
					'heading' => __( 'Right Icon', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'icon_only', 
					'heading' => __( 'Show only Icon', 'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ), 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'icon_color', 
					'heading' => __( 'Icon Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'icon', 'not_empty' => true ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'shape', 
					'heading' => __( 'Shape', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Square', 'noo' ) => 'square', 
						__( 'Rounded', 'noo' ) => 'rounded', 
						__( 'Pill', 'noo' ) => 'pill' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'style', 
					'heading' => __( 'Style', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( '3D Pressable', 'noo' ) => 'pressable', 
						__( 'Metro', 'noo' ) => 'metro', 
						__( 'Blank', 'noo' ) => '' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'skin', 
					'heading' => __( 'Skin', 'noo' ), 
					'description' => '', 
					'type' => 'dropdown', 
					'holder' => $param_holder, 
					'value' => array( 
						__( 'Default', 'noo' ) => 'default', 
						__( 'Custom', 'noo' ) => 'custom', 
						__( 'Primary', 'noo' ) => 'primary', 
						__( 'Success', 'noo' ) => 'success', 
						__( 'Info', 'noo' ) => 'info', 
						__( 'Warning', 'noo' ) => 'warning', 
						__( 'Danger', 'noo' ) => 'danger', 
						__( 'Link', 'noo' ) => 'link' ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'text_color', 
					'heading' => __( 'Text Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'hover_text_color', 
					'heading' => __( 'Hover Text Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'bg_color', 
					'heading' => __( 'Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'hover_bg_color', 
					'heading' => __( 'Hover Background Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'skin', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'border_color', 
					'heading' => __( 'Border Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => 'hover_border_color', 
					'heading' => __( 'Hover Border Color', 'noo' ), 
					'type' => 'colorpicker', 
					'holder' => $param_holder, 
					'value' => '', 
					'dependency' => array( 'element' => 'style', 'value' => array( 'custom' ) ) ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_cta_button', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [counter]
			// ============================
			vc_map( 
				array( 
					'base' => 'counter', 
					'name' => __( 'Counter', 'noo' ), 
					'weight' => 710, 
					'class' => 'noo-vc-element noo-vc-element-counter', 
					'icon' => 'noo-vc-icon-counter', 
					'category' => $category_content, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'number', 
							'heading' => __( 'Number', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'size', 
							'heading' => __( 'Size (px)', 'noo' ), 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '50', 
							'data_min' => '10', 
							'data_max' => '100' ), 
						array( 
							'param_name' => 'color', 
							'heading' => __( 'Color', 'noo' ), 
							'description' => '', 
							'type' => 'colorpicker', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'alignment', 
							'heading' => __( 'Alignment', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Center', 'noo' ) => 'center', 
								__( 'Left', 'noo' ) => 'left', 
								__( 'Right', 'noo' ) => 'right' ) ), 
						array( 
							'param_name' => $param_content_name, 
							'heading' => $param_content_heading, 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [vc_message]
			// ============================
			vc_map_update( 
				'vc_message', 
				array( 
					'category' => $category_content, 
					'class' => 'noo-vc-element noo-vc-element-message', 
					'icon' => 'noo-vc-icon-message', 
					'weight' => 700 ) );
			
			// vc_remove_param( 'vc_message', 'color' );
			// vc_remove_param( 'vc_message', 'style' );
			// vc_remove_param( 'vc_message', 'css_animation' );
			// vc_remove_param( 'vc_message', 'el_class' );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => 'title', 
			// 		'heading' => __( 'Title (Heading)', 'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => $param_content_name, 
			// 		'heading' => $param_content_heading, 
			// 		'description' => $param_content_description, 
			// 		'type' => $param_content_type, 
			// 		'holder' => $param_content_holder, 
			// 		'value' => $param_content_value ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => 'type', 
			// 		'heading' => __( 'Message Type', 'noo' ), 
			// 		'description' => '', 
			// 		'type' => 'dropdown', 
			// 		'holder' => $param_holder, 
			// 		'value' => array( 
			// 			__( 'Success', 'noo' ) => 'success', 
			// 			__( 'Info', 'noo' ) => 'info', 
			// 			__( 'Warning', 'noo' ) => 'warning', 
			// 			__( 'Danger', 'noo' ) => 'danger' ) ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => 'dismissible', 
			// 		'heading' => __( 'Dismissible', 'noo' ), 
			// 		'type' => 'checkbox', 
			// 		'holder' => $param_holder, 
			// 		'value' => array( '' => 'true' ) ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => $param_visibility_name, 
			// 		'heading' => $param_visibility_heading, 
			// 		'description' => $param_visibility_description, 
			// 		'type' => $param_visibility_type, 
			// 		'holder' => $param_visibility_holder, 
			// 		'value' => $param_visibility_value ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => $param_class_name, 
			// 		'heading' => $param_class_heading, 
			// 		'description' => $param_class_description, 
			// 		'type' => $param_class_type, 
			// 		'holder' => $param_class_holder ) );
			
			// vc_add_param( 
			// 	'vc_message', 
			// 	array( 
			// 		'param_name' => $param_custom_style_name, 
			// 		'heading' => $param_custom_style_heading, 
			// 		'description' => $param_custom_style_description, 
			// 		'type' => $param_custom_style_type, 
			// 		'holder' => $param_custom_style_holder ) );
		}
		
		add_action( 'admin_init', 'noo_vc_content' );
		
		//
		// Extend container class (parents).
		//
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {

			class WPBakeryShortCode_Block_Grid extends WPBakeryShortCodesContainer {
			}

			class WPBakeryShortCode_Progress_Bar extends WPBakeryShortCodesContainer {
			}

			// class WPBakeryShortCode_Pricing_Table extends WPBakeryShortCodesContainer {
			// }
		}
		
		//
		// Extend item class (children).
		//
		if ( class_exists( 'WPBakeryShortCode' ) ) {

			class WPBakeryShortCode_Block_Grid_Item extends WPBakeryShortCode {
			}

			class WPBakeryShortCode_Progress_Bar_Item extends WPBakeryShortCode {
			}

			// class WPBakeryShortCode_Pricing_Table_Column extends WPBakeryShortCode {
			// }
		}
	

	endif;
	
	if ( ! function_exists( 'noo_vc_wp_content' ) ) :

		function noo_vc_wp_content() {
			
			//
			// Variables.
			//
			$category_base_element = __( 'Base Elements', 'noo' );
			$category_typography = __( 'Typography', 'noo' );
			$category_content = __( 'Content', 'noo' );
			$category_wp_content = __( 'WordPress Content', 'noo' );
			$category_media = __( 'Media', 'noo' );
			$category_custom = __( 'Custom', 'noo' );
			
			$param_content_name = 'content';
			$param_content_heading = __( 'Text', 'noo' );
			$param_content_description = __( 'Enter your text.', 'noo' );
			$param_content_type = 'textarea_html';
			$param_content_holder = 'div';
			$param_content_value = '';
			
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array( 
				__( 'All Devices', 'noo' ) => "all", 
				__( 'Hidden Phone', 'noo' ) => "hidden-phone", 
				__( 'Hidden Tablet', 'noo' ) => "hidden-tablet", 
				__( 'Hidden PC', 'noo' ) => "hidden-pc", 
				__( 'Visible Phone', 'noo' ) => "visible-phone", 
				__( 'Visible Tablet', 'noo' ) => "visible-tablet", 
				__( 'Visible PC', 'noo' ) => "visible-pc" );
			
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
			
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			
			$param_holder = 'div';
			
			// [vc_widget_sidebar]
			// ============================
			vc_map_update( 
				'vc_widget_sidebar', 
				array( 
					'category' => $category_wp_content, 
					'weight' => 690, 
					'class' => 'noo-vc-element noo-vc-element-widget_sidebar', 
					'icon' => 'noo-vc-icon-widget_sidebar' ) );
			
			vc_remove_param( 'vc_widget_sidebar', 'el_class' );
			
			vc_add_param( 
				'vc_widget_sidebar', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_widget_sidebar', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_widget_sidebar', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [blog]
			// ============================
			vc_map(
				array(
					'base'            => 'blog',
					'name'            => __( 'Post List', 'noo' ),
					'weight'          => 680,
					'class'           => 'noo-vc-element noo-vc-element-blog',
					'icon'            => 'noo-vc-icon-blog',
					'category'        => $category_wp_content,
					'description'     => '',
					'params'          => array(
						array(
							'param_name'	=> 'title',
							'heading'		=> __( 'Heading (optional)', 'noo' ),
							'type'          => 'textfield',
							'holder'        => $param_holder,
						),
						array(
							'param_name'	=> 'categories',
							'heading'		=> __( 'Blog Categories', 'noo' ),
							'description'   => '',
							'type'          => 'post_categories',
							'holder'        => $param_holder,
						),
						array(
							'param_name'	=> 'orderby',
							'heading'		=> __( 'Order By', 'noo' ),
							'description'   => '',
							'type'          => 'dropdown',
							'holder'        => $param_holder,
							'value'         => array(
								__( 'Recent First', 'noo' )            => 'latest',
								__( 'Older First', 'noo' )             => 'oldest',
								__( 'Title Alphabet', 'noo' )          => 'alphabet',
								__( 'Title Reversed Alphabet', 'noo' ) => 'ralphabet',
							)
						),
						array(
							'param_name'	=> 'post_count',
							'heading'		=> __( 'Max Number of Post', 'noo' ),
							'description'   => __( 'Set zero to show all posts ( should use pagination ).', 'noo' ),
							'type'          => 'ui_slider',
							'holder'        => $param_holder,
							'value'         => '4',
							'data_min'      => '1',
							'data_max'      => '20'
						),
						array(
							'param_name'	=> 'show_pagination',
							'heading'		=> __( 'Show pagination', 'noo' ),
							'type'          => 'checkbox',
							'holder'        => $param_holder,
							'value'         => array( '' => 'true' )
						),
						array(
							'param_name'	=> 'hide_featured',
							'heading'		=> __( 'Hide Featured Image/Gallery', 'noo' ),
							'type'          => 'checkbox',
							'holder'        => $param_holder,
							'value'         => array( '' => 'true' )
						),
						array(
							'param_name'	=> 'hide_post_meta',
							'heading'		=> __( 'Hide Post Meta', 'noo' ),
							'type'          => 'checkbox',
							'holder'        => $param_holder,
							'value'         => array( '' => 'true' )
						),
						array(
							'param_name'	=> 'hide_readmore',
							'heading'		=> __( 'Hide Readmore link', 'noo' ),
							'type'          => 'checkbox',
							'holder'        => $param_holder,
							'value'         => array( '' => 'true' )
						),
						array(
							'param_name'	=> 'excerpt_length',
							'heading'		=> __( 'Excerpt length', 'noo' ),
							'type'          => 'textfield',
							'std'			=> 55,
							'holder'        => $param_holder,
						),
						array(
							'param_name'  => $param_visibility_name,
							'heading'     => $param_visibility_heading,
							'description' => $param_visibility_description,
							'type'        => $param_visibility_type,
							'holder'      => $param_visibility_holder,
							'value'       => $param_visibility_value
						),
						array(
							'param_name'  => $param_class_name,
							'heading'     => $param_class_heading,
							'description' => $param_class_description,
							'type'        => $param_class_type,
							'holder'      => $param_class_holder
						),
						array(
							'param_name'  => $param_custom_style_name,
							'heading'     => $param_custom_style_heading,
							'description' => $param_custom_style_description,
							'type'        => $param_custom_style_type,
							'holder'      => $param_custom_style_holder
						)
					)
				)
			);

			// [contact-form-7]
			// ============================
			if ( class_exists( 'WPCF7_ContactForm' ) ) {
				vc_map_update( 
					'contact-form-7', 
					array( 'category' => $category_wp_content, 'weight' => 650 ) );
			}
		}
		
		add_action( 'admin_init', 'noo_vc_wp_content' );
	

	endif;
	
	if ( ! function_exists( 'noo_vc_media' ) ) :

		function noo_vc_media() {
			
			//
			// Variables.
			//
			$category_name = __( 'Media', 'noo' );
			
			$param_content_name = 'content';
			$param_content_heading = __( 'Text', 'noo' );
			$param_content_description = __( 'Enter your text.', 'noo' );
			$param_content_type = 'textarea_html';
			$param_content_holder = 'div';
			$param_content_value = '';
			
			$param_visibility_name = 'visibility';
			$param_visibility_heading = __( 'Visibility', 'noo' );
			$param_visibility_description = '';
			$param_visibility_type = 'dropdown';
			$param_visibility_holder = 'div';
			$param_visibility_value = array( 
				__( 'All Devices', 'noo' ) => "all", 
				__( 'Hidden Phone', 'noo' ) => "hidden-phone", 
				__( 'Hidden Tablet', 'noo' ) => "hidden-tablet", 
				__( 'Hidden PC', 'noo' ) => "hidden-pc", 
				__( 'Visible Phone', 'noo' ) => "visible-phone", 
				__( 'Visible Tablet', 'noo' ) => "visible-tablet", 
				__( 'Visible PC', 'noo' ) => "visible-pc" );
			
			$param_class_name = 'class';
			$param_class_heading = __( 'Class', 'noo' );
			$param_class_description = __( '(Optional) Enter a unique class name.', 'noo' );
			$param_class_type = 'textfield';
			$param_class_holder = 'div';
			
			$param_custom_style_name = 'custom_style';
			$param_custom_style_heading = __( 'Custom Style', 'noo' );
			$param_custom_style_description = __( '(Optional) Enter inline CSS.', 'noo' );
			$param_custom_style_type = 'textfield';
			$param_custom_style_holder = 'div';
			
			$param_holder = 'div';
			
			// [vc_single_image]
			// ============================
			vc_map_update( 
				'vc_single_image', 
				array( 
					'category' => $category_name, 
					'class' => 'noo-vc-element noo-vc-element-image', 
					'icon' => 'noo-vc-icon-image', 
					'weight' => 590 ) );
			
			// vc_remove_param( 'vc_single_image', 'title' );
			// vc_remove_param( 'vc_single_image', 'img_size' );
			// vc_remove_param( 'vc_single_image', 'add_caption' );
			// vc_remove_param( 'vc_single_image', 'onclick' );
			// vc_remove_param( 'vc_single_image', 'link' );
			// vc_remove_param( 'vc_single_image', 'alignment' );
			// vc_remove_param( 'vc_single_image', 'style' );
			// vc_remove_param( 'vc_single_image', 'border_color' );
			// vc_remove_param( 'vc_single_image', 'css_animation' );
			// vc_remove_param( 'vc_single_image', 'img_link' );
			// vc_remove_param( 'vc_single_image', 'img_link_large' );
			// vc_remove_param( 'vc_single_image', 'img_link_target' );
			// vc_remove_param( 'vc_single_image', 'el_class' );
			// vc_remove_param( 'vc_single_image', 'css' );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => 'alt', 
			// 		'heading' => __( 'Alt Text', 'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => 'style', 
			// 		'heading' => __( 'Image Style', 'noo' ), 
			// 		'type' => 'dropdown', 
			// 		'holder' => $param_holder, 
			// 		'value' => array( 
			// 			__( 'None', 'noo' ) => '', 
			// 			__( 'Rounded', 'noo' ) => 'rounded', 
			// 			__( 'Circle', 'noo' ) => 'circle', 
			// 			__( 'Thumbnail', 'noo' ) => 'thumbnail' ) ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => 'href', 
			// 		'heading' => __( 'Image Link', 'noo' ), 
			// 		'description' => __( 
			// 			'Input the URL if you want the image to wrap inside an anchor.', 
			// 			'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => 'target', 
			// 		'heading' => __( 'Open in New Tab', 'noo' ), 
			// 		'type' => 'checkbox', 
			// 		'holder' => $param_holder, 
			// 		'value' => array( '' => 'true' ), 
			// 		'dependency' => array( 'element' => 'href', 'not_empty' => true ) ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => 'link_title', 
			// 		'heading' => __( 'Link Title', 'noo' ), 
			// 		'type' => 'textfield', 
			// 		'holder' => $param_holder, 
			// 		'value' => '', 
			// 		'dependency' => array( 'element' => 'href', 'not_empty' => true ) ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => $param_visibility_name, 
			// 		'heading' => $param_visibility_heading, 
			// 		'description' => $param_visibility_description, 
			// 		'type' => $param_visibility_type, 
			// 		'holder' => $param_visibility_holder, 
			// 		'value' => $param_visibility_value ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => $param_class_name, 
			// 		'heading' => $param_class_heading, 
			// 		'description' => $param_class_description, 
			// 		'type' => $param_class_type, 
			// 		'holder' => $param_class_holder ) );
			
			// vc_add_param( 
			// 	'vc_single_image', 
			// 	array( 
			// 		'param_name' => $param_custom_style_name, 
			// 		'heading' => $param_custom_style_heading, 
			// 		'description' => $param_custom_style_description, 
			// 		'type' => $param_custom_style_type, 
			// 		'holder' => $param_custom_style_holder ) );
			
			// [noo_rev_slider] Revolution Slider
			// ============================
			if ( class_exists( 'RevSlider' ) ) {
				vc_map( 
					array( 
						'base' => 'noo_rev_slider', 
						'name' => __( 'Revolution Slider', 'noo' ), 
						'weight' => 580, 
						'class' => 'noo-vc-element noo-vc-element-rev_slider', 
						'icon' => 'noo-vc-icon-rev_slider', 
						'category' => $category_name, 
						'description' => '', 
						'params' => array( 
							array( 
								'param_name' => 'slider', 
								'heading' => __( 'Revolution Slider', 'noo' ), 
								'description' => '', 
								'type' => 'noo_rev_slider', 
								'holder' => $param_holder ), 
							array( 
								'param_name' => $param_visibility_name, 
								'heading' => $param_visibility_heading, 
								'description' => $param_visibility_description, 
								'type' => $param_visibility_type, 
								'holder' => $param_visibility_holder, 
								'value' => $param_visibility_value ), 
							array( 
								'param_name' => $param_class_name, 
								'heading' => $param_class_heading, 
								'description' => $param_class_description, 
								'type' => $param_class_type, 
								'holder' => $param_class_holder ), 
							array( 
								'param_name' => $param_custom_style_name, 
								'heading' => $param_custom_style_heading, 
								'description' => $param_custom_style_description, 
								'type' => $param_custom_style_type, 
								'holder' => $param_custom_style_holder ) ) ) );
			}
			
			// [slider] Responsive Slider
			// ============================
			vc_map( 
				array( 
					'base' => 'slider', 
					'name' => __( 'Responsive Slider', 'noo' ), 
					'weight' => 570, 
					'class' => 'noo-vc-element noo-vc-element-slider', 
					'icon' => 'noo-vc-icon-slider', 
					'category' => $category_name, 
					'description' => '', 
					'as_parent' => array( 'only' => 'slide' ), 
					'content_element' => true, 
					'js_view' => 'VcColumnView', 
					'params' => array( 
						array( 
							'param_name' => 'animation', 
							'heading' => __( 'Animation', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Slide', 'noo' ) => 'slide', 
								__( 'Fade', 'noo' ) => 'fade' ) ), 
						
						// array(
						// 'param_name' => 'visible_items',
						// 'heading' => __( 'Max Number of Visible Item', 'noo' ),
						// 'description' => '',
						// 'type' => 'ui_slider',
						// 'holder' => $param_holder,
						// 'value' => '1'
						// 'data_min' => '1',
						// 'data_max' => '10',
						// ),
						array( 
							'param_name' => 'slider_time', 
							'heading' => __( 'Slide Time (ms)', 'noo' ), 
							'description' => '', 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '3000', 
							'data_min' => '500', 
							'data_max' => '8000', 
							'data_step' => '100' ), 
						array( 
							'param_name' => 'slider_speed', 
							'heading' => __( 'Slide Speed (ms)', 'noo' ), 
							'description' => '', 
							'type' => 'ui_slider', 
							'holder' => $param_holder, 
							'value' => '600', 
							'data_min' => '100', 
							'data_max' => '3000', 
							'data_step' => '100' ), 
						array( 
							'param_name' => 'auto_play', 
							'heading' => __( 'Auto Play Slider', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'pause_on_hover', 
							'heading' => __( 'Pause on Hover', 'noo' ), 
							'description' => __( 'If auto play, pause slider when mouse over it?', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'random', 
							'heading' => __( 'Random Slider', 'noo' ), 
							'description' => __( 'Random Choose Slide to Start', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'indicator', 
							'heading' => __( 'Show Slide Indicator', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'indicator_position', 
							'heading' => __( 'Indicator Position', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Top', 'noo' ) => 'top', 
								__( 'Bottom', 'noo' ) => 'bottom' ) ), 
						array( 
							'param_name' => 'prev_next_control', 
							'heading' => __( 'Show Previous/Next Navigation', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'timer', 
							'heading' => __( 'Show Timer', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'swipe', 
							'heading' => __( 'Enable Swipe on Mobile', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [slide] Responsive Slider Item
			// ============================
			vc_map( 
				array( 
					'base' => 'slide', 
					'name' => __( 'Slide', 'noo' ), 
					'weight' => 575, 
					'class' => 'noo-vc-element noo-vc-element-slide', 
					'icon' => 'noo-vc-icon-slide', 
					'category' => $category_name, 
					'description' => '', 
					'as_child' => array( 'only' => 'slider' ), 
					'content_element' => true, 
					'params' => array( 
						array( 
							'param_name' => 'type', 
							'heading' => __( 'Type', 'noo' ), 
							'description' => __( 
								'Choose the type of this slide: Image, Video or HTML Content', 
								'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Image', 'noo' ) => 'image', 
								__( 'HTML Content', 'noo' ) => 'content' ) ), 
						array( 
							'param_name' => 'image', 
							'heading' => __( 'Image', 'noo' ), 
							'description' => '', 
							'type' => 'attach_image', 
							'admin_label' => true, 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'image' ) ) ), 
						array( 
							'param_name' => 'caption', 
							'heading' => __( 'Image Caption', 'noo' ), 
							'description' => '', 
							'type' => 'textarea', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'image' ) ) ), 
						
						// array(
						// 'param_name' => 'video_url',
						// 'heading' => __( 'Video URL', 'noo' ),
						// 'description' => '',
						// 'type' => 'textfield',
						// 'holder' => $param_holder,
						// 'dependency' => array( 'element' => 'type', 'value' => array( 'video' ) )
						// ),
						// array(
						// 'param_name' => 'video_poster',
						// 'heading' => __( 'Video Poster Image', 'noo' ),
						// 'description' => __( 'Poster Image to show on Mobile or un-supported devices.',
						// 'noo' ),
						// 'type' => 'attach_image',
						// 'holder' => $param_holder,
						// 'dependency' => array( 'element' => 'type', 'value' => array( 'video' ) )
						// ),
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'HTML Content (only for HTML Content slide)', 'noo' ), 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'content' ) ) ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [lightbox] Responsive Lightbox
			// ============================
			vc_map( 
				array( 
					'base' => 'lightbox', 
					'name' => __( 'Responsive Lightbox', 'noo' ), 
					'weight' => 560, 
					'class' => 'noo-vc-element noo-vc-element-lightbox', 
					'icon' => 'noo-vc-icon-lightbox', 
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'gallery_id', 
							'heading' => __( 'Gallery ID', 'noo' ), 
							'description' => __( 
								'Lightbox elements with the same Gallery ID will be grouped to in the same slider lightbox.', 
								'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'type', 
							'heading' => __( 'Content Type', 'noo' ), 
							'description' => __( 
								'Choose the content type of this slide. We support: Image, Iframe (for other site and embed video) and HTML Content', 
								'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Image', 'noo' ) => 'image', 
								__( 'IFrame', 'noo' ) => 'iframe', 
								__( 'HTML Content', 'noo' ) => 'inline' ) ), 
						array( 
							'param_name' => 'image', 
							'heading' => __( 'Image', 'noo' ), 
							'description' => '', 
							'type' => 'attach_image', 
							'admin_label' => true, 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'image' ) ) ), 
						array( 
							'param_name' => 'image_title', 
							'heading' => __( 'Image Title', 'noo' ), 
							'description' => '', 
							'type' => 'textfield', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'image' ) ) ), 
						array( 
							'param_name' => 'iframe_url', 
							'heading' => __( 'Iframe URL', 'noo' ), 
							'description' => __( 
								'You can input any link like http://wikipedia.com. Youtube and Vimeo link will be converted to embed video, other video site will need embeded link.', 
								'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'iframe' ) ) ), 
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'HTML Content (only for Inline HTML Lightbox)', 'noo' ), 
							'description' => $param_content_description, 
							'type' => $param_content_type, 
							'holder' => $param_content_holder, 
							'value' => $param_content_value, 
							'dependency' => array( 'element' => 'type', 'value' => array( 'inline' ) ) ), 
						array( 
							'param_name' => 'thumbnail_type', 
							'heading' => __( 'Thumbnail Type', 'noo' ), 
							'description' => '', 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'Image', 'noo' ) => 'image', 
								__( 'Link', 'noo' ) => 'link' ) ), 
						array( 
							'param_name' => 'thumbnail_image', 
							'heading' => __( 'Thumbnail Image', 'noo' ), 
							'description' => __( 
								'For Image lightbox, thumbnail of original Image is automatically created if you do not choose any thumbnail.', 
								'noo' ), 
							'type' => 'attach_image', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'thumbnail_type', 'value' => array( 'image' ) ) ), 
						array( 
							'param_name' => 'thumbnail_style', 
							'heading' => __( 'Thumbnail Style', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								__( 'None', 'noo' ) => '', 
								__( 'Rounded', 'noo' ) => 'rounded', 
								__( 'Circle', 'noo' ) => 'circle', 
								__( 'Thumbnail', 'noo' ) => 'thumbnail' ), 
							'dependency' => array( 'element' => 'thumbnail_type', 'value' => array( 'image' ) ) ), 
						array( 
							'param_name' => 'thumbnail_title', 
							'heading' => __( 'Thumbnail Title', 'noo' ), 
							'description' => __( 'Title for Thumbnail link.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder, 
							'dependency' => array( 'element' => 'thumbnail_type', 'value' => array( 'link' ) ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [video_player] Video (Self Hosted)
			// ============================
			vc_map( 
				array( 
					'base' => 'video_player', 
					'name' => __( 'Video (Self Hosted)', 'noo' ), 
					'weight' => 555, 
					'class' => 'noo-vc-element noo-vc-element-video_player', 
					'icon' => 'noo-vc-icon-video_player', 
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'video_m4v', 
							'heading' => __( 'M4V File URL', 'noo' ), 
							'description' => __( 'Place the URL to your .m4v video file here.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'video_ogv', 
							'heading' => __( 'OGV File URL', 'noo' ), 
							'description' => __( 'Place the URL to your .ogv video file here.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'video_ratio', 
							'heading' => __( 'Video Aspect Ratio', 'noo' ), 
							'description' => __( 'Choose the aspect ratio for your video.', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								'16:9' => '16:9', 
								'5:3' => '5:3', 
								'5:4' => '5:4', 
								'4:3' => '4:3', 
								'3:2' => '3:2' ) ), 
						array( 
							'param_name' => 'video_poster', 
							'heading' => __( 'Poster Image', 'noo' ), 
							'description' => '', 
							'type' => 'attach_image', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'auto_play', 
							'heading' => __( 'Auto Play Video', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'hide_controls', 
							'heading' => __( 'Hide Player Controls', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'show_play_icon', 
							'heading' => __( 'Show Play Icon', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [video_embed] Video Embed
			// ============================
			vc_map( 
				array( 
					'base' => 'video_embed', 
					'name' => __( 'Video Embed', 'noo' ), 
					'weight' => 550, 
					'class' => 'noo-vc-element noo-vc-element-video_embed', 
					'icon' => 'noo-vc-icon-video_embed', 
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'video_ratio', 
							'heading' => __( 'Video Aspect Ratio', 'noo' ), 
							'description' => __( 'Choose the aspect ratio for your video.', 'noo' ), 
							'type' => 'dropdown', 
							'holder' => $param_holder, 
							'value' => array( 
								'16:9' => '16:9', 
								'5:3' => '5:3', 
								'5:4' => '5:4', 
								'4:3' => '4:3', 
								'3:2' => '3:2' ) ), 
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'Embed Code', 'noo' ), 
							'description' => __( 'Input your &lt;iframe&gt; or &lt;embed&gt; code.', 'noo' ), 
							'type' => 'textarea_safe', 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [audio_player] Audio (Self Hosted)
			// ============================
			vc_map( 
				array( 
					'base' => 'audio_player', 
					'name' => __( 'Audio (Self Hosted)', 'noo' ), 
					'weight' => 545, 
					'class' => 'noo-vc-element noo-vc-element-audio_player', 
					'icon' => 'noo-vc-icon-audio_player', 
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'audio_mp3', 
							'heading' => __( 'MP3 File URL', 'noo' ), 
							'description' => __( 'Place the URL to your .mp3 audio file here.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'audio_oga', 
							'heading' => __( 'OGA File URL', 'noo' ), 
							'description' => __( 'Place the URL to your .oga audio file here.', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder ), 
						array( 
							'param_name' => 'auto_play', 
							'heading' => __( 'Auto Play Audio', 'noo' ), 
							'description' => '', 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [audio_embed] Audio Embed
			// ============================
			vc_map( 
				array( 
					'base' => 'audio_embed', 
					'name' => __( 'Audio Embed', 'noo' ), 
					'weight' => 540, 
					'class' => 'noo-vc-element noo-vc-element-audio_embed', 
					'icon' => 'noo-vc-icon-audio_embed', 
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => $param_content_name, 
							'heading' => __( 'Embed Code', 'noo' ), 
							'description' => __( 'Input your &lt;iframe&gt; or &lt;embed&gt; code.', 'noo' ), 
							'type' => 'textarea_safe', 
							'holder' => $param_content_holder, 
							'value' => $param_content_value ), 
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
			
			// [vc_gmaps]
			// ============================
			vc_map_update( 
				'vc_gmaps', 
				array( 
					'category' => $category_name, 
					'class' => 'noo-vc-element noo-vc-element-maps', 
					'icon' => 'noo-vc-icon-maps', 
					'weight' => 530 ) );
			
			vc_remove_param( 'vc_gmaps', 'link' );
			vc_remove_param( 'vc_gmaps', 'title' );
			vc_remove_param( 'vc_gmaps', 'size' );
			vc_remove_param( 'vc_gmaps', 'el_class' );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => 'link', 
					'heading' => __( 'Map Embed Iframe', 'noo' ), 
					'description' => sprintf( 
						__( 
							'Visit <a href="%s" target="_blank">Google maps</a> and create your map with following steps: 1) Find a location 2) Click "Share" and make sure map is public on the web 3) Click folder icon to reveal "Embed on my site" link 4) Copy iframe code and paste it here.</span>', 
							'noo' ), 
						'http://maps.google.com/' ), 
					'type' => 'textarea_safe', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => 'size', 
					'heading' => __( 'Map Height', 'noo' ), 
					'description' => __( 
						'Enter map height in pixels. Example: 200 or leave it empty to make map responsive.', 
						'noo' ), 
					'type' => 'textfield', 
					'holder' => $param_holder ) );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => 'disable_zooming', 
					'heading' => __( 'Diable Zooming', 'noo' ), 
					'description' => __( 
						'Disable zooming to prevent map from being accidentally zoomed when mouse scrolls over it.', 
						'noo' ), 
					'type' => 'checkbox', 
					'holder' => $param_holder, 
					'value' => array( '' => 'true' ) ) );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => $param_visibility_name, 
					'heading' => $param_visibility_heading, 
					'description' => $param_visibility_description, 
					'type' => $param_visibility_type, 
					'holder' => $param_visibility_holder, 
					'value' => $param_visibility_value ) );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => $param_class_name, 
					'heading' => $param_class_heading, 
					'description' => $param_class_description, 
					'type' => $param_class_type, 
					'holder' => $param_class_holder ) );
			
			vc_add_param( 
				'vc_gmaps', 
				array( 
					'param_name' => $param_custom_style_name, 
					'heading' => $param_custom_style_heading, 
					'description' => $param_custom_style_description, 
					'type' => $param_custom_style_type, 
					'holder' => $param_custom_style_holder ) );
			
			// [social_share]
			// ============================
			vc_map( 
				array( 
					'base' => 'social_share', 
					'name' => __( 'Social Sharing', 'noo' ), 
					'weight' => 510, 
					'class' => 'noo-vc-element noo-vc-element-social_share', 
					'icon' => 'noo-vc-icon-social_share', 
					'deprecated' => '4.9',
					'category' => $category_name, 
					'description' => '', 
					'params' => array( 
						array( 
							'param_name' => 'title', 
							'heading' => __( 'Sharing Title', 'noo' ), 
							'type' => 'textfield', 
							'holder' => $param_holder, 
							'value' => __( 'Share this Post', 'noo' ) ), 
						array( 
							'param_name' => 'facebook', 
							'heading' => __( 'Facebook', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'twitter', 
							'heading' => __( 'Twitter', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'googleplus', 
							'heading' => __( 'Google+', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'pinterest', 
							'heading' => __( 'Pinterest', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						array( 
							'param_name' => 'linkedin', 
							'heading' => __( 'LinkedIn', 'noo' ), 
							'type' => 'checkbox', 
							'holder' => $param_holder, 
							'value' => array( '' => 'true' ) ), 
						
						// array(
						// 'param_name' => 'reddit',
						// 'heading' => __( 'Reddit', 'noo' ),
						// 'type' => 'checkbox',
						// 'holder' => $param_holder,
						// 'value' => array( '' => 'true' ),
						// ),
						// array(
						// 'param_name' => 'email',
						// 'heading' => __( 'Email', 'noo' ),
						// 'type' => 'checkbox',
						// 'holder' => $param_holder,
						// 'value' => array( '' => 'true' ),
						// ),
						array( 
							'param_name' => $param_visibility_name, 
							'heading' => $param_visibility_heading, 
							'description' => $param_visibility_description, 
							'type' => $param_visibility_type, 
							'holder' => $param_visibility_holder, 
							'value' => $param_visibility_value ), 
						array( 
							'param_name' => $param_class_name, 
							'heading' => $param_class_heading, 
							'description' => $param_class_description, 
							'type' => $param_class_type, 
							'holder' => $param_class_holder ), 
						array( 
							'param_name' => $param_custom_style_name, 
							'heading' => $param_custom_style_heading, 
							'description' => $param_custom_style_description, 
							'type' => $param_custom_style_type, 
							'holder' => $param_custom_style_holder ) ) ) );
		}
		
		add_action( 'admin_init', 'noo_vc_media' );
		
		//
		// Extend container class (parents).
		//
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {

			class WPBakeryShortCode_Slider extends WPBakeryShortCodesContainer {
			}
		}
		
		//
		// Extend item class (children).
		//
		if ( class_exists( 'WPBakeryShortCode' ) ) {

			class WPBakeryShortCode_Slide extends WPBakeryShortCode {
			}
		}
	

	endif;
	
	if ( ! function_exists( 'noo_vc_other' ) ) :

		function noo_vc_other() {
			
			//
			// Variables.
			//
			$category_name = __( 'Custom', 'noo' );
			$param_holder = 'div';
			
			// [vc_raw_html]
			// ============================
			vc_map_update( 
				'vc_raw_html', 
				array( 
					'category' => $category_name, 
					'class' => 'noo-vc-element noo-vc-element-raw_html', 
					'icon' => 'noo-vc-icon-raw_html', 
					'weight' => 490 ) );
			
			// [vc_raw_js]
			// ============================
			vc_map_update( 
				'vc_raw_js', 
				array( 
					'category' => $category_name, 
					'class' => 'noo-vc-element noo-vc-element-raw_js', 
					'icon' => 'noo-vc-icon-raw_js', 
					'weight' => 480 ) );
		}
		
		add_action( 'admin_init', 'noo_vc_other' );
	
	
	endif;
endif;