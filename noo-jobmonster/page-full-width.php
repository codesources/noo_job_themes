<?php
/*
Template Name: Full Width
*/
?>
<?php get_header(); ?>
<div class="container-wrap">	
	<div class="main-content container-fullwidth">
		<div class="row">
			<div class="<?php noo_main_class(); ?>" role="main">
				<!-- Begin The loop -->
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>
				<?php endif; ?>
				<!-- End The loop -->
			</div> <!-- /.main -->
		</div><!--/.row-->
	</div><!--/.container-full-->
</div><!--/.container-wrap-->
	
<?php get_footer(); ?>