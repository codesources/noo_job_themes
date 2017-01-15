/**
 * NOO Site Script.
 *
 * Javascript used in NOO-Framework
 * This file contains base script used on the frontend of NOO theme.
 *
 * @package    NOO Framework
 * @subpackage NOO Site
 * @version    1.0.0
 * @author     Kan Nguyen <khanhnq@nootheme.com>
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       http://nootheme.com
 */
// =============================================================================

;
( function( $ ) {
	"use strict";
	$.fn.nooLoadmore = function( options, callback ) {
		var defaults = {
			contentSelector: null,
			contentWrapper: null,
			nextSelector: "div.navigation a:first",
			navSelector: "div.navigation",
			itemSelector: "div.post",
			dataType: 'html',
			finishedMsg: "<em>Congratulations, you've reached the end of the internet.</em>",
			loading: {
				speed: 'fast',
				start: undefined
			},
			state: {
				isDuringAjax: false,
				isInvalidPage: false,
				isDestroyed: false,
				isDone: false, // For when it goes all the way through the archive.
				isPaused: false,
				isBeyondMaxPage: false,
				currPage: 1
			}
		};
		var options = $.extend( defaults, options );

		return this.each( function() {
			var self = this;
			var $this = $( this ),
				wrapper = $this.find( '.loadmore-wrap' ),
				action = $this.find( '.loadmore-action' ),
				btn = action.find( ".btn-loadmore" ),
				loading = action.find( '.loadmore-loading' );

			options.contentWrapper = options.contentWrapper || wrapper;



			var _determinepath = function( path ) {
				if ( path.match( /^(.*?)\b2\b(.*?$)/ ) ) {
					path = path.match( /^(.*?)\b2\b(.*?$)/ ).slice( 1 );
				} else if ( path.match( /^(.*?)2(.*?$)/ ) ) {
					if ( path.match( /^(.*?page=)2(\/.*|$)/ ) ) {
						path = path.match( /^(.*?page=)2(\/.*|$)/ ).slice( 1 );
						return path;
					}
					path = path.match( /^(.*?)2(.*?$)/ ).slice( 1 );

				} else {
					if ( path.match( /^(.*?page=)1(\/.*|$)/ ) ) {
						path = path.match( /^(.*?page=)1(\/.*|$)/ ).slice( 1 );
						return path;
					} else {
						options.state.isInvalidPage = true;
					}
				}
				return path;
			}
			if ( !$( options.nextSelector ).length ) {
				return;
			}


			// callback loading
			options.callback = function( data, url ) {
				if ( callback ) {
					callback.call( $( options.contentSelector )[ 0 ], data, options, url );
				}
			};

			options.loading.start = options.loading.start || function() {
				btn.hide();
				$( options.navSelector ).hide();
				loading.show( options.loading.speed, $.proxy( function() {
					loadAjax( options );
				}, self ) );
			};

			var loadAjax = function( options ) {
				var path = $( options.nextSelector ).attr( 'href' );
				path = _determinepath( path );

				var callback = options.callback,
					desturl, frag, box, children, data;

				options.state.currPage++;
				// Manually control maximum page
				if ( options.maxPage !== undefined && options.state.currPage > options.maxPage ) {
					options.state.isBeyondMaxPage = true;
					return;
				}
				desturl = path.join( options.state.currPage );
				box = $( '<div/>' );
				box.load( desturl + ' ' + options.itemSelector, undefined, function( responseText ) {
					children = box.children();
					if ( children.length === 0 ) {
						//loading.hide();
						btn.hide();
						action.append( '<div style="margin-top:5px;">' + options.finishedMsg + '</div>' ).animate( {
							opacity: 1
						}, 2000, function() {
							action.fadeOut( options.loading.speed );
						} );
						return;
					}
					frag = document.createDocumentFragment();
					while ( box[ 0 ].firstChild ) {
						frag.appendChild( box[ 0 ].firstChild );
					}
					$( options.contentWrapper )[ 0 ].appendChild( frag );
					data = children.get();
					loading.hide();
					btn.show( options.loading.speed );
					options.callback( data );

				} );
			}


			btn.on( 'click', function( e ) {
				e.stopPropagation();
				e.preventDefault();
				options.loading.start.call( $( options.contentWrapper )[ 0 ], options );
			} );
		} );
	};

	var nooGetViewport = function() {
		var e = window,
			a = 'inner';
		if ( !( 'innerWidth' in window ) ) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return {
			width: e[ a + 'Width' ],
			height: e[ a + 'Height' ]
		};
	};
	var nooGetURLParameters = function( url ) {
		var result = {};
		var searchIndex = url.indexOf( "?" );
		if ( searchIndex == -1 ) return result;
		var sPageURL = url.substring( searchIndex + 1 );
		var sURLVariables = sPageURL.split( '&' );
		for ( var i = 0; i < sURLVariables.length; i++ ) {
			var sParameterName = sURLVariables[ i ].split( '=' );
			result[ sParameterName[ 0 ] ] = sParameterName[ 1 ];
		}
		return result;
	};
	var nooInit = function() {
		if ( $( '.navbar' ).length ) {
			var $window = $( window );
			var $body = $( 'body' );
			var navTop = $( '.navbar' ).offset().top;
			var lastScrollTop = 0,
				navHeight = 0,
				$navbar = $( '.navbar' ),
				defaultnavHeight = $( '.navbar' ).outerHeight();


			var adminbarHeight = 0;
			if ( $body.hasClass( 'admin-bar' ) ) {
				adminbarHeight = $( '#wpadminbar' ).outerHeight();
			}

			var navbarInit = function() {
				if ( nooGetViewport().width > 992 ) {
					//var $this = $( window );
					if ( $navbar.hasClass( 'fixed-top' ) ) {

						var navFixedClass = 'navbar-fixed-top';
						if ( $navbar.hasClass( 'shrinkable' ) && !$body.hasClass( 'one-page-layout' ) ) {
							navFixedClass += ' navbar-shrink';
						}

						var checkingPoint = navTop + defaultnavHeight;
						if ( ( $window.scrollTop() + adminbarHeight ) > checkingPoint ) {
							if ( $navbar.hasClass( 'navbar-fixed-top' ) ) {
								return;
							}

							if ( !$navbar.hasClass( 'navbar-fixed-top' ) ) {
								navHeight = defaultnavHeight; //$navbar.hasClass( 'shrinkable' ) ? Math.max( Math.round( $( '.navbar-nav' ).outerHeight() - ( $window.scrollTop() + adminbarHeight ) + navTop ), 60 ) : $( '.navbar-nav' ).outerHeight();
								$( '.navbar-wrapper' ).css( {
									'min-height': navHeight + 'px'
								} );
								$navbar.closest( '.noo-header' ).css( {
									'position': 'relative'
								} );
								// $navbar.css( {
								// 	'min-height': navHeight + 'px'
								// } );
								// $navbar.find( '.navbar-nav > li > a' ).css( {
								// 	'line-height': navHeight + 'px'
								// } );
								// $navbar.find( '.navbar-brand' ).css( {
								// 	'height': navHeight + 'px'
								// } );
								// $navbar.find( '.navbar-brand img' ).css( {
								// 	'max-height': navHeight + 'px'
								// } );
								// $navbar.find( '.navbar-brand' ).css( {
								// 	'line-height': navHeight + 'px'
								// } );

								$navbar.addClass( navFixedClass ).css( 'top', 0 - navHeight ).animate( {
									'top': adminbarHeight
								}, 300 );

								return;
							}
						} else {
							if ( !$navbar.hasClass( 'navbar-fixed-top' ) ) {
								return;
							}

							$navbar.removeClass( navFixedClass );
							$navbar.css( {
								'top': ''
							} );

							$( '.navbar-wrapper' ).css( {
								'min-height': 'none'
							} );
							$navbar.closest( '.noo-header' ).css( {
								'position': ''
							} );
							// $navbar.css( {
							// 	'min-height': ''
							// } );
							// $navbar.find( '.navbar-nav > li > a' ).css( {
							// 	'line-height': ''
							// } );
							// $navbar.find( '.navbar-brand' ).css( {
							// 	'height': ''
							// } );
							// $navbar.find( '.navbar-brand img' ).css( {
							// 	'max-height': ''
							// } );
							// $navbar.find( '.navbar-brand' ).css( {
							// 	'line-height': ''
							// } );

						}
					}
				}
			};
			$window.bind( 'scroll', navbarInit ).resize( navbarInit );
			if ( $body.hasClass( 'one-page-layout' ) ) {

				// Scroll link
				$( '.navbar-scrollspy > .nav > li > a[href^="#"]' ).click( function( e ) {
					e.preventDefault();
					var target = $( this ).attr( 'href' ).replace( /.*(?=#[^\s]+$)/, '' );
					if ( target && ( $( target ).length ) ) {
						var position = Math.max( 0, $( target ).offset().top );
						position = Math.max( 0, position - ( adminbarHeight + $( '.navbar' ).outerHeight() ) + 5 );

						$( 'html, body' ).animate( {
							scrollTop: position
						}, {
							duration: 800,
							easing: 'easeInOutCubic',
							complete: window.reflow
						} );
					}
				} );

				// Initialize scrollspy.
				$body.scrollspy( {
					target: '.navbar-scrollspy',
					offset: ( adminbarHeight + $( '.navbar' ).outerHeight() )
				} );

				// Trigger scrollspy when resize.
				$( window ).resize( function() {
					$body.scrollspy( 'refresh' );
				} );

			}

		}

		// Slider scroll bottom button
		$( '.noo-slider-revolution-container .noo-slider-scroll-bottom' ).click( function( e ) {
			e.preventDefault();
			var sliderHeight = $( '.noo-slider-revolution-container' ).outerHeight();
			$( 'html, body' ).animate( {
				scrollTop: sliderHeight
			}, 900, 'easeInOutExpo' );
		} );

		//Init masonry isotope
		$( '.masonry' ).each( function() {
			if( !$().isotope ) {
				return false;
			}
			var self = $( this );
			var $container = $( this ).find( '.masonry-container' );
			var $filter = $( '.company-letters a' );

			$container.isotope( {
				itemSelector: '.masonry-item',
				transitionDuration: '0.8s',
				masonry: {
					'gutter': 30
				}
			} );

			imagesLoaded( self, function() {
				$container.isotope( 'layout' );
			} );

			$filter.click( function( e ) {
				e.stopPropagation();
				e.preventDefault();

				var $this = jQuery( this );
				$filter.removeClass('selected');
				$this.addClass( 'selected' );

				var filterValue = $this.attr('data-filter');

				$container.isotope({
					itemSelector: '.masonry-item',
					transitionDuration: '0.5s',
					masonry: {
						'gutter': 30
					},
					filter: filterValue
				});

			} );
		} );

		// Fix bug masonry inside tabs
		$('a[data-vc-tabs]').on('show.vc.tab shown.bs.tab', function ( e ) {
			var $target = $($( e.target ).attr('href'));
			if( $target.find('.masonry-container').length ) {
				$target.find('.masonry-container' ).each( function() {
					if( $().isotope ) {
						$(this).isotope( {
							itemSelector: '.masonry-item',
							transitionDuration: '0.8s',
							masonry: {
								'gutter': 30
							}
						} );
					}
				});
			}
		});

		//Go to top
		$( window ).scroll( function() {
			if ( $( this ).scrollTop() > 500 ) {
				$( '.go-to-top' ).addClass( 'on' );
			} else {
				$( '.go-to-top' ).removeClass( 'on' );
			}
		} );
		$( 'body' ).on( 'click', '.go-to-top', function() {
			$( "html, body" ).animate( {
				scrollTop: 0
			}, 800 );
			return false;
		} );

		//Search
		$( 'body' ).on( 'click', '.search-button', function() {
			if ( $( '.searchbar' ).hasClass( 'hide' ) ) {
				$( '.searchbar' ).removeClass( 'hide' ).addClass( 'show' );
				$( '.searchbar #s' ).focus();
			}
			return false;
		} );
		$( 'body' ).on( 'mousedown', $.proxy( function( e ) {
			var element = $( e.target );
			if ( !element.is( '.searchbar' ) && element.parents( '.searchbar' ).length === 0 ) {
				$( '.searchbar' ).removeClass( 'show' ).addClass( 'hide' );
			}
		}, this ) );


	};
	$( document ).ready( function() {
		// MailChimp subscribe
		$( ".mc-subscribe-form" ).submit( function( event ) {
			event.preventDefault();

			var $form = $( this );
			var data = $form.serializeArray();
			$form.find( 'label.noo-message' ).remove();
			$.ajax( {
				type: 'POST',
				url: nooL10n.ajax_url,
				data: data,
				success: function( response ) {
					var result = $.parseJSON( response );
					var message = '';
					if ( result.success ) {
						if ( result.data !== '' ) {
							message = '<label class="noo-message error" role="alert">' + result.data + '</label>';
							$form.addClass( 'submited' );
							$form.html( message );
						}
					} else {
						if ( result.data !== '' ) {
							$form.removeClass( 'submited' );
							$( '<label class="noo-message" role="alert">' + result.data + '</label>' ).prependTo( $form );
						}
					}
				},
				error: function( errorThrown ) {}
			} );
		} );
		$( '[data-toggle="tooltip"]' ).tooltip();
		// $( '.form-control-chosen' ).chosen( {
		// 	placeholder_text_multiple: nooL10n.chosen_multiple_text,
		// 	placeholder_text_single: nooL10n.chosen_single_text,
		// 	no_results_text: nooL10n.chosen_no_result_text,
		// 	allow_single_deselect: true
		// } );
		$( '.noo-user-navbar-collapse' ).on( 'show.bs.collapse', function() {
			if ( $( '.noo-navbar-collapse' ).hasClass( 'in' ) ) {
				$( '.noo-navbar-collapse' ).collapse( 'hide' );
			}
		} );
		$( '.noo-navbar-collapse' ).on( 'show.bs.collapse', function() {
			if ( $( '.noo-user-navbar-collapse' ).hasClass( 'in' ) ) {
				$( '.noo-user-navbar-collapse' ).collapse( 'hide' );
			}
		} );
		nooInit();
	} );

	$( document ).bind( 'noo-layout-changed', function() {
		nooInit();
	} );

	/**
	 * Fix menu when active plugin Noo Menu
	 */
		$(window).ready(function() {
			if ( $('.noo-megamenu').length > 0 ) {
				$('.noo-megamenu').find('ul.noo-nav').addClass('sf-menu');
			}
			if ( $('.company-info-content').length > 0 ) {
				$('.company-info-content').readmore({
					speed: 75,
					lessLink: '<a class="btn-readmore" href="#">'+noo_readmore.lessLink+'</a>',
					moreLink: '<a class="btn-readmore" href="#">'+noo_readmore.moreLink+'</a>'
				});
			}
		});

} )( jQuery );