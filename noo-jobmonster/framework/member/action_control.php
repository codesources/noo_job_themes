<?php

if( !function_exists( 'jm_action_control_admin_init' ) ) :
	function jm_action_control_admin_init(){
		register_setting('jm_action_control', 'jm_action_control');
	}
	
	add_filter('admin_init', 'jm_action_control_admin_init' );
endif;


if( !function_exists( 'jm_get_action_list' ) ) :
	function jm_get_action_list(){
		$default_post_job = defined('WOOCOMMERCE_VERSION') ? 'package' : 'employer';
		$actions = array(
			'post_job' => array(
					'label' => __('Post Job', 'noo'),
					'default' => $default_post_job,
					'options' => array(
							'employer' => __('Employers', 'noo'),
							'package' => __('Employers with Paid Packages', 'noo'),
						)
				),
			'view_resume' => array(
					'label' => __('View and Search Resume', 'noo'),
					'default' => 'employer',
					'options' => array(
							'public' => __('Public', 'noo'),
							'user' => __('Logged-in Users', 'noo'),
							'employer' => __('Employers', 'noo'),
							'package' => __('Employers with Paid Packages', 'noo'),
						)
				),
			'view_candidate_contact' => array(
					'label' => __('View Candidate Contact', 'noo'),
					'default' => '',
					'options' => array(
							'' => __('All who can View Resume', 'noo'),
							'public' => __('Public', 'noo'),
							'employer' => __('Employers', 'noo'),
							'package' => __('Employers with Paid Packages', 'noo'),
							'noone' => __('Private', 'noo')
						),
					'desc' => __('In any case, employers who received resumes from applications can view contact information.', 'noo')
				),
			'post_resume' => array(
					'label' => __('Post Resume', 'noo'),
					'default' => 'candidate',
					'options' => array(
							'candidate' => __('Candidates', 'noo'),
							'package' => __('Candidates with Paid Packages', 'noo'),
						)
				),
			'view_job' => array(
					'label' => __('View Job Detail', 'noo'),
					'default' => 'public',
					'options' => array(
							'public' => __('Public', 'noo'),
							'user' => __('Logged-in Users', 'noo'),
							'candidate' => __('Candidates', 'noo'),
							'package' => __('Candidates with Paid Packages', 'noo'),
						)
				),
			'apply_job' => array(
					'label' => __('Apply Job', 'noo'),
					'default' => 'public',
					'options' => array(
							'public' => __('Public', 'noo'),
							'candidate' => __('Candidates', 'noo'),
							'package' => __('Candidates with Paid Packages', 'noo'),
						)
				),
			'bookmark_job' => array(
					'label' => __('Bookmark Job', 'noo'),
					'default' => 'enable',
					'options' => array(
							'enable' => __('Enable', 'noo'),
							'disable' => __('Disable', 'noo'),
						)
				),
			// 'view_company_contact' => array(
			// 		'label' => __('View Company Contact', 'noo'),
			// 		'default' => '',
			// 		'options' => array(
			// 				'' => __('All who can View Job', 'noo'),
			// 				'public' => __('Public', 'noo'),
			// 				'candidate' => __('Candidates', 'noo'),
			// 				'package' => __('Candidates with Paid Packages', 'noo'),
			// 			)
			// 	),
		);
		return apply_filters( 'jm_action_control_list', $actions );
	}
endif;

if( !function_exists( 'jm_get_action_control' ) ) :
	function jm_get_action_control( $action = '' ) {
		$actions = jm_get_action_list();
		if( !array_key_exists( $action, $actions ) ) return null;

		return jm_get_setting('jm_action_control', $action, $actions[$action]['default'] );
	}
endif;

if( !function_exists( 'jm_action_control_settings_tabs' ) ) :
	function jm_action_control_settings_tabs( $tabs = array() ) {
		$index = 0; //array_search('job_package', array_keys( $tabs ) ) + 1;
        $before = array_slice($tabs, 0, $index);
        $after = array_slice($tabs, $index);

		$action_control_tab = array( 'action_control' => __('Action Control','noo') );
		return array_merge($before, $action_control_tab, $after);
	}
	
	add_filter('noo_job_settings_tabs_array', 'jm_action_control_settings_tabs', 99 );
endif;

if( !function_exists( 'jm_action_control_setting_form' ) ) :
	function jm_action_control_setting_form() {
		jm_action_control_correct_settings();
		$actions = jm_get_action_list();
		
		?>
		<?php settings_fields('jm_action_control'); ?>
		<h3><?php echo __('Action and Permissions','noo')?></h3>
		<p><?php echo __('This page consists of setting related to the main actions of users on your site. Depending on the actions, you can select if the user is allowed freely or require purchasing the packages.', 'noo'); ?></p>
		<p><?php echo __('With action requires buying packages, you will see proper settings on the package product edit page.', 'noo'); ?></p>
		<table class="form-table" cellspacing="0" >
			<tbody>
				<?php if( !empty( $actions ) ) : foreach ($actions as $key => $action) : ?>
					<tr>
						<th>
							<?php echo $action['label']; ?>
						</th>
						<td>
							<select name="jm_action_control[<?php echo $key; ?>]">
								<?php $setting = jm_get_action_control( $key ); ?>
								<?php foreach ($action['options'] as $opt_key => $opt_label) : ?>
									<option <?php selected( $setting, $opt_key ); ?> value="<?php echo $opt_key; ?>"><?php echo $opt_label; ?></option>
								<?php endforeach; ?>
							</select>
							<?php if( isset( $action['desc'] ) ) : ?>
								<p><?php echo $action['desc']; ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; endif;?>
			</tbody>
		</table>
		<?php
	}

	add_action('noo_job_setting_action_control', 'jm_action_control_setting_form');
endif;

if( !function_exists( 'jm_action_control_correct_settings') ) :
	function jm_action_control_correct_settings () {
		if( $action_settings = jm_get_setting( 'jm_action_control' ) ) {
			return;
		}
		$default_post_job = defined('WOOCOMMERCE_VERSION') ? 'package' : 'employer';
		if( $setting = jm_get_job_setting( 'job_posting_mode' ) ) {
			$default_post_job = ( $setting == 'woo' ) ? 'package' : 'employer';
		}
		$default_view_resume = 'employer';
		if( $setting = jm_get_resume_setting('can_view_resume') ) {
			$default_view_resume = ( $setting == 'premium_package' ) ? 'package' : $setting;
		}
		$default_view_candidate = '';
		if( $setting = jm_get_resume_setting('can_view_candidate_contact') ) {
			$default_view_candidate = ( $setting == 'premium_package' ) ? 'package' : $setting;
		}
		$default_post_resume = 'candidate';
		if( $setting = jm_get_resume_setting( 'resume_posting_mode' ) ) {
			$default_post_resume = ( $setting == 'woo' ) ? 'package' : 'candidate';
		}
		$default_apply_job = 'public';
		if( $setting = jm_get_application_setting( 'member_apply' ) ) {
			$default_apply_job = 'candidate';
		}
		$action_settings = array(
			'post_job' => $default_post_job,
			'view_resume' => $default_view_resume,
			'view_candidate_contact' => $default_view_candidate,
			'post_resume' => $default_post_resume,
			'view_job' => 'public',
			'apply_job' => $default_apply_job,
			'view_company_contact' => ''
		);

		update_option( 'jm_action_control', $action_settings );
	}
endif;