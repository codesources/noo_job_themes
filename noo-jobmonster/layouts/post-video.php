
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php noo_get_layout('post', 'left'); ?>
	<?php if ( !is_singular() ) : ?>
	<header class="content-header">		
		<h2 class="content-title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permanent link to: "%s"','noo' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php the_title(); ?></a>
		</h2>
		<?php if(is_sticky()): ?>
			<span class="sticky_post"><i class="fa fa-thumb-tack"></i></span>
		<?php endif;?>
		<?php if(noo_get_option('noo_blog_show_post_meta', true)) : ?>
		<?php noo_content_meta(); ?>
		<?php endif;?>
	</header>
	<?php endif; ?>
	<?php if( has_featured_content() && !is_singular() ) : ?>
	<div class="content-featured">
		<?php noo_featured_video(); ?>		
	</div>
	<?php endif; ?>
	<div class="content-wrap">
		
		<?php if ( is_singular() ) : ?>
			<?php if( has_featured_content()) : ?>
				<div class="content-featured">
					<?php noo_featured_video(); ?>
					<?php if(is_sticky()): ?>
						<span class="sticky_post"><i class="fa fa-thumb-tack"></i></span>
					<?php endif;?>
				</div>
				<?php endif; ?>
			<div class="content">
				
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
				<?php if(is_singular()): ?>
					<?php if(noo_get_option('noo_blog_post_show_post_tag', true) && has_tag()) : ?>
					<div class="entry-tags">
					<?php the_tags(sprintf('<span>%s</span>',__('<i class="fa fa-tag"></i>','noo')),'')?>
					</div>
					<?php endif;?>
				<?php endif;?>
			</div>
		<?php else : ?>
			<div class="content-excerpt">
				<?php if(get_the_excerpt()):?>
					<?php the_excerpt(); ?>
				<?php endif;?>
				<?php if(noo_get_option('noo_blog_show_readmore', true)) : ?>
					<?php noo_readmore_link(); ?>
				<?php endif;?>
			</div>
		<?php endif; ?>
		
	</div>
	<?php noo_get_layout('post', 'footer'); ?>
</article> <!-- /#post- -->