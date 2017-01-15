<?php
wp_enqueue_script('noo-timeline-vendor');
wp_enqueue_script('noo-timeline');

$enable_education = jm_get_resume_setting('enable_education', '1');
$enable_experience = jm_get_resume_setting('enable_experience', '1');
$enable_skill = jm_get_resume_setting('enable_skill', '1');
$hide_profile = isset( $hide_profile ) ? $hide_profile : false;

$fields = jm_get_resume_custom_fields();

$education					= array();
if( $enable_education ) {
	$education['school']		= noo_json_decode( noo_get_post_meta( $resume_id, '_education_school' ) );
	$education['qualification']	= noo_json_decode( noo_get_post_meta( $resume_id, '_education_qualification' ) );
	$education['date']			= noo_json_decode( noo_get_post_meta( $resume_id, '_education_date' ) );
	$education['note']			= noo_json_decode( noo_get_post_meta( $resume_id, '_education_note' ) );
}

$experience					= array();
if( $enable_experience ) {
	$experience['employer']		= noo_json_decode( noo_get_post_meta( $resume_id, '_experience_employer' ) );
	$experience['job']			= noo_json_decode( noo_get_post_meta( $resume_id, '_experience_job' ) );
	$experience['date']			= noo_json_decode( noo_get_post_meta( $resume_id, '_experience_date' ) );
	$experience['note']			= noo_json_decode( noo_get_post_meta( $resume_id, '_experience_note' ) );
}

