<?php
/*
Template Name: Page with Right Sidebar
*/
?>
<?php get_header(); ?>

<div class="container-wrap">
		
	<div class="main-content container-boxed max offset">
		
		<div class="row">
			
			<div class="<?php noo_main_class(); ?> <?php noo_page_class(); ?>" role="main">
				<!-- Begin The loop -->
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>
				<?php endif; ?>
				<!-- End The loop -->
			</div>
			<?php get_sidebar(); ?>
		</div><!--/.row-->
	</div><!--/.container-boxed-->
</div>

<?php get_footer(); ?>
