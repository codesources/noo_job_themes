<?php 
$resume_id = isset($_GET['resume_id']) ?absint($_GET['resume_id']) : 0;
$resume = $resume_id ? get_post($resume_id) : '';
?>
<?php do_action('noo_post_resume_general_before'); ?>
<div class="resume-form">
	<div class="resume-form-general row">
		<div class="col-sm-6">
			<div class="form-group row required-field">
				<label for="title" class="col-sm-5 control-label"><?php _e('Resume Title','noo')?></label>
				<div class="col-sm-7">
			    	<input type="text" value="<?php echo ($resume ? $resume->post_title : '')?>" class="form-control jform-validate" id="title"  name="title" autofocus required>
			    </div>
			</div>
			<?php 
			$fields = jm_get_resume_custom_fields();
			if( !empty( $fields ) ) {
				foreach ($fields as $field) {
					jm_resume_render_form_field( $field, $resume_id );
				}
			}
			?>
		</div>
		<?php if( jm_get_resume_setting('enable_upload_resume', '1') ) : ?>
			<div class="col-sm-5">
				<label for="file_cv" class="control-label"><?php _e('Upload your Attachment','noo')?></label>
				<div class="form-control-flat">
					<div class="upload-to-cv clearfix">
				    	<?php noo_file_upload_form_field( 'file_cv', jm_get_allowed_attach_file_types(), noo_get_post_meta( $resume_id, '_noo_file_cv' ) ) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="col-sm-6">
			<div class="form-group">
			    <label for="desc" class="control-label"><?php _e('Professional Summary','noo')?></label>
				<?php
				$resume_content = $resume ? $resume->post_content : '';
				noo_wp_editor($resume_content, 'desc');
				?>
			</div>
		</div>
	</div>
</div>
<?php do_action('noo_post_resume_general_after'); ?>