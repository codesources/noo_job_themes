<?php
if( !function_exists( 'jm_company_render_social_field') ) :
	function jm_company_render_social_field( $social = '', $company_id = 0 ) {
		$all_socials = noo_get_social_fields();
		if( empty( $social ) || !isset( $all_socials[$social] ) ) return;

		$field = $all_socials[$social];
		$field['name'] = '_' . $social;
		$field['type'] = 'text';
		$field['value'] = $social == 'email' ? 'email@' . $_SERVER['HTTP_HOST'] : 'http://';
		$field_id = $field['name'];

		$value = !empty( $company_id ) ? get_post_meta( $company_id, $field_id, true ) : '';
		$value = !is_array($value) ? trim($value) : $value;

		$params = apply_filters( 'jm_company_render_social_field_params', compact( 'field', 'field_id', 'value' ), $company_id );
		extract($params);
		$object = array( 'ID' => $company_id, 'type' => 'post' );

		$field_id = esc_attr($field_id);
		?>
		<div class="form-group row <?php noo_custom_field_class( $field, $object ); ?>">
			<label for="<?php echo $field_id; ?>" class="col-sm-3 control-label"><?php echo esc_html( $field['label'] ); ?></label>
			<div class="col-sm-9">
				<?php noo_render_field( $field, $field_id, $value, '', $object ); ?>
			</div>
		</div>
		<?php
	}
endif;
