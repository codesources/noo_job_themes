<?php
global $post;
if ( is_user_logged_in() ) {
	$user            = wp_get_current_user();
	$candidate_name  = $user->display_name;
	$candidate_email = $user->user_email;
}else{
	$candidate_name  = '';
	$candidate_email = '';
}

$application_attachment = jm_get_application_setting( 'application_attachment', 'enabled' ) == 'enabled';
$application_resume = jm_get_application_setting( 'application_resume', 'enabled' ) == 'enabled' && Noo_Member::is_candidate() && jm_resume_enabled();
$allow_multiple_attachment = jm_get_application_setting( 'allow_multiple_attachment', '') == 'yes';
$require_attachment = jm_get_application_setting( 'require_attachment', 'yes') == 'yes';

$show_button = true;

if ( $application_resume ) {
	$args = apply_filters( 'noo_application_resume_query_args', array(
		'post_type'=>'noo_resume',
		'posts_per_page' => -1,
		'post_status'=>array('publish'),
		'author'=>get_current_user_id(),
	) );
	$resumes = get_posts($args);
} else {
	$resumes = false;
}
$application_resume = $application_resume && !empty( $resumes ) && count( $resumes );

$can_apply = !$require_attachment || $application_attachment || $application_resume;
$message_opt = jm_get_application_setting('application_message', 'required');

?>
<div id="applyJobModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="applyJobModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="applyJobModalLabel"><?php esc_html_e('Apply for job','noo')?></h4>
			</div>
			<div class="modal-body">
				<?php if( $can_apply ) : ?>
					<form id="apply_job_form" class="form-horizontal jform-validate" method="post" enctype="multipart/form-data">
						<div style="display: none">
							<input type="hidden" name="action" value="apply_job"> 
							<input type="hidden" name="job_id" value="<?php echo esc_attr($post->ID)?>">			
							<?php wp_nonce_field('noo-apply-job')?>
						</div>
						<div class="form-group text-center noo-ajax-result" style="display: none"></div>
						<div class="form-group required-field">
							<label for="candidate_name" class="control-label"><?php _e('Name','noo')?></label>
							<input type="text" class="form-control jform-validate" id="candidate_name" value="<?php echo esc_attr($candidate_name)?>" name="candidate_name" autofocus required placeholder="<?php echo esc_attr__('Name','noo')?>">
						</div>
						<div class="form-group required-field">
							<label for="candidate_email" class="control-label"><?php _e('Email','noo')?></label>
							<input type="email" class="form-control jform-validate jform-validate-email" id="candidate_email" value="<?php echo esc_attr($candidate_email)?>" name="candidate_email" required placeholder="<?php echo esc_attr__('Email','noo')?>">
						</div>
						<?php 
						$fields = jm_get_application_custom_fields();
						if( !empty( $fields ) ) {
							foreach ($fields as $field) {
								jm_application_render_apply_form_field( $field );
							}
						}
						?>
						<div class="form-group">
							<div class="row">
								<?php if( $application_attachment ) : ?>
									<?php 
										$allowed_exts = jm_get_allowed_attach_file_types();
								    	$max_upload_size = wp_max_upload_size();
								    	if ( ! $max_upload_size ) {
								    		$max_upload_size = 0;
								    	}
								    	$atm_name = $allow_multiple_attachment ? 'application_attachment[]' : 'application_attachment';
								    	$atm_multiple = $allow_multiple_attachment ? 'multiple' : '';
								    	$atm_required = $require_attachment && !$application_resume ? 'required' : '';
								    ?>
									<div class="col-sm-6">
										<label for="application_attachment" class="control-label"><?php _e('Upload CV','noo')?></label>
										<div class="form-control-flat">
											<label class="form-control-file"> <span class="form-control-file-button"><i class="fa fa-folder-open"></i> <?php _e('Browse','noo')?></span>
												<input type="text" readonly value="" class="form-control" autocomplete="off">
												<input type="file" name="<?php echo $atm_name; ?>" class="jform-validate-uploadcv" <?php echo "{$atm_required} {$atm_multiple}"; ?> accept="<?php echo '.' . implode(',.', $allowed_exts); ?>">
											</label>
										</div>
										<p class="help-block"><?php printf( __( 'Maximum upload file size: %s', 'noo' ), esc_html( size_format( $max_upload_size ) ) ); ?></p>
										<p class="help-block"><?php echo sprintf( __('Allowed file: %s', 'noo'), '.' . implode(', .', $allowed_exts) ); ?></p>
									</div>
								<?php endif; ?>
								<?php if($application_resume) : ?>
									<?php $resume_required = $require_attachment && !$application_attachment ? 'required' : ''; ?>
									<div class="col-sm-6 <?php echo ( $resume_required == 'required' ? 'required-field' : '' ); ?>">
										<label for="email" class="control-label"><?php _e('Select Resume','noo')?></label>
										<div class="form-control-flat">
											<select name="resume" <?php echo $resume_required; ?>>
												<option value=""><?php _e('-Select-','noo')?></option>
												<?php foreach ( $resumes as $resume ) : ?>
												<option value="<?php echo $resume->ID; ?>" ><?php echo $resume->post_title; ?></option>
												<?php endforeach;?>
											</select>
											<i></i>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php do_action( 'after_apply_job_form' ); ?>
						<?php if ( $show_button == true ) : ?>
							<div class="modal-actions">
								<button type="submit" class="btn btn-primary"><?php _e('Send application','noo')?></button>
							</div>
						<?php endif; ?>
					</form>
				<?php else : ?>
					<h4><?php echo __("You need a resume to apply for a job. Please create a resume first.",'noo')?></h4>
					<p>
						<a href="<?php echo Noo_Member::get_post_resume_url(); ?>" class="btn btn-primary"><?php _e('Create New Resume', 'noo'); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>