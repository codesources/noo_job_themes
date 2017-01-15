<?php
if( !function_exists('jm_job_pre_get_posts') ) :
	function jm_job_pre_get_posts($query) {
		if( is_admin() ) {
			return $query;
		}

		if( jm_is_job_query( $query) ) {
			if( $query->is_main_query() && $query->is_singular ) {
				if( !$query->is_preview && empty( $query->query_vars['post_status'] ) ) {
					// add expired to viewable link
					$post_status = array( 'publish', 'expired' );
					if( current_user_can( 'edit_posts' ) ) {
						$post_status[] = 'pending';
					}
					$query->set( 'post_status', $post_status );
				}

				return $query;
			}

			if (is_post_type_archive('noo_job') && noo_get_option('noo_jobs_orderby_featured', false)){
				$query->set( 'orderby', 'meta_value' );
				$query->set( 'meta_key', '_featured' );
			}

			$paged = get_query_var('page');
			if(!empty($paged)){
				$query->set('paged', $paged);
			}
			// if ( $query->is_search ) {
				$query = jm_job_query_from_request( $query, $_GET );
			// }
		}
	}

	add_action( 'pre_get_posts', 'jm_job_pre_get_posts' );
endif;

if( !function_exists('jm_is_job_query') ) :
	function jm_is_job_query( &$query ) {
		$job_query = ( isset($query->query_vars['post_type']) && ($query->query_vars['post_type'] === 'noo_job') );
		if( $job_query ) return true;

		if( $query->is_tax ) {
			if( ( isset( $query->query_vars['job_category'] ) && !empty( $query->query_vars['job_category'] ) )
				|| ( isset( $query->query_vars['job_type'] ) && !empty( $query->query_vars['job_type'] ) )
				|| ( isset( $query->query_vars['job_location'] ) && !empty( $query->query_vars['job_location'] ) )
				|| ( isset( $query->query_vars['job_tag'] ) && !empty( $query->query_vars['job_tag'] ) ) ) {
				return true;
			}
		}

		$home_query = $query->get( 'page_id' ) == get_option( 'page_on_front' ) && get_option( 'page_on_front' );
		if( $home_query && get_post_field( 'post_name', $query->get('page_id') ) == jm_get_job_setting('archive_slug') ) {
			$query->set('post_type', 'noo_job');
	        $query->set('page_id', ''); //Empty

	        //Set properties that describe the page to reflect that
	        //we aren't really displaying a static page
	        $query->is_page = 0;
	        $query->is_singular = 0;
	        $query->is_post_type_archive = 1;
	        $query->is_archive = 1;

			return true;
		}

		return false;
	}
endif;

if( !function_exists('jm_user_job_query') ) :
	function jm_user_job_query( $employer_id='', $is_paged = true, $status = array() ) {
		if(empty($employer_id)){
			$employer_id = get_current_user_ID();
		}
		
		$args = array(
			'post_type'=>'noo_job',
			'author'=>$employer_id,
		);
		
		if($is_paged){
			if( is_front_page() || is_home()) {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
			} else {
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			}
			$args['paged'] = $paged;
		}

		if( !empty( $status ) ) {
			$args['post_status'] = $status;
		} else {
			$args['post_status'] = array('publish','pending','pending_payment','expired','inactive');
		}

		$user_job_query = new WP_Query($args);

		return $user_job_query;
	}
endif;

