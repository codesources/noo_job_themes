<?php
// Variables
$default_link_color = '#2e2e2e'; // noo_default_text_color();

$noo_site_link_color = $default_link_color;
$noo_site_link_hover_color = noo_get_option( 'noo_site_link_color',  noo_default_primary_color() );

$noo_site_link_color_lighten_10 = lighten( $noo_site_link_hover_color, '10%' );
$noo_site_link_color_darken_5   = darken( $noo_site_link_hover_color, '5%' );
$noo_site_link_color_darken_10   = darken( $noo_site_link_hover_color, '10%' );
$noo_site_link_color_darken_15   = darken( $noo_site_link_hover_color, '15%' );

$default_font_color = noo_default_text_color();
$default_headings_color = noo_default_headings_color();

$noo_typo_use_custom_fonts_color = noo_get_option( 'noo_typo_use_custom_fonts_color', false );
$noo_typo_body_font_color = $noo_typo_use_custom_fonts_color ? noo_get_option( 'noo_typo_body_font_color', $default_font_color ) : $default_font_color;
$noo_typo_headings_font_color = $noo_typo_use_custom_fonts_color ? noo_get_option( 'noo_typo_headings_font_color', $default_headings_color ) : $default_headings_color; 

$noo_header_custom_nav_font = noo_get_option( 'noo_header_custom_nav_font', false );
$noo_header_nav_link_color = $noo_header_custom_nav_font ? noo_get_option( 'noo_header_nav_link_color', $noo_site_link_color ) : $noo_site_link_color;
$noo_header_nav_link_hover_color = $noo_header_custom_nav_font ? noo_get_option( 'noo_header_nav_link_hover_color', $noo_site_link_hover_color ) : $noo_site_link_hover_color;

?>

body {
	color: <?php echo esc_html($noo_typo_body_font_color); ?>;
}

h1, h2, h3, h4, h5, h6,
.h1, .h2, .h3, .h4, .h5, .h6,
h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
.h1 a, .h2 a, .h3 a, .h4 a, .h5 a, .h6 a {
	color: <?php echo esc_html($noo_typo_headings_font_color); ?>;
}

