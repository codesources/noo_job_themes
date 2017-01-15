<?php
	$noo_single_jobs_layout = noo_get_option('noo_single_jobs_layout', 'right_company');
	$is_candidate = Noo_Member::is_candidate();
	$closing_date = get_post_meta( $job_id, '_closing', true );
	$closing_date = empty( $closing_date ) || !is_numeric( $closing_date ) ? $closing_date : strtotime( $closing_date );

	$is_expired = ( 'expired' == get_post_status( $job_id ) ) || ( !empty( $closing_date ) && $closing_date <= time() );
?>

<div class="<?php noo_main_class(); ?>" role="main">
	<div class="job-desc" itemprop="description">
		<?php do_action( 'jm_job_detail_content_before' ); ?>
		<?php the_content(); ?>
		<?php do_action( 'jm_job_detail_content_after' ); ?>
	</div>
	<div class="job-action hidden-print clearfix">
		<?php if( $is_expired ) : ?>
			<div class="noo-messages noo-message-error">
				<ul>
					<li><?php echo __('This job has expired!', 'noo'); ?></li>
				</ul>
			</div>
		<?php else : ?>
			<?php if( $is_candidate ) : ?>
				<div class="noo-ajax-result" style="display: none"></div>
			<?php endif; ?>
			<?php $has_applied = $is_candidate ? Noo_Application::has_applied( 0, $job_id ) : false; ?>
			<?php if( $has_applied ) : ?>
				<div class="noo-messages noo-message-notice pull-left">
					<ul>
						<li><?php echo __('You have already applied for this job', 'noo'); ?></li>
					</ul>
				</div>
			<?php else: ?>
				<?php $can_apply = jm_can_apply_job( $job_id ); ?>
				<?php if( !$can_apply ) : ?>
					<?php list($title, $link) = jm_get_cannot_apply_job_message( $job_id ); ?>
					<?php if( !empty( $title ) ) echo "<div><strong>$title</strong></div>"; ?>
					<?php if( !empty( $link ) ) echo $link; ?>
					<?php do_action( 'jm_job_detail_cannot_apply', $job_id ); ?>
				<?php else : ?>
					<?php 
					$custom_apply_link = jm_get_setting('noo_job_linkedin', 'custom_apply_link' );
					$apply_url = !empty( $custom_apply_link ) ? noo_get_post_meta( $job_id, '_custom_application_url', '' ) : '';
					?>
					<?php if( empty( $apply_url ) ) : ?>
						<a class="btn btn-primary" data-target="#applyJobModal" href="#" data-toggle="modal"><?php _e('Apply for this job','noo');?></a>
						<?php include(locate_template("layouts/apply_job_form.php")); ?>
					<?php else : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( $apply_url ); ?>" target="_blank" ><?php _e('Apply for this job','noo');?></a>
					<?php endif; ?>
					<?php do_action( 'jm_job_detail_apply', $job_id ); ?>
					<?php 
						if(jm_get_setting('noo_job_linkedin','use_apply_with_linkedin') == 'yes'):
							include(locate_template("layouts/apply_job_via_linkedin_form.php"));
						endif;
					?>
				<?php endif; ?>
			<?php endif; ?>
			<?php do_action( 'jm_job_detail_actions', $job_id ); ?>
		<?php endif; ?>
	</div>
	<?php jm_the_job_tag(); ?>
	<?php
		//  -- Check display company
		if ( $noo_single_jobs_layout == 'left_sidebar' || $noo_single_jobs_layout == 'fullwidth' || $noo_single_jobs_layout == 'sidebar' ) :

			// -- Job Social Share
			jm_the_job_social($job_id, __('Share this job','noo'));

			// -- check option turn on/off show company info
			if( noo_get_option('noo_company_info_in_jobs', true) ) :
				Noo_Company::display_sidebar($company_id, true);
			endif;

		endif;
	?>
	<?php if (  noo_get_option( 'noo_job_related', true ) ) : ?>
		<?php jm_related_jobs($job_id, __('Related Jobs','noo')); ?>
	<?php endif; ?>
	<?php if ( noo_get_option( 'noo_job_comment', false ) && comments_open() ) : ?>
		<?php comments_template( '', true ); ?>
	<?php endif; ?>
</div> <!-- /.main -->
<?php if( $noo_single_jobs_layout != 'fullwidth' ) : ?>
<div class="<?php noo_sidebar_class(); ?> hidden-print">
	<div class="noo-sidebar-wrap">
	<?php
		//  -- Check display company
		if ( $noo_single_jobs_layout != 'left_sidebar' && $noo_single_jobs_layout != 'sidebar' ) :

			// -- Job Social Share
				jm_the_job_social($job_id, __('Share this job','noo'));

			// -- show company info
				Noo_Company::display_sidebar($company_id, true);

		else :
			// -- show siderbar
				if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( ) ) :
					dynamic_sidebar( noo_get_option('noo_single_jobs_sidebar', true) );
				endif;
		endif;
	?>
	</div>
</div>
<?php endif; ?>
