<?php 
$company_id = isset($_GET['company_id']) ? absint($_GET['company_id']) : 0;
$company = get_post($company_id);
$user_ID = get_current_user_id();
$company_name = ( $company_id ? $company->post_title  : '' );
$content = $company_id ? $company->post_content : '';
?>
<div class="company-profile-form">
	<div class="form-group row">
		<label for="company_name" class="col-sm-3 control-label"><?php _e('Company Name','noo')?></label>
		<div class="col-sm-9">
	    	<input type="text" class="form-control" autofocus id="company_name" value="<?php echo $company_name;?>"  name="company_name" placeholder="<?php echo esc_attr__('Enter your company name','noo')?>">
	    </div>
	</div>
	<div class="form-group row">
	    <label for="company_desc" class="col-sm-3 control-label"><?php _e('Company Description','noo')?></label>
	    <div class="col-sm-9">
			<?php
			noo_wp_editor($content, 'company_desc');
			?>
	    </div>
	</div>
	<?php
		$fields = jm_get_company_custom_fields();
		if( !empty( $fields ) ) {
			foreach ($fields as $field) {
				jm_company_render_form_field( $field, $company_id );
			}
		}
	?>
	<?php $socials = jm_get_company_socials();
		if(!empty($socials)) {
			foreach ($socials as $social) {
				jm_company_render_social_field( $social, $company_id );
			}
		}
	?>
</div>