<?php 
if( Noo_Resume::enable_resume_detail() ) :
	$resume_id = isset($_GET['resume_id']) ?absint($_GET['resume_id']) : 0;
	$resume = $resume_id ? get_post($resume_id) : '';

	$education = array();
	$enable_education = jm_get_resume_setting('enable_education', '1');
	if( $enable_education ) {
		$education['school'] = noo_json_decode( noo_get_post_meta( $resume_id, '_education_school', '' ) );
		$education['qualification'] = noo_json_decode( noo_get_post_meta( $resume_id, '_education_qualification', '' ) );
		$education['date'] = noo_json_decode( noo_get_post_meta( $resume_id, '_education_date', '' ) );
		$education['note'] = noo_json_decode( noo_get_post_meta( $resume_id, '_education_note', '' ) );
	}

	$experience = array();
	$enable_experience = jm_get_resume_setting('enable_experience', '1');
	if( $enable_experience ) {
		$experience['employer'] = noo_json_decode( noo_get_post_meta( $resume_id, '_experience_employer', '' ) );
		$experience['job'] = noo_json_decode( noo_get_post_meta( $resume_id, '_experience_job', '' ) );
		$experience['date'] = noo_json_decode( noo_get_post_meta( $resume_id, '_experience_date', '' ) );
		$experience['note'] = noo_json_decode( noo_get_post_meta( $resume_id, '_experience_note', '' ) );
	}

	$skill = array();
	$enable_skill = jm_get_resume_setting('enable_skill', '1');
	if( $enable_skill ) {
		$skill['name'] = noo_json_decode( noo_get_post_meta( $resume_id, '_skill_name', '' ) );
		$skill['percent'] = noo_json_decode( noo_get_post_meta( $resume_id, '_skill_percent', '' ) );
	}
	?>
	<?php do_action('noo_post_resume_detail_before'); ?>
	<div class="resume-form">
		<div class="resume-form-detail">
		<?php if( $enable_education ) : ?>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _e('Education','noo')?></label>
				<div class="col-sm-9">
					<div class="noo-metabox-addable" data-name="_education" >
						<div class="noo-addable-fields">
							<input type="hidden" value="" name="_education_school" />
							<input type="hidden" value="" name="_education_qualification" />
							<input type="hidden" value="" name="_education_date" />
							<input type="hidden" value="" name="_education_note" />
							<?php
							if( isset( $education['school'] ) && is_array( $education['school'] ) && count($education['school']) ) :
								foreach( $education['school'] as $index => $school ) : 
								?>
									<div class="fields-group">
										<input type="text" class="form-control" placeholder="<?php _e('School name', 'noo'); ?>" name='_education_school[]' value="<?php echo esc_attr($education['school'][$index]); ?>" />
										<input type="text" class="form-control" placeholder="<?php _e('Qualification(s)', 'noo'); ?>" name='_education_qualification[]' value="<?php echo esc_attr($education['qualification'][$index]); ?>" />
										<input type="text" class="form-control" placeholder="<?php _e('Start/end date', 'noo'); ?>" name='_education_date[]' value="<?php echo esc_attr($education['date'][$index]); ?>" />
										<textarea class="form-control form-control-editor ignore-valid" id="_education_note" name="_education_note[]" rows="5" placeholder="<?php _e('Note', 'noo'); ?>"><?php echo html_entity_decode($education['note'][$index])?></textarea>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="fields-group">
									<input type="text" class="form-control" placeholder="<?php _e('School name', 'noo'); ?>" name='_education_school[]' />
									<input type="text" class="form-control" placeholder="<?php _e('Qualification(s)', 'noo'); ?>" name='_education_qualification[]' />
									<input type="text" class="form-control" placeholder="<?php _e('Start/end date', 'noo'); ?>" name='_education_date[]' />
									<textarea class="form-control form-control-editor ignore-valid" id="_education_note" name="_education_note[]" rows="5" placeholder="<?php _e('Note', 'noo'); ?>"></textarea>
								</div>
							<?php endif; ?>
						</div>
						<div class="noo-addable-actions">
							<a href="javascript:void()" class="noo-clone-fields pull-left" data-template="<div class='fields-group'><input type='text' class='form-control' placeholder='<?php _e('School name', 'noo'); ?>' name='_education_school[]' /><input type='text' class='form-control' placeholder='<?php _e('Qualification(s)', 'noo'); ?>' name='_education_qualification[]' /><input type='text' class='form-control' placeholder='<?php _e('Start/end date', 'noo'); ?>' name='_education_date[]' /><textarea class='form-control form-control-editor ignore-valid' id='_education_note' name='_education_note[]' rows='5' placeholder='<?php _e('Note', 'noo'); ?>'></textarea></div>">
								<i class="fa fa-plus-circle text-primary"></i>
								<?php _e('Add Education', 'noo'); ?>
							</a>
							<a href="javascript:void()" class="noo-remove-fields pull-right">
								<i class="fa fa-times-circle"></i>
								<?php _e('Delete', 'noo'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if( $enable_experience ) : ?>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _e('Work Experience','noo')?></label>
				<div class="col-sm-9">
					<div class="noo-metabox-addable" data-name="_experience" >
						<div class="noo-addable-fields">
							<input type="hidden" value="" name="_experience_employer" />
							<input type="hidden" value="" name="_experience_job" />
							<input type="hidden" value="" name="_experience_date" />
							<input type="hidden" value="" name="_experience_note" />
							<?php
							if( isset( $experience['employer'] ) && is_array( $experience['employer'] ) && count( $experience['employer'] ) ) :
								foreach( $experience['employer'] as $index => $employer ) : 
								?>
									<div class="fields-group">
										<input type="text" class="form-control" placeholder="<?php _e('Employer', 'noo'); ?>" name='_experience_employer[]' value="<?php echo esc_attr($experience['employer'][$index]); ?>" />
										<input type="text" class="form-control" placeholder="<?php _e('Job Title', 'noo'); ?>" name='_experience_job[]' value="<?php echo esc_attr($experience['job'][$index]); ?>" />
										<input type="text" class="form-control" placeholder="<?php _e('Start/end date', 'noo'); ?>" name='_experience_date[]' value="<?php echo esc_attr($experience['date'][$index]); ?>" />
										<textarea class="form-control form-control-editor ignore-valid" id="_experience_note" name="_experience_note[]" rows="5" placeholder="<?php _e('Note', 'noo'); ?>"><?php echo html_entity_decode($experience['note'][$index])?></textarea>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="fields-group">
									<input type="text" class="form-control" placeholder="<?php _e('Employer', 'noo'); ?>" name='_experience_employer[]' />
									<input type="text" class="form-control" placeholder="<?php _e('Job Title', 'noo'); ?>" name='_experience_job[]' />
									<input type="text" class="form-control" placeholder="<?php _e('Start/end date', 'noo'); ?>" name='_experience_date[]' />
									<textarea class="form-control form-control-editor ignore-valid" id="_experience_note" name="_experience_note[]" rows="5" placeholder="<?php _e('Note', 'noo'); ?>"></textarea>
								</div>
							<?php endif; ?>
						</div>
						<div class="noo-addable-actions">
							<a href="javascript:void()" class="noo-clone-fields pull-left" data-template="<div class='fields-group'><input type='text' class='form-control' placeholder='<?php _e('Employer', 'noo'); ?>' name='_experience_employer[]' /><input type='text' class='form-control' placeholder='<?php _e('Job Title', 'noo'); ?>' name='_experience_job[]' /><input type='text' class='form-control' placeholder='<?php _e('Start/end date', 'noo'); ?>' name='_experience_date[]' /><textarea class='form-control form-control-editor ignore-valid' id='_experience_note' name='_experience_note[]' rows='5' placeholder='<?php _e('Note', 'noo'); ?>'></textarea></div>">
								<i class="fa fa-plus-circle text-primary"></i>
								<?php _e('Add Experience', 'noo'); ?>
							</a>
							<a href="javascript:void()" class="noo-remove-fields pull-right">
								<i class="fa fa-times-circle"></i>
								<?php _e('Delete', 'noo'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if( $enable_skill ) : ?>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _e('Summary of Skills','noo')?></label>
				<div class="col-sm-9">
					<div class="noo-metabox-addable" data-name="_skill" >
						<div class="noo-addable-fields">
							<input type="hidden" value="" name="_skill_name" />
							<input type="hidden" value="" name="_skill_percent" />
							<input type="hidden" value="" name="_skill_date" />
							<input type="hidden" value="" name="_skill_note" />
							<?php
							if( isset( $skill['name'] ) && is_array( $skill['name'] ) && count($skill['name'] ) ) :
								foreach( $skill['name'] as $index => $name ) : 
								?>
									<div class="fields-group row">
										<div class="col-sm-9 col-xs-6">
											<input type="text" class="form-control" placeholder="<?php _e('Skill Name', 'noo'); ?>" name='_skill_name[]' value="<?php echo esc_attr($skill['name'][$index]); ?>" />
										</div>
										<div class="col-sm-3 col-xs-6">
											<input type="text" class="form-control" name='_skill_percent[]' value="<?php echo esc_attr($skill['percent'][$index]); ?>" />
											<span class="percent-text"><?php _e('% (1 to 100)', 'noo'); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="fields-group row">
									<div class="col-sm-9 col-xs-6">
										<input type="text" class="form-control" placeholder="<?php _e('Skill Name', 'noo'); ?>" name='_skill_name[]' />
									</div>
									<div class="col-sm-3 col-xs-6">
										<input type="text" class="form-control" name='_skill_percent[]' />
										<span class="percent-text"><?php _e('% (1 to 100)', 'noo'); ?></span>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<div class="noo-addable-actions">
							<a href="javascript:void()" class="noo-clone-fields pull-left" data-template="<div class='fields-group row'><div class='col-sm-9 col-xs-6'><input type='text' class='form-control' placeholder='<?php _e('Skill Name', 'noo'); ?>' name='_skill_name[]' /></div><div class='col-sm-3 col-xs-6'><input type='text' class='form-control' name='_skill_percent[]' /><span class='percent-text'><?php _e('% (1 to 100)', 'noo'); ?></span></div></div>">
								<i class="fa fa-plus-circle text-primary"></i>
								<?php _e('Add Skill', 'noo'); ?>
							</a>
							<a href="javascript:void()" class="noo-remove-fields pull-right">
								<i class="fa fa-times-circle"></i>
								<?php _e('Delete', 'noo'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		</div>
	</div>
	<?php do_action('noo_post_resume_detail_after'); ?>
<?php endif; ?>