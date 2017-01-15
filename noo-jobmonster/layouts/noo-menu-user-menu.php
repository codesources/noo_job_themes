<?php if(Noo_Member::is_logged_in()):?>
	<li class="menu-item-has-children nav-item-member-profile login-link align-right noo-nav-item dropdown mega">
		<a id="thumb-info" href="<?php echo Noo_Member::get_member_page_url(); ?>">
			<span class="profile-name"><?php echo Noo_Member::get_display_name(); ?></span>
			<span class="profile-avatar"><?php echo noo_get_avatar( get_current_user_id(), 40 ); ?></span>
			<?php echo user_notifications_number(); ?>
		</a>
		<div class="noo-nav-child dropdown-menu mega-dropdown-menu">
			<div class="mega-dropdown-inner">
				<div class="noo-row">
					<div class="noo-col noo-span12 noo-col-nav">
						<div class="mega-inner">
							<ul class="mega-nav level1">
								<?php if(Noo_Member::is_employer()):?>
									<li class="menu-item" ><a href="<?php echo Noo_Member::get_post_job_url()?>"><i class="fa fa-edit"></i> <?php _e('Post a Job','noo')?></a></li>
									<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-job')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Jobs','noo')?></a></li>
									<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-application')?>" style="white-space: nowrap;"><i class="fa fa-newspaper-o"></i> <?php _e('Manage Applications','noo')?></a></li>
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
										<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('job-alert')?>"><i class="fa fa-bell-o"></i> <?php _e('Jobs Alert','noo')?></a></li>
									<?php endif; ?>
									<?php do_action( 'noo-member-candidate-menu' ); ?>
									<li class="divider" role="presentation"></li>
									<?php if(jm_is_woo_resume_posting()) : ?>
										<li class="menu-item" ><a href="<?php echo Noo_Member::get_endpoint_url('manage-plan')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Plan','noo')?></a></li>
									<?php endif; ?>
									<li class="menu-item" ><a href="<?php echo Noo_Member::get_candidate_profile_url()?>"><i class="fa fa-user"></i> <?php _e('My Profile','noo')?></a></li>
								<?php endif; ?>
								<li class="menu-item" ><a href="<?php echo Noo_Member::get_logout_url() ?>"><i class="fa fa-sign-out"></i> <?php _e('Sign Out','noo')?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
<?php else:?>
	<li class="menu-item nav-item-member-profile login-link align-center noo-nav-item dropdown mega">
		<a href="<?php echo Noo_Member::get_login_url(); ?>" class="member-links member-login-link"><i class="fa fa-sign-in"></i>&nbsp;<?php _e('Login', 'noo')?></a>
		<?php do_action( 'noo_user_menu_login_dropdown' ); ?>
	</li>
	<?php if( Noo_Member::can_register() ) : ?>
		<li class="menu-item nav-item-member-profile register-link noo-nav-item dropdown mega">
			<a class="member-links member-register-link" href="<?php echo Noo_Member::get_register_url(); ?>"><i class="fa fa-key"></i>&nbsp;<?php _e('Register', 'noo')?></a>
			<?php do_action( 'noo_user_menu_register_dropdown' ); ?>
		</li>
	<?php endif; ?>
<?php endif;?>