<?php
	$quote = '';
	$quote = noo_get_post_meta(get_the_id() , '_noo_wp_post_quote', '');
	if($quote == '') {
		$quote = get_the_title( get_the_id() );
	}
	$cite = noo_get_post_meta(get_the_id() , '_noo_wp_post_quote_citation', '');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php noo_get_layout('post', 'left'); ?>
<?php 
$bg_url= '';
if(has_post_thumbnail()){
	// $bg_url = wp_get_attachment_url(get_post_thumbnail_id());
	$bg_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'noo-full');
	$bg_url = $bg_url[0];
} else {
	$bg_url = NOO_ASSETS_URI.'/images/default-bg.png';
}
?>
	<div class="content-featured"<?php if(!empty($bg_url)):?> style="background-image:url(<?php echo esc_url($bg_url)?>)"<?php endif;?>>
		<?php noo_featured_default(); ?>
		<?php if(is_sticky()): ?>
			<span class="sticky_post"><i class="fa fa-thumb-tack"></i></span>
		<?php endif;?>
		<header class="content-header">
			<?php if (is_singular()): ?>
				<h1 class="content-title content-quote">
					<?php echo esc_html($quote); ?>
				</h1>
				<cite class="content-sub-title content-cite"><?php echo esc_html($cite); ?></cite>
			<?php else : ?>
				<h2 class="content-title content-quote">
					<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(__('Permanent link to: "%s"', 'noo') , the_title_attribute('echo=0'))); ?>">
						<?php echo esc_html($quote); ?>
					</a>
				</h2>
				<cite class="content-sub-title content-cite"><i class="fa fa-quote-right"></i> <?php echo esc_html($cite); ?></cite>
			<?php endif; ?>
			<?php //noo_content_meta(); ?>
		</header>
	</div>
	<div class="content-wrap">
		
		<?php if (is_singular()): ?>
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
		<?php else: ?>
			<!-- Don't use excerpt in Quote post -->
			<!-- <div class="content-excerpt">
				<?php // the_excerpt(); ?>
			</div> -->
		<?php endif; ?>
	</div>
	<?php noo_get_layout('post', 'footer'); ?>
</article> <!-- /#post- -->