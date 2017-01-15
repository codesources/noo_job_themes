<?php
require_once NOO_FRAMEWORK . '/common/google-map/location.php';

if( !function_exists('jm_geolocation_enabled') ) :
	function jm_geolocation_enabled(){
		return apply_filters( 'noo_job_geolocation_enabled', true );	
	}
endif;

if( !function_exists('jm_get_geolocation') ) :
	function jm_get_geolocation( $raw_address = '' ) {
		$invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "" );
		$raw_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );
	
		if ( empty( $raw_address ) ) {
			return false;
		}
	
		$transient_name              = 'geocode_' . md5( $raw_address );
		$geocoded_address            = get_transient( $transient_name );
		$jm_geocode_over_query_limit = get_transient( 'jm_geocode_over_query_limit' );
	
		// Query limit reached - don't geocode for a while
		if ( $jm_geocode_over_query_limit && false === $geocoded_address ) {
			return false;
		}
	
		try {
			if ( false === $geocoded_address || empty( $geocoded_address->results[0] ) ) {
				$result = wp_remote_get(
					apply_filters( 'noo_job_geolocation_endpoint', "http://maps.googleapis.com/maps/api/geocode/json?address=" . $raw_address . "&region=" . apply_filters( 'noo_job_geolocation_region_cctld', '', $raw_address ), $raw_address ),
					array(
						'timeout'     => 60,
						'redirection' => 1,
						'httpversion' => '1.1',
						'user-agent'  => 'NooJob; ' . home_url( '/' ),
						'sslverify'   => false
					)
				);
				if ( ! is_wp_error( $result ) && $result['body'] ) {
					$result           = wp_remote_retrieve_body( $result );
					$geocoded_address = json_decode( $result );
		
					if ( $geocoded_address->status ) {
						switch ( $geocoded_address->status ) {
							case 'ZERO_RESULTS' :
								throw new Exception( __( "No results found", 'noo' ) );
								break;
							case 'OVER_QUERY_LIMIT' :
								set_transient( 'jm_geocode_over_query_limit', 1, MINUTE_IN_SECONDS );
								throw new Exception( __( "Query limit reached", 'noo' ) );
								break;
							case 'OK' :
								if ( ! empty( $geocoded_address->results[0] ) ) {
									set_transient( $transient_name, $geocoded_address, MONTH_IN_SECONDS );
								} else {
									throw new Exception( __( "Geocoding error", 'noo' ) );
								}
								break;
							default :
								throw new Exception( __( "Geocoding error", 'noo' ) );
								break;
						}
					} else {
						throw new Exception( __( "Geocoding error", 'noo' ) );
					}
				}else {
					throw new Exception( __( "Geocoding error", 'noo' ) );
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}
	
		$address                      = array();
		$address['lat']               = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lat );
		$address['long']              = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lng );
		$address['formatted_address'] = sanitize_text_field( $geocoded_address->results[0]->formatted_address );
	
		if ( ! empty( $geocoded_address->results[0]->address_components ) ) {
			$address_data             = $geocoded_address->results[0]->address_components;
			$street_number            = false;
			$address['street']        = false;
			$address['city']          = false;
			$address['state_short']   = false;
			$address['state_long']    = false;
			$address['zipcode']       = false;
			$address['country_short'] = false;
			$address['country_long']  = false;
	
			foreach ( $address_data as $data ) {
				switch ( $data->types[0] ) {
					case 'street_number' :
						$address['street']        = sanitize_text_field( $data->long_name );
						break;
					case 'route' :
						$route = sanitize_text_field( $data->long_name );
	
						if ( ! empty( $address['street'] ) )
							$address['street'] = $address['street'] . ' ' . $route;
						else
							$address['street'] = $route;
						break;
					case 'sublocality_level_1' :
					case 'locality' :
						$address['city']          = sanitize_text_field( $data->long_name );
						break;
					case 'administrative_area_level_1' :
						$address['state_short']   = sanitize_text_field( $data->short_name );
						$address['state_long']    = sanitize_text_field( $data->long_name );
						break;
					case 'postal_code' :
						$address['postcode']      = sanitize_text_field( $data->long_name );
						break;
					case 'country' :
						$address['country_short'] = sanitize_text_field( $data->short_name );
						$address['country_long']  = sanitize_text_field( $data->long_name );
						break;
				}
			}
		}
	
		return $address;
	}
