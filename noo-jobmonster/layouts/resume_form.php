<?php do_action('noo_post_resume_before'); ?>
<div class="noo-vc-accordion panel-group icon-right_arrow" id="resume-post-accordion">
    <div class="panel panel-default">
		<div class="panel-heading active">
			<h3 class="panel-title"><a data-toggle="collapse" class="accordion-toggle" data-parent="resume-post-accordion" href="#collapseGeneral"><?php _e('General Information', 'noo'); ?></a></h3>
		</div>
        <div id="collapseGeneral" class="noo-accordion-tab collapse in">
	        <div class="panel-body">
				<?php noo_get_layout('resume_general')?>
			</div>
		</div>
	</div>
    <div class="panel panel-default">
		<?php if( Noo_Resume::enable_resume_detail() ) : ?>
			<div class="panel-heading">
				<h3 class="panel-title"><a data-toggle="collapse" class="accordion-toggle" data-parent="resume-post-accordion" href="#collapseDetail"><?php _e('Resume Details', 'noo'); ?></a></h3>
			</div>
	        <div id="collapseDetail" class="noo-accordion-tab collapse">
		        <div class="panel-body">
					<?php noo_get_layout('resume_detail')?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
	jQuery('document').ready(function ($) {
		$('#resume-post-accordion').on('show.bs.collapse', function (e) {
			$(e.target).prev('.panel-heading').addClass('active');
		});
		$('#resume-post-accordion').on('hide.bs.collapse', function (e) {
			$(e.target).prev('.panel-heading').removeClass('active');
		});
	});
</script>
<?php do_action('noo_post_resume_after'); ?>