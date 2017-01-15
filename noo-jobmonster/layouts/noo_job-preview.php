<?php
	$noo_single_jobs_layout = noo_get_option('noo_single_jobs_layout', 'right_company');
	$preview_class = '';
	if ($noo_single_jobs_layout == 'left_company') {
		$preview_class = ' col-md-8 left-sidebar';
	} elseif ($noo_single_jobs_layout == 'right_company') {
		$preview_class = ' col-md-8';
	} else {
		$preview_class = ' col-md-12';
	}
?>

<div class="<?php echo $preview_class; ?>" role="main">
	<div class="job-head">
		<div class="job-title">
			<?php the_title( ); ?>
		</div>
		<div class="job-sub">
			<?php
			jm_the_job_meta(array('show_company' => false, 'fields' => array( 'job_type', 'job_location', 'job_date', '_closing', 'job_category' ) ), $post); ?>
		</div>
	</div>
	<div class="job-desc" itemprop="description">
		<?php do_action( 'jm_job_detail_content_before' ); ?>
		<?php the_content(); ?>
		<?php do_action( 'jm_job_detail_content_after' ); ?>
	</div>
	<?php jm_the_job_tag(); ?>
	<?php
		//  -- Check display company
		if ( $noo_single_jobs_layout == 'left_sidebar' || $noo_single_jobs_layout == 'fullwidth' || $noo_single_jobs_layout == 'sidebar' ) :

		// -- check option turn on/off show company info
			if( noo_get_option('noo_company_info_in_jobs', true) ) :
				Noo_Company::display_sidebar($company_id, true);
			endif;

		endif;
	?>
</div> <!-- /.main -->
<?php if( $noo_single_jobs_layout != 'fullwidth' && $noo_single_jobs_layout != 'left_sidebar' && $noo_single_jobs_layout != 'sidebar' ) : ?>
<div class="<?php noo_sidebar_class(); ?> hidden-print">
	<div class="noo-sidebar-wrap">
	<?php Noo_Company::display_sidebar($company_id, true); ?>
	</div>
</div>
<?php endif; ?>
