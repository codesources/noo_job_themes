<?php
list($heading, $sub_heading) = get_page_heading();
$noo_enable_parallax = noo_get_option( 'noo_enable_parallax', 1 );
if( ! empty($heading) ) :
	$heading_image = get_page_heading_image();
?>
<?php if( !empty( $heading_image ) ) : ?>
    <?php if(is_singular('noo_company') && Noo_Company::get_layout() == 'two' ) : ?>
        <header class="noo-page-heading noo-page-heading-company-2" style="<?php echo ( !$noo_enable_parallax ) ? 'background: url(' . esc_url($heading_image) . ') no-repeat center center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;' : 'background: rgba(67, 67, 67, 0.55);'; ?>">
    <?php else: ?>
        <header class="noo-page-heading" style="<?php echo ( !$noo_enable_parallax ) ? 'background: url(' . esc_url($heading_image) . ') no-repeat center center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;' : 'background: rgba(67, 67, 67, 0.55);'; ?>">
    <?php endif; ?>
<?php else : ?>
	<header class="noo-page-heading">
<?php endif; ?>
	<div class="container-boxed max" style="position: relative; z-index: 1;">
		<?php 
		$page_temp = get_page_template_slug();
		?>
		<?php  if('page-post-resume.php' === $page_temp || 'page-post-job.php' === $page_temp || (is_user_logged_in() && get_the_ID() == Noo_Member::get_member_page_id())):?>
			<div class="member-heading-avatar">
				<?php echo noo_get_avatar( get_current_user_id(), 100); ?>
			</div>
			<div class="page-heading-info ">
				<h1 class="page-title" <?php noo_page_title_schema(); ?>><?php echo esc_html($heading); ?></h1>
			</div>
		<?php else: ?>
			<div class="page-heading-info">
				<h1 class="page-title" <?php noo_page_title_schema(); ?>>
					<?php echo esc_html($heading); ?>
					<?php
						if( is_singular('noo_job') || is_singular('noo_resume') || is_singular('noo_company') ) :
							global $post;
							$post_view = noo_get_post_views($post->ID);
							if( $post_view > 0 ) {
								echo '<span class="count">' . sprintf( _n( '%d view', '%d views', $post_view, 'noo' ), $post_view ) .'</span>';
							}
							if( is_singular('noo_job') ) {
								$applications_count = noo_get_job_applications_count($post->ID);
								if( $applications_count > 0 ) {
									echo '<span class="count applications">' . sprintf( _n( '%d application', '%d applications', $applications_count, 'noo' ), $applications_count ) .'</span>';
								}
							}
						endif;
					?>
				</h1>
			</div>
		<?php endif;?>
		<div class="page-sub-heading-info">
			<?php if( is_singular('noo_job') ) :
				jm_the_job_meta(array('show_company' => false, 'fields' => array( 'job_type', 'job_location', 'job_date', '_closing', 'job_category' ), 'schema' => true));
			elseif( is_singular('noo_resume') ) :
				echo '';
			elseif( is_singular('noo_company') ) :
				echo '';
			elseif( is_single( 'post' ) ) :
				noo_content_meta(); 
			elseif( !empty( $sub_heading ) ) :
				echo $sub_heading;
			endif; ?>
		</div>
	</div><!-- /.container-boxed -->
	<?php if( !empty( $heading_image ) ) : ?>
		<?php if ( $noo_enable_parallax ) : ?>
			<div class=" parallax" data-parallax="1" data-parallax_no_mobile="1" data-velocity="0.1" style="background-image: url(<?php echo esc_url($heading_image); ?>); background-position: 50% 25px;"></div>
		<?php endif; ?>
	<?php endif; ?>
</header>
<?php endif; ?>
<?php  if(is_user_logged_in() && get_the_ID() == Noo_Member::get_member_page_id()):?>
<div class="member-heading">
	<div class="container-boxed max">
		
		<div class="member-heading-nav">
			<ul>
				<?php if( Noo_Member::is_employer() ) : ?>
					<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class(array( 'manage-job', 'preview-job', 'edit-job' )))?>"><a href="<?php echo Noo_Member::get_endpoint_url('manage-job')?>"><i class="fa fa-file-text-o"></i> <?php _e('Jobs','noo')?></a></li>
					<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('manage-application'))?>">
						<a href="<?php echo Noo_Member::get_endpoint_url('manage-application')?>" style="white-space: nowrap;">
							<i class="fa fa-newspaper-o"></i> 
							<?php _e('Applications','noo')?>
							<?php echo unseen_applications_number(); ?>
						</a>
					</li>
					<?php do_action( 'noo-member-employer-heading' ); ?>
					<li class="divider" role="presentation"></li>
					<?php //if(jm_is_woo_job_posting()) : ?>
						<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('manage-plan'))?>"><a href="<?php echo Noo_Member::get_endpoint_url('manage-plan')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Plan','noo')?></a></li>
					<?php //endif; ?>
					<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('company-profile'))?>"><a href="<?php echo Noo_Member::get_company_profile_url()?>"><i class="fa fa-users"></i> <?php _e('Company Profile','noo')?></a></li>
				<?php elseif( Noo_Member::is_candidate() ) : ?>
					<?php if( jm_resume_enabled() ) : ?>
						<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class(array( 'manage-resume', 'preview-resume', 'edit-resume' )))?>"><a href="<?php echo Noo_Member::get_endpoint_url('manage-resume')?>"><i class="fa fa-file-text-o"></i> <?php _e('Resumes','noo')?></a></li>
					<?php endif; ?>
					<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('manage-job-applied'))?>"><a href="<?php echo Noo_Member::get_endpoint_url('manage-job-applied')?>" style="white-space: nowrap;"><i class="fa fa-newspaper-o"></i> <?php _e('Applications','noo')?></a></li>
					<?php if( Noo_Job_Alert::enable_job_alert() ) : ?>
						<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class(array( 'job-alert', 'add-job-alert', 'edit-job-alert' )))?>"><a href="<?php echo Noo_Member::get_endpoint_url('job-alert')?>"><i class="fa fa-bell-o"></i> <?php _e('Job Alerts','noo')?></a></li>
					<?php endif; ?>
					<?php do_action( 'noo-member-candidate-heading' ); ?>
					<li class="divider" role="presentation"></li>
					<?php if(jm_is_woo_resume_posting()) : ?>
						<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('manage-plan'))?>"><a href="<?php echo Noo_Member::get_endpoint_url('manage-plan')?>"><i class="fa fa-file-text-o"></i> <?php _e('Manage Plan','noo')?></a></li>
					<?php endif; ?>
					<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('candidate-profile'))?>"><a href="<?php echo Noo_Member::get_endpoint_url('candidate-profile')?>"><i class="fa fa-user"></i> <?php _e('My Profile','noo')?></a></li>
				<?php endif; ?>

				<li><a href="<?php echo Noo_Member::get_logout_url() ?>"><i class="fa fa-sign-out"></i> <?php _e('Sign Out','noo')?></a></li>
			</ul>
		</div>
	</div>
</div>
<?php endif;?>
<?php
do_action('after_heading');
?>
