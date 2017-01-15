;(function($){
	"use strict";
	$(document).ready(function(){
		if($('[data-paginate="loadmore"]').find('.loadmore-action').length){
			$('[data-paginate="loadmore"]').each(function(){
				var $this = $(this);
				$this.nooLoadmore({
					navSelector  : $this.find('div.pagination'),            
			   	    nextSelector : $this.find('div.pagination a.next'),
			   	    itemSelector : 'article.loadmore-item',
			   	    finishedMsg  : nooJobL10n.ajax_finishedMsg
				});
			});
		}
		if($('[data-paginate="nextajax"]').length){
			$('[data-paginate="nextajax"]').each(function(){
				var _this 			= $(this),
				 	_pagination 	= _this.find('div.pagination');
				
				_pagination.find('.next').on('click',function(e){
					e.stopPropagation();
					e.preventDefault();
					var _pagination_data = _pagination.data(),
						_max_page 		= _pagination_data.max_page,
						_current_page 	= _pagination_data.current_page,
						_scroll 	    = _pagination_data.scroll;
					 
					if($(this).hasClass('disabled')){
						return;
					}
					_current_page++;
					
					if(_current_page >= _max_page){
						$(this).addClass('disabled');
					}
					if(_current_page > 1){
						_pagination.find('.prev').removeClass('disabled');
					}
					_this.addClass('is-waiting');
					_pagination_data.action = 'noo_nextajax';
					_pagination_data.page = _current_page;

					$.post(nooJobL10n.ajax_url,
						_pagination_data,
						function(res){
							_pagination.data('current_page',_current_page);
							if(res){
								_this.find('.nextajax-wrap').html(res);
							}
							if(_this.hasClass('is-waiting')){
		            			_this.removeClass('is-waiting');
		            		}
		            		// ===== Scroll Top
			            		$('html, body').animate({
							        scrollTop: $("#" + _scroll).offset().top
							    }, 2000);
							// ===== Scroll Top
					});
				});
				_pagination.find('.prev').on('click',function(e){
					e.stopPropagation();
					e.preventDefault();
					var _pagination_data = _pagination.data(),
						_max_page 		 = _pagination_data.max_page,
						_current_page 	 = _pagination_data.current_page,
						_scroll 	     = _pagination_data.scroll;
					 
					if($(this).hasClass('disabled')){
						return;
					}
					_current_page--;
					
					if(_current_page <= 1){
						$(this).addClass('disabled');
					}
					if(_current_page <= _max_page){
						_pagination.find('.next').removeClass('disabled');
					}
					_pagination_data.action = 'noo_nextajax';
					_pagination_data.page = _current_page;
					$.post(nooJobL10n.ajax_url,
						_pagination_data,
						function(res){
							_pagination.data('current_page',_current_page);
							if(res){
								_this.find('.nextajax-wrap').html(res);
							}
							if(_this.hasClass('is-waiting')){
		            			_this.removeClass('is-waiting');
		            		}
		            		// ===== Scroll Top
			            		$('html, body').animate({
							        scrollTop: $("#" + _scroll).offset().top
							    }, 2000);
							// ===== Scroll Top
						});
				});
			});
		}
		if($('[data-paginate="resume_nextajax"]').length){
			$('[data-paginate="resume_nextajax"]').each(function(){
				var _this 			= $(this),
				 	_pagination 	= _this.find('div.pagination'),
				 	_max_page 		= _pagination.data('max-page'),
				 	_posts_per_page	= _pagination.data('posts-per-page'),
				 	_show			= _pagination.data('show'),
				 	_display_style  = _pagination.data('style');
					_pagination.find('.next').on('click',function(e){
					 e.stopPropagation();
					 e.preventDefault();
					 var _current_page 	= _pagination.data('current-page');
					 
					if($(this).hasClass('disabled')){
						return;
					}
					_current_page++;
					
					if(_current_page >= _max_page){
						$(this).addClass('disabled');
					}
					if(_current_page > 1){
						_pagination.find('.prev').removeClass('disabled');
					}
					_this.addClass('is-waiting');
					$.post(nooJobL10n.ajax_url,
					{
						action			:'noo_resume_nextajax',
						page  			: _current_page,
						posts_per_page	:_posts_per_page,
						show			: _show,
						display_style	: _display_style
					},function(res){
						_pagination.data('current-page',_current_page);
						if(res){
							_this.find('.resume_nextajax-wrap').html(res);
						}
						if(_this.hasClass('is-waiting')){
	            			_this.removeClass('is-waiting');
	            		}
						
					})
				});
				_pagination.find('.prev').on('click',function(e){
					e.stopPropagation();
					e.preventDefault();
					var _current_page 	= _pagination.data('current-page');
					 
					if($(this).hasClass('disabled')){
						return;
					}
					_current_page--;
					
					if(_current_page <= 1){
						$(this).addClass('disabled');
					}
					if(_current_page <= _max_page){
						_pagination.find('.next').removeClass('disabled');
					}
					$.post(nooJobL10n.ajax_url,
					{
						action			: 'noo_resume_nextajax',
						page  			: _current_page,
						posts_per_page	: _posts_per_page,
						show			: _show,
						display_style	: _display_style
					},function(res){
						_pagination.data('current-page',_current_page);
						if(res){
							_this.find('.resume_nextajax-wrap').html(res);
						}
						if(_this.hasClass('is-waiting')){
	            			_this.removeClass('is-waiting');
	            		}
					})
				});
			});
		}
		$('.form-control-file').find('input[type=file]').bind('change',function(){
			$(this).closest('label').find('.form-control').prop('value',$(this).val());
		});

		// -- event job slider
		
		// if ( typeof max != 'undefined' ) {
		// 	for( var sl = 2; sl <= max; sl++ ) {
		// 		$('.list_slider_' + sl).hide();
		// 	}
		// }
		$('.slider_post').css('display', 'none');
        $('.list_slider_1').css('display', 'block');
		var current = 1;
		$('.slider .next').click(function(e){
			e.stopPropagation();
			e.preventDefault();
			var max = $(this).closest('.slider').find('.total-slider').data('total-slider');
			$(this).closest('.slider').find('.list_slider_' + current).animate({opacity: 0}, 200 );
        	$(this).closest('.slider').find('.list_slider_' + current).css('display','none');
        	current = current + 1;
        	if(current > max){
        		current = 1;
        	}
        	$(this).closest('.slider').find('.list_slider_' + current).css('display','block');
        	$(this).closest('.slider').find('.list_slider_' + current).animate({opacity: 1.0}, 800 ) ;
        	// $(this).closest('.slider').find('.posts-loop-title').html( max) ;
		});

		$('.slider .prev').click(function(e){
			e.stopPropagation();
			e.preventDefault();
			var max = $(this).closest('.slider').find('.total-slider').data('total-slider');
			$(this).closest('.slider').find('.list_slider_' + current).animate({opacity: 0}, 200 );
        	$(this).closest('.slider').find('.list_slider_' + current).css('display','none');
        	current = current - 1;
        	if(current == 0){
        		current = max;
        	}
        	$(this).closest('.slider').find('.list_slider_' + current).css('display','block');
        	$(this).closest('.slider').find('.list_slider_' + current).animate({opacity: 1.0}, 800 ) ;
        	// $(this).closest('.slider').find('.posts-loop-title').html(current + max) ;
		});
        
		
		// check all checkboxes
		var checks,checked,first,last,sliced,
			lastClicked = false;
		$('.member-manage-table tbody').children().children('.check-column').find(':checkbox').click( function(e) {
			if ( 'undefined' == e.shiftKey ) { return true; }
			if ( e.shiftKey ) {
				if ( !lastClicked ) { return true; }
				checks = $( lastClicked ).closest( 'form' ).find( ':checkbox' );
				first = checks.index( lastClicked );
				last = checks.index( this );
				checked = $(this).prop('checked');
				if ( 0 < first && 0 < last && first != last ) {
					sliced = ( last > first ) ? checks.slice( first, last ) : checks.slice( last, first );
					sliced.prop( 'checked', function() {
						if ( $(this).closest('tr').is(':visible') )
							return checked;

						return false;
					});
				}
			}
			lastClicked = this;

			// toggle "check all" checkboxes
			var unchecked = $(this).closest('tbody').find(':checkbox').filter(':visible').not(':checked');
		
			$(this).closest('table').children('thead, tfoot').find(':checkbox').prop('checked', function() {
				return ( 0 === unchecked.length );
			});

			return true;
		});
		
		$('.member-manage-table thead, .member-manage-table  tfoot').find('.check-column :checkbox').on( 'click.noo-toggle-checkboxes', function( event ) {
			var $this = $(this),
				$table = $this.closest( 'table' ),
				controlChecked = $this.prop('checked'),
				toggle = event.shiftKey || $this.data('wp-toggle');

			$table.children( 'tbody' ).filter(':visible')
				.children().children('.check-column').find(':checkbox')
				.prop('checked', function() {
					if ( $(this).is(':hidden') ) {
						return false;
					}

					if ( toggle ) {
						return ! $(this).prop( 'checked' );
					} else if ( controlChecked ) {
						return true;
					}

					return false;
				});

			$table.children('thead,  tfoot').filter(':visible')
				.children().children('.check-column').find(':checkbox')
				.prop('checked', function() {
					if ( toggle ) {
						return false;
					} else if ( controlChecked ) {
						return true;
					}

					return false;
				});
		});

		
		$('.form-control-editor').wysihtml5({
			"font-styles": true,
			"blockquote": true,
			"emphasis": true,
			"lists": true,
			"align": false,
			"html": true,
			"link": true,
			"image": true,
			"stylesheets": [wysihtml5L10n.stylesheet_rtl]
		});
		
		var date_format = nooJobL10n.date_format ? nooJobL10n.date_format : 'Y/m/d';
		$('.jform-datepicker').datetimepicker({
			format:date_format,
			timepicker: false,
			scrollMonth: false,
			scrollTime: false,
			scrollInput: false,
			step:15,
			validateOnBlur: false,
			onChangeDateTime:function(dp,$input){
				if( $input.next('.jform-datepicker_value').length ) {
					$input.next('.jform-datepicker_value').val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());
				}
			}
		});

		$('#closing.jform-datepicker').datetimepicker({
			format:date_format,
			timepicker: false,
			scrollMonth: false,
			scrollTime: false,
			scrollInput: false,
			step:15,
			minDate:0,
			validateOnBlur: false,
			onChangeDateTime:function(dp,$input){
				if( dp && $input.next('.jform-datepicker_value').length ) {
					$input.next('.jform-datepicker_value').val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());
				}
			}
		});

		jQuery('.jform-datepicker_start').datetimepicker({
			format:date_format,
			timepicker: false,
			scrollMonth: false,
			scrollTime: false,
			scrollInput: false,
			step:15,
			validateOnBlur: false,
			onShow:function( ct, $input ){
				var $maxDate = $input.siblings('.jform-datepicker_end_value').val() ? $input.siblings('.jform-datepicker_end_value').val() : false;
				if( $maxDate ) {
					$maxDate = Date.parseDate( $maxDate, 'unixtime' );
					if( $maxDate ) {
						this.setOptions({
							maxDate:$maxDate.format0()
						});
					}
				} else {
					this.setOptions({
						maxDate:false
					});
				}
			},
			onChangeDateTime:function(dp,$input){
				if( dp && $input.next('.jform-datepicker_start_value').length ) {
					$input.next('.jform-datepicker_start_value').val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());
				}
			}
		});
		jQuery('.jform-datepicker_end').datetimepicker({
			format:date_format,
			timepicker: false,
			scrollMonth: false,
			scrollTime: false,
			scrollInput: false,
			step:15,
			validateOnBlur: false,
			onShow:function( ct, $input ){
				var $minDate = $input.siblings('.jform-datepicker_start_value').val() ? $input.siblings('.jform-datepicker_start_value').val() : false;
				if( $minDate ) {
					$minDate = Date.parseDate( $minDate, 'unixtime' );
					if( $minDate ) {
						this.setOptions({
							minDate:$minDate.format0()
						});
					}
				} else {
					this.setOptions({
						minDate:false
					});
				}
			},
			onChangeDateTime:function(dp,$input){
				if( dp && $input.next('.jform-datepicker_end_value').length ) {
					$input.next('.jform-datepicker_end_value').val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());
				}
			}
		});

		$('.jform-datepicker, .jform-datepicker_start, .jform-datepicker_end').change( function() {
			var $this = $(this);
			if( $this.val() == '' ) {
				$this.next('input[type="hidden"]').val('');
			}
		});
		
		$('.job-package').find('button[data-package]').each(function(){
			var _this = $(this);
			_this.click(function(e){
				_this.closest('.job-package').find('input#package').val(_this.data('package'));
				_this.closest('form').submit();
			});
		});
		//settup validate
		$.extend($.validator.messages, {
			required: nooJobL10n.validate_messages.required,
			remote: nooJobL10n.validate_messages.remote,
			email: nooJobL10n.validate_messages.email,
			url: nooJobL10n.validate_messages.url,
			date: nooJobL10n.validate_messages.date,
			dateISO: nooJobL10n.validate_messages.dateISO,
			number: nooJobL10n.validate_messages.number,
			digits: nooJobL10n.validate_messages.digits,
			creditcard: nooJobL10n.validate_messages.creditcard,
			equalTo: nooJobL10n.validate_messages.equalTo,
			maxlength: $.validator.format(nooJobL10n.validate_messages.maxlength),
			minlength: $.validator.format(nooJobL10n.validate_messages.minlength),
			rangelength: $.validator.format(nooJobL10n.validate_messages.rangelength),
			range: $.validator.format(nooJobL10n.validate_messages.range),
			max: $.validator.format(nooJobL10n.validate_messages.max),
			min: $.validator.format(nooJobL10n.validate_messages.min)
		});
		$.validator.addMethod("uploadimage", function(value, element, param) {
			param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
			return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
		}, nooJobL10n.validate_messages.uploadimage);
		
		$.validator.addMethod("uploadcv", function(value, element, param) {
			param = typeof param === "string" ? param.replace(/,/g, "|") : "pdf|doc|docx";
			return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
		}, nooJobL10n.validate_messages.extension);
		
		$.validator.addClassRules({
			'jform-validate':{
				required : true
			},
			'jform-validate-email':{
				email: true
			},
			'jform-chosen-validate':{
				required : true
			},
			'jform-validate-uploadimage':{
				uploadimage:"png|jpe?g|gif"
			},
			'jform-validate-uploadcv':{
				uploadcv: ( nooJobL10n.file_exts !== undefined ? nooJobL10n.file_exts : "pdf|doc|docx" )
			}
		});
		var post_job_form = $('#post_job_form');
		post_job_form.validate({
			onkeyup: false,
			onfocusout: false,
			onclick: false,
			errorClass: "jform-error",
			validClass: "jform-valid",
			errorElement: "span",
			ignore: ":hidden:not(.ignore-valid)",
			errorPlacement: function(error, element) {
				if ( element.is( ':radio' ) || element.is( ':checkbox' ) || element.is( ':file' ) )
					error.appendTo( element.parent().parent() );
				else
					error.appendTo( element.parent());
			},
			rules:{
				recaptcha_response_field:{
					required: true,
					recaptcha: true
				}
			}
		});
		$('form.jform-validate').each(function(){
			$(this).validate({
				onkeyup: false,
				onfocusout: false,
				onclick: false,
				errorClass: "jform-error",
				validClass: "jform-valid",
				errorElement: "span",
				ignore: ":hidden:not(.ignore-valid)",
				errorPlacement: function(error, element) {
					if ( element.is( ':radio' ) || element.is( ':checkbox' ) || element.is( ':file' ) )
						error.appendTo( element.parent().parent() );
					else
						error.appendTo( element.parent());
				}
			});
		});
	});
})(jQuery);