<?php get_header(); ?>
<div class="container-wrap">
	<div class="main-content container-boxed max offset">
		<div class="row">
			<div class="<?php noo_main_class(); ?>" role="main">
				<?php jm_resume_loop(); ?>
			</div> <!-- /.main -->
			<?php get_sidebar(); ?>
		</div><!--/.row-->
	</div><!--/.container-boxed-->
</div><!--/.container-wrap-->
	
<?php get_footer(); ?>