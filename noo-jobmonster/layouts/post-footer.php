<?php if((noo_get_option('noo_blog_post_show_post_tag', true)) && has_tag($post->ID)):?>
	<footer class="content-footer">
		<div class="content-tags">
			<?php echo get_the_tag_list()?>
		</div>
	</footer>
<?php endif;?>
<?php if((noo_get_option('noo_blog_show_post_tag', true)) && has_tag($post->ID)):?>
	<footer class="content-footer">
		<div class="content-tags">
			<?php echo get_the_tag_list('','','',$post->ID)?>
		</div>
	</footer>
<?php endif;?>
