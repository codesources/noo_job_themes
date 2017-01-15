<?php

if( !function_exists( 'jm_get_resume_default_fields' ) ) :
	function jm_get_resume_default_fields() {
		$default_fields = array(
			'_job_location' => array(
					'name' => '_job_location',
					'label' => __('Job Location', 'noo'),
					'type' => 'multi_tax_location',
					'allowed_type' => array(
						'multi_tax_location'		=> __('Multiple Location', 'noo'),
						'multi_tax_location_input'	=> __('Multiple Location with Input', 'noo'),
						'single_tax_location'		=> __('Single Location', 'noo'),
						'single_tax_location_input'	=> __('Single Location with Input', 'noo'),
					),
					// 'allowed_type' => array(
					// 	'select'			=> __('Select', 'noo'),
					// 	'multiple_select'	=> __( 'Multiple Select', 'noo' ),
					// 	'radio'				=> __( 'Radio', 'noo' ),
					// 	'checkbox'			=> __( 'Checkbox', 'noo' )
					// ),
					'value' => '',
					'std' => '',
					'is_default' => true,
					'is_tax' => true,
					'required' => false
				),
			'_job_category' => array(
					'name' => '_job_category',
					'label' => __('Job Category', 'noo'),
					'type' => 'multiple_select',
					'allowed_type' => array(
						'select'			=> __('Select', 'noo'),
						'multiple_select'	=> __( 'Multiple Select', 'noo' ),
						'radio'				=> __( 'Radio', 'noo' ),
						'checkbox'			=> __( 'Checkbox', 'noo' )
					),
					'value' => '',
					'std' => '',
					'is_default' => true,
					'is_tax' => true,
					'required' => true
				),
			'_language' => array(
					'name' => '_language',
					'label' => __('Language', 'noo'),
					'type' => 'text',
					'value' => '',
					'std' => __( 'Your working language', 'noo' ),
					'is_default' => true,
					'required' => false
				),
			'_highest_degree' => array(
					'name' => '_highest_degree',
					'label' => __('Highest Degree Level', 'noo'),
					'type' => 'text',
					'value' => '',
					'std' => __( 'eg. &quot;Bachelor Degree&quot;', 'noo' ),
					'is_default' => true,
					'required' => false
				),
			'_experience_year' => array(
					'name' => '_experience_year',
					'label' => __('Total Years of Experience', 'noo'),
					'type' => 'text',
					'value' => '',
					'std' => __( 'eg. &quot;1&quot;, &quot;2&quot;', 'noo' ),
					'is_default' => true,
					'required' => false
				),
			'_job_level' => array(
					'name' => '_job_level',
					'label' => __('Expected Job Level', 'noo'),
					'type' => 'text',
					'value' => '',
					'std' => __( 'eg. &quot;Junior&quot;, &quot;Senior&quot;', 'noo' ),
					'is_default' => true,
					'required' => false
				),
			'_noo_url_video' => array(
					'name' => '_noo_url_video',
					'label' => __('Video URL', 'noo'),
					'type' => 'embed_video',
					'allowed_type' => array(
						'embed_video' => __( 'Embedded Video', 'noo' ),
					),
					'value' => '',
					'is_disabled' => ( (bool) jm_get_resume_setting( 'enable_video','') ) ? '' : 'yes',
					'is_default' => true,
					'required' => false
				),
			);

		return apply_filters( 'jm_resume_default_fields', $default_fields );
	}
endif;

if( !function_exists( 'jm_resume_tax_field_params' ) ) :
	function jm_resume_tax_field_params( $args = array(), $resume_id = 0 )  {
		extract($args);

		if( in_array( $field['name'], array( '_job_category', '_job_location' ) ) ) {
			$field_id = $field['name'];

			$field_value = array();
			$term_id = substr( $field_id, 1 );
			$terms = get_terms( $term_id, array( 'hide_empty' => 0 ) );
			foreach ($terms as $term) {
				$field_value[] = $term->term_id . '|' . $term->name;
			}
			$field['value'] = $field_value;
			$field['no_translate'] = true;

			if( !empty( $resume_id ) ) {
				$value = jm_resume_get_tax_value( $resume_id, $field_id );
			}

			if( empty( $field['type'] ) || $field['type'] == 'text' ) {
				$default_fields = jm_get_resume_default_fields();
				$field['type'] = $default_fields[$field['name']]['type'];
			}
		}

		return compact( 'field', 'field_id', 'value' );
	}
	
	add_filter( 'jm_resume_render_form_field_params', 'jm_resume_tax_field_params', 10, 2 );
	add_filter( 'jm_resume_render_search_field_params', 'jm_resume_tax_field_params' );
endif;

if( !function_exists( 'jm_resume_meta_box_tax_field_params' ) ) :
	function jm_resume_meta_box_tax_field_params( $args = array(), $resume = null )  {
		if( !empty( $resume->ID ) && $resume->post_type == 'noo_resume' && in_array( $args['id'], array( '_job_category', '_job_location' ) ) ) {
			$args['meta'] = jm_resume_get_tax_value( $resume->ID, $args['id'] );
		}

		return $args;
	}
	
	add_filter( 'noo_meta_box_field_params', 'jm_resume_meta_box_tax_field_params', 10, 2 );
endif;

if( !function_exists( 'jm_resume_get_tax_value' ) ) :
	function jm_resume_get_tax_value( $resume_id = 0, $field_id = '_job_location' )  {
		if( empty( $resume_id ) ) return array();

		$value = noo_get_post_meta( $resume_id, $field_id, '' );
		$value = noo_json_decode( $value );

		if( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$taxonomy = substr( $field_id, 1 );
			foreach ( $value as $index => $v ) {
				$value[$index] = apply_filters( 'wpml_object_id', $v, $taxonomy, true );
			}
		}

		return $value;
	}
endif;

if( !function_exists( 'jm_wpml_duplicate_resume_tax_fields' ) ) :
	function jm_wpml_duplicate_resume_tax_fields( $master_post_id, $lang, $post_array, $id )  {
		if( empty( $id ) || empty( $master_post_id ) ) return false;
		if( $post_array['post_type'] == 'noo_resume' ) {
			foreach (array( '_job_category', '_job_location' ) as $tax) {
				$tax_values = get_post_meta( $master_post_id, $tax, true );
				$tax_values = noo_json_decode( $tax_values );
				$tax_name = substr( $tax, 1 );

				foreach ( $tax_values as $index => $v ) {
					$tax_values[$index] = apply_filters( 'wpml_object_id', $v, $tax_name, true, $lang );
				}

				update_post_meta( $id, $tax, json_encode($tax_values, JSON_UNESCAPED_UNICODE) );
			}
		}
	}

	add_action( 'icl_make_duplicate', 'jm_wpml_duplicate_resume_tax_fields', 10, 4 );
endif;
