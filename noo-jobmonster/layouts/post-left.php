<?php global $post;?>
<?php if(is_singular()):?>
	<?php if( noo_get_option('noo_blog_post_author_bio', true) || noo_get_option("noo_blog_social", true ) ) : ?>
		<div class="content-left">
			<?php if(noo_get_option('noo_blog_post_author_bio', true)):?>
				<div class="author-bio">
					<div class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ),95); ?>
					</div>
				</div>
			<?php endif;?>
			<?php noo_social_share();?>
		</div>
	<?php endif;?>
<?php else:?>
	<?php if( noo_get_option('noo_blog_post_author_bio', true) || noo_get_option("noo_blog_social", true ) ) : ?>
		<div class="content-left">
			<?php if(noo_get_option('noo_blog_post_author_bio', true)):?>
				<div class="author-bio">
					<div class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ),95); ?>
					</div>
					<?php noo_social_share();?>
				</div>
			<?php else : ?>
				<?php noo_social_share();?>
			<?php endif;?>
		</div>
	<?php endif;?>
<?php endif;?>
