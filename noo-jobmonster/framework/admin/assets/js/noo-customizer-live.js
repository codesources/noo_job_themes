/**
 * This file adds some LIVE to the Theme Customizer live preview.
 */

var g_ajax_counter = 0;
alertify.set({
	labels: {
		ok    : nooCustomizerL10n.ok,
		cancel: nooCustomizerL10n.cancel
	},
	buttonReverse: true,
	buttonFocus: 'none'
});

function showCannotPreviewMsg() {
	// if( jQuery('.alertify-log-cannot_preview_msg').length === 0 ) {
	// 	// Display updating message
	// 	alertify.log(nooCustomizerL10n.cannot_preview_msg, 'cannot_preview_msg', 3000);
	// }
}

function showUpdatingMsg() {
	g_ajax_counter ++;
	if( g_ajax_counter > 1 ) {
		return;
	}

	// Display updating message
	alertify.log(nooCustomizerL10n.ajax_update_msg, 'ajax_update_msg', 0);
}

function hideUpdatingMsg() {
	g_ajax_counter = Math.max( 0, g_ajax_counter - 1 );
	if( g_ajax_counter > 0 ) {
		return;
	}

	// Hide updating message
	jQuery('.alertify-log-ajax_update_msg').remove();
}

function noo_redirect_url ( url ) {
	if( url === '' )
		return;

	var noo_preview = new wp.customize.Preview({
		url: url,
		channel: wp.customize.settings.channel
	});

	showUpdatingMsg();
	noo_preview.send( 'scroll', 0 );
	noo_preview.send( 'url', url );
}

function noo_redirect_preview( type ) {
	if( typeof wp.customize === "undefined" )
		return;

	var url     = '';
	var message = '';

	switch ( type ) {
		case 'blog':
			url = nooCustomizerL10n.blog_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.blog_page_txt);
			break;
		case 'jobs':
			url = nooCustomizerL10n.jobs_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.jobs_page_txt);
			break;
		case 'companies':
			url = nooCustomizerL10n.companies_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.companies_page_txt);
			break;
		case 'resumes':
			url = nooCustomizerL10n.resumes_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.resumes_page_txt);
			break;
		case 'shop':
			url = nooCustomizerL10n.shop_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.shop_page_txt);
			break;
		case 'archive':
			url = nooCustomizerL10n.archive_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.archive_page_txt);
			break;
		case 'post':
			url = nooCustomizerL10n.post_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.post_page_txt);
			break;
		case 'job':
			url = nooCustomizerL10n.job_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.job_page_txt);
			break;
		case 'resume':
			url = nooCustomizerL10n.resume_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.resume_page_txt);
			break;
		case 'product':
			url = nooCustomizerL10n.product_page;
			message = nooCustomizerL10n.redirect_msg.replace( '%s', nooCustomizerL10n.product_page_txt);
			break;
	}

	if( url === '' )
		return;

	// Display updating message
	alertify.alert(message, function( e ) {
		noo_redirect_url( url );
		return false;
	});
}

function noo_refresh_preview() {
	showUpdatingMsg();
	parent.wp.customize.previewer.refresh();
}

function noo_refresh_preview_blog() {
	if( nooL10n.is_blog === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'blog' );
	}
}

function noo_refresh_preview_post() {
	if( nooL10n.is_single === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'post' );
	}
}

function noo_refresh_preview_jobs() {
	if( nooL10n.is_jobs === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'jobs' );
	}
}

function noo_refresh_preview_companies() {
	if( nooL10n.is_companies === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'companies' );
	}
}

function noo_refresh_preview_job() {
	if( nooL10n.is_job === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'job' );
	}
}

function noo_refresh_preview_resumes() {
	if( nooL10n.is_resumes === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'resumes' );
	}
}

function noo_refresh_preview_resume() {
	if( nooL10n.is_resume === "true" ) {
		noo_refresh_preview( );
	} else {
		noo_redirect_preview( 'resume' );
	}
}

function noo_update_customizer_css( type ) {
	query = {
			'noo_customize_ajax': 'on',
			'customized'        : JSON.stringify( wp.customize.get() ),
			'action'            : 'noo_get_customizer_css_' + type,
			'nonce'             : nooCustomizerL10n.customize_live_css
		};
	showUpdatingMsg();
	jQuery.ajax( nooL10n.ajax_url, {
		type: 'POST',
		data: query
	}).done(function ( data ) {
		// Clear live css
		jQuery('#noo-customizer-live-css').empty();

		// Place new css to customizer css
		var $customizeCSS = jQuery( '#noo-customizer-css-' + type).length ? jQuery( '#noo-customizer-css-' + type) : jQuery('<style id="noo-customizer-css-' + type + '" type="text/css" />').appendTo('head');
		$customizeCSS.text( data );

		g_ajax_counter = Math.max( 0, g_ajax_counter - 1 );
	} ).always( function() {
		hideUpdatingMsg();
	} );
}

function noo_get_attachment_url_ajax( image, doneFn ) {
	if(Math.floor(image) == image && jQuery.isNumeric(image)) {
		showUpdatingMsg();
		return jQuery.ajax( nooL10n.ajax_url, {
			type: 'POST',
			data: {
				'attachment_id': image,
				'action'       : 'noo_ajax_get_attachment_url',
				'nonce'             : nooCustomizerL10n.customize_attachment
			}
		} ).done( doneFn ).fail( function() {
			noo_redirect_url( window.location.href );
		} ).always( function() {
			hideUpdatingMsg();
		} );
	} else {
		doneFn( image );
	}
}

