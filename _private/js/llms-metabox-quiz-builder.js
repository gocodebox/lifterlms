jQuery(document).ready(function($) {

	$('#add_new_question').click(function() {
		get_questions();
		return false;
	});

	$('#publish').click(function() {
		update_question_options();
	});

	$('.llms-points').blur(function () {
    	llms_total_points();
	});

	delete_option();
	single_option_sortable();
	order_single_options();
	llms_total_points();

	//only display quiz results options if show results field is checked
	if ( $( '#_llms_show_results').attr('checked') ) {
		$( '#_llms_show_correct_answer' ).parent().parent().show();
		$( '#_llms_show_options_description_right_answer ').parent().parent().show();
		$( '#_llms_show_options_description_wrong_answer').parent().parent().show();
	}

	$( '#_llms_show_results').on('change', function() {
		if( $( '#_llms_show_results').attr('checked')) {
			$( '#_llms_show_correct_answer' ).parent().parent().fadeIn(300);
			$( '#_llms_show_options_description_right_answer ').parent().parent().fadeIn(300);
			$( '#_llms_show_options_description_wrong_answer').parent().parent().fadeIn(300);
		} else {
			$( '#_llms_show_correct_answer' ).parent().parent().fadeOut(300);
			$( '#_llms_show_options_description_right_answer ').parent().parent().fadeOut(300);
			$( '#_llms_show_options_description_wrong_answer').parent().parent().fadeOut(300);
		}
	});
});

llms_total_points = function() {
	var sum = 0;
    jQuery('.llms-points').each(function() {
        sum += Number(jQuery(this).val());
        jQuery('#llms_points_total').text(sum);
	});
}

 /**
 * Generate single choice question template
 */
single_question_template = function (response) {
	var order = (jQuery("#llms-single-options tr").length);
	var questions = response;

	jQuery('<tr class="list_item" id="question_' + order + '" data-order="' + order + '" style="display: table-row;"><td class="llms-table-select"> \
	<select id="question_select_' + order + '" name="_llms_question[]" data-placeholder="Choose a Section" class="chosen-select question-select"></select> \
	</td> \
	<td class="llms-table-points"><input type="text" class="llms-points" name="_llms_points[]" id="llms_points[]" value=""/> \
	</td><td class="llms-table-options"> \
	<i class="fa fa-bars llms-fa-move"></i><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn single-option-delete"></i> \
	</td></tr>').appendTo('#llms-single-options .question-list tbody').hide().fadeIn(300);

	
	jQuery('.llms-points').blur(function () {
    var sum = 0;
    jQuery('.llms-points').each(function() {
        sum += Number(jQuery(this).val());
        jQuery('#llms_total_points').val(sum);
	    });
	});


	for (var key in questions ) {

	    if (questions.hasOwnProperty(key)) {
    		var select = jQuery('#question_' + order + ' #question_select_' + order);
    		var option = '<option value="' + questions[key]['ID'] + '">' +  questions[key]['post_title'] + '</option>'
    		jQuery(select).append(option);
    		//jQuery(select).prepend('<option value="" selected disabled>Please select a lesson...</option>');
	    }
	}
	jQuery(select).prepend('<option value="" selected >None</option>');
	jQuery('.question-select').chosen();
	jQuery('#question_' + order  + ' .question-select').change(function() { get_edit_link(jQuery(this)); });
	
	delete_option();
	order_single_options();
	jQuery('.llms-points').blur(function () {
    	llms_total_points();
	});
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

    jQuery('.question-list').sortable({
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
		},
        update		: function(event, ui) {
            var start_pos = ui.item.data('start_pos');
            var end_pos = jQuery(ui.item).index();
            jQuery(ui.item).attr("data-order", end_pos);
            order_single_options();

            
        } 
    });
}

order_single_options = function() {
	jQuery("#llms-single-options tr").each( function(index) {
		jQuery(this).attr("data-order", index);
		jQuery(this).attr("id", 'question_' + index);
		jQuery(this).find('.question-select').each( function() {
			jQuery(this).attr("id", 'question_select_' + index);
		});
	});
}

/**
 * Returns array of all questions
 */
get_questions = function() {
	var ajax = new Ajax('post', {'action':'get_questions'}, true);
	ajax.get_questions();
}

get_edit_link = function(element) {
	console.log('get edit link called');
	console.log(element);
	var question_id = jQuery(element).val();
	var element_id = jQuery(element).attr('id');
	console.log('element_id');
	console.log(element_id);

	console.log(question_id);
	var ajax = new Ajax('post', {'action':'get_question', 'question_id' : question_id}, true);
	ajax.get_question(question_id, element_id);
}
add_edit_link = function(post, question_id, row_id) {
	console.log('add_edit_link called');
	console.log(question_id);
	console.log(post);
	console.log(row_id);

	var edit_link = document.createElement('a');
	edit_link.setAttribute('href', encodeURI(post.edit_url));

	var edit_icon = document.createElement('i');
	edit_icon.setAttribute('class', 'fa fa-pencil-square-o llms-fa-edit ');

	edit_link.appendChild(edit_icon);
	jQuery('#' + row_id).parent().parent().find('.llms-fa-edit').hide();
	jQuery('#' + row_id).parent().next().next().append(edit_link);

	
}