$skill						= array();
if( $enable_skill ) {
	$skill['name']				= noo_json_decode( noo_get_post_meta( $resume_id, '_skill_name' ) );
	$skill['percent']			= noo_json_decode( noo_get_post_meta( $resume_id, '_skill_percent' ) );
}
?>
<article id="post-<?php the_ID(); ?>" class="resume">
	<?php if( !apply_filters( 'jm_resume_hide_candidate_contact', $hide_profile, $resume_id ) ) noo_get_layout('resume_candidate_profile'); ?>
	<div class="resume-content">
		<div class="row">
			<div class="col-md-12">
				<div class="resume-desc">
					<div class="resume-general row">
						<div class="col-sm-3">
						<h3 class="title-general">
						<span><?php _e('General Information','noo');?></span>
						</h3>										
						</div>
						<div class="col-sm-9">
							<ul>
								<?php
								if($fields) : foreach ($fields as $field) : 
									$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
									$value = jm_get_resume_field_value( $resume_id, $field );
									$field_id = jm_resume_custom_fields_name($field['name'], $field);

									if( empty($value) ) continue;
									?>
									<li class="<?php echo esc_attr( $field_id ); ?>">
										<?php noo_display_field( $field, $field_id, $value ); ?>
									</li>

								<?php endforeach; endif; ?>
							</ul>
						</div>
					</div>
					<?php if( $enable_education ) : ?>
						<?php $education['school'] = isset( $education['school'] ) ? array_filter( $education['school'] ) : array(); ?>
						<?php if( !empty( $education['school'] ) ) : ?>
							<div class="resume-timeline row">
								<div class="col-md-3 col-sm-12">
									<h3 class="title-general">
										<span><?php _e('Education','noo');?></span>
									</h3>
								</div>
								<div class="col-md-9 col-sm-12">
									<div id="education-timeline" class="timeline-container education">
											<?php $education_count = count( $education['school'] );
											for( $index = 0; $index < $education_count; $index++ ) :
												if( empty( $education['school'][$index] ) ) continue;
												$status = empty($education['note'][$index]) ? 'empty' : '';
												?>
												<div class="timeline-wrapper <?php echo ( $index == ( $education_count - 1 ) ) ? 'last' : ''; ?>">
													<div class="timeline-time"><span><?php esc_attr_e( $education['date'][$index] ); ?></span></div>
													<dl class="timeline-series">
														<span class="tick tick-before"></span>
														<dt id="<?php echo 'education'.$index ?>" class="timeline-event"><a class="<?php echo $status; ?>"><?php esc_attr_e( $education['school'][$index] ); ?><span><?php esc_attr_e( $education['qualification'][$index] ); ?></span></a></dt>
														<span class="tick tick-after"></span>
														<dd class="timeline-event-content" id="<?php echo 'education'.$index.'EX' ?>">
															<div><?php echo wpautop( html_entity_decode( $education['note'][$index] ) ); ?></div>
														<br class="clear">
														</dd><!-- /.timeline-event-content -->
													</dl><!-- /.timeline-series -->
												</div><!-- /.timeline-wrapper -->
											<?php endfor; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if( $enable_experience ) : ?>
						<?php $experience['employer'] = isset( $experience['employer'] ) ? array_filter( $experience['employer'] ) : array(); ?>
						<?php if( !empty( $experience['employer'] ) ) : ?>
							<div class="resume-timeline row">
								<div class="col-md-3 col-sm-12">
									<h3 class="title-general">
										<span><?php _e('Work Experience','noo');?></span>
									</h3>
								</div>
								<div class="col-md-9 col-sm-12">
									<div id="experience-timeline" class="timeline-container experience">
										<?php $experience_count = count( $experience['employer'] );
											for( $index = 0; $index < $experience_count; $index++ ) : 
												if( empty( $experience['employer'][$index] ) ) continue;
												$status = empty($education['note'][$index]) ? 'empty' : '';
												?>
												<div class="timeline-wrapper <?php echo ( $index == ( $experience_count - 1 ) ) ? 'last' : ''; ?>">
													<div class="timeline-time"><span><?php esc_attr_e( $experience['date'][$index] ); ?></span></div>
													<dl class="timeline-series">
														<span class="tick tick-before"></span>
														<dt id="<?php echo 'experience'.$index ?>" class="timeline-event"><a class="<?php echo $status; ?>"><?php esc_attr_e( $experience['employer'][$index] ); ?><span class="tick tick-after"><?php esc_attr_e( $experience['job'][$index] ); ?></span></a></dt>
														
														<dd class="timeline-event-content" id="<?php echo 'experience'.$index.'EX' ?>">
															<div><?php echo wpautop( html_entity_decode( $experience['note'][$index] ) ); ?></div>
														<br class="clear">
														</dd><!-- /.timeline-event-content -->
													</dl><!-- /.timeline-series -->
												</div><!-- /.timeline-wrapper -->
										<?php endfor; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if( $enable_skill ) : ?>
						<?php $skill['name'] = isset( $skill['name'] ) ? array_filter( $skill['name'] ) : array(); ?>
						<?php if( !empty( $skill['name'] ) ) : ?>
							<div class="resume-timeline row">
								<div class="col-md-3 col-sm-12">
									<h3 class="title-general">
										<span><?php _e('Summary of Skills','noo');?></span>
									</h3>
								</div>
								<div class="col-md-9 col-sm-12">
									<div id="skill" class="skill">
										<?php $skill_count = count( $skill['name'] );
											for( $index = 0; $index < $skill_count; $index++ ) : 
												if( empty( $skill['name'][$index] ) ) continue;
												$skill_value = min( intval( $skill['percent'][$index] ), 100 );
												$skill_value = max( $skill_value, 0 );
												?>
											<div class="pregress-bar clearfix">
												<div class="progress_title"><span><?php esc_attr_e( $skill['name'][$index] ); ?></span></div>
												<div class="progress">
													<div aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" class="progress-bar progress-bar-bg" data-valuenow="<?php esc_attr_e( $skill_value ); ?>" role="progressbar" style="width: <?php esc_attr_e( $skill_value ); ?>%;">
														<div class="progress_label" style="opacity: 1;"><span><?php esc_attr_e( $skill_value ); ?></span><?php _e('%', 'noo'); ?></div>
													</div>
												</div>
											</div>
										<?php endfor; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</article> <!-- /#post- -->
<script>
	jQuery(document).ready(function() {
		jQuery.timeliner({
			timelineContainer:'.resume-timeline .timeline-container',
		});
		jQuery('.venobox').venobox();
	});
</script>