endif;

if( !function_exists('jm_job_location_save_geo_data') ) :
	function jm_job_location_save_geo_data($term_id, $tt_id, $taxonomy) {
		if( 'job_location' === $taxonomy && jm_geolocation_enabled() ) {
			if( function_exists( 'get_term_meta' ) ) {
				// $geolocation = get_term_meta( $term_id, '_geolocation', true );

				// if( empty( $geolocation ) ) {
					$term = get_term($term_id, 'job_location');
					if( $term && !is_wp_error($term) ) {
						$geolocation = jm_get_geolocation($term->slug);

						update_term_meta( $term_id, '_geolocation', $geolocation );
					}
				// }
			} else {
				// Support for WordPress version 4.3 and older.
				$noo_job_geolocation = get_option('noo_job_geolocation');
				if ( ! $noo_job_geolocation ) {
					$noo_job_geolocation = array();
				}
				
				$term = get_term($term_id, 'job_location');
				if($term && !is_wp_error($term) ){
					if(!isset($noo_job_geolocation[$term->slug])){
						$location_geo_data = jm_get_geolocation($term->name);
						if($location_geo_data && !is_wp_error($location_geo_data)){
							$noo_job_geolocation[$term->slug] = $location_geo_data;
						}
					}
				}

				//update geo option
				update_option('noo_job_geolocation', $noo_job_geolocation);
			}

			delete_transient( 'jm_transient_job_markers' );
		}
	}

	add_action( 'created_term', 'jm_job_location_save_geo_data', 10, 3 );
	add_action( 'edit_term', 'jm_job_location_save_geo_data', 10, 3 );
endif;

if( !function_exists('jm_location_enqueue_scripts') ) :
	function jm_location_enqueue_scripts(){
		if( is_page() && ( jm_is_job_posting_page() || jm_is_resume_posting_page() || get_the_ID() == Noo_Member::get_member_page_id() ) ) {
			wp_enqueue_script('google-map');
		}
	}
	add_action( 'wp_enqueue_scripts', 'jm_location_enqueue_scripts', 100 );
endif;

if( !function_exists( 'jm_job_render_field_job_location' ) ) :
	function jm_job_render_field_job_location( $field = array(), $field_id = '', $value = array(), $form_type = '', $object = array() )  {
		$company_location = false;
		$checkbox_value = false;
		if( !empty( $object ) && isset( $object['ID'] ) ) {
			$job_id = absint( $object['ID'] );
			$checkbox_label = __('The same as company Address', 'noo');
			$checkbox_id = '_use_company_location';

			$company_id = jm_get_employer_company();
			$location_term_id = !empty( $company_id ) ? get_post_meta( $company_id, '_address', true ) : '';
			$location_term = !empty( $location_term_id ) ? get_term( $location_term_id, 'job_location' ) : '';
			$location_term = !empty( $location_term ) ? $location_term->slug : '';

			if( !empty( $location_term ) )  :
				$company_location = true;
				$checkbox_value = empty( $job_id ) ? 1 : get_post_meta( $job_id, $checkbox_id, true );
				if( $checkbox_value && empty( $value ) ) {
					$value = array( $location_term );
				}
				?>
				<input name="<?php echo $checkbox_id; ?>" type="hidden" value="0" />
				<div class="form-control-flat">
					<label class="checkbox">
						<input id="use_company_location" name="<?php echo $checkbox_id; ?>" type="checkbox" <?php checked( $checkbox_value ); ?> value="1" /><i></i>
						<?php echo esc_html($checkbox_label); ?>
					</label>
				</div>

			<?php endif;
		}
		
		?>
		<div id="job_location_field" class="<?php echo $checkbox_value ? 'hidden' : ''; ?>">
			<?php
			$allow_user_input = strpos($field['type'], 'input') !== false;
			$field['type'] = strpos($field['type'], 'single') !== false ? 'select' : 'multiple_select';
			noo_render_select_field( $field, $field_id, $value, $form_type );

			if( $form_type != 'search' && $allow_user_input ) {
				jm_job_add_new_location();
			} ?>
		</div>
		<?php if( $company_location ) : ?>
			<script>
				jQuery(document).ready( function (){
					jQuery("#use_company_location").change( function() {
						if( jQuery(this).is(":checked") ) {
							jQuery("#job_location_field").addClass('hidden');
						} else {
							jQuery("#job_location_field").removeClass('hidden');
						}
					}).change();
				});				
			</script>
		<?php endif;
	}
	
	add_filter( 'noo_render_field_job_location',			'jm_job_render_field_job_location', 10, 5 );
	add_filter( 'noo_render_field_multi_location_input',	'jm_job_render_field_job_location', 10, 5 );
	add_filter( 'noo_render_field_multi_location',			'jm_job_render_field_job_location', 10, 5 );
	add_filter( 'noo_render_field_single_location_input',	'jm_job_render_field_job_location', 10, 5 );
	add_filter( 'noo_render_field_single_location',			'jm_job_render_field_job_location', 10, 5 );
