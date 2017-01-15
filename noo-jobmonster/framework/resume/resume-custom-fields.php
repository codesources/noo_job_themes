<?php

if( !function_exists( 'jm_get_resume_custom_fields' ) ) :
	function jm_get_resume_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
		$custom_fields = noo_get_custom_fields( 'noo_resume_custom_field', 'noo_resume_field_');

		$default_fields = jm_get_resume_default_fields();

		$custom_fields = noo_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

		return $suppress_filters ? $custom_fields : apply_filters('jm_resume_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_get_resume_search_custom_fields' ) ) :
	function jm_get_resume_search_custom_fields() {
		$custom_fields = jm_get_resume_custom_fields();

		$not_searchable = noo_not_searchable_custom_fields_type();
		foreach ($custom_fields as $key => $field) {
			if(!empty($field['type'])){
				if( in_array( $field['type'], $not_searchable ) ) {
					unset( $custom_fields[$key] );
				}
			}
		}

		return apply_filters( 'jm_resume_search_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_get_resume_custom_fields_option' ) ) :
	function jm_get_resume_custom_fields_option($key = '', $default = null){
		$custom_fields = jm_get_setting('noo_resume_custom_field', array());
		
		if( !$custom_fields || !is_array($custom_fields) ) {
			return $default;
		}

		if( isset($custom_fields['__options__']) && isset($custom_fields['__options__'][$key]) ) {

			return $custom_fields['__options__'][$key];
		}
	
		return $default;
	}
endif;

if( !function_exists( 'jm_rcf_settings_tabs' ) ) :
	function jm_rcf_settings_tabs( $tabs = array() ) {
		$temp1 = array_slice($tabs, 0, 1);
		$temp2 = array_slice($tabs, 1);

		$resume_cf_tab = array( 'resume' => __('Resume','noo') );
		return array_merge($temp1, $resume_cf_tab, $temp2);
	}
	// add to page Custom field (cf) tab.
	add_filter('jm_cf_settings_tabs_array', 'jm_rcf_settings_tabs' );
endif;

if (!function_exists('jm_resume_custom_fields_setting')) :
	function jm_resume_custom_fields_setting()
	{
		wp_enqueue_style('noo-custom-fields');
		wp_enqueue_script('noo-custom-fields');

		noo_custom_fields_setting(
			'noo_resume_custom_field',
			'noo_resume_field_',
			jm_get_resume_custom_fields( true )
		);

		do_action( 'jm_resume_custom_fields_setting_options' );
	}
	add_action('jm_cf_setting_resume', 'jm_resume_custom_fields_setting');
endif;

