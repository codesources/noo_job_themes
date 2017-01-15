<?php get_header(); ?>
<div class="container-wrap">
	<div class="container-boxed max offset main-content">
		<div class="row">
				<?php 
				do_action('noo_before_single_job');
				jm_job_detail();
				do_action('noo_after_single_job');
				?>
			
		</div> <!-- /.row -->
	</div> <!-- /.container-boxed.max.offset -->
</div><!--/.container-wrap-->
<?php get_footer(); ?>