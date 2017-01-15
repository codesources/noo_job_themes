<?php noo_get_layout( 'footer', 'widgetized' ); ?>
<?php
	$noo_bottom_bar_content = noo_get_option( 'noo_bottom_bar_content', '&copy; 2016 JobMonster. Designed with <i class="fa fa-heart text-primary"></i> by NooTheme' );
	$allowed_html = array(
		'a' => array(
			'href' => array(),
			'target' => array(),
			'title' => array(),
			'rel' => array(),
			'class' => array(),
			'style' => array(),
		),
		'img' => array(
			'src' => array(),
			'class' => array(),
			'style' => array(),
		),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'p' => array(
			'class' => array(),
			'style' => array()
		),
		'br' => array(
			'class' => array(),
			'style' => array()
		),
		'hr' => array(
			'class' => array(),
			'style' => array()
		),
		'span' => array(
			'class' => array(),
			'style' => array()
		),
		'em' => array(
			'class' => array(),
			'style' => array()
		),
		'strong' => array(
			'class' => array(),
			'style' => array()
		),
		'small' => array(
			'class' => array(),
			'style' => array()
		),
		'b' => array(
			'class' => array(),
			'style' => array()
		),
		'i' => array(
			'class' => array(),
			'style' => array()
		),
		'u' => array(
			'class' => array(),
			'style' => array()
		),
		'ul' => array(
			'class' => array(),
			'style' => array()
		),
		'ol' => array(
			'class' => array(),
			'style' => array()
		),
		'li' => array(
			'class' => array(),
			'style' => array()
		),
		'blockquote' => array(
			'class' => array(),
			'style' => array()
		),
	);

	$noo_bottom_bar_content = wp_kses( $noo_bottom_bar_content, $allowed_html );
?>
<?php if ( !empty( $noo_bottom_bar_content ) ) : ?>

	<footer class="colophon site-info">
		<div class="container-full">
			<div class="footer-more">
				<div class="container-boxed">
					<div class="row">
						<div class="col-md-12">
						<?php if ( $noo_bottom_bar_content != '' ) : ?>
							<div class="noo-bottom-bar-content">
								<?php echo $noo_bottom_bar_content; ?>
							</div>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- /.container-boxed -->
	</footer> <!-- /.colophon.site-info -->
<?php endif; ?>
</div> <!-- /#top.site -->
<?php wp_footer(); ?>
</body>
</html>
