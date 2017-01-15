<?php

if( !function_exists( 'jm_get_job_custom_fields' ) ) :
	function jm_get_job_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
		$custom_fields = noo_get_custom_fields( 'noo_job_custom_field', 'noo_job_field_');

		$default_fields = jm_get_job_default_fields();

		$custom_fields = noo_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

		return $suppress_filters ? $custom_fields : apply_filters('jm_job_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_get_job_search_custom_fields' ) ) :
	function jm_get_job_search_custom_fields() {
		$custom_fields = jm_get_job_custom_fields();
		$date_field = array(
			'name' => 'date',
			'type' => 'datepicker',
			'label' => __('Publishing Date', 'noo'),
			'is_default' => true,
		);
		$custom_fields[] = $date_field;
		$not_searchable = noo_not_searchable_custom_fields_type();
		foreach ($custom_fields as $key => $field) {
			if( in_array( $field['type'], $not_searchable ) ) {
				unset( $custom_fields[$key] );
			}
		}

		return apply_filters( 'jm_job_search_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_job_custom_fields_prefix' ) ) :
	function jm_job_custom_fields_prefix() {
		return apply_filters( 'jm_job_custom_fields_prefix', '_noo_job_field_' );
	}
endif;

if( !function_exists( 'jm_job_custom_fields_name' ) ) :
	function jm_job_custom_fields_name( $field_name = '', $field = array() ) {
		if( empty( $field_name ) ) return '';

		$cf_name = jm_job_custom_fields_prefix() . sanitize_title( $field_name );

		if( !empty( $field ) && isset( $field['is_default'] ) ) {
			$cf_name = $field['name'];
		}

		return apply_filters( 'jm_job_custom_fields_name', $cf_name, $field_name, $field );
	}
endif;

