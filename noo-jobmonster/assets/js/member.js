;(function($){
	"use strict";
	$(document).ready(function(){
		function nooIsMobile() {
			var isMobile = false;

			if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
				|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) isMobile = true;

			return isMobile;
		}

		function showLoginModal( message ) {
			message = message || "";

			hideRegisterModal();
			$('.memberModalLogin').modal('show');
			if( message !== "" ) {
				$('.memberModalLogin').find('.noo-ajax-result').show().html( '<span class="success-response">' + message + '</span>' );
			} else {
				$('.memberModalLogin').find('.noo-ajax-result').html("").hide();
			}
		}

		function hideLoginModal() {
			$('.memberModalLogin').modal('hide');
			$('.memberModalLogin').find('.noo-ajax-result').html("").hide();
		}

		function showRegisterModal( message ) {
			message = message || "";

			hideLoginModal();
			$('.memberModalRegister').modal('show');
			if( message !== "" ) {
				$('.memberModalRegister').find('.noo-ajax-result').show().html( '<span class="success-response">' + message + '</span>' );
			} else {
				$('.memberModalRegister').find('.noo-ajax-result').html("").hide();
			}
		}

		function hideRegisterModal() {
			$('.memberModalRegister').modal('hide');
			$('.memberModalRegister').find('.noo-ajax-result').html("").hide();
		}
		
		$(document).on('click','.add-new-location-btn',function(e){
			e.stopPropagation();
			e.preventDefault();
			$('.add-new-location-content').toggle();

			return false;
		});
		
		$(document).on('click','.add-new-location-submit',function(e){
			e.stopPropagation();
			e.preventDefault();
			var _this = $(this);
			var _location =  _this.closest('.add-new-location-content').find('input').val();
			if($.trim(_location) !=''){
				_this.prop('disabled', 'disabled').prepend('<i class="fa fa-spinner fa-spin"></i>');
				$.post(nooMemberL10n.ajax_url,{
					action: 'add_new_job_location',
					location: _location,
					security: nooMemberL10n.ajax_security
				},function(res){
					if(res.success == true){
						_this.closest('.add-new-location-content').find('input').val('');
						var option = $('<option>');
						var chosenLocation = _this.closest('.form-group').find('.form-control-chosen');
						var value = _this.data( 'return-type' ) === 'id' ? res.location_id : res.location_slug;
						option.text(res.location_title).val(value);
						option.prop('selected', true).attr('selected', 'selected');
						if( !chosenLocation.is("[multiple]") ) {
							chosenLocation.find("option:selected").attr("selected",false);
						}
						chosenLocation.append(option);
						chosenLocation.trigger('chosen:updated'); 
					}
					_this.prop('disabled', '').find('i').remove();
				},'json');
			}
			return false;
		});
		
		
		$(document).on('click','.member-register-link',function(e){
			if(!nooIsMobile()){
				e.stopPropagation();
				e.preventDefault();

				var message = $(this).attr("data-register-message") ? $(this).data( "register-message") : "";
				showRegisterModal( message );

				return false;
			}
		});

		$(document).on('click','.member-login-link',function(e){
			if(!nooIsMobile()) {
				e.stopPropagation();
				e.preventDefault();
				
				var message = $(this).attr("data-login-message") ? $(this).data( "login-message") : "";
				showLoginModal( message );

				return false;
			}
		});
		
		$('form.noo-ajax-login-form').on('submit', function (e) {
			e.stopPropagation();
			e.preventDefault();
			var _this = $(this);
			_this.find('.noo-ajax-result').show().html(nooMemberL10n.loadingmessage);
			$.ajax({
                type: 'POST',
                dataType: 'json',
                url: nooMemberL10n.ajax_url,
                data: {
                    action: 'noo_ajax_login',
                    log: _this.find('.log').val(),
                    pwd: _this.find('.pwd').val(),
                    remember: (_this.find('.rememberme').is(':checked') ? true : false),
                    security: _this.find('.security').val(),
                    redirect_to: _this.find('.redirect_to').length ? _this.find('.redirect_to').val() : ''
                },
                success: function (data) {
                	if(typeof data =='object' && data != null) {
                		_this.find('.noo-ajax-result').show().html(data.message);
                		if (data.loggedin == true) {
                			if( $('body').hasClass( 'interim-login' ) ) {
                				$('body').addClass( 'interim-login-success' );
                				$('.wp-auth-check-close', window.parent.document).click();
                			} else {
	                			if (data.redirecturl == null) {
	                				document.location.reload();
	                			}
	                			else {
	                				document.location.href = data.redirecturl;
	                			}
                			}
                		}
					} else {
						document.location.reload();
					}
                },
                complete: function () {

                },
                error: function () {
                	_this.off('submit');
                	_this.submit();
                }
			});

			return false;
		});
		
		$('.job-manage-action.action-delete').click(function(){
			return confirm(nooMemberL10n.confirm_delete);
		});
		$('form.noo-ajax-register-form').on('submit', function (e) {
			e.stopPropagation();
			e.preventDefault();
			var _this = $(this);
			if(_this.find(".account_reg_term").length > 0 && !_this.find(".account_reg_term").is(':checked')){
				_this.find('.noo-ajax-result').hide();
				alert(nooMemberL10n.confirm_not_agree_term);
				return false;
			}else{
				_this.find('.noo-ajax-result').show().html(nooMemberL10n.loadingmessage);
				var formData = _this.serializeArray();
				$.ajax({
	                type: 'POST',
	                dataType: 'json',
	                url: nooMemberL10n.ajax_url,
	                data: formData,
	                success: function (data) {
	                	// console.log(data);
		                if(typeof data =='object' && data != null) {
	                		_this.find('.noo-ajax-result').show().html(data.message);
                			if (typeof data.redirecturl !== "undefined" && data.redirecturl != null && data.redirecturl != '') {
                				document.location.href = data.redirecturl;
                			// } else {
                			// 	document.location.reload();
                			} else if( typeof grecaptcha !== "undefined" ) {
                				grecaptcha.reset();
                			}
						} else {
							document.location.reload();
						}
	                },
	                complete: function (data) {
						// console.log(data);
	                },
	                error: function (data) {
	                	_this.off('submit');
	                	_this.submit();
	                }
				});
			}

			return false;
		});
		
		// Init validate
		$('form#candidate_profile_form').validate({
			onkeyup: false,
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

		// Init validate
		$("form#noo-ajax-update-password, form#noo-ajax-update-email").each( function( index ) {
			$(this).validate({
				onkeyup: false,
				errorClass: "jform-error",
				validClass: "jform-valid",
				errorElement: "span",
				ignore: ":hidden:not(.ignore-valid)",
				errorPlacement: function(error, element) {
					if ( element.is( ":radio" ) || element.is( ":checkbox" ) || element.is( ":file" ) )
						error.appendTo( element.parent().parent() );
					else
						error.appendTo( element.parent());
				},
				submitHandler: function(form) {
					var _form = $(form);
					_form.find(".noo-ajax-result").show().html(nooMemberL10n.loadingmessage);
					$.ajax({
		                type: "POST",
		                dataType: "json",
		                url: nooMemberL10n.ajax_url,
		                data: _form.serializeArray(),
		                success: function (data) {
		                	_form.find(".noo-ajax-result").show().html(data.message);
		                    if (data.success == true) {
		                        if (data.redirecturl == null) {
		                            document.location.reload();
		                        }
		                        else {
		                            document.location.href = data.redirecturl;
		                        }
		                    }
		                },
		                complete: function () {

		                },
		                error: function () {
		                	_form.off("submit");
		                	_form.submit();
		                }
					});
				}
			});

			$(this).on("submit", function (e) {
				e.stopPropagation();
				e.preventDefault();
				$(this).validate();
			});
		});

		
		// Init validate
		$('form#post_resume_form').validate({
			onkeyup: false,
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
		
		// Init validate
		$('form#add_job_alert_form').validate({
			onkeyup: false,
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

		$(".noo-clone-fields").on("click", function() {
			var $this = $(this);
			var $template = $( $this.data('template') );
			$this.closest('.noo-metabox-addable').find('.noo-addable-fields').append( $template );
			$template.find('.form-control-editor').wysihtml5({
				"font-styles": true,
				"blockquote": true,
				"emphasis": true,
				"lists": true,
				"html": true,
				"link": true,
				"image": true,
				"stylesheets": [wysihtml5L10n.stylesheet_rtl]
			});
			$template.find('input:first-child').focus();
			return false;
		});

		$(".noo-remove-fields").on("click", function(){
			var $lastFields = $(this).closest('.noo-metabox-addable').find('.noo-addable-fields .fields-group:last');
			if( $lastFields.length ) $lastFields.remove();
			return false;
		});
		
		$(document).on('click', '.bulk-manage-application-action', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $this = $(this);
			var $form = $this.closest('form');
			var $action = $this.closest(".bulk-actions").find("select[name='action']").val();

			if( $form.find("input[name='ids[]']:checked").length && $action !== "-1" ) {
				if( $action == 'delete' ) {
					$form.submit();

					return false;
				}
				var $application_id = $form.find("input[name='ids[]']:checked").map(function() {return this.value;}).get().join(',');

				$form.find('.member-manage-table').block({ message: null,overlayCSS:  { 
			        backgroundColor: '#fff', 
			        opacity:         0.5, 
			        cursor:          'wait' 
			    }});

			    $.post(nooMemberL10n.ajax_url,{
					action: 'noo_approve_reject_application_modal',
					application_id: $application_id,
					hander:  $action,
					security: nooMemberL10n.ajax_security
				},function(respon){
					$form.find('.member-manage-table').unblock();
					if(respon){
						var $modal = $(respon);
						$('body').append($modal);
						$modal.modal('show');
						$('form#noo-ajax-approve-reject-application-form').validate({
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
						$modal.on('hidden.bs.modal',function(){
							$modal.remove();
						});
					}
				});
			}

			return false;
		});
		
		$(document).on('click','.member-manage-action.approve-reject-action',function(e){
			e.preventDefault();
			e.stopPropagation();
			var $this = $(this);
			$this.closest('.member-manage-table').block({ message: null,overlayCSS:  { 
		        backgroundColor: '#fff', 
		        opacity:         0.5, 
		        cursor:          'wait' 
		    }}); 
			$.post(nooMemberL10n.ajax_url,{
				action: 'noo_approve_reject_application_modal',
				application_id: $this.data('application-id'),
				hander:  $this.data('hander'),
				security: nooMemberL10n.ajax_security
			},function(respon){
				$this.closest('.member-manage-table').unblock();
				if(respon){
					var $modal = $(respon);
					$('body').append($modal);
					$modal.modal('show');
					$('form#noo-ajax-approve-reject-application-form').validate({
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
					$modal.on('hidden.bs.modal',function(){
						$modal.remove();
					});
				}
			});

			return false;
		});

		$("a.bookmark-job").on("click", function(){
			var $this = $(this);
			$this.find('.fa').addClass('fa-spin');
			var bookmarked = $this.hasClass('bookmarked');
			$.ajax({
                type: 'POST',
                dataType: 'json',
                url: nooMemberL10n.ajax_url,
                data: {
                	action: $this.attr('data-action'),
                	security: $this.attr('data-security'),
                	job_id: $this.attr('data-job-id')
                },
                success: function (data) {
                	$('.noo-ajax-result').show().html(data.message);
                    if (data.success == true) {
						$this.find('.fa').removeClass('fa-spin');
                    	if( bookmarked )
                    		$this.removeClass('bookmarked');
                    	else
						$this.addClass('bookmarked');
                    	$this.closest('.job-action').find('.noo-ajax-result').show().html(data.message);
                    	$this.find('.noo-bookmark-label').html(data.message_text);
                        if (data.redirecturl == null) {
                            // document.location.reload();
                        }
                        else {
                            document.location.href = data.redirecturl;
                        }
                    }
                },
                complete: function () {

                },
                error: function () {
                }
			});

			return false;
		});
		
		$(document).on('click','.member-manage-action.view-employer-message',function(e){
			e.preventDefault();
			e.stopPropagation();
			var $this = $(this);
			$this.closest('.member-manage-table').block({ message: null,overlayCSS:  { 
		        backgroundColor: '#fff', 
		        opacity:         0.5, 
		        cursor:          'wait' 
		    }}); 
			$.post(nooMemberL10n.ajax_url,{
				action: 'noo_employer_message_application_modal',
				application_id: $this.data('application-id'),
				security: nooMemberL10n.ajax_security,
				mode: $this.data('mode') || 0
			},function(respon){
				$this.closest('.member-manage-table').unblock();
				if(respon){
					var $modal = $(respon);
					$('body').append($modal);
					$modal.modal('show');
					$modal.on('hidden.bs.modal',function(){
						$modal.remove();
					});
				}
			});
		});
		$('.job-preview').find('[type="submit"]').addClass('disabled');
		// -- Check for submit jobs
		$(document).on('click','.job-preview',function(event){
			var $check = $('input[name=agreement]:checked');
			if ( $check.length == 1 )
				$(this).find('[type="submit"]').removeClass('disabled');
			else 
				$(this).find('[type="submit"]').addClass('disabled');
		});

		// -- create order free package
		$(document).on('click', '.auto_create_order_free', function(event) {
			event.preventDefault();
			var user_id = $(this).data('id');
			var package_id = $(this).data('package');
			var security = $(this).data('security');
			var url_package = $(this).data('url-package');
			$.post(nooMemberL10n.ajax_url, {
   				action: 'auto_create_order',
    			user_id: user_id,
    			package_id: package_id,
    			security: security
   			}, function(data) {
   				window.location.href = url_package;
   			});
		});

		$("#contact_company_form").validate({
			onkeyup: false,
			errorClass: "jform-error",
			validClass: "jform-valid",
			errorElement: "span",
			ignore: ":hidden:not(.ignore-valid)",
			errorPlacement: function(error, element) {
				if ( element.is( ":radio" ) || element.is( ":checkbox" ) || element.is( ":file" ) )
					error.appendTo( element.parent().parent() );
				else
					error.appendTo( element.parent());
			},
			submitHandler: function(form) {
				var _form = $(form);
				_form.find(".noo-ajax-result").show().html(nooMemberL10n.loadingmessage);
				var formData = _form.serializeArray();
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: nooMemberL10n.ajax_url,
					data: formData,
					success: function (data) {
						if(typeof data =='object' && data != null) {
							_form.find('.noo-ajax-result').show().html(data.message);
							if(data.success == true){
								_form.find('input, textarea').val('');
							}
						}
					},
					complete: function () {

					},
					error: function () {
						_form.off('submit');
						_form.submit();
					}
				});
			}
		});

	});
})(jQuery);