endif;

if( !function_exists( 'jm_job_add_new_location' ) ) :
	function jm_job_add_new_location( $data_type = 'slug' )  {
		?>
		<p class="help-block add-new-location"><a class="add-new-location-btn" href="#add-location"><?php esc_html_e('+ Add New Location','noo')?></a></p>
		<div class="add-new-location-content" style="display:none">	
			<div class="row">
				<div class="col-sm-6">
					<input id="add-google-location" type="text" value="" class="form-control input-sm" placeholder="<?php echo esc_attr__('Enter new location','noo')?>" style="height: 36px;">
				</div>
				<div class="col-sm-6">
					<button class="btn btn-small btn-default add-new-location-submit" data-return-type="<?php echo $data_type; ?>" type="button" style="height: 36px;"><?php _e('Add','noo')?></button>
				</div>
			</div>
		</div>
		<?php $enable_auto_complete = jm_get_location_setting( 'enable_auto_complete', 1 ); ?>
		<?php if( $enable_auto_complete ) :
			$country_restriction = jm_get_location_setting('country_restriction', '');
		?>
			<script>
				jQuery(document).ready( function (){
					var input = document.getElementById('add-google-location');
					var options = {
					  <?php if( !empty( $country_restriction ) ) : ?>
					  	componentRestrictions: {country: '<?php echo $country_restriction; ?>'},
					  <?php endif; ?>
					  types: ['<?php echo jm_get_location_setting( 'location_type', 'cities' ); ?>']
					};

					autocomplete = new google.maps.places.Autocomplete(input, options);
					google.maps.event.addDomListener(input, 'keydown', function(e) { 
						if (e.keyCode == 13) { 
							e.preventDefault(); 
						}
					});
				});
			</script>
		<?php endif;
	}
endif;

if( !function_exists( 'jm_job_get_term_geolocation' ) ) :
	function jm_job_get_term_geolocation( $term = null )  {
		$term_id = is_object( $term ) ? $term->term_id : (is_numeric( $term ) ? $term : 0);
		if( empty( $term_id ) ) return false;

		$term = is_object( $term ) ? $term : get_term( $term_id, 'job_location' );
		if( empty( $term ) || is_wp_error($term) ) return false;
		$geolocation = false;
		if( function_exists( 'get_term_meta' ) ) {
			$geolocation = get_term_meta( $term_id, '_geolocation', true );

			if( empty( $geolocation ) ) {
				$geolocation = jm_get_geolocation($term->slug);

				update_term_meta( $term_id, '_geolocation', $geolocation );
			}
		} else {
			// Support for WordPress version 4.3 and older.
			$noo_job_geolocation = get_option('noo_job_geolocation');
			if( !empty( $noo_job_geolocation ) && isset( $noo_job_geolocation[$term->slug] ) ) {
				$geolocation = $noo_job_geolocation[$term->slug];
			} else {
				$geolocation = jm_get_geolocation($term->slug);

				$noo_job_geolocation = empty( $noo_job_geolocation ) ? array() : $noo_job_geolocation;
				$noo_job_geolocation[$term->slug] = $geolocation;

				update_option('noo_job_geolocation', $noo_job_geolocation);
			}
		}

		return $geolocation;
	}
