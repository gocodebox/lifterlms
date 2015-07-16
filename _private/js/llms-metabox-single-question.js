jQuery(document).ready(function($) {

$('#add_new_option').click(function() {
	single_course_template();
	return false;
});

$('#publish').click(function() {
	update_question_options();
});

delete_option();
single_option_sortable();

	
});

 /**
 * Generate single choice question template
 */
single_course_template = function () {
	var order = (jQuery("#llms-single-options tr").length);

	jQuery('<tr class="list_item" data-order="' + order + '" style="display: table-row;"> \
				<td>
					<i class="fa fa-bars llms-fa-move-lesson"></i> \
					<i data-code="f153" class="dashicons dashicons-dismiss deleteBtn single-option-delete"></i> \
					<input type="radio" name="correct_option" value="' + order + '">
					<label>Correct Answer</label> \
					<textarea name="option_text[]" class="option-text"></textarea>
					<br>
					<label>Description</label>
					<textarea name="option_description[]" class="option-text"> </textarea>
				</td>
			</tr>
				').appendTo('#llms-single-options .dad-list tbody').hide().fadeIn(300);
	
	delete_option();
};

delete_option = function() {
	jQuery('.deleteBtn').click(function() {
	    var contentPanelId = jQuery(this).attr("class");
	    jQuery(this).parent().parent().remove();
	    order_single_options();
	});
}

/**
 * Sortable function
 */
single_option_sortable = function() {

    jQuery('.dad-list').sortable({
    	items		: '.list_item',
    	axis 		: 'y',
    	placeholder : "placeholder",
    	cursor		: "move",
    	forcePlaceholderSize:true,
    	helper 		: function(e, tr) {
		    var jQueryoriginals = tr.children();
		    var jQueryhelper = tr.clone();
		    jQueryhelper.children().each(function(index)
		    {
		      jQuery(this).width(jQueryoriginals.eq(index).width())
		    });
		    return jQueryhelper;
		},
        start 		: function(event, ui) {
			var start_pos = ui.item.index();
			ui.item.data('start_pos', start_pos);
			var radio_checked= {};
 
            var radio_checked= {};
            jQuery('input[type="radio"]', this).each(function(){
                if(jQuery(this).is(':checked'))
                    radio_checked[jQuery(this).attr('name')] = jQuery(this).val();
                jQuery(document).data('radio_checked', radio_checked);
            });
		},
        update		: function(event, ui) {
            var start_pos = ui.item.data('start_pos');
            var end_pos = jQuery(ui.item).index();
            jQuery(ui.item).attr("data-order", end_pos);
            
        } 
    }).bind('sortstop', function (event, ui) {
        var radio_restore = jQuery(document).data('radio_checked');
        jQuery.each(radio_restore, function(index, value){
        	jQuery('input[name="'+index+'"][value="'+value+'"]').prop('checked', true);
        });
        order_single_options();
    });
}

order_single_options = function() {
	jQuery("#llms-single-options tr").each( function(index) {
		jQuery(this).attr("data-order", index);
		var option = jQuery(this).find('input[type="radio"]').val(index);
	});
}