if( !function_exists('jm_job_query_from_request') ) :
	function jm_job_query_from_request( &$query, $REQUEST = array() ) {
		if( empty( $query ) || empty( $REQUEST ) ) {
			return $query;
		}

		$tax_query = array();
		$tax_list = array(
			'location' => 'job_location',
			'category' => 'job_category',
			'type' => 'job_type',
			'tag' => 'job_tag'
		);
		$tax_list = apply_filters( 'jm_job_query_tax_list', $tax_list );
		foreach ($tax_list as $tax_key => $term) {
			if( isset( $REQUEST[$tax_key] ) && !empty( $REQUEST[$tax_key] ) ) {
				$tax_query[] = array(
					'taxonomy'     => $term,
					'field'        => 'slug',
					'terms'        => $REQUEST[$tax_key]
				);
			}
		}

		$tax_query = apply_filters( 'jm_job_search_tax_query', $tax_query, $REQUEST );

		if( !empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
				$query->tax_query->queries = $tax_query;
				$query->query_vars['tax_query'] = $query->tax_query->queries;

				// tag is a reserved keyword so we'll have to remove it from the query
				unset( $query->query['tag'] );
				unset( $query->query_vars['tag'] );
				unset( $query->query_vars['tag__in'] );
				unset( $query->query_vars['tag_slug__in'] );
			} elseif( is_array( $query ) ) {
				$query['tax_query'] = $tax_query;
			}
		}

		$meta_query = array();
		$get_keys = array_keys($REQUEST);

		$job_fields = jm_get_job_search_custom_fields();
		foreach ($job_fields as $field) {
			$field_id = jm_job_custom_fields_name( $field['name'], $field );
			if( isset( $REQUEST[$field_id] ) && !empty( $REQUEST[$field_id]) ) {
				$value = noo_sanitize_field( $REQUEST[$field_id], $field );
				if(is_array($value)){
					$temp_meta_query = array( 'relation' => 'OR' );
					foreach ($value as $v) {
						if( empty( $v ) ) continue;
						$temp_meta_query[]	= array(
							'key'     => $field_id,
							'value'   => '"'.$v.'"',
							'compare' => 'LIKE'
						);
					}
					$meta_query[] = $temp_meta_query;
				} else {
					$meta_query[]	= array(
						'key'     => $field_id,
						'value'   => $value
					);
				}
			} elseif( ( isset( $field['type'] ) && $field['type'] == 'datepicker' ) && ( isset( $REQUEST[$field_id.'_start'] ) || isset( $REQUEST[$field_id.'_end'] ) ) ) {
				$value_start = isset( $REQUEST[$field_id.'_start'] ) && !empty( $REQUEST[$field_id.'_start'] ) ? $REQUEST[$field_id.'_start'] : 0;
				$value_end = isset( $REQUEST[$field_id.'_end'] ) && !empty( $REQUEST[$field_id.'_end'] ) ? $REQUEST[$field_id.'_end'] : 0;
				if( !empty( $value_start ) || !empty( $value_end ) ) {
					if( $field_id == 'date' ) {
						$date_query = array();
						if( !empty( $value_start ) ) {
							$start = is_numeric( $value_start ) ? date('Y-m-d', $value_start) : $value_start;
							$date_query['after'] = date('Y-m-d', strtotime( $start . ' -1 day' ) );
						}
						if( isset( $value_end ) && !empty( $value_end ) ) {
							$end = is_numeric( $value_end ) ? date('Y-m-d', $value_end) : $value_end;
							$date_query['before'] = date('Y-m-d', strtotime( $end . ' +1 day' ) );
						}

						if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
							$query->query_vars['date_query'][] = $date_query;
						} elseif( is_array( $query ) ) {
							$query['date_query'] = $date_query;
						}
					} else {
						$value_start = !empty( $value_start ) ? noo_sanitize_field( $value_start, $field ) : 0;
						$value_start = !empty( $value_start ) ? strtotime("midnight", $value_start) : 0;
						$value_end = !empty( $value_end ) ? noo_sanitize_field( $value_end, $field ) : 0;
						$value_end = !empty( $value_end ) ? strtotime("tomorrow", strtotime("midnight", $value_end)) - 1 : strtotime( '2090/12/31');

						$meta_query[]	= array(
							'key'     => $field_id,
							'value'   => array( $value_start, $value_end ),
							'compare' => 'BETWEEN',
							'type' => 'NUMERIC'
						);
					}
				}
			}
		}

		$meta_query = apply_filters( 'jm_job_search_meta_query', $meta_query, $REQUEST );

		if( !empty( $meta_query ) ) {
			$meta_query['relation'] = 'AND';
			if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
				$query->query_vars['meta_query'][] = $meta_query;
			} elseif( is_array( $query ) ) {
				$query['meta_query'] = $meta_query;
			}
		}

		return apply_filters( 'jm_job_search_query', $query, $REQUEST );
	}
endif;
