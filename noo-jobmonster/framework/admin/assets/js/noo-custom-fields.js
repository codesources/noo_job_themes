
function toggle_disable_custom_field(el) {
	$this = jQuery(el);
	var disabledEl = $this.siblings('input[type="hidden"]');
	var parentEl = $this.closest('tr');
	if( disabledEl.val() == 'yes' ) {
		disabledEl.val('no');
		$this.prop('value', nooCustomFieldL10n.disable_text);
		parentEl.find('input[type="text"], input[type="checkbox"], input[type="radio"], select, textarea').removeAttr( "readonly" );
		parentEl.removeClass('noo-disable-field');
	} else {
		disabledEl.val('yes');
		$this.prop('value', nooCustomFieldL10n.enable_text);
		parentEl.find('input[type="text"], input[type="checkbox"], input[type="radio"], select, textarea').attr( "readonly", "readonly" );
		parentEl.addClass('noo-disable-field');
	}
	
}

function delete_custom_field(el){
	jQuery(el).closest('tr').remove();
	return false;
}

jQuery( document ).ready( function ( $ ) {
	// Clone Education, Experience and Skill
	$(".noo-clone-fields").on("click", function() {
		var $this = $(this);
		var $template = $( $this.data('template') );
		$template.find(".noo-remove-fields").on("click", remove );
		$this.parents('.noo-addable-fields').find('tbody').append( $template );
	});

	function remove() {
		$(this).parents('tr').remove();
	}

	$(".noo-remove-fields").on("click", remove);

	// Custom field for resume
	$(".noo_custom_field_table").sortable({
		'items': 'tbody tr',
		'axis': 'y',
		placeholder: "noo-state-highlight"
	});
	
	$('#add_custom_field').click(function(){
		var table = $('.noo_custom_field_table'),
			n = 0,
			num = table.data('num'),
			field_name = table.data('field_name');
		
		n = num + 1;
		var tmpl = nooCustomFieldL10n.custom_field_tmpl.replace( /__i__|%i%/g, n );
		tmpl = tmpl.replace( /__name__/g, field_name );
		table.append(tmpl);
		table.data('num',n);
		
		$(".noo_custom_field_table").sortable({
			'items': 'tbody tr',
			'axis': 'y',
			 placeholder: "noo-state-highlight"
		});
	});

	$('.help_tip').tooltip({
	    content: function() {
	        return $(this).attr('title');
	    }
	});
} );

