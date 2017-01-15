<?php

if( !function_exists( 'jm_get_job_default_fields' ) ) :
	function jm_get_job_default_fields() {
		$default_fields = array(
				'_cover_image' => array(
					'name' => '_cover_image',
					'label' => __('Cover Image', 'noo'),
					'is_default' => true,
					'is_disabled' => ( jm_get_job_setting( 'cover_image','yes') == 'yes' ? '' : 'yes' ),
					'type' => 'single_image',
					'allowed_type' => array(
						'single_image' => __( 'Single Image', 'noo' ),
					),
					'required' => false
				),
				'job_category' => array(
					'name' => 'job_category',
					'label' => __('Job Category', 'noo'),
					'is_default' => true,
					'is_tax' => true,
					'type' => 'multiple_select',
					'allowed_type' => array(
						'select'			=> __('Select', 'noo'),
						'multiple_select'	=> __( 'Multiple Select', 'noo' ),
						'radio'				=> __( 'Radio', 'noo' ),
						'checkbox'			=> __( 'Checkbox', 'noo' )
					),
					'required' => true
				),
				'job_type' => array(
					'name' => 'job_type',
					'label' => __('Job Type', 'noo'),
					'is_default' => true,
					'is_tax' => true,
					'type' => 'select',
					'allowed_type' => array( 
						'select'			=> __( 'Select', 'noo'),
						'radio'				=> __( 'Radio', 'noo' ),
					),
					'required' => true
				),
				'job_location' => array(
					'name' => 'job_location',
					'label' => __('Job Location', 'noo'),
					'is_default' => true,
					'is_tax' => true,
					'type' => 'multi_location_input',
					'allowed_type' => array(
						'multi_location_input'	=> __('Multiple Location with Input', 'noo'),
						'multi_location'		=> __('Multiple Location', 'noo'),
						'single_location_input'	=> __('Single Location with Input', 'noo'),
						'single_location'		=> __('Single Location', 'noo'),
					),
					'required' => true
				),
				'job_tag' => array(
					'name' => 'job_tag',
					'label' => __('Job Tag', 'noo'),
					'is_default' => true,
					'is_tax' => true,
					'type' => 'multiple_select',
					'allowed_type' => array(
						'select'			=> __('Select', 'noo'),
						'multiple_select'	=> __( 'Multiple Select', 'noo' ),
						'radio'				=> __( 'Radio', 'noo' ),
						'checkbox'			=> __( 'Checkbox', 'noo' )
					),
					'is_disabled' => 'yes',
					'required' => false
				),
				'_closing' => array(
					'name' => '_closing',
					'label' => __('Closing Date', 'noo'),
					'desc' => __('Set a date or leave blank to automatically use the Expired date', 'noo'),
					'is_default' => true,
					'type' => 'datepicker',
					'allowed_type' => array(
						'datepicker'			=> __('Date Picker', 'noo')
					),
					'required' => false
				),
			);

		return apply_filters( 'jm_job_default_fields', $default_fields );
	}
endif;

if( !function_exists( 'jm_job_tax_field_params' ) ) :
	function jm_job_tax_field_params( $args = array(), $job_id = 0 )  {
		extract($args);

		if( in_array( $field['name'], array( 'job_category', 'job_location', 'job_type', 'job_tag' ) ) ) {
			$field_id = str_replace('job_', '', $field['name']);
			$field_value = array();
			$terms = get_terms( $field['name'], array( 'hide_empty' => 0 ) );
			foreach ($terms as $term) {
				if( $field['name'] == 'job_category' || $field['name'] == 'job_type' ) {
					$field_value[] = $term->term_id . '|' . $term->name;
				} else {
					$field_value[] = $term->slug . '|' . $term->name;
				}
			}

			$field['value'] = $field_value;
			$field['no_translate'] = true;

			$value = array();
			if( !empty( $job_id ) ) {
				if( $field['name'] == 'job_category' || $field['name'] == 'job_type' ) {
					$value = wp_get_object_terms( $job_id, $field['name'], array( 'fields' => 'ids' ) );
				} else {
					$value = wp_get_object_terms( $job_id, $field['name'], array( 'fields' => 'slugs' ) );
				}
			}

			if( empty( $field['type'] ) || $field['type'] == 'text' ) {
				$default_fields = jm_get_job_default_fields();
				$field['type'] = $default_fields[$field['name']]['type'];
			}
		}

		return compact( 'field', 'field_id', 'value' );
	}
	
	add_filter( 'jm_job_render_form_field_params', 'jm_job_tax_field_params', 10, 2 );
endif;

if( !function_exists( 'jm_job_tax_search_field_params' ) ) :
	function jm_job_tax_search_field_params( $args = array(), $job_id = 0 )  {
		extract($args);

		if( in_array( $field['name'], array( 'job_category', 'job_location', 'job_type', 'job_tag' ) ) ) {
			$field_id = str_replace('job_', '', $field['name']);
			$field_value = array();
			$terms = get_terms( $field['name'], array( 'hide_empty' => 1 ) );
			foreach ($terms as $term) {
				$field_value[] = $term->slug . '|' . $term->name;
			}
			$field['value'] = $field_value;
			$field['no_translate'] = true;

			if( isset( $_GET[$field_id] ) && !empty( $_GET[$field_id] ) ) {
				$value = $_GET[$field_id];
			} else {
				if( is_tax( $field['name'] ) ) {
					global $wp_query;
					$term_id = $wp_query->get_queried_object_id();
					$term = get_term( $term_id, $field['name'] );
					$value = !empty( $term ) && !is_wp_error( $term ) ? $term->slug : '';
				}
			}

			$value = !is_array($value) ? trim($value) : $value;

			if( empty( $field['type'] ) || $field['type'] == 'text' ) {
				$default_fields = jm_get_job_default_fields();
				$field['type'] = $default_fields[$field['name']]['type'];
			}
		}

		return compact( 'field', 'field_id', 'value' );
	}
	
	add_filter( 'jm_job_render_search_field_params', 'jm_job_tax_search_field_params' );
endif;

if( !function_exists( 'jm_job_get_tax_value' ) ) :
	function jm_job_get_tax_value( $job_id = 0, $field_id = 'job_category' )  {
		if( empty( $job_id ) ) return array();

		$value = array();
		$terms = get_the_terms( $job_id, $field_id );
		if( !empty( $terms ) && !is_wp_error( $terms ) ) {
			foreach ($terms as $term) {
				$value[] = '<a href="' . get_term_link($term->term_id,$field_id) . '"><em>' . $term->name . '</em></a>';
			}
		}

		return $value;
	}
endif;
