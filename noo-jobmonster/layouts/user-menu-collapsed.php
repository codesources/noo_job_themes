<?php if(Noo_Member::is_logged_in()):?>
	<?php if(Noo_Member::is_employer()):?>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_post_job_url()?>"><i class="fa fa-edit"></i> <?php _e('Post a Job','noo')?></a></li>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-application')?>" style="white-space: nowrap;"><i class="fa fa-newspaper-o"></i> <?php _e('Manage Applications','noo')?></a></li>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-job')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Jobs','noo')?></a></li>
		<?php do_action( 'noo-member-employer-menu' ); ?>
		<li class="divider" role="presentation"></li>
		<?php //if(jm_is_woo_job_posting()) : ?>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-plan')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Plan','noo')?></a></li>
		<?php //endif; ?>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_company_profile_url()?>"><i class="fa fa-users"></i> <?php _e('Company Profile','noo')?></a></li>
	<?php elseif(Noo_Member::is_candidate()):?>
		<?php if( jm_resume_enabled() ) : ?>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_post_resume_url()?>"><i class="fa fa-edit"></i> <?php _e('Post a Resume','noo')?></a></li>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-resume')?>" style="white-space: nowrap;"><i class="fa fa-file-text-o"></i> <?php _e('Manage Resumes','noo')?></a></li>
		<?php endif; ?>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-job-applied')?>" style="white-space: nowrap;"><i class="fa fa-newspaper-o"></i> <?php _e('Manage Applications','noo')?></a></li>
		<?php if( Noo_Job_Alert::enable_job_alert() ) : ?>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('job-alert')?>"><i class="fa fa-bell-o"></i> <?php _e('Job Alerts','noo')?></a></li>
		<?php endif; ?>
		<?php do_action( 'noo-member-candidate-menu' ); ?>
		<li class="divider" role="presentation"></li>
		<?php if(jm_is_woo_resume_posting()) : ?>
			<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-plan')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Plan','noo')?></a></li>
		<?php endif; ?>
		<li class="menu-item" ><a href="<?php echo Noo_Member::get_candidate_profile_url()?>"><i class="fa fa-user"></i> <?php _e('My Profile','noo')?></a></li>
	<?php endif; ?>
	<li class="menu-item" ><a href="<?php echo Noo_Member::get_logout_url() ?>"><i class="fa fa-sign-out"></i> <?php _e('Sign Out','noo')?></a></li>
<?php else:?>
	<li class="menu-item" >
		<a href="<?php echo Noo_Member::get_login_url()?>"><i class="fa fa-sign-in"></i>&nbsp;<?php _e('Login', 'noo')?></a>
	</li>
	<?php do_action( 'noo_collapsed_user_menu_login_after' ); ?>
	<?php if( Noo_Member::can_register() ) : ?>
		<li class="menu-item" >
			<a href="<?php echo Noo_Member::get_register_url();?>"><i class="fa fa-key"></i> <?php _e('Register', 'noo')?></a>
		</li>
		<?php do_action( 'noo_collapsed_user_menu_register_after' ); ?>
	<?php endif;?>
<?php endif;?>
