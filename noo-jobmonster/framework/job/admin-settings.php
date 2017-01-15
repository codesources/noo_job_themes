<?php

if( !function_exists( 'jm_job_settings_tabs' ) ) :
	function jm_job_settings_tabs( $tabs = array() ) {
		return array_merge( array(
				'general'=>__('Jobs','noo'),
			), $tabs
		);
	}
	
	add_filter('noo_job_settings_tabs_array', 'jm_job_settings_tabs' );
endif;

if( !function_exists( 'jm_job_settings_form' ) ) :
	function jm_job_settings_form(){
		if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
			flush_rewrite_rules();
			do_action( 'jm_job_setting_changed' );
		}
		$archive_slug = jm_get_job_setting( 'archive_slug');
		$job_approve = jm_get_job_setting( 'job_approve','');
		$default_job_content = jm_get_job_setting( 'default_job_content', '');
		$submit_agreement = jm_get_job_setting( 'submit_agreement', null);
		$job_posting_limit = jm_get_job_setting( 'job_posting_limit',5);
		$job_display_duration = jm_get_job_setting( 'job_display_duration',30);
		$job_feature_limit = jm_get_job_setting( 'job_feature_limit',1);
		$job_posting_reset = jm_get_job_setting( 'job_posting_reset',0);

		?>
		<?php settings_fields('noo_job_general'); ?>
		<h3><?php echo __('Job Display','noo')?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr>
					<th>
						<?php esc_html_e('Job Archive base (slug)','noo')?>
					</th>
					<td>
						<input type="text" name="noo_job_general[archive_slug]" value="<?php echo ($archive_slug ? sanitize_title( $archive_slug ) :'jobs') ?>">
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e('Job Display','noo')?>
					</th>
					<td>
						<p><?php 
						$customizer_job_link = esc_url( add_query_arg( array('autofocus%5Bpanel%5D' => 'noo_customizer_section_job'), admin_url( '/customize.php' ) ) );
						echo sprintf( __('Go to <a href="%s">Customizer</a> to change settings for Job(s) layout or displayed sections.','noo'), $customizer_job_link); ?></p>
					</td>
				</tr>
				<?php do_action( 'noo_setting_job_display_fields' ); ?>
			</tbody>
		</table>
		<br/><hr/><br/>
		<h3><?php echo __('Job Posting','noo')?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr>
					<th>
						<?php esc_html_e('Job Approval','noo')?>
					</th>
					<td>
						<input type="hidden" name="noo_job_general[job_approve]" value="">
						<input type="checkbox" <?php checked( $job_approve, 'yes' ); ?> name="noo_job_general[job_approve]" value="yes">
						<p><small><?php echo __('Each newly submitted job needs the manual approval of Admin.','noo') ?></small></p>
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e('Default Job Content','noo')?>
						<p><small><?php echo __('Default content that will auto populated when Employers post new Jobs.','noo') ?></small></p>
					</th>
					<td>
						<?php
						$default_text = __('<h3>Job Description</h3><p>What is the job about? Enter the overall description of your job.</p>', 'noo');
						$default_text .= __('<h3>Benefits</h3><ul><li>What can candidates get from the position?</li><li>What can candidates get from the position?</li><li>What can candidates get from the position?</li></ul>', 'noo');
						$default_text .= __('<h3>Job Requirements</h3><ol><li>Detailed requirement for the vacancy.?</li><li>Detailed requirement for the vacancy.?</li><li>Detailed requirement for the vacancy.?</li></ol>', 'noo');
				        $default_text .= __('<h3>How To Apply</h3><p>How candidate can apply for your job. You can leave your contact information to receive hard copy application or any detailed guide for application.</p>', 'noo');

				        $text = !empty( $default_job_content ) ? $default_job_content : $default_text;
						
						$editor_id = 'textblock' . uniqid();
				        // add_filter( 'wp_default_editor', create_function('', 'return "tinymce";') );
				        wp_editor( $text, $editor_id, array(
				                    'media_buttons' => false,
				                    'quicktags' => true,
				                    'textarea_rows' => 15,
				                    'textarea_cols' => 80,
				                    'textarea_name' => 'noo_job_general[default_job_content]',
				                    'wpautop' => false)); ?>						
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e('Job submission condition','noo')?>
						<p><small><?php echo __('The condition that employers must agree to before submitting a new job. Leave it blank for no condition.','noo') ?></small></p>
					</th>
					<td>
						<?php
						$submit_agreement = !is_null( $submit_agreement ) ? $submit_agreement : sprintf(__('Job seekers can find your job and contact you via email or %s regarding your application options. Preview all information thoroughly before submitting your job for approval.','noo'), get_bloginfo('name') );
						?>	
						<textarea name="noo_job_general[submit_agreement]" rows="5" cols="80"><?php echo esc_html($submit_agreement); ?></textarea>					
					</td>
				</tr>
				<?php if( jm_is_free_job_posting() ) : ?>
					<tr>
						<th>
							<?php esc_html_e('Job Limit','noo')?>
						</th>
						<td>
							<input type="text" name="noo_job_general[job_posting_limit]" value="<?php echo absint($job_posting_limit) ?>">
							<p><small><?php echo __('If you don\'t use Woocommerce Job Package for manage job submission, you can use this setting for limiting the number of jobs per employer.','noo') ?></small></p>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e('Job Duration (day)','noo')?>
						</th>
						<td>
							<input type="text" name="noo_job_general[job_display_duration]" value="<?php echo absint($job_display_duration) ?>">
							<p><small><?php echo __('If you don\'t use Woocommerce Job Package for manage job submission, you can use this setting for job duration.','noo') ?></small></p>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e('Featured Job Limit','noo')?>
						</th>
						<td>
							<input type="text" name="noo_job_general[job_feature_limit]" value="<?php echo absint($job_feature_limit) ?>">
							<p><small><?php echo __('If you don\'t use Woocommerce Job Package for manage job submission, you can use this setting for limiting the number of featured jobs per employer.','noo') ?></small></p>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e('Reset counter every','noo')?>
						</th>
						<td>
							<input type="text" name="noo_job_general[job_posting_reset]" value="<?php echo absint($job_posting_reset) ?>">&nbsp;<?php echo __('Month', 'noo'); ?>
							<p><small><?php echo __('Reset the counter will allow Employers to re-post jobs after using up the limitation. Input zero for no reset.','noo') ?></small></p>
						</td>
					</tr>
				<?php endif ?>
				<?php do_action( 'noo_setting_job_submission_fields' ); ?>
			</tbody>
		</table>
		<?php 
	}
endif;

