<?php
if( !function_exists( 'jm_get_candidate_custom_fields_option' ) ) :
	function jm_get_candidate_custom_fields_option($key = '', $default = null){
		$custom_fields = jm_get_setting('jm_candidate_custom_field', array());
		
		if( !$custom_fields || !is_array($custom_fields) ) {
			return $default;
		}

		if( isset($custom_fields['__options__']) && isset($custom_fields['__options__'][$key]) ) {

			return $custom_fields['__options__'][$key];
		}
	
		return $default;
	}
endif;

if( !function_exists( 'jm_get_candidate_custom_fields' ) ) :
	function jm_get_candidate_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
		$custom_fields = noo_get_custom_fields( 'jm_candidate_custom_field', 'jm_candidate_field_');

		if( empty( $custom_fields ) ) {
			$custom_fields = noo_get_custom_fields( 'jm_candidate', 'jm_candidate_field_');
		}

		$default_fields = jm_get_candidate_default_fields();

		$custom_fields = noo_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

		return $suppress_filters ? $custom_fields : apply_filters( 'jm_candidate_custom_fields', $custom_fields );
	}
endif;

if( !function_exists( 'jm_get_candidate_socials' ) ) :
	function jm_get_candidate_socials() {
		$socials = jm_get_candidate_custom_fields_option('socials','facebook,twitter,linkedin,behance,instagram');
		$socials = !is_array( $socials ) ? explode(',', $socials) : $socials;

		return apply_filters( 'jm_get_candidate_socials', $socials );
	}
endif;

if( !function_exists( 'jm_candidate_cf_settings_tabs' ) ) :
	function jm_candidate_cf_settings_tabs( $tabs = array() ) {
		$temp1 = array_slice($tabs, 0, 3);
		$temp2 = array_slice($tabs, 3);

		$resume_cf_tab = array( 'candidate' => __('Candidate','noo') );
		return array_merge($temp1, $resume_cf_tab, $temp2);
	}
	// add to page Custom field (cf) tab.
	add_filter('jm_cf_settings_tabs_array', 'jm_candidate_cf_settings_tabs' );
endif;

if (!function_exists('jm_candidate_custom_fields_setting')) :
	function jm_candidate_custom_fields_setting()
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
		wp_enqueue_style('vendor-chosen-css');
		wp_enqueue_script('vendor-chosen-js');

		$all_socials = noo_get_social_fields();
		$selected_arr = jm_get_candidate_socials();

		noo_custom_fields_setting(
			'jm_candidate_custom_field',
			'jm_candidate_field_',
			jm_get_candidate_custom_fields( true )
		);
		?>
		<h3><?php echo __('Social Fields','noo')?></h3>
			<table class="form-table" cellspacing="0">
				<tbody>
					<tr>
						<th>
							<?php _e('Select Social Networks','noo')?>
						</th>
						<td>
							<?php if($all_socials): ?>
								<select class="social_list_field" name="jm_candidate_custom_field[__options__][socials]" multiple="multiple" style="width: 500px;max-width: 100%;">
									<?php if($selected_arr): ?>
										<?php foreach ((array)$selected_arr as $index => $key): ?>
											<?php if( isset( $all_socials[$key] ) ) : ?>
												<option value="<?php echo esc_attr($key)?>" selected ><?php echo esc_html($all_socials[$key]['label'] ); ?></option>
												<?php unset( $all_socials[$key]); ?>
											<?php else : unset( $selected_arr[$index]); ?>
											<?php endif; ?>
										<?php endforeach;?>
									<?php endif; ?>
									<?php foreach ($all_socials as $key=>$social): ?>
										<option value="<?php echo esc_attr($key)?>" ><?php echo esc_html($social['label'] ); ?></option>
									<?php endforeach;?>
								</select>
								<input name="jm_candidate_custom_field[__options__][socials]" type="hidden" value="<?php echo implode(',', $selected_arr ); ?>"/>
								<script type="text/javascript">
									jQuery(document).ready(function($){
										$("select.social_list_field").chosen( {
											placeholder_text_multiple: "<?php echo __('Select social networks', 'noo'); ?>"
										});

										$("select.social_list_field").change(function() {
											var $this = $(this);
											var chosen_choices = $this.siblings(".chosen-container");
											var values = []; 
											chosen_choices.find('.search-choice > .search-choice-close').each(function(i, el){
												var index = parseInt( $(el).data('option-array-index') ) + 1;
												values[i] = $this.find( 'option:nth-child(' + index + ')' ).val();
											});

											$(this).siblings('input').val( values.join() );
										});
									});
								</script>
								<style type="text/css">
								.chosen-container input[type="text"]{
									height: auto !important;
								}
								</style>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}
	add_action('jm_cf_setting_candidate', 'jm_candidate_custom_fields_setting');
