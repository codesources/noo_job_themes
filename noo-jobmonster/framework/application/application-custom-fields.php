<?php

if( !function_exists( 'jm_application_admin_init' ) ) :
	function jm_application_admin_init(){
		register_setting('jm_application_custom_field', 'jm_application_custom_field');
	}
	
	add_filter('admin_init', 'jm_application_admin_init' );
endif;

if( !function_exists( 'jm_get_application_custom_fields' ) ) :
	function jm_get_application_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
		$custom_fields = noo_get_custom_fields( 'jm_application_custom_field', 'jm_application_field_');

		if( empty( $custom_fields ) ) {
			$custom_fields = noo_get_custom_fields( 'jm_application', 'jm_application_field_');
		}

		$default_fields = jm_get_application_default_fields();

		$custom_fields = noo_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

		return $suppress_filters ? $custom_fields : apply_filters('jm_application_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_application_cf_settings_tabs' ) ) :
	function jm_application_cf_settings_tabs( $tabs = array() ) {
		$tabs['application'] = __('Application','noo');

		return $tabs;
	}
	// add to page Custom field (cf) tab.
	add_filter('jm_cf_settings_tabs_array', 'jm_application_cf_settings_tabs' );
endif;

if (!function_exists('jm_application_custom_fields_setting')) :
	function jm_application_custom_fields_setting()
	{
		wp_enqueue_style('noo-custom-fields');
		wp_enqueue_script('noo-custom-fields');

		if(function_exists( 'wp_enqueue_media' )){
			wp_enqueue_media();
		}else{
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}

		noo_custom_fields_setting(
			'jm_application_custom_field',
			'jm_application_field_',
			jm_get_application_custom_fields( true )
		);
	}
	add_action('jm_cf_setting_application', 'jm_application_custom_fields_setting');
endif;

if( !function_exists( 'jm_application_render_apply_form_field') ) :
	function jm_application_render_apply_form_field( $field = array() ) {
		$field_id = jm_application_custom_fields_name( $field['name'], $field );

		$params = apply_filters( 'jm_application_render_apply_form_field_params', compact( 'field', 'field_id' ) );
		extract($params);

		?>
		<div class="form-group <?php noo_custom_field_class( $field ); ?>">
			<label for="<?php echo esc_attr($field_id)?>" class="control-label"><?php echo(isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'])  ?></label>
		    <?php noo_render_field( $field, $field_id ); ?>
		</div>
		<?php
	}
endif;

// if( !function_exists( 'jm_candidate_save_custom_fields') ) :
// 	function jm_candidate_save_custom_fields( $user_id = 0, $args = array() ) {
// 		if( empty( $user_id ) ) return;

// 		$fields = jm_get_candidate_custom_fields();
// 		if( !empty( $fields ) ) {
// 			foreach ($fields as $field) {
// 				if( isset( $field['is_default'] ) ) {
// 					if( in_array( $field['name'], array( 'first_name', 'last_name', 'full_name', 'email' ) ) )
// 						continue; // don't display WordPress default user fields
// 				}
// 				$field_id = jm_candidate_custom_fields_name( $field['name'], $field );

// 				if( isset( $args[$field_id] ) ) {
// 					update_user_meta( $user_id, $field_id, noo_sanitize_field( $args[$field_id], $field ) );
// 					// noo_save_field( $user_id, $field_id, $args[$field_id], $field, 'user' );
// 				}
// 			}
// 		}
// 	}
// endif;

if( !function_exists( 'jm_application_custom_fields_prefix' ) ) :
	function jm_application_custom_fields_prefix() {
		return apply_filters( 'jm_application_custom_fields_prefix', '_jm_application_field_' );
	}
endif;

if( !function_exists( 'jm_application_custom_fields_name' ) ) :
	function jm_application_custom_fields_name( $field_name = '', $field = array() ) {
		if( empty( $field_name ) ) return '';

		$cf_name = jm_application_custom_fields_prefix() . sanitize_title( $field_name );

		if( !empty( $field ) && isset( $field['is_default'] ) ) {
			$cf_name = $field['name'];
		}

		return apply_filters( 'jm_application_custom_fields_name', $cf_name, $field_name, $field );
	}
endif;

if ( ! function_exists( 'jm_get_application_field_value' ) ) :
	function jm_get_application_field_value( $post_id, $field = array() ) {
		$field['type'] = isset( $field['type'] ) ? $field['type'] : 'text';

		$id = jm_application_custom_fields_name($field['name'], $field);

		$value = $post_id ? get_post_meta( $post_id, $id, true ) : '';
		$value = !is_array($value) ? trim($value) : $value;
		if( !empty( $value ) ) {
			$value = noo_convert_custom_field_value( $field, $value );
			if( is_array( $value ) ) {
				$value = implode(', ', $value);
			}
		}

		return $value;
	}
endif;