function noo_get_menu( menu_location ) {
	showUpdatingMsg();
	return jQuery.ajax( nooL10n.ajax_url, {
		type: 'POST',
		data: {
			'menu_location': menu_location,
			'action'       : 'noo_ajax_get_menu',
			'nonce'        : nooCustomizerL10n.customize_menu
		}
	} ).fail( function() {
		noo_redirect_url( window.location.href );
	} ).always( function() {
		hideUpdatingMsg();
	} );
}

function noo_get_social() {
	showUpdatingMsg();
	return jQuery.ajax( nooL10n.ajax_url, {
		type: 'POST',
		data: {
			'noo_customize_ajax': 'on',
			'customized'        : JSON.stringify( wp.customize.get() ),
			'action'            : 'noo_ajax_get_social_icons',
			'nonce'             : nooCustomizerL10n.customize_social_icons
		}
	} ).fail( function() {
		noo_redirect_url( window.location.href );
	} ).always( function() {
		hideUpdatingMsg();
	} );
}

function noo_update_live_css( additionalCSS ) {
	var $tempCSS = jQuery('#noo-customizer-live-css').length ? jQuery('#noo-customizer-live-css') : jQuery('<style id="noo-customizer-live-css" type="text/css" />').appendTo('head');
	currentStyle = $tempCSS.text();
	$tempCSS.text(currentStyle + additionalCSS);
}

function noo_update_font( prefix, linkID ) {
	font        = wp.customize.value( prefix + 'font' )();
	if( font !== '' ) {
		fontLink = jQuery(linkID).length ? jQuery(linkID) : jQuery('<link rel="stylesheet" id="' + linkID + '" type="text/css" media="all" />').appendTo('head');

		font_style  = wp.customize.value( prefix + 'font_style' )();
		font_weight = wp.customize.value( prefix + 'font_weight' )();
		font_subset = wp.customize.value( prefix + 'font_subset' )();

		font        = font.replace( ' ', '+' );
		font_style  = ( font_style === '' ) ? 'normal' : font_style;
		font_weight = ( font_weight === '' ) ? '400' : font_weight;
		font_subset = ( font_subset === '' ) ? 'latin' : font_subset;

		fontHref = '//fonts.googleapis.com/css?family=' + font + ':' + font_weight + font_style;
		if( font_subset !== 'latin' ) {
			fontHref += '&subset' + font_subset;
		}

		fontLink.attr( 'href', fontHref );
	}
}

