jQuery(document).ready(function($) {
	// === << Hide main
		$('.hide_main').click(function(event) {
			$('#noo_main_select').toggle('fast');
		});
	// === << Check select setting
		// === << Change post
			$( "#import_post" ).change(function() {
			  	var $input = $( this );
			  	if ( $input.prop( "checked" ) ) {
			  		$('.install-demo').data('import-post', 'true');
			  	} else {
			  		$('.install-demo').data('import-post', 'false');
			  	}
			}).change();


		// === << Change comment
			$( "#import_comment" ).change(function() {
			  	var $input = $( this );
			  	if ( $input.prop( "checked" ) ) {
			  		$('.install-demo').data('import-comment', 'true');
			  	} else {
			  		$('.install-demo').data('import-comment', 'false');
			  	}
			}).change();


		// === << Change nav
			$( "#import_nav" ).change(function() {
			  	var $input = $( this );
			  	if ( $input.prop( "checked" ) ) {
			  		$('.install-demo').data('import-nav', 'true');
			  	} else {
			  		$('.install-demo').data('import-nav', 'false');
			  	}
			}).change();
	
	// --- Check select
		$('#noo_tools').on('click', '.item_tools', function(event) {
			event.preventDefault();
			
			var show = $(this).data('show');
			$('.'+ show).toggle('fast');

		});

	// --- Hover image
		// --- hiden default
			$('#button-1').hide();
		
		// --- Event hover
			$('.noo_hide').on('mouseover', '.item', function(event) {
				event.preventDefault();
				var demo = $(this).data('demo');

				$(this).find('.button-install').show().css({
					background: '#000',
					padding: '5px 10px',
					color: '#fff',
					cursor: 'pointer'
				});

				$(this).find('img').css({
					opacity: '0.7',
					filter: 'alpha(opacity=70)'
				});

				$(this).find('#img-' + demo).css('background', 'rgb(25, 255, 255, 0.8)');
			});

			$('.noo_hide').on('mouseout', '.item', function(event) {
				event.preventDefault();
				$(this).find('.button-install').hide();
				$(this).find('img').css({
					opacity: '1',
					filter: 'alpha(opacity=100)'
				});
			});

	// --- Event click install demo
		$('.theme').on('click', '.install-demo', function(event) {
			event.preventDefault();
            var $parent = $(this).parent().parent();
			var answer = confirm (nooSetupDemo.notice);
			if (answer) {
                $parent.find('.noo-load-ajax').addClass('noo-load-show');
			    var name = $(this).data('name');
			    var import_post 	= $(this).data('import-post');
			    var import_nav		= $(this).data('import-nav');
			    var import_comment  = $(this).data('import-comment');
			    var data = {
			    	action : 'process_data',
			    	security : nooSetupDemo.ajax_nonce,
			    	name : name,
			    	import_post : import_post,
			    	import_nav : import_nav,
			    	import_comment : import_comment
			    };
			    $.post(nooSetupDemo.ajax_url, data, function(response) {
                    $parent.find('.noo-load-ajax').removeClass('noo-load-show');
			    	$('#process_import').html( response );
			    });
			}
			else {
			    
			}
		});

});
