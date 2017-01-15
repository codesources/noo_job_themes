<?php
if( !function_exists('jm_is_enabled_resume_package_cf') ) :
	function jm_is_enabled_resume_package_cf() {
		$enabled = (bool) jm_get_resume_custom_fields_option('resume_package', '0');

		return jm_is_woo_resume_posting() && $enabled;
	}
endif;

if( !function_exists('jm_get_resume_package_cf') ) :
	function jm_get_resume_package_cf( $package_id = null ) {
		if( ! jm_is_enabled_resume_package_cf() ) {
			return array();
		}

		if( !empty( $package_id ) ) {
			$package_cf = get_post_meta($package_id, '_resume_package_cf', true);
			$package_cf = !is_array( $package_cf ) ? array( $package_cf ) : $package_cf;
			if( empty( $package_cf ) || ( 1 ==count( $package_cf ) && empty( $package_cf[0] ) ) ) {
				return array();
			} elseif( in_array( 'all', $package_cf ) ) {
				return jm_get_resume_custom_fields_option('resume_package_fields', array());
			} elseif( is_array( $package_cf ) ) {
				return $package_cf;
			}

			return array();
		}

		return jm_get_resume_custom_fields_option('resume_package_fields', array());
	}
endif;

if( !function_exists('jm_resume_package_cf_data') ) :
	function jm_resume_package_cf_data() {
		// if( ! jm_is_enabled_resume_package_cf() ) {
		// 	return;
		// }

		global $post;

		$resume_package_cfs = jm_get_resume_custom_fields_option('resume_package_fields', array());

		if( !empty( $resume_package_cfs ) ) {
			$resume_custom_fields = jm_get_resume_custom_fields();
			$selected_fields = array( '' => __('None', 'noo'), 'all' => __('All fields', 'noo') );
			foreach ($resume_custom_fields as $field) {
				if( in_array( $field['name'], $resume_package_cfs ) ) {
					$field_label = ( isset( $field['label_translated'] ) && !empty( $field['label_translated'] ) ) ? $field['label_translated'] : $field['label'];
					$selected_fields[$field['name']] = $field_label;
				}
			}

            noo_wc_wp_select_multiple(
                array(
                    'id' => '_resume_package_cf',
                    'label' => __('Custom Fields', 'noo'),
                    'description' => __('Choose fields that come with this package', 'noo'),
                    'options' => $selected_fields,
                    'desc_tip' => true,
                )
            );
		}
	}

	add_action( 'noo_resume_package_data', 'jm_resume_package_cf_data' );
endif;

if( !function_exists('jm_resume_package_cf_save_data') ) :
	function jm_resume_package_cf_save_data($post_id) {
		// Save meta
		if( isset( $_POST[ '_resume_package_cf' ] ) ) {
			update_post_meta( $post_id, '_resume_package_cf', $_POST[ '_resume_package_cf' ] );
		}
	}

	add_action( 'noo_resume_package_save_data', 'jm_resume_package_cf_save_data' );
endif;

if( !function_exists('jm_resume_package_cf_resume_form') ) :
	function jm_resume_package_cf_resume_form( $custom_fields ) {
		if( !jm_is_enabled_resume_package_cf() ) {
			return $custom_fields;
		}

		// Only work on the member page or resume posting page
		if( !jm_is_resume_posting_page() && ( get_the_ID() != Noo_Member::get_member_page_id() ) ) {
			return $custom_fields;
		}

		$all_resume_package_cfs = jm_get_resume_custom_fields_option('resume_package_fields', array());
		$package = jm_get_resume_posting_info();

		$package_cfs = isset( $package['product_id'] ) ? jm_get_resume_package_cf( $package['product_id'] ) : array();

		$remove_fields = array_diff($all_resume_package_cfs, $package_cfs);
		if( !empty( $remove_fields ) ) {
			foreach ($custom_fields as $index => $field) {
				if( in_array( $field['name'], $remove_fields ) ) {
					unset( $custom_fields[$index] );
				}
			}
		}

		return $custom_fields;
	}

	add_filter( 'jm_resume_custom_fields', 'jm_resume_package_cf_resume_form' );
endif;