if ( ! function_exists( 'jm_get_job_field' ) ) :
	function jm_get_job_field( $field_name = '' ) {
		
		$custom_fields = jm_get_job_custom_fields();
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

if( !function_exists( 'jm_get_job_custom_fields_option' ) ) :
	function jm_get_job_custom_fields_option($key = '', $default = null){
		$custom_fields = jm_get_setting('noo_job_custom_field', array());
		
		if( !$custom_fields || !is_array($custom_fields) ) {
			return $default;
		}

		if( isset($custom_fields['__options__']) && isset($custom_fields['__options__'][$key]) ) {

			return $custom_fields['__options__'][$key];
		}
	
		return $default;
	}
endif;

if( !function_exists( 'jm_job_cf_settings_tabs' ) ) :
	function jm_job_cf_settings_tabs( $tabs = array() ) {
		$temp1 = array_slice($tabs, 0, 1);
		$temp2 = array_slice($tabs, 1);

		$job_cf_tab = array( 'job' => __('Job','noo') );
		return array_merge($temp1, $job_cf_tab, $temp2);
	}
	// Add to Custom Field (cf) tab.
	add_filter('jm_cf_settings_tabs_array', 'jm_job_cf_settings_tabs', 5 );
endif;


if( !function_exists( 'jm_job_custom_fields_setting' ) ) :
	function jm_job_custom_fields_setting(){
		wp_enqueue_style('noo-custom-fields');
		wp_enqueue_script('noo-custom-fields');

		noo_custom_fields_setting( 
			'noo_job_custom_field',
			'noo_job_field_',
			jm_get_job_custom_fields( true )
		);

		$field_display = jm_get_job_custom_fields_option('display_position', 'after');
		?>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr>
					<th>
						<?php _e('Show Custom Fields:','noo') ?>
					</th>
					<td>
						<select class="regular-text" name="noo_job_custom_field[__options__][display_position]">
							<option <?php selected( $field_display,'before')?> value="before"><?php _e('Before Description','noo')?></option>
							<option <?php selected( $field_display,'after')?>  value="after"><?php _e('After Description','noo')?></option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<?php do_action( 'jm_job_custom_fields_setting_options' );
	}
	add_action('jm_cf_setting_job', 'jm_job_custom_fields_setting');
endif;

if( !function_exists( 'jm_job_render_form_field') ) :
	function jm_job_render_form_field( $field = array(), $job_id = 0 ) {
		$field_id = jm_job_custom_fields_name( $field['name'], $field );

		$value = !empty( $job_id ) ? noo_get_post_meta( $job_id, $field_id, '' ) : '';
		$value = isset( $_REQUEST[$field_id] ) ? $_REQUEST[$field_id] : $value;
		$value = !is_array($value) ? trim($value) : $value;

		$params = apply_filters( 'jm_job_render_form_field_params', compact( 'field', 'field_id', 'value' ), $job_id );
		extract($params);
		$object = array( 'ID' => $job_id, 'type' => 'post' );

		?>
		<div class="form-group row col-md-12 <?php noo_custom_field_class( $field, $object ); ?>">
			<label for="<?php echo esc_attr($field_id)?>" class="col-sm-3 control-label"><?php echo(isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'])  ?></label>
			<div class="col-sm-9">
				<?php noo_render_field( $field, $field_id, $value, '', $object ); ?>
		    </div>
		</div>
		<?php
	}
endif;

if( !function_exists( 'jm_job_render_search_field') ) :
	function jm_job_render_search_field( $field = array() ) {
		$field_id = jm_job_custom_fields_name($field['name'], $field);

		$field['required'] = ''; // no need for required fields in search form

		$value = isset($_GET[$field_id]) ? $_GET[$field_id] : '';
		$value = !is_array($value) ? trim($value) : $value;

		$params = apply_filters( 'jm_job_render_search_field_params', compact( 'field', 'field_id', 'value' ) );
		extract($params);
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
					// 			', $wpdb->postmeta, $wpdb->posts, $field_id, 'noo_job', 'publish'));
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

if( !function_exists( 'jm_job_advanced_search_field' ) ) :
	function jm_job_advanced_search_field( $field_val = '' ) {
		if(empty($field_val) || $field_val == 'no' )
			return '';

		$field_arr = explode('|', $field_val);
		$field_id = isset( $field_arr[0] ) ? $field_arr[0] : '';

		if( empty( $field_id ) ) return '';

		$fields = jm_get_job_search_custom_fields();

		$field_prefix = jm_job_custom_fields_prefix();
		$field_id = str_replace($field_prefix, '', $field_id);

		foreach ($fields as $field) {
			if ( sanitize_title( $field['name'] ) == str_replace($field_prefix, '', $field_id) ) {
				jm_job_render_search_field( $field );
				break;
			}
		}
		return '';
	}
endif;

if( !function_exists( 'jm_job_save_custom_fields') ) :
	function jm_job_save_custom_fields( $post_id = 0, $args = array() ) {
		if( empty( $post_id ) ) return;

		// Update custom fields
		$fields = jm_get_job_custom_fields();
		if(!empty($fields)) {
			foreach ($fields as $field) {
				if( isset( $field['is_tax'] ) && $field['is_tax'] ) {
					continue;
				}

				$id = jm_job_custom_fields_name($field['name'], $field);
				if( isset( $args[$id] ) ) {
					noo_save_field( $post_id, $id, $args[$id], $field );
				}
			}
		}
	}
endif;

if( !function_exists( 'jm_job_display_custom_fields') ) :
	function jm_job_display_custom_fields() {
		$fields = jm_get_job_custom_fields();
		if(!empty($fields)) {
			$html = array();

			foreach ( $fields as $field ) {
				// if( isset( $field['is_tax'] ) )
				// 	continue;
				if( $field['name'] == '_closing' ) // reserve the _closing field
					continue;

				$id = jm_job_custom_fields_name($field['name'], $field);
				if( isset( $field['is_tax'] ) ) {
					$value = jm_job_get_tax_value();
					$value = implode( ',', $value );
				} else {
					$value = noo_get_post_meta(get_the_ID(), $id, '');
				}

				if( !empty( $value ) ) {
					$html[] = '<li class="job-cf">' . noo_display_field( $field, $id, $value, array( 'label_tag' => 'strong', 'label_class' => '', 'value_tag' => 'span' ), false) . '</li>';
				}
			}

			if( !empty( $html ) && count( $html ) > 0 ) : ?>
				<div class="job-custom-fields">
					<h3><?php echo esc_html__( 'More Information', 'noo' ) ?></h3>
					<ul>
						<?php echo implode("\n", $html); ?>
					</ul>
				</div>

			<?php endif;
		}
	}

	$field_pos = jm_get_job_custom_fields_option('display_position', 'after');
	if( $field_pos == 'before' ) {
		add_action( 'jm_job_detail_content_before', 'jm_job_display_custom_fields' );
	} else {
		add_action( 'jm_job_detail_content_after', 'jm_job_display_custom_fields' );
	}
endif;