h1 a:hover, h2 a:hover, h3 a:hover, h4 a:hover, h5 a:hover, h6 a:hover,
.h1 a:hover, .h2 a:hover, .h3 a:hover, .h4 a:hover, .h5 a:hover, .h6 a:hover {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Global Link */
/* ====================== */
a {
	color: <?php echo esc_html($noo_site_link_color); ?>;
}
a:hover,
a:focus,
.text-primary,
a.text-primary:hover {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.bg-primary,
.navbar-nav li.menu-item-post-btn > a,
.navbar-nav li.menu-item-post-btn > a:hover,
.navbar-nav li.menu-item-post-btn > a:focus,
.navbar-nav li.menu-item-post-btn > a:hover:hover {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.bg-primary-overlay {
  background: <?php echo fade($noo_site_link_hover_color, '90%'); ?>;
}

/* Navigation Color */
/* ====================== */

/* Default menu style */
.noo-menu li > a {
	color: <?php echo esc_html($noo_header_nav_link_color); ?>;
}
.noo-menu li > a:hover,
.noo-menu li > a:active,
.noo-menu li.current-menu-item > a {
	color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}

/* NavBar: Link */
.navbar-nav li > a,
.navbar-nav ul.sub-menu li > a {
	color: <?php echo esc_html($noo_header_nav_link_color); ?>;
}

body.page-menu-transparent .navbar:not(.navbar-fixed-top) .navbar-nav > li > a:hover,
.navbar-nav li > a:hover,
.navbar-nav li > a:focus,
.navbar-nav li:hover > a,
.navbar-nav li.sfHover > a,
.navbar-nav li.current-menu-item > a {
	color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}

/* Border color */
@media (min-width: 992px) {
	.navbar-default .navbar-nav.sf-menu > li > ul.sub-menu {
		border-top-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
	}
	.navbar-default .navbar-nav.sf-menu > li > ul.sub-menu:before,
	.navbar-nav.sf-menu > li.align-center > ul.sub-menu:before,
	.navbar-nav.sf-menu > li.align-right > ul.sub-menu:before,
	.navbar-nav.sf-menu > li.align-left > ul.sub-menu:before,
	.navbar-nav.sf-menu > li.full-width.sfHover > a:before {
		border-bottom-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
	}
}

/* Dropdown Color */
.navbar-nav ul.sub-menu li > a:hover,
.navbar-nav ul.sub-menu li > a:focus,
.navbar-nav ul.sub-menu li:hover > a,
.navbar-nav ul.sub-menu li.sfHover > a,
.navbar-nav ul.sub-menu li.current-menu-item > a {
	color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}


/* Button Color */
/* ====================== */
.read-more,
.read-more:hover {
	background-color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}


/* Other Text/Link Color */
/* ====================== */

.noo-page-heading .page-title .count {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Loop */
.posts-loop.grid .event-info a:hover,
.posts-loop.grid.staffs .loop-item-title a {
	color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}
.posts-loop.grid.staffs .loop-item-title:before {
	background-color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}
.posts-loop.slider .loop-thumb-content .carousel-indicators li.active {
	border-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Job + Resume */
.job-action a.bookmark-job i:hover,
.job-action a.bookmark-job.bookmarked,
.job-desc ul li:before,
.more-jobs ul li:before,
.company-custom-fields ul li:before,
.noo-ajax-result ajob,
.resume .title-general span {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.link-alt {
	border-bottom-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Member */
.member-manage .table tbody tr:hover a:not(.btn-primary):hover,
.member-manage table tbody tr:hover a:not(.btn-primary):hover,
.noo-pricing-table .noo-pricing-column .pricing-content .pricing-info .readmore,
.noo-pricing-table .noo-pricing-column .pricing-content .pricing-info i {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.noo-pricing-table .noo-pricing-column .pricing-content .pricing-header .pricing-value .noo-price,
.jsteps li.completed .jstep-num a:before,
.jsteps li.active .jstep-num a:before,
.noo-pricing-table .noo-pricing-column .pricing-content .pricing-info .readmore:hover {
	border-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.jpanel-title {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.featured_slider .page a.selected,
.jsteps li.completed .jstep-num a,
.jsteps li.active .jstep-num a,
.gmap-loading .gmap-loader > div {
	background-color: <?php echo esc_html($noo_site_link_color_darken_10); ?>;
}

<?php if( NOO_WOOCOMMERCE_EXIST ) : ?>
/* WooCommerce */
/* ====================== */
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions a:hover,
.woocommerce ul.products li.product figcaption .product_title a:hover {
	color: <?php echo esc_html($noo_header_nav_link_hover_color); ?>;
}

.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .button:hover,
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .shop-loop-quickview:hover,
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .yith-wcwl-add-to-wishlist .add_to_wishlist:hover,
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .yith-wcwl-add-to-wishlist .add_to_wishlist:hover:before,
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .yith-wcwl-add-to-wishlist .yith-wcwl-wishlistaddedbrowse a:hover,
.woocommerce ul.products li.product figure .product-wrap .shop-loop-actions .yith-wcwl-add-to-wishlist .yith-wcwl-wishlistexistsbrowse a:hover,
.woocommerce .widget_layered_nav ul li.chosen a:hover {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
<?php endif; ?>

/* WordPress Element */
/* ====================== */

/* Comment */
h2.comments-title span,
.comment-reply-link,
.comment-author a:hover {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Post */
.content-meta > span > a:hover,
.hentry.format-quote a:hover,
.hentry.format-link a:hover,
.single .hentry.format-quote .content-title:hover,
.single .hentry.format-link .content-title:hover,
.single .hentry.format-quote a:hover,
.single .hentry.format-link a:hover,
.sticky h2.content-title:before {
	color: <?php echo esc_html($noo_site_link_hover_color); ?> !important;
}

.content-thumb:before,
.entry-tags a:hover {
	background: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Pagination */
.pagination .page-numbers:hover:not(.disabled),
.pagination .page-numbers.current:not(.disabled),
.post-navigation .prev-post,
.post-navigation .next-post,
.loadmore-loading span {
	background: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Widget */
.wigetized .widget a:hover,
.wigetized .widget ul li a:hover,
.wigetized .widget ol li a:hover,
.wigetized .widget.widget_recent_entries li a:hover {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

/* Shortcode */
/* ====================== */

.btn-primary,
.form-submit input[type="submit"],
.wpcf7-submit,
.widget_newsletterwidget .newsletter-submit,
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active,
.form-submit input[type="submit"]:hover,
.form-submit input[type="submit"]:focus,
.btn-primary.active,
.wpcf7-submit:hover,
.progress-bar {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.btn-primary.pressable {
	-webkit-box-shadow: 0 4px 0 0 <?php echo esc_html($noo_site_link_color_darken_15); ?>,0 4px 9px rgba(0,0,0,0.75) !important;
	box-shadow: 0 4px 0 0  <?php echo esc_html($noo_site_link_color_darken_15); ?>,0 4px 9px rgba(0,0,0,0.75) !important;
}

.btn-link,
.btn.btn-white:hover,
.wpcf7-submit.btn-white:hover,
.widget_newsletterwidget .newsletter-submit.btn-white:hover,
.colophon.site-info .footer-more a:hover {
	color: <?php echo esc_html($noo_site_link_color); ?>;
}

.noo-tabs.vc_tta .vc_tta-tab > a:hover, .noo-tabs.vc_tta .vc_tta-tab > a:focus {
	color: <?php echo esc_html($noo_site_link_hover_color); ?> !important;
}

.btn-link:hover,
.btn-link:focus {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.noo-social a {
	color: <?php echo esc_html($noo_typo_body_font_color); ?>;
}
.noo-social a:hover,
.login-form-links > span a,
.login-form-links > span .fa,
.form-control-flat > .radio i,
.form-control-flat > .checkbox i {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.form-control-flat .radio i:after {
	background: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.noo-step-icon .noo-step-icon-class:hover {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.noo-step-icon .noo-step-icon-class:after {
	border-color: <?php echo esc_html($noo_site_link_color_lighten_10); ?>;
}
.noo-step-icon .noo-step-icon-item:after,
.noo-step-icon .noo-step-icon-item:before {
	background-color: <?php echo esc_html($noo_site_link_color_lighten_10); ?>;
}
.noo-recent-news.grid .loop-item-wrap:hover .loop-item-featured:before {
	background-color: <?php echo fade($noo_site_link_hover_color, '70%'); ?>;
}

/* FED */
#fep-content input[type="submit"],
#fep-content input[type="submit"]:hover,
#fep-content input[type="submit"]:focus {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.member-manage #fep-content a {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.member-manage #fep-content a:hover {
	border-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.noo-counter-icon .noo-counter-item .fa {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.noo_job.noo_job-style-2.featured-job:before {
	border-left: solid 50px <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.company-list .company-item .company-item-meta .fa{
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.company-list .company-item.featured-company:before{
	border-left: solid 50px <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.company-letters a.selected{
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}

.wp-editor-wrap .button {
	background:  <?php echo esc_html($noo_site_link_hover_color); ?>!important;
}
.btn-readmore {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.add-new-location a {
	color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}
.woocommerce .woocommerce-MyAccount-navigation ul li.is-active a, .woocommerce .woocommerce-MyAccount-navigation ul li:hover a {
	background-color: <?php echo esc_html($noo_site_link_hover_color); ?>;
}