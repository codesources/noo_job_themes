<?php 
$job_id = isset($_GET['job_id']) ?absint($_GET['job_id']) : 0;
$employer_id = get_current_user_id();
$job = get_post($job_id);
$default_job_content = jm_get_job_setting( 'default_job_content', '' );
$content = $job_id ? $job->post_content : $default_job_content;
?>
<div class="job-form">
	<div class="job-form-detail row">
		<div class="form-group row col-md-12 required-field">
			<label for="position" class="col-sm-3 control-label"><?php _e('Job Title','noo')?></label>
			<div class="col-sm-9">
		    	<input type="text" value="<?php echo ($job_id ? $job->post_title : '')?>" class="form-control jform-validate" id="position"  name="position" autofocus required placeholder="<?php echo esc_attr__('Enter a short title for your job','noo')?>">
		    </div>
		</div>
		<div class="form-group row col-md-12">
		    <label for="desc" class="col-sm-3 control-label"><?php _e('Job Description','noo')?></label>
		    <div class="col-sm-9">
				<?php noo_wp_editor($content, 'desc'); ?>
			</div>
		</div>
		<?php 
			$fields = jm_get_job_custom_fields();
			if( !empty( $fields ) ) {
				foreach ($fields as $field) {
					jm_job_render_form_field( $field, $job_id );
				}
			}
		?>
		<div class="form-group row col-md-12">
			<label for="application_email" class="col-sm-3 control-label"><?php _e('Notification Email','noo')?></label>
			<div class="col-sm-9">
		    	<input type="text" value="<?php echo ($job ? noo_get_post_meta($job_id,'_application_email') : '')?>" class="form-control" id="application_email"  name="application_email" >
		    	<em><?php _e('Email to receive application notification. Leave it blank to use your account email.','noo'); ?></em>
		    </div>
		</div>
		<?php $custom_apply_link = jm_get_setting('noo_job_linkedin', 'custom_apply_link' );
			if( $custom_apply_link == 'employer' ) :
		?>
			<div class="form-group row col-md-12">
				<label for="custom_application_url" class="col-sm-3 control-label"><?php _e('Custom Application URL','noo')?></label>
				<div class="col-sm-9">
			    	<input type="text" value="<?php echo ($job ? noo_get_post_meta($job_id,'_custom_application_url') : '')?>" class="form-control" id="custom_application_url"  name="custom_application_url" >
			    	<em><?php _e('Custom link to redirect job seekers to when applying for this job.','noo'); ?></em>
			    </div>
			</div>
		<?php endif; ?>
		<?php do_action('job_form_after_field', $job_id); ?>
	</div>
	<?php if(!jm_get_employer_company($employer_id)):?>
	<div class="job-form-company">
	<h4><?php _e('Company Profile', 'noo')?></h4>
	<?php echo Noo_Member::get_company_profile_form(false)?>
	</div>
	<?php 
	endif;
	?>
</div>