endif;

if( !function_exists( 'jm_build_job_map_data' ) ) :
	function jm_build_job_map_data(){
		if ( false !== ( $result = get_transient( 'jm_transient_job_markers' ) ) ) {
			return $result;
		}

		$args = array(
			'post_type'		=>'noo_job',
			'nopaging'		=>true,
			'post_status'   =>'publish',
		);
		$markers = array();
		$r = new WP_Query($args);
		if($r->have_posts()):
			while ($r->have_posts()):
				$r->the_post();
				global $post;
					
				$job_locations = get_the_terms($post->ID, 'job_location');
				if($job_locations && !is_wp_error($job_locations)){
					foreach($job_locations as $job_location){
						$job_location_geo_data = jm_job_get_term_geolocation( $job_location );
						if( empty( $job_location_geo_data ) or is_wp_error($job_location_geo_data) ) {
							continue;
						}

						$company_logo		= '';
						$company_url		= '';
						$company_name		= '';
						$company_id			= jm_get_job_company($post);
						$type_name			= '';
						$type_url			= '';
						$type_color			= '';
		
						$type				= jm_get_job_type( $post );
						if( $type ) {
							$type_name		= $type->name;
							$type_url		= get_term_link($type,'job_type');
							$type_color		= $type->color;
						}

						if( !empty( $company_id ) ) {
							$company_logo   = Noo_Company::get_company_logo( $company_id );
							$company_url	= get_permalink( $company_id );
							$company_name	= get_the_title( $company_id );
						}

						$marker = array(
							'latitude'=>$job_location_geo_data['lat'],
							'longitude'=>$job_location_geo_data['long'],
							'title'=> utf8_encode( htmlentities(get_the_title($post->ID)) ),
							'image'=>$company_logo,
							'type'=>$type_name,
							'type_url'=>$type_url,
							'type_color'=>$type_color,
							'url'=>get_permalink($post->ID),
							'company_url'=>$company_url,
							'company'=> utf8_encode( htmlentities($company_name) ),
							'term'=>$job_location->slug,
							'term_url'=>get_term_link($job_location,'job_location')
						);
						$markers[] = $marker;
					}
				}
			endwhile;
			wp_reset_postdata();
			wp_reset_query();
		endif;

		$result = json_encode($markers);
		set_transient( 'jm_transient_job_markers', $result, DAY_IN_SECONDS );

		return $result;
	}
endif;

if( !function_exists( 'jm_remove_transient_job_markers' ) ) :

	/**
	 * Remove job markers transient whenever a job is created or updated
	 * @param  int $post_id ID of the job
	 */
	function jm_remove_transient_job_markers( $post_id ) {
		if( 'noo_job' == get_post_type( $post_id ) ) {
			delete_transient( 'jm_transient_job_markers' );
		}
	}
	add_action( 'save_post', 'jm_remove_transient_job_markers', 10, 1 );
endif;


if( !function_exists( 'jm_search_job_location' ) ) :
	function jm_search_job_location($search_name = ''){
		$data   =   array();
		$args = array(
			'hide_empty'=>false,
		);
		if(!empty($search_name)){
			$args['name__like'] = $search_name;	
		}
		$locations = (array) get_terms('job_location',$args);
		foreach($locations as $location){
			$key = esc_attr($location->slug);
			$data[$key] =  $location->name;
		};
		return $data;
	}
endif;