if( !function_exists( 'jm_resume_render_form_field') ) :
	function jm_resume_render_form_field( $field = array(), $resume_id = 0 ) {
		$field_id = jm_resume_custom_fields_name( $field['name'], $field );
		$value = !empty( $resume_id ) ? noo_get_post_meta( $resume_id, $field_id, '' ) : '';
		$value = !is_array($value) ? trim($value) : $value;

		$params = apply_filters( 'jm_resume_render_form_field_params', compact( 'field', 'field_id', 'value' ), $resume_id );
		extract($params);
		$object = array( 'ID' => $resume_id, 'type' => 'post' );

		?>
		<div class="form-group row <?php noo_custom_field_class( $field, $object ); ?>">
			<label for="<?php echo esc_attr($field_id)?>" class="col-sm-5 control-label"><?php echo(isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'])  ?></label>
			<div class="col-sm-7">
				<?php noo_render_field( $field, $field_id, $value, '', $object ); ?>
		    </div>
		</div>
		<?php
	}
endif;

if( !function_exists( 'jm_resume_render_search_field') ) :
	function jm_resume_render_search_field( $field = array() ) {
		$field_id = jm_resume_custom_fields_name( $field['name'], $field );

		$params = apply_filters( 'jm_resume_render_search_field_params', compact( 'field', 'field_id', 'value' ) );
		extract($params);

		$field['required'] = ''; // no need for required fields in search form

		$value = isset($_GET[$field_id]) ? $_GET[$field_id] : '';
		$value = !is_array($value) ? trim($value) : $value;
		?>
		<div class="form-group">
			<label for="<?php echo 'search-' . esc_attr($field_id)?>" class="control-label"><?php echo(isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'])  ?></label>
			<div class="advance-search-form-control">
				<?php
					// if ( $field['type'] == "text" ) {
					// 	global $wpdb;
					// 	$field['value'] = $wpdb->get_col(
					// 		$wpdb->prepare('
					// 			SELECT DISTINCT meta_value
					// 			FROM %1$s
					// 			LEFT JOIN %2$s ON %1$s.post_id = %2$s.ID
					// 			WHERE meta_key = \'%3$s\' AND post_type = \'%4$s\' AND post_status = \'%5$s\'
					// 			', $wpdb->postmeta, $wpdb->posts, $field_id, 'noo_resume', 'publish'));
					// 	$field['type'] = 'select';
					// 	$field['no_translate'] = true;
					// }
					noo_render_field( $field, $field_id, $value, 'search' );
				?>
		    </div>
		</div>
		<?php
	}
endif;

if( !function_exists( 'jm_resume_advanced_search_field' ) ) :
	function jm_resume_advanced_search_field( $field_val = '' ) {
		if(empty($field_val) || $field_val == 'no' )
			return '';

		$field_arr = explode('|', $field_val);
		$field_id = isset( $field_arr[0] ) ? $field_arr[0] : '';

		if( empty( $field_id ) ) return '';

		$fields = jm_get_resume_search_custom_fields();

		$field_prefix = jm_resume_custom_fields_prefix();
		$field_id = str_replace($field_prefix, '', $field_id);

		foreach ($fields as $field) {
			if ( sanitize_title( $field['name'] ) == $field_id ) {
				jm_resume_render_search_field( $field );
				break;
			}
		}
		return '';
	}
endif;

if( !function_exists( 'jm_resume_custom_fields_prefix' ) ) :
	function jm_resume_custom_fields_prefix() {
		return apply_filters( 'jm_resume_custom_fields_prefix', '_noo_resume_field_' );
	}
endif;

if( !function_exists( 'jm_resume_custom_fields_name' ) ) :
	function jm_resume_custom_fields_name( $field_name = '', $field = array() ) {
		if( empty( $field_name ) ) return '';

		$cf_name = jm_resume_custom_fields_prefix() . sanitize_title( $field_name );

		if( !empty( $field ) && isset( $field['is_default'] ) ) {
			$cf_name = $field['name'];
		}

		return apply_filters( 'jm_resume_custom_fields_name', $cf_name, $field_name, $field );
	}
endif;

if ( ! function_exists( 'jm_get_resume_field' ) ) :
	function jm_get_resume_field( $field_name = '' ) {
		
		$custom_fields = jm_get_resume_custom_fields();
		if( isset( $custom_fields[$field_name] ) ) {
			return $custom_fields[$field_name];
		}

		foreach ($custom_fields as $field) {
			if( $field_name == $field['name'] ) {
				return $field;
			}
		}

		return array();
	}
endif;

if ( ! function_exists( 'jm_get_resume_field_value' ) ) :
	function jm_get_resume_field_value( $resume_id, $field = array() ) {
		$field['type'] = isset( $field['type'] ) ? $field['type'] : 'text';
		$id = jm_resume_custom_fields_name($field['name'],$field);

		$value = $resume_id ? noo_get_post_meta($resume_id, $id, '') : '';
		if( $id == '_job_category' ) {
			if( !empty( $value ) ) {
				$value = jm_resume_get_tax_value( $resume_id, $id );
				$category_terms = empty( $value ) ? array() : get_terms( 'job_category', array('include' => array_merge($value, array(-1)), 'hide_empty' => 0, 'fields' => 'names') );
				$value = implode(', ', $category_terms);
			}
		} elseif( $id == '_job_location' ) {
			if( !empty( $value ) ) {
				$value = jm_resume_get_tax_value( $resume_id, $id );
				$location_terms = empty( $value ) ? array() : get_terms( 'job_location', array('include' => array_merge($value, array(-1)), 'hide_empty' => 0, 'fields' => 'names') );
				$value = implode(', ', $location_terms);
			}
		} else {
			$value = !is_array($value) ? trim($value) : $value;
			// if( !empty( $value ) ) {
			// 	$value = noo_convert_custom_field_value( $field, $value );
			// 	if( is_array( $value ) ) {
			// 		$value = implode(', ', $value);
			// 	}
			// }
		}

		return $value;
	}
endif;