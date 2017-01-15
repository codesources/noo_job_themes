
<?php get_header(); ?>
<div class="container-wrap">
	<div class="main-content container-boxed max offset">
		<div class="row">
			<div class="<?php noo_main_class(); ?> <?php noo_blog_class(); ?>" role="main">

				<!-- Begin The loop -->
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php noo_get_layout( 'post', get_post_format()); ?>
					<?php endwhile; ?>
				<?php else : ?>
					<?php noo_get_layout( 'no-content' ); ?>
				<?php endif; ?>
				<!-- End The loop -->

				<?php noo_pagination(); ?>

			</div> <!-- /.main -->
			
			<?php get_sidebar(); ?>
		</div><!--/.row-->
	</div><!--/.container-boxed-->
</div><!--/.container-wrap-->
	
<?php get_footer(); ?>