( function( $ ) {

	//
	// Site Enhancement
	// 

	// Custom Favicon
	wp.customize( 'noo_custom_favicon', function( value ) {
		value.bind( function( newval ) {
			showCannotPreviewMsg();
		} );
	} );

	// Back To Top
	wp.customize( 'noo_back_to_top', function( value ) {
		value.bind( function( newval ) {
			// Update Main CSS
			$back_to_top = ( $('.go-to-top').length > 0 ) ? $('.go-to-top') : $('<a href="#" class="go-to-top"><i class="fa fa-angle-up"></i></a>').appendTo($('body'));

			if( newval ) {
				$back_to_top.show();
			} else {
				$back_to_top.hide();
			}
		} );
	} );

	// Smooth Scrolling
	wp.customize( 'noo_smooth_scrolling', function( value ) {
		value.bind( function( newval ) {
			showCannotPreviewMsg();
		} );
	} );

	//
	// Design & Layout
	// 

	// Site Design
	wp.customize( 'noo_site_skin', function( value ) {
		value.bind( function( newval ) {
			// Call ajax update first because it needs time to finish.
			noo_update_customizer_css( 'design' );
			noo_update_customizer_css( 'header' );

			// Update Main CSS
			$mainCSS = $('#noo-main-style-css');
			$cssHref = $mainCSS.attr( 'href');

			if( newval === 'dark' ) {
				oldHref = 'noo.css';
				newHref = 'noo-dark.css';
			} else {
				oldHref = 'noo-dark.css';
				newHref = 'noo.css';
			}

			$cssHref = $cssHref.replace( oldHref, newHref );

			$mainCSS.attr( 'href', $cssHref );
		} );
	} );

	// Site layout
	wp.customize( 'noo_site_layout', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			switch( newval ) {
				case 'fullwidth':
					$body.removeClass('boxed-layout').addClass('full-width-layout');
					break;
				case 'boxed':
					$body.removeClass('full-width-layout').addClass('boxed-layout');
					break;
			}

			$(document).trigger('noo-layout-changed');
		} );
	} );

	// Site Width
	wp.customize( 'noo_layout_site_width', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') ) {
				$( 'body > .site' ).css( 'width', newval + '%' );
				$( 'body .navbar.navbar-fixed-top' ).css( 'width', newval + '%' );

				$(document).trigger('noo-layout-changed');
			} else {
				
			}
		} );
	} );

	// Site Max Width
	wp.customize( 'noo_layout_site_max_width', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') ) {
				$( 'body > .site' ).css( 'max-width', newval + 'px' );
				$( 'body .navbar.navbar-fixed-top' ).css( 'max-width', newval + 'px' );

				$(document).trigger('noo-layout-changed');
			}
		} );
	} );

	// Background Color
	wp.customize( 'noo_layout_bg_color', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') ) {
				$body.css( 'background-color', newval );
			}
		} );
	} );

	// Background Image
	wp.customize( 'noo_layout_bg_image', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') ) {
				if( newval === '' ) {
					$body.css( 'background-image', 'none' );
					return;
				}

				noo_get_attachment_url_ajax( newval, function ( data ) {
					// Background Image
					$body.css( 'background-image', 'url("' + data + '")' );
				} );
			}
		} );
	} );

	// Background Image Repeat
	wp.customize( 'noo_layout_bg_repeat', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') && newval !== '' ) {
				$body.css( 'background-repeat', newval );
			}
		} );
	} );

	// Background Image Position
	wp.customize( 'noo_layout_bg_align', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') && newval !== '' ) {
				$body.css( 'background-position', newval );
			}
		} );
	} );

	// Background Image Attachment
	wp.customize( 'noo_layout_bg_attachment', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') && newval !== '' ) {
				$body.css( 'background-attachment', newval );
			}
		} );
	} );

	// Background Image Auto Resize
	wp.customize( 'noo_layout_bg_cover', function( value ) {
		value.bind( function( newval ) {
			$body = $('body');
			if( $body.hasClass('boxed-layout') && newval ) {
				$body.css( '-webkit-background-size', 'cover' )
					.css( '-moz-background-size', 'cover' )
					.css( '-o-background-size', 'cover' )
					.css( 'background-size', 'cover' );
			} else {
				$body.css( '-webkit-background-size', 'auto' )
					.css( '-moz-background-size', 'auto' )
					.css( '-o-background-size', 'auto' )
					.css( 'background-size', 'auto' );
			}
		} );
	} );

	// Site Links Color
	wp.customize( 'noo_site_link_color', function( value ) {
		value.bind( function( newval ) {
			noo_update_customizer_css( 'design' );
			// noo_update_customizer_css( 'header' );
		} );
	} );

	// Site Links Color
	wp.customize( 'noo_enable_rtl_support', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// // Site Links Hover Color
	// wp.customize( 'noo_site_link_hover_color', function( value ) {
	// 	value.bind( function( newval ) {
	// 		noo_update_customizer_css( 'design' );
	// 		// noo_update_customizer_css( 'header' );
	// 	} );
	// } );

	// // Secondary Color
	// wp.customize( 'noo_site_secondary_color', function( value ) {
	// 	value.bind( function( newval ) {
	// 		noo_update_customizer_css( 'design' );
	// 		// noo_update_customizer_css( 'header' );
	// 	} );
	// } );

	//
	// Typography
	// 

	// Use Custom Fonts
	wp.customize( 'noo_typo_use_custom_fonts', function( value ) {
		value.bind( function( newval ) {
			noo_update_customizer_css( 'typography' );
		} );
	} );

	// Custom Fonts Color
	wp.customize( 'noo_typo_use_custom_fonts_color', function( value ) {
		value.bind( function( newval ) {
			noo_update_customizer_css( 'design' );
		} );
	} );

	// Headings Font
	wp.customize( 'noo_typo_headings_font', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_headings_', '#noo-google-fonts-headings-css' );

			// Update style
			additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {font-family: "' + newval + '", sans-serif;}';
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Headings Font Style
	wp.customize( 'noo_typo_headings_font_style', function( value ) {
		value.bind( function( newval ) {
			newval = ( newval == '' ) ? 'normal' : newval;

			// Update Google Font Link
			noo_update_font( 'noo_typo_headings_', '#noo-google-fonts-headings-css' );

			// Update style
			additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {font-style: ' + newval + ';}';
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Headings Font Weight
	wp.customize( 'noo_typo_headings_font_weight', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_headings_', '#noo-google-fonts-headings-css' );

			// Update style
			additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {font-weight: ' + newval + ';}';
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Headings Font Subset
	wp.customize( 'noo_typo_headings_font_subset', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_headings_', '#noo-google-fonts-headings-css' );
		} );
	} );

	// Headings Font Color
	wp.customize( 'noo_typo_headings_font_color', function( value ) {
		value.bind( function( newval ) {
			additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {color: ' + newval + ';}';
			additionalStyle += 'h1 a, h2 a, h3 a, h4 a, h5 a, h6 a, .h1 a, .h2 a, .h3 a, .h4 a, .h5 a, .h6 a {color: ' + newval + ';}';

			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Headings Font Uppercase
	wp.customize( 'noo_typo_headings_uppercase', function( value ) {
		value.bind( function( newval ) {
			if( newval ) {
				additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {text-transform: uppercase;}';
			} else {
				additionalStyle = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {text-transform: none;}';
			}

			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Body Font
	wp.customize( 'noo_typo_body_font', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_body_', '#noo-google-fonts-body-css' );

			// Update style
			noo_update_live_css( 'body {font-family: "' + newval + '", sans-serif;}' );
		} );
	} );

	// Body Font Style
	wp.customize( 'noo_typo_body_font_style', function( value ) {
		value.bind( function( newval ) {
			newval = ( newval == '' ) ? 'normal' : newval;
			// Update Google Font Link
			noo_update_font( 'noo_typo_body_', '#noo-google-fonts-body-css' );

			// Update style
			noo_update_live_css( 'body {font-style: ' + newval + ';}' );
		} );
	} );

	// Body Font Weight
	wp.customize( 'noo_typo_body_font_weight', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_body_', '#noo-google-fonts-body-css' );

			// Update style
			noo_update_live_css( 'body {font-weight: ' + newval + ';}' );
		} );
	} );

	// Body Font Subset
	wp.customize( 'noo_typo_body_font_subset', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_typo_body_', '#noo-google-fonts-body-css' );
		} );
	} );

	// Body Font Size
	wp.customize( 'noo_typo_body_font_size', function( value ) {
		value.bind( function( newval ) {
			// Update Style
			noo_update_customizer_css( 'typography' );
		} );
	} );

	// Body Font Color
	wp.customize( 'noo_typo_body_font_color', function( value ) {
		value.bind( function( newval ) {
			// Update Style
			noo_update_customizer_css( 'design' );
		} );
	} );

	//
	// Header
	// 

	// // Header Background Color
	// wp.customize( 'noo_header_bg_color', function( value ) {
	// 	value.bind( function( newval ) {
	// 		if( newval === '' ) {
	// 			newval = 'transparent';
	// 		}

	// 		additionalStyle  = '@media (min-width: 992px) {'; // start @media
	// 		additionalStyle += '.noo-topbar,.noo-header,.navbar-fixed-left,.navbar-fixed-right{background-color: ' + newval + ';}';
	// 		additionalStyle += '}'; // end @media

	// 		// Update Style
	// 		noo_update_live_css( additionalStyle );
	// 	} );
	// } );

	// NavBar Position
	wp.customize( 'noo_header_nav_position', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Show Post Job/Resume Button
	wp.customize( 'noo_header_nav_post_btn', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Show User Menu
	wp.customize( 'noo_header_nav_user_menu', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// // NavBar Shrinkable
	// wp.customize( 'noo_header_nav_shrinkable', function( value ) {
	// 	value.bind( function( newval ) {
	// 		$navbar = $('.navbar');
	// 		if( $navbar.hasClass( 'fixed-top' ) && newval ) {
	// 			$navbar.addClass( 'shrinkable' );
	// 		} else {
	// 			$navbar.removeClass( 'shrinkable' );
	// 		}
	// 	} );
	// } );

	// // Smart Scroll
	// wp.customize( 'noo_header_nav_smart_scroll', function( value ) {
	// 	value.bind( function( newval ) {
	// 		$navbar = $('.navbar');
	// 		if( $navbar.hasClass( 'fixed-top' ) && newval ) {
	// 			$navbar.addClass( 'smart_scroll' );
	// 		} else {
	// 			$navbar.removeClass( 'smart_scroll' );
	// 		}
	// 	} );
	// } );

	// // Show Search Icon
	// wp.customize( 'noo_header_nav_icon_search', function( value ) {
	// 	value.bind( function( newval ) {
	// 		noo_refresh_preview();
	// 	} );
	// } );

	// // Show Cart Icon
	// wp.customize( 'noo_header_nav_icon_cart', function( value ) {
	// 	value.bind( function( newval ) {
	// 		noo_refresh_preview();
	// 	} );
	// } );

	// Custom NavBar Font
	wp.customize( 'noo_header_custom_nav_font', function( value ) {
		value.bind( function( newval ) {
			if ( newval ) {
				// Use all the custom font setting available
				nav_font = wp.customize.value( 'noo_header_nav_font' )();
				if( nav_font === '' ) {
					return;
				}
				nav_font_style  = wp.customize.value( 'noo_header_nav_font_style' )();
				nav_font_weight = wp.customize.value( 'noo_header_nav_font_weight' )();
				nav_font_subset = wp.customize.value( 'noo_header_nav_font_subset' )();
				nav_font_color  = wp.customize.value( 'noo_header_nav_link_color' )();
				nav_hover_color = wp.customize.value( 'noo_header_nav_link_hover_color' )();

				nav_font_style  = ( nav_font_style === '' ) ? 'normal' : nav_font_style;
				nav_font_weight = ( nav_font_weight === '' ) ? '400' : nav_font_weight;
				nav_font_subset = ( nav_font_subset === '' ) ? 'latin' : nav_font_subset;

				// Update Google Font Link
				$fontLink = $('#noo-google-fonts-nav-css').length ? $('#noo-google-fonts-nav-css') : $('<link rel="stylesheet" id="noo-google-fonts-nav-css" type="text/css" media="all" />').appendTo('head');
				$fontHref = '//fonts.googleapis.com/css?family=' + nav_font + ':' + nav_font_weight + nav_font_style;
				if( nav_font_subset !== 'latin' ) {
					$fontHref += '&subset' + nav_font_subset;
				}

				$fontLink.attr( 'href', $fontHref );
			
			} else {
				// Use the default font for NavBar
				nav_font        = 'Montserrat';
				nav_font_style  = 'normal';
				nav_font_weight = '400';
				nav_font_subset = 'latin';

				nav_font_color  = wp.customize.value( 'noo_typo_body_font_color' )();
				nav_hover_color = wp.customize.value( 'noo_site_link_color' )();
			}

			additionalStyle  = '.navbar-nav li > a {';
			additionalStyle += 'font-family: "' + nav_font + '", sans-serif;';
			additionalStyle += 'font-style: '  + nav_font_style + ';';
			additionalStyle += 'font-weight: ' + nav_font_weight + ';';

			additionalStyle += '}';

			// NavBar Link Color
			// Default menu style
			additionalStyle += '.noo-menu li > a {color: ' + nav_font_color + ';}';

			// NavBar style
			additionalStyle += '.navbar-nav li > a,.navbar-nav ul.sub-menu li > a {color: ' + nav_font_color + ';}';

			// NavBar Link Hover Color
			// Default menu style
			additionalStyle += '.noo-menu li > a:hover,.noo-menu li > a:active {color: ' + nav_hover_color + ';}';

			// NavBar style
			additionalStyle += '.navbar-nav li > a:hover,.navbar-nav li > a:focus,.navbar-nav li:hover > a,.navbar-nav ul.sub-menu li > a:hover,.navbar-nav ul.sub-menu li > a:focus,.navbar-nav ul.sub-menu li:hover > a,.navbar-nav ul.sub-menu li.sfHover > a,.navbar-nav ul.sub-menu li.current-menu-item > a {color: ' + nav_hover_color + ';}';

			noo_update_live_css(additionalStyle);
		} );
	} );

	// NavBar Font
	wp.customize( 'noo_header_nav_font', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_nav_', '#noo-google-fonts-nav-css' );

			// Update Style
			noo_update_live_css( '.navbar-nav li > a {font-family: "' + newval + '", sans-serif;}' );
		} );
	} );

	// NavBar Font Style
	wp.customize( 'noo_header_nav_font_style', function( value ) {
		value.bind( function( newval ) {
			newval = ( newval == '' ) ? 'normal' : newval;
			// Update Google Font Link
			noo_update_font( 'noo_header_nav_', '#noo-google-fonts-nav-css' );

			// Update Style
			noo_update_live_css( '.navbar-nav li > a {font-style: ' + newval + ';}' );
		} );
	} );

	// NavBar Font Weight
	wp.customize( 'noo_header_nav_font_weight', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_nav_', '#noo-google-fonts-nav-css' );

			// Update Style
			noo_update_live_css( '.navbar-nav > li > a {font-weight: ' + newval + ';}' );
		} );
	} );

	// NavBar Font Subset
	wp.customize( 'noo_header_nav_font_subset', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_nav_', '#noo-google-fonts-nav-css' );
		} );
	} );

	// NavBar Font Size
	wp.customize( 'noo_header_nav_font_size', function( value ) {
		value.bind( function( newval ) {
			additionalStyle = '.navbar-nav li > a {font-size: ' + newval + 'px;}';

			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// NavBar Link Color
	wp.customize( 'noo_header_nav_link_color', function( value ) {
		value.bind( function( newval ) {
			// Default menu style
			additionalStyle = '.noo-menu li > a {color: ' + newval + ';}';

			// NavBar style
			additionalStyle += '.navbar-nav li > a {color: ' + newval + ';}';

			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// NavBar Link Hover Color
	wp.customize( 'noo_header_nav_link_hover_color', function( value ) {
		value.bind( function( newval ) {
			// Default menu style
			additionalStyle = '.noo-menu li > a:hover,.noo-menu li > a:active {color: ' + newval + ';}';

			// NavBar style
			additionalStyle += '.navbar-nav li > a:hover,.navbar-nav li > a:focus,.navbar-nav li:hover > a {color: ' + newval + ';}';
			
			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// NavBar Font Uppercase
	wp.customize( 'noo_header_nav_uppercase', function( value ) {
		value.bind( function( newval ) {

			if( newval ) {
				additionalStyle = '.navbar-nav > li > a {text-transform: uppercase;}';
			} else {
				additionalStyle = '.navbar-nav > li > a {text-transform: none;}';
			}

			// Update Style
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Use Image for Logo
	wp.customize( 'noo_header_use_image_logo', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Blog Name
	wp.customize( 'blogname', function( value ) {
		value.bind( function( newval ) {
			$('.navbar-brand').text( newval );
		} );
	} );

	// Logo Font
	wp.customize( 'noo_header_logo_font', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_logo_', '#noo-google-fonts-logo-css' );

			// Update Style
			noo_update_live_css( '.navbar-brand {font-family: "' + newval + '", sans-serif;}' );
		} );
	} );

	// Logo Font Style
	wp.customize( 'noo_header_logo_font_style', function( value ) {
		value.bind( function( newval ) {
			newval = ( newval == '' ) ? 'normal' : newval;
			// Update Google Font Link
			noo_update_font( 'noo_header_logo_', '#noo-google-fonts-logo-css' );

			// Update Style
			noo_update_live_css( '.navbar-brand {font-style: ' + newval + ';}' );
		} );
	} );

	// Logo Font Weight
	wp.customize( 'noo_header_logo_font_weight', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_logo_', '#noo-google-fonts-logo-css' );

			// Update Style
			noo_update_live_css( '.navbar-brand {font-weight: ' + newval + ';}' );
		} );
	} );

	// Logo Font Subset
	wp.customize( 'noo_header_logo_font_subset', function( value ) {
		value.bind( function( newval ) {
			// Update Google Font Link
			noo_update_font( 'noo_header_logo_', '#noo-google-fonts-logo-css' );
		} );
	} );

	// Logo Font Size
	wp.customize( 'noo_header_logo_font_size', function( value ) {
		value.bind( function( newval ) {
			additionalStyle = '.navbar-brand {font-size: ' + newval + 'px;}';

			noo_update_live_css( additionalStyle );
		} );
	} );

	// Logo Font Color
	wp.customize( 'noo_header_logo_font_color', function( value ) {
		value.bind( function( newval ) {
			if( newval === '' ) {
				return;
			}
			noo_update_live_css( '.navbar-brand {color: ' + newval + ';}' );
		} );
	} );

	// Logo Font Uppercase
	wp.customize( 'noo_header_logo_uppercase', function( value ) {
		value.bind( function( newval ) {
			if( newval ) {
				additionalStyle = '.navbar-brand {text-transform: uppercase;}';
			} else {
				additionalStyle = '.navbar-brand {text-transform: none;}';
			}
			noo_update_live_css( additionalStyle );
		} );
	} );

	// Logo Image
	wp.customize( 'noo_header_logo_image', function( value ) {
		value.bind( function( newval ) {
			if( newval === '' ) {
				$('.navbar-brand .noo-logo-img.noo-logo-normal').remove();
				return;
			}

			noo_get_attachment_url_ajax( newval, function ( data ) {
				// Image Logo
				$('.navbar-brand .noo-logo-img.noo-logo-normal').remove();
				$('.navbar-brand').append('<img class="noo-logo-img noo-logo-normal" src="' + data + '">');
			} );
		} );
	} );

	wp.customize( 'noo_header_logo_mobile_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Logo Retina Image
	wp.customize( 'noo_header_logo_retina_image', function( value ) {
		value.bind( function( newval ) {
			if( newval === '' ) {
				$('.navbar-brand .noo-logo-retina-img.noo-logo-normal').remove();
				return;
			}

			noo_get_attachment_url_ajax( newval, function ( data ) {
				// Retina Logo
				$('.navbar-brand .noo-logo-retina-img.noo-logo-normal').remove();
				$('.navbar-brand').append('<img class="noo-logo-retina-img noo-logo-normal" src="' + data + '">');
			} );
		} );
	} );

	// Logo Image Height
	wp.customize( 'noo_header_logo_image_height', function( value ) {
		value.bind( function( newval ) {
			additionalStyle  = '.navbar-brand .noo-logo-img, .navbar-brand .noo-logo-retina-img {';
			additionalStyle += 'height: ' + newval + 'px;}';

			noo_update_live_css(additionalStyle);
		} );
	} );

	// // Side Nav - Link Alignment
	// wp.customize( 'noo_header_side_nav_alignment', function( value ) {
	// 	value.bind( function( newval ) {
	// 		$('.navbar').removeClass(function (index, css) {
	// 			return (css.match (/(^|\s)align-\S+/g) || []).join(' ');
	// 		}).addClass( 'align-' + newval );
	// 	} );
	// } );

	// // Side Nav Width
	// wp.customize( 'noo_header_side_nav_width', function( value ) {
	// 	value.bind( function( newval ) {
	// 		additionalStyle  = '@media (min-width: 992px) {'; // start @media

	// 		additionalStyle += '.navbar-fixed-left, .navbar-fixed-right {width: ' + newval + 'px;}';
	// 		additionalStyle += 'body.navbar-fixed-left-layout {padding-left: ' + newval + 'px;}';
	// 		additionalStyle += 'body.navbar-fixed-right-layout {padding-right: ' + newval + 'px;}';
			
	// 		additionalStyle += '}'; // end @media

	// 		noo_update_live_css(additionalStyle);
	// 		$(document).trigger('noo-layout-changed');
	// 	} );
	// } );

	// // Side Nav - Link Height
	// wp.customize( 'noo_header_side_nav_link_height', function( value ) {
	// 	value.bind( function( newval ) {
	// 		additionalStyle = '@media (min-width: 992px) {.navbar-fixed-left .navbar-nav > li > a,.navbar-fixed-right .navbar-nav > li > a{line-height:' + newval + 'px;}}';
	// 		noo_update_live_css(additionalStyle);
	// 	} );
	// } );

	// NavBar Height
	wp.customize( 'noo_header_nav_height', function( value ) {
		value.bind( function( newval ) {
			additionalStyle = '.navbar {min-height: ' + newval + 'px;} .navbar:not(.navbar-shrink) .navbar-brand { height: ' + newval + 'px;line-height: ' + newval + 'px;}';

			additionalStyle += '@media (min-width: 992px) {'; // start @media
			
			// Line-Height
			additionalStyle += '.navbar:not(.navbar-shrink) .navbar-nav > li.menu-item > a { height: ' + newval + 'px;line-height: ' + newval + 'px;}';

			additionalStyle += '}'; // end @media

			// Toggle Height
			additionalStyle += '.navbar-toggle, .mobile-minicart-icon {height: ' + newval + 'px;}';

			noo_update_live_css(additionalStyle);
		} );
	} );

	// NavBar Link Spacing (px)
	wp.customize( 'noo_header_nav_link_spacing', function( value ) {
		value.bind( function( newval ) {
			additionalStyle = '@media (min-width: 992px) {'; // start @media
			
			// Padding-Left, Padding Right
			additionalStyle += '.navbar-nav > li.menu-item > a {padding-left: ' + newval + 'px; padding-right: ' + newval + 'px;}';

			additionalStyle += '}'; // end @media

			noo_update_live_css(additionalStyle);
		} );
	} );

	// Mobile Button Size
	wp.customize( 'noo_header_nav_toggle_size', function( value ) {
		value.bind( function( newval ) {

			additionalStyle = '.navbar-toggle, .mobile-minicart-icon {';
			
			additionalStyle += 'font-size: ' + newval + 'px;';
			
			additionalStyle += '}';

			noo_update_live_css( additionalStyle );
		} );
	} );


	// Enable Top Bar
	wp.customize( 'noo_header_top_bar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Top Bar Content
	wp.customize( 'noo_top_bar_content', function( value ) {
		value.bind( function( newval ) {
			$topbarInner = $('.noo-topbar .topbar-left');
			if( ! $topbarInner.length ) {
				noo_refresh_preview();
				return;
			}

			$topbarInner.html( newval );
		} );
	} );

	// Top Bar Search
	wp.customize( 'noo_top_bar_search', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	//
	// Footer
	//

	// Footer Columns (Widgetized)
	wp.customize( 'noo_footer_widgets', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Show Footer Menu
	wp.customize( 'noo_bottom_bar_menu', function( value ) {
		value.bind( function( newval ) {
			$footer = $('footer.colophon.site-info').length > 0  ?
						$('footer.colophon.site-info') :
						$('<footer/>').addClass('colophon site-info').attr( 'role', 'contentinfo')
							.append($('<div/>').addClass('.container-full'))
							.appendTo('body > .site');
			$footer = $footer.find('.container-full');

			$footerMenu = $footer.find('.footer-menu').length > 0 ? $footer.find('.footer-menu') : $('<div/>').addClass('footer-menu').prependTo($footer);
			
			if( newval ) {
				$footerMenu.show();
				if( $footerMenu.find('.noo-menu').length === 0 ) {
					// Ajax Get Menu by location
					var footermenu = noo_get_menu( 'footer-menu' );
					footermenu.done( function ( data ) {
						$footerMenu.find('.noo-menu').remove();
						$footerMenu.append( data );
						$footerMenu.find('.noo-menu').addClass('list-center');
					} );
				}
			} else {
				$footerMenu.hide();
			}
		} );
	} );

	// Show Footer Social Icons
	wp.customize( 'noo_bottom_bar_social', function( value ) {
		value.bind( function( newval ) {
			$footer = $('footer.colophon.site-info').length > 0  ?
						$('footer.colophon.site-info') :
						$('<footer/>').addClass('colophon site-info').attr( 'role', 'contentinfo')
							.append($('<div/>').addClass('.container-full'))
							.appendTo('body > .site');
			$footer = $footer.find('.container-full');

			if( $footer.find('.footer-more').length === 0 ) {
				$footer.append(
					$('<div/>').addClass('footer-more')
						.append($('<div/>').addClass('container-boxed')
							.append($('<div/>').addClass('row')
								.append($('<div/>').addClass('col-md-6'), $('<div/>').addClass('col-md-6')))));
			}

			$footerMore     = $footer.find('.footer-more');
			$footerMoreCol2 = $footer.find('.footer-more .row .col-md-6:last-child');
			
			if( newval ) {
				$footerMore.show();
				if( $footerMoreCol2.find('.noo-social.social-icons').length === 0 ) {
					// Ajax Get social
					var socialIcons = noo_get_social();
					socialIcons.done( function (data) {
						$footerMoreCol2.find('.noo-social.social-icons').remove();
						$footerMoreCol2.append( data );
					} );
				} else {
					$footerMoreCol2.find('.noo-social.social-icons').show();
				}
			} else {
				$footerMoreCol2.find('.noo-social.social-icons').hide();
				if( wp.customize.value('noo_bottom_bar_content')() === '' ) {
					$footerMore.hide();
				}
			}
		} );
	} );

	// Footer Image
	wp.customize( 'noo_bottom_bar_logo_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	// Bottom Bar Content
	wp.customize( 'noo_bottom_bar_content', function( value ) {
		value.bind( function( newval ) {
			$footer = $('footer.colophon.site-info').length > 0  ?
						$('footer.colophon.site-info') :
						$('<footer/>').addClass('colophon site-info').attr( 'role', 'contentinfo')
							.append($('<div/>').addClass('.container-full'))
							.appendTo('body > .site');
			$footer = $footer.find('.container-full');

			if( $footer.find('.footer-more').length === 0 ) {
				$footer.append(
					$('<div/>').addClass('footer-more')
						.append($('<div/>').addClass('container-boxed')
							.append($('<div/>').addClass('row')
								.append($('<div/>').addClass('col-md-6'), $('<div/>').addClass('col-md-6')))));
			}

			$footerMore     = $footer.find('.footer-more');
			$footerMoreCol1 = $footer.find('.footer-more .row .col-md-6:first-child');

			$footerContent = $footerMoreCol1.find('.noo-bottom-bar-content').length ? $footerMoreCol1.find('.noo-bottom-bar-content') : $('<div class="noo-bottom-bar-content"></div>').attachTo( $footerMoreCol1 );
			$footerContent.html( newval );
			if( newval === '' ) {
				$footerMore.hide();
			} else {
				$footerMore.show();
			}

		} );
	} );

	//
	// Blog
	// 

	// Blog Layout
	wp.customize( 'noo_blog_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	// Blog Sidebar
	wp.customize( 'noo_blog_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	wp.customize( 'noo_blog_heading_title', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	wp.customize( 'noo_blog_heading_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );
	
	// Blog Style
	wp.customize( 'noo_blog_style', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	// Show Post Meta
	wp.customize( 'noo_blog_show_post_meta', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	// Show Post Tag
	wp.customize( 'noo_blog_show_post_tag', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	// Excerpt Length
	wp.customize( 'noo_blog_excerpt_length', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_blog();
		} );
	} );

	// Archive Page
	// 

	// Archive Layout
	wp.customize( 'noo_blog_archive_layout', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_archive === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'archive' );
			}
		} );
	} );

	// Archive Sidebar
	wp.customize( 'noo_blog_archive_sidebar', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_archive === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'archive' );
			}
		} );
	} );

	// Archive Style
	wp.customize( 'noo_blog_archive_style', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_archive === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'archive' );
			}
		} );
	} );

	// Single Post
	//

	// Post Layout
	wp.customize( 'noo_blog_post_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Post Sidebar
	wp.customize( 'noo_blog_post_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Show Post Meta
	wp.customize( 'noo_blog_post_show_post_meta', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Show Post Tags
	wp.customize( 'noo_blog_post_show_post_tag', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Show Author Bio
	wp.customize( 'noo_blog_post_author_bio', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Enable Social Sharing
	wp.customize( 'noo_blog_social', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Sharing Title
	wp.customize( 'noo_blog_social_title', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Facebook Share
	wp.customize( 'noo_blog_social_facebook', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Twitter Share
	wp.customize( 'noo_blog_social_twitter', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Google+ Share
	wp.customize( 'noo_blog_social_google', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// Pinterest Share
	wp.customize( 'noo_blog_social_pinterest', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );

	// LinkedIn Share
	wp.customize( 'noo_blog_social_linkedin', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_post();
		} );
	} );


	//
	// Jobs
	//

	// Jobs List Layout
	wp.customize( 'noo_jobs_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	// Jobs Sidebar
	wp.customize( 'noo_jobs_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_featured', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_featured_num', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_company_logo', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_company_name', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_job_type', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_job_category', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_job_location', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_job_date', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_jobs_show_job_closing', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_job_heading_title', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	wp.customize( 'noo_job_heading_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_jobs();
		} );
	} );

	// Single Jobs Layout
	wp.customize( 'noo_single_jobs_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Single Jobs Sidebar
	wp.customize( 'noo_single_jobs_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Single company info
	wp.customize( 'noo_company_info_in_jobs', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Related Jobs
	wp.customize( 'noo_job_related', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	wp.customize( 'noo_job_related_view_more', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Related Jobs Number
	wp.customize( 'noo_job_related_num', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Social share
	wp.customize( 'noo_job_social', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Facebook Share
	wp.customize( 'noo_job_social_facebook', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Twitter Share
	wp.customize( 'noo_job_social_twitter', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Google+ Share
	wp.customize( 'noo_job_social_google', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Pinterest Share
	wp.customize( 'noo_job_social_pinterest', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// LinkedIn Share
	wp.customize( 'noo_job_social_linkedin', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Email Share
	wp.customize( 'noo_job_social_email', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_job();
		} );
	} );

	// Companies

	// Companies List Layout
	wp.customize( 'noo_companies_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_companies();
		} );
	} );

	// Companies Sidebar
	wp.customize( 'noo_companies_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_companies();
		} );
	} );

	wp.customize( 'noo_companies_heading_title', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_companies();
		} );
	} );

	wp.customize( 'noo_companies_heading_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_companies();
		} );
	} );

	//
	// Resumes
	//

	// Resumes List Layout
	wp.customize( 'noo_resumes_layout', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_resumes();
		} );
	} );

	// Resumes List Sidebar
	wp.customize( 'noo_resumes_sidebar', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_resumes();
		} );
	} );

	wp.customize( 'noo_resume_heading_title', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_resumes();
		} );
	} );
	wp.customize( 'noo_resume_heading_image', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_resumes();
		} );
	} );
	wp.customize( 'noo_resume_list_fields', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview_resumes();
		} );
	} );

	//
	// WooCommerce
	//

	// Shop Page
	//

	// Shop Layout
	wp.customize( 'noo_shop_layout', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Shop Sidebar
	wp.customize( 'noo_shop_sidebar', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Number of Product per Page
	wp.customize( 'noo_shop_num', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Show Shop Headline
	wp.customize( 'noo_shop_heading', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Headline Background Image
	wp.customize( 'noo_shop_heading_image', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Headline Title
	wp.customize( 'noo_shop_heading_title', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Headline Sub-Title
	wp.customize( 'noo_shop_heading_sub_title', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_shop === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'shop' );
			}
		} );
	} );

	// Single Product
	//

	// Product Layout
	wp.customize( 'noo_woocommerce_product_layout', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_product === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'product' );
			}
		} );
	} );

	// Product Sidebar
	wp.customize( 'noo_woocommerce_product_sidebar', function( value ) {
		value.bind( function( newval ) {
			if( nooL10n.is_product === "true" ) {
				noo_refresh_preview();
			} else {
				noo_redirect_preview( 'product' );
			}
		} );
	} );

	//
	// Social Media
	//

	// Facebook Profile URL
	wp.customize( 'noo_social_facebook', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_facebook', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_twitter', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_google', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_pinterest', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_linkedin', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_rss', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_youtube', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	wp.customize( 'noo_social_instagram', function( value ) {
		value.bind( function( newval ) {
			noo_refresh_preview();
		} );
	} );

	//
	// Custom Code
	//

	wp.customize( 'noo_custom_javascript', function( value ) {
		value.bind( function( newval ) {
			// showCannotPreviewMsg();
		} );
	} );

	wp.customize( 'noo_custom_css', function( value ) {
		value.bind( function( newval ) {
			// showCannotPreviewMsg();
		} );
	} );

} ) ( jQuery );