<?php 
$job_id = isset($_GET['job_id']) ? absint($_GET['job_id']) : 0;
$query = new WP_Query(array(
	'post__in' => array($job_id),
	'post_type'=>'noo_job',
	'post_status'=>'draft',
));
$back_location_query=array(
	'action'=>'postjob',
	'job_id'=>$job_id
);
?>
<div class="jpanel jpanel-job-preview">
	<div class="jpanel-title">
		<h3><?php _e('You have almost finished. Preview and submit your job for approval','noo')?></h3>
	</div>
	<div class="jpanel-body">
		<div class="job-preview">
			<?php 
				if($query->post_count){
					?>
					<div class="job-form-detail row">
						<?php jm_job_detail($query,true); ?>
					</div>
					<div class="form-actions job-preview-actions text-center clearfix">
						<?php $submit_agreement = jm_get_job_setting( 'submit_agreement', null); 
						$submit_agreement = !is_null( $submit_agreement ) ? $submit_agreement : sprintf(__('Job seekers can find your job and contact you via email or %s regarding your application options. Preview all information thoroughly before submitting your job for approval.','noo'), get_bloginfo('name') );
						if( !empty( $submit_agreement ) ) :
						?>
						<div class="job-preview-notice">
							<div class="checkbox">
								<div class="form-control-flat"><label class="checkbox"><input name="agreement" type="checkbox" class="jform-validate" required title="<?php esc_attr_e('You must agree with these terms.','noo')?>"><i></i> <?php echo apply_filters('noo_post_job_preview_notice', $submit_agreement)?></label></div>
							</div>
						</div>
						<?php endif; ?>
						
						<a href="<?php echo esc_url(add_query_arg($back_location_query))?>" class="btn btn-primary"><?php _e('Back','noo')?></a>&nbsp;&nbsp;&nbsp;
				 		<button type="submit" class="btn btn-primary"><?php _e('Submit','noo')?></button>
				 	</div>
						<?php
					}else{
						echo '<h2 class="text-center" style="min-height:200px">'.__('Job not found !','noo').'</h2>';
					}
				?>
		</div>
	</div>
</div>