endif;

if( !function_exists( 'jm_candidate_render_form_field') ) :
	function jm_candidate_render_form_field( $field = array(), $candidate_id = 0 ) {
		$field_id = jm_candidate_custom_fields_name( $field['name'], $field );

		$value = !empty( $candidate_id ) ? get_user_meta( $candidate_id, $field_id, true ) : '';
		$value = !is_array($value) ? trim($value) : $value;

		$params = apply_filters( 'jm_candidate_render_form_field_params', compact( 'field', 'field_id', 'value' ), $candidate_id );
		extract($params);
		$object = array( 'ID' => $candidate_id, 'type' => 'user' );

		?>
		<div class="form-group row <?php noo_custom_field_class( $field, $object ); ?>">
			<label for="<?php echo esc_attr($field_id)?>" class="col-sm-4 control-label"><?php echo(isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'])  ?></label>
			<div class="col-sm-8">
		    	<?php noo_render_field( $field, $field_id, $value, '', $object ); ?>
		    </div>
		</div>
		<?php
	}
endif;

if( !function_exists( 'jm_candidate_save_custom_fields') ) :
	function jm_candidate_save_custom_fields( $user_id = 0, $args = array() ) {
		if( empty( $user_id ) ) return;

		$fields = jm_get_candidate_custom_fields();
		if( !empty( $fields ) ) {
			foreach ($fields as $field) {
				if( isset( $field['is_default'] ) ) {
					if( in_array( $field['name'], array( 'first_name', 'last_name', 'full_name', 'email' ) ) )
						continue; // don't display WordPress default user fields
				}
				$field_id = jm_candidate_custom_fields_name( $field['name'], $field );

				if( isset( $args[$field_id] ) ) {
					update_user_meta( $user_id, $field_id, noo_sanitize_field( $args[$field_id], $field ) );
					// noo_save_field( $user_id, $field_id, $args[$field_id], $field, 'user' );
				}
			}
		}
		$socials = jm_get_candidate_socials();
		$all_socials = noo_get_social_fields();
		if(!empty($socials)) {
			foreach ($socials as $social) {
				if( empty( $social ) || !isset( $all_socials[$social] ) ) return;
				
				if( isset( $args[$social] ) ) {
					update_user_meta( $user_id,  $social, noo_sanitize_field( $args[$social], array( 'type' => 'text' ) ) );
					// noo_save_field( $user_id, $field_id, $args[$field_id], array( 'type' => 'text' ), 'user' );
				}
			}
		}
	}
endif;

if( !function_exists( 'jm_candidate_custom_fields_prefix' ) ) :
	function jm_candidate_custom_fields_prefix() {
		return apply_filters( 'jm_candidate_custom_fields_prefix', '_jm_candidate_field_' );
	}
endif;

if( !function_exists( 'jm_candidate_custom_fields_name' ) ) :
	function jm_candidate_custom_fields_name( $field_name = '', $field = array() ) {
		if( empty( $field_name ) ) return '';

		$cf_name = jm_candidate_custom_fields_prefix() . sanitize_title( $field_name );

		if( !empty( $field ) && isset( $field['is_default'] ) ) {
			$cf_name = $field['name'];
		}

		return apply_filters( 'jm_candidate_custom_fields_name', $cf_name, $field_name, $field );
	}
endif;

if ( ! function_exists( 'jm_get_candidate_field' ) ) :
	function jm_get_candidate_field( $field_name = '' ) {
		
		$custom_fields = jm_get_candidate_custom_fields();
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

if ( ! function_exists( 'jm_get_candidate_field_value' ) ) :
	function jm_get_candidate_field_value( $post_id, $field = array() ) {
		$field['type'] = isset( $field['type'] ) ? $field['type'] : 'text';

		$id = jm_candidate_custom_fields_name($field['name'], $field);

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