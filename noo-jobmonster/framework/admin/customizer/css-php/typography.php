<?php
// Variables
$noo_typo_use_custom_fonts = noo_get_option( 'noo_typo_use_custom_fonts', false );
$noo_typo_headings_uppercase = noo_get_option( 'noo_typo_headings_uppercase', false );
$noo_typo_body_font_size = noo_get_option( 'noo_typo_body_font_size', noo_default_font_size() );

// Font size computed
$font_size_base       = $noo_typo_body_font_size;
$font_size_large      = ceil( $font_size_base * 1.285 );
$font_size_small      = ceil(($font_size_base * 0.8));
$line_height_computed = floor(($font_size_base * 1.7));

if( $noo_typo_use_custom_fonts ) :
	$noo_typo_headings_font = noo_get_option( 'noo_typo_headings_font', noo_default_font_family() );
	$noo_typo_headings_font_style = noo_get_option( 'noo_typo_headings_font_style', 'bold' );
	$noo_typo_headings_font_weight = noo_get_option( 'noo_typo_headings_font_weight', 'bold' );
	$noo_typo_headings_uppercase = noo_get_option( 'noo_typo_headings_uppercase', false );

	$noo_typo_body_font = noo_get_option( 'noo_typo_body_font', noo_default_font_family() );
	$noo_typo_body_font_style = noo_get_option( 'noo_typo_body_font_style', 'normal' );
	$noo_typo_body_font_weight = noo_get_option( 'noo_typo_body_font_weight', noo_default_font_weight() );

?>

/* Body style */
/* ===================== */
body {
	font-family: "<?php echo esc_html( $noo_typo_body_font ); ?>", sans-serif;
	font-size: <?php echo esc_html( $noo_typo_body_font_size ) . 'px'; ?>;
	font-style: <?php echo esc_html( $noo_typo_body_font_style ); ?>;
	font-weight: <?php echo esc_html( $noo_typo_body_font_weight ); ?>;
}

/* Headings */
/* ====================== */
h1, h2, h3, h4, h5, h6,
.h1, .h2, .h3, .h4, .h5, .h6 {
	font-family: "<?php echo esc_html( $noo_typo_headings_font ); ?>", sans-serif;
	font-style: <?php echo esc_html( $noo_typo_headings_font_style ); ?>;
	font-weight: <?php echo esc_html( $noo_typo_headings_font_weight ); ?>;	
	<?php if ( !empty( $noo_typo_headings_uppercase ) ) : ?>
		text-transform: uppercase;
	<?php else : ?>
		text-transform: none;
	<?php endif; ?>
}

/* Scaffolding */
/* ====================== */
select {
	font-size: <?php echo esc_html($font_size_base) . 'px'; ?>;
}

/* Bootstrap */
.btn,
.dropdown-menu,
.input-group-addon,
.popover-title
output,
.form-control {
	font-size: <?php echo esc_html($font_size_base) . 'px'; ?>;
}
legend,
.close {
	font-size: <?php echo floor($font_size_base * 1.5) . 'px'; ?>;
}
.lead {
	font-size: <?php echo floor($font_size_base * 1.15) . 'px'; ?>;
}
@media (min-width: 768px) {
	.lead {
		font-size: <?php echo floor($font_size_base * 1.5) . 'px'; ?>;
	}
}
pre {
	padding: <?php echo (($line_height_computed - 1) / 2) . 'px'; ?>;
	margin: 0 0 <?php echo ($line_height_computed / 2) . 'px'; ?>;
	font-size: <?php echo ($font_size_base - 1) . 'px'; ?>;
}
.panel-title {
	font-size: <?php echo ceil($font_size_base * 1.125) . 'px'; ?>;
}

@media screen and (min-width: 768px) {
	.jumbotron h1, .h1 {
		font-size: <?php echo ceil($font_size_base * 4.5) . 'px'; ?>;
	}
}

.badge,
.btn-sm,
.btn-xs,
.dropdown-header,
.input-sm,
.input-group-addon.input-sm,
.pagination-sm,
.tooltip {
	<?php echo esc_html($font_size_small) . 'px'; ?>;
}

.btn-lg,
.input-lg,
.input-group-addon.input-lg,
 pagination-lg {
	font-size: <?php echo esc_html($font_size_large) . 'px'; ?>;
}

/* WordPress Element */
/* ====================== */
.content-link,
.content-cite,
.comment-form-author input,
.comment-form-email input,
.comment-form-url input,
.comment-form-comment textarea,
.pagination .page-numbers,
.entry-tags span,
.widget.widget_recent_entries li a,
.default_list_products .woocommerce ul.products.grid li.product figcaption h3.product_title,
.default_list_products .woocommerce ul.products li.product figure figcaption .product_title,
.woocommerce div.product .wpn_buttons,
.woocommerce div.product .product-navigation .next-product a > span,
.woocommerce div.product .product-navigation .next-product a .next-product-info .next-desc .amount,
.woocommerce div.product .product-navigation .prev-product a > span,
.woocommerce div.product div.summary .variations_form label,
.woocommerce div.product div.summary .product_meta > span,
.woocommerce .list_products_toolbar .products-toolbar span,
.woocommerce ul.products li.product .price,
.woocommerce ul.products.list li.product h3.product_title,
.woocommerce div.product span.price,
.woocommerce div.product p.price,
.woocommerce div.product .woocommerce-tabs .nav-tabs > li > a,
.woocommerce .quantity .plus,
.woocommerce .quantity .minus,
.woocommerce #reviews #comments ol.commentlist li .comment-text p.meta strong,
.woocommerce table.shop_attributes th,
.woocommerce table.cart .product-price,
.woocommerce table.cart .product-subtotal,
.woocommerce .checkout #order_review td.product-total,
.woocommerce .checkout #order_review .cart-subtotal td,
.woocommerce .checkout #order_review .order-total td,
.woocommerce .view_order .wrap_order_details table tr .amount,
.woocommerce .checkout_complete ul.order_details.general li.total strong,
.woocommerce table.my_account_orders tr td.order-total .amount,
.woocommerce .widget_price_filter .price_slider_amount {
	font-family: "<?php echo esc_html( $noo_typo_headings_font ); ?>", sans-serif;
}
<?php else : ?>
/* Body style */
/* ===================== */
body {
	font-size: <?php echo esc_html( $noo_typo_body_font_size ) . 'px'; ?>;
}

/* Headings */
/* ====================== */
h1, h2, h3, h4, h5, h6,
.h1, .h2, .h3, .h4, .h5, .h6 {
	<?php if ( !empty( $noo_typo_headings_uppercase ) ) : ?>
		text-transform: uppercase;
	<?php else : ?>
		text-transform: none;
	<?php endif; ?>
}
<?php endif; ?>