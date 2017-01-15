/*
 * jQuery JSON Plugin
 * version: 1.0 (2008-04-17)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris technically wrote this plugin, but it is based somewhat
 * on the JSON.org website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.  I really just cleaned it up.
 *
 * It is also based heavily on MochiKit's serializeJSON, which is
 * copywrited 2005 by Bob Ippolito.
 */
;(function($){function toIntegersAtLease(n){return n<10?"0"+n:n}Date.prototype.toJSON=function(date){return this.getUTCFullYear()+"-"+toIntegersAtLease(this.getUTCMonth())+"-"+toIntegersAtLease(this.getUTCDate())};var escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var meta={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"};$.quoteString=function(string){return'"'+string.replace(escapeable,function(a){var c=meta[a];if(typeof c==="string"){return c}c=a.charCodeAt();return"\\u00"+Math.floor(c/16).toString(16)+(c%16).toString(16)})+'"';return'"'+string+'"'};$.toJSON=function(o,compact){var type=typeof(o);if(type=="undefined"){return"undefined"}else{if(type=="number"||type=="boolean"){return o+""}else{if(o===null){return"null"}}}if(type=="string"){var str=$.quoteString(o);return str}if(type=="object"&&typeof o.toJSON=="function"){return o.toJSON(compact)}if(type!="function"&&typeof(o.length)=="number"){var ret=[];for(var i=0;i<o.length;i++){ret.push($.toJSON(o[i],compact))}if(compact){return"["+ret.join(",")+"]"}else{return"["+ret.join(", ")+"]"}}if(type=="function"){throw new TypeError("Unable to convert object of type 'function' to json.")}var ret=[];for(var k in o){var name;type=typeof(k);if(type=="number"){name='"'+k+'"'}else{if(type=="string"){name=$.quoteString(k)}else{continue}}var val=$.toJSON(o[k],compact);if(typeof(val)!="string"){continue}if(compact){ret.push(name+":"+val)}else{ret.push(name+": "+val)}}return"{"+ret.join(", ")+"}"};$.compactJSON=function(o){return $.toJSON(o,true)};$.evalJSON=function(src){return eval("("+src+")")};$.secureEvalJSON=function(src){var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,"@");filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]");filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered)){return eval("("+src+")")}else{throw new SyntaxError("Error parsing JSON, source is not valid.")}}})(jQuery);

jQuery(document).ready(function ($) {
	"use strict";
	var NooPlupload = {
		init:function () {
			var self = this;
			$('.noo-plupload').each(function() {
				var _this = $(this);
				// _this.find('.noo-plupload-value').each(function(){
				//   $(this).val('');
				// });
				_this.on('click','a.delete-pluploaded',function(e) {
					e.preventDefault();
					if (confirm(nooPluploadL10n.confirmMsg)) {
						var el = $(this),
						data = {
							'filename':el.data('filename'),
							'nonce':nooPluploadL10n.remove,
							'action':'noo_plupload_delete_file'
						};
						var fileIndex = el.parent().index();
						$.post(nooPluploadL10n.ajaxurl, data, function (response) {
							el.parent().remove();
							var filed_values = _this.find('.noo-plupload-value');
							var files = filed_values.val();
							files = $.secureEvalJSON(files);
							files.splice(fileIndex, 1);
							filed_values.val($.toJSON(files));
						});
					}
				});
				if (typeof(plupload) === 'undefined') {
					return;
				}
				_this.find('.noo-plupload-btn').each(function() {
					var uploader = new plupload.Uploader($(this).data('settings'));
					uploader.init();
					uploader.bind('FilesAdded', function (up, files) {
						$.each(files, function (i, file) {
							_this.find('.noo-plupload-preview').html(
								'<div id="' + file.id + '">' +
								file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
								'</div>');
						});
						up.refresh(); // Reposition Flash/Silverlight
						uploader.start();
					});
					uploader.bind('UploadProgress', function (up, file) {
						$('#' + file.id + " b").html(file.percent + "%");
					});
					// On erro occur
					uploader.bind('Error', function (up, err) {
						_this.find('.noo-plupload-preview').html("<div>Error: " + err.code +
							", Message: " + err.message +
							(err.file ? ", File: " + err.file.name : "") +
							"</div>"
							);

						up.refresh(); // Reposition Flash/Silverlight
					});
					uploader.bind('FileUploaded', function (up, file, result) {
						var response = $.secureEvalJSON(result.response);
						if(response.status == "error"){
							_this.find('.noo-plupload-preview').prepend(response.error.message);
							return;
						}
						var html = '<strong>' + response.data.filename + '</strong>';
						html = "<a class='delete-pluploaded' data-filename='" + response.data.filename + "' href='#' title='Delete File'><i class='fa fa-trash-o' style='color:#f00'></i></a> " + html;

						$('#' + file.id ).html(html);

						if(file.percent == 100){
							if(response.status && response.status == 'ok'){
								var filed_values = _this.find('.noo-plupload-value');
								var files = filed_values.val();
								files = '' === files ? [] : $.parseJSON(files);
								files.push(response.data.filename);
								filed_values.val($.toJSON(files));
							}
						}
					});
				});
				_this.on('click','.noo-plupload-btn .plupload-btn',function(e){
					uploader.start();
					e.preventDefault();
				});
			});
		}
	};
	NooPlupload.init();
});