if( !function_exists('jm_resume_package_cf_features') ) :
	function jm_resume_package_cf_features( $product ) {
		if( ! jm_is_enabled_resume_package_cf() ) {
			return;
		}
		$all_resume_package_cfs = jm_get_resume_custom_fields_option('resume_package_fields', array());
		$package_cfs = jm_get_resume_package_cf( $product->id );

		foreach ( $all_resume_package_cfs as $field_name ) :
			$field = jm_get_resume_field( $field_name );
			if( empty( $field ) ) continue;

			$field_label = ( isset( $field['label_translated'] ) && !empty( $field['label_translated'] ) ) ? $field['label_translated'] : $field['label'];
			if( in_array( $field_name, $package_cfs ) ) : ?>
				<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( __('Post resume with %s','noo'), $field_label );?></li>
			<?php else : ?>
				<li class="noo-li-icon"><i class="fa fa-times-circle-o not-good"></i> <?php echo sprintf( __('No %s','noo'), $field_label );?></li>
			<?php endif; ?>
		<?php endforeach;
	}

	add_action( 'jm_resume_package_features_list', 'jm_resume_package_cf_features' );
endif;

if( !function_exists('jm_candidate_manage_plan_cf_features') ) :
	function jm_candidate_manage_plan_cf_features( $package ) {
		if( !Noo_Member::is_candidate() || !jm_is_enabled_resume_package_cf() || !isset( $package['product_id'] ) ) {
			return;
		}
		$all_resume_package_cfs = jm_get_resume_custom_fields_option('resume_package_fields', array());
		$package_cfs = jm_get_resume_package_cf( $package['product_id'] );

		foreach ( $all_resume_package_cfs as $field_name ) :
			$field = jm_get_resume_field( $field_name );
			if( empty( $field ) ) continue;

			$field_label = ( isset( $field['label_translated'] ) && !empty( $field['label_translated'] ) ) ? $field['label_translated'] : $field['label'];
			$icon = in_array( $field_name, $package_cfs ) ? 'fa-check-circle' : 'fa-times-circle-o not-good';
			?>
			<div class="col-xs-6"><strong><?php echo sprintf( __('Post with %s','noo'), $field_label ); ?></strong></div>
			<div class="col-xs-6"><?php echo in_array( $field_name, $package_cfs ) ? __('Yes','noo') : __('No', 'noo');?></div>
		<?php endforeach;
	}

	add_action( 'jm_manage_plan_features_list', 'jm_candidate_manage_plan_cf_features' );
endif;


if( !function_exists('jm_resume_package_cf_options') ) :
	function jm_resume_package_cf_options() {
		$resume_package = jm_get_resume_custom_fields_option('resume_package', '0');
		$resume_package_fields = jm_get_resume_custom_fields_option('resume_package_fields', array());
		?>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr>
					<th>
						<?php _e('Integrate Custom Field with Resume Package','noo') ?>
					</th>
					<td>
						<input type="hidden" name="noo_resume_custom_field[__options__][resume_package]" value="0" />
						<input type="checkbox" name="noo_resume_custom_field[__options__][resume_package]" value="1" <?php checked( $resume_package ); ?> id="resume-package-cf-enabled"/><br/>
						<em><?php echo __('Enable this function and you can decide which fields employer can use with each Resume Package', 'noo'); ?></em>
					</td>
				</tr>
				<tr class="resume-package-cf">
					<th>
						<?php _e('Fields to add to Resume Package','noo'); ?>
					</th>
					<td>
						<?php $custom_fields = jm_get_resume_custom_fields(); ?>
						<select class="noo-admin-chosen" name="noo_resume_custom_field[__options__][resume_package_fields][]" multiple="multiple" style="width: 500px;max-width: 100%;">
							<?php foreach ($custom_fields as $key => $field) : ?>
								<option <?php selected( in_array( $field['name'], $resume_package_fields ), true ); ?> value="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></option>
							<?php endforeach; ?>
						</select>
						<br/><em><?php echo __('Fields selected here will required buying specific Resume Package. Please continue edit on Resume Package Products.', 'noo'); ?></em>
					</td>
				</tr>
			</tbody>
		</table>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#resume-package-cf-enabled').change(function(event) {
					if( $(this).is(':checked') ) {
						$('.resume-package-cf').show();
					} else {
						$('.resume-package-cf').hide();
					}
				});

				$('#resume-package-cf-enabled').change();
			});
		</script>
		<?php
	}

	add_action( 'jm_resume_custom_fields_setting_options', 'jm_resume_package_cf_options' );
endif;

