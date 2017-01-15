<?php 
$resume_id = isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0;
$query = new WP_Query(array(
	'post__in' => array($resume_id),
	'post_type'=>'noo_resume',
	'post_status'=>'draft',
	// 'post_status'=>array('publish', 'pending'),
));

$back_location_query=array(
	'action'=>'resume_detail',
	'resume_id'=>$resume_id
);
?>
<div class="jpanel jpanel-resume-preview">
	<div class="jpanel-body">
		<div class="resume-preview">
			<div class="resume-form-detail">
				<?php 
					if($query->post_count){
						?>
						<?php jm_resume_detail($query); ?>
						<div class="form-actions resume-preview-actions text-center clearfix">
							<a href="<?php echo esc_url(add_query_arg($back_location_query))?>" class="btn btn-primary"><?php _e('Back','noo')?></a>
					 		<button type="submit" class="btn btn-primary"><?php _e('Save','noo')?></button>
					 	</div>
						<?php
					}else{
						echo '<h2 class="text-center" style="min-height:200px">'.__('Resume not found !','noo').'</h2>';
					}
				?>
			</div>
		</div>
	</div>
</div>
<?php wp_reset_query();?>