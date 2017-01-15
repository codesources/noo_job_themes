jQuery(document).ready(function($) {
	$('.correct-setting').on('click', function(event) {
		event.preventDefault();
		var title = $(this).data('title');
		var content = $(this).data('content');
		var page_template = $(this).data('page-template');
		var setting_group = $(this).data('setting-group');
		var setting_key = $(this).data('setting-key');
		$this = $(this);
		var data = {
			action : 'noo_setup',
			title : title,
			content : content,
			page_template : page_template,
			setting_group : setting_group,
			setting_key : setting_key
		}
		$.post( nooSetup.ajax_url, data, function( result ) {
			result = $.parseJSON(result);
			$this.closest('tr').find('mark.error').html( result.id + ' - /' + result.slug + '/' ).removeClass('error').addClass('yes'); // -- event anh em $(this)
			$this.closest('.button').hide();
		});
	});
	$('.help_tip').tooltip();
});