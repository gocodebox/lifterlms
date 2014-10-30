jQuery(document).ready(function($) {
	  // set up chosen dropdown
    var config = {
      '.chosen-select'           : {},
      '.chosen-select-deselect'  : {allow_single_deselect:true},
      '.chosen-select-no-single' : {disable_search_threshold:10},
      '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
      '.chosen-select-width'     : {width:"95%"}
    }

    for (var selector in config) {
      $(selector).chosen(config[selector]);
    }

    // set data element "last_selected" to current selected value on page load
	$("select").each(function () {
    	reverse_selection(this);
	});

	// set data element "last_selected" to current selected value before change
    $('.section-select').click(function() {
    	reverse_selection(this);
    });

    // check for duplicate sections on select change
    $('.section-select').change(function() {
 		catch_duplicates(this);
    });

	// calls ajax update function on lesson change
    $('.lesson-select').change(function() {
    	catch_blanks();
    });

	// creates new section on Add a new Section button click
	$('#addNewSection').click(function(e){
		add_new_section();
		return false;
	});

	// Creates new lesson tr element on Add Lesson button click
	$('.addNewLesson').click(function( event ){
		add_new_lesson(event);
		return false;
	});

	// deletes the parent element and updates syllabus when delete button clicked
	$('.deleteBtn').click(function(){
		delete_this(this)
		return false;
	});

    // deletes the parent element and updates syllabus when delete button clicked
	$('.section-dismiss').click(function(){
		delete_this(this)
		return false;
	})

	// Sets lesson tr elements to sortable on page load
	lessons_sortable();
	load_ajax_animation();

});

/**
 * Sets the currently selected element in the dropdown as the last_selected data element
 */
reverse_selection = function( element ) {
    jQuery(element).data( 'last_selected', jQuery(element).val() );
};

/**
 * Return an alert message when a duplicate section is selected
 */
catch_blanks = function (){
	jQuery('.section-select').each(function () {
		if ( jQuery(this).val() == null || jQuery(this).val() == '' ) {
			alert( 'Unable to save. Please make sure all of your sections are assigned.' );
		}
		else {
			update_syllabus();
		}
	});
}

catch_duplicates = function(element){
	var that = jQuery(this);
	var names = [];
	var new_value = jQuery(element).val();
	var old_value = jQuery(element).data('last_selected');

	jQuery('.section-select').each(function () {

		if (new_value == jQuery(this).val()) {
			names.push(jQuery(this).val())
		}
		//TODO im starting to really need a message class
		if (names.length > 1){
			alert("You cannot select a section already assigned to this course.");
			jQuery(element).val(old_value).attr("selected", true);
     		return false;
		}
		else {
			update_syllabus();
		}
	});
}

/**
 * Click event function to append a new row to the tasks table
 */
lessons_sortable = function() {

    jQuery('.dad-list').sortable({
    	items		: '.list_item',
    	axis 		: 'y',
        start 		: function(event, ui) {
			var start_pos = ui.item.index();
			ui.item.data('start_pos', start_pos);
		},
        update		: function(event, ui) {
            var start_pos = ui.item.data('start_pos');
            var end_pos = jQuery(ui.item).index();

            jQuery(ui.item).attr("data-order", end_pos);
            update_syllabus();
        }
    });
}

/**
 * get current sections from page
 */
get_sections = function() {
    var request_obj = [];
    var i = 0;

    jQuery('#syllabus .section-select').each(function() {
        i++;
        var obj = {};
        var section_id = jQuery(this).val();

        obj['section_id'] = jQuery(this).val();
        obj['position'] = i;

        var order = jQuery(this).parent().parent().attr('id');
        jQuery( '#' + order  ).find( "a" ).attr("data-section_id", section_id);
        jQuery( '#' + order  ).find( "tr" ).attr("data-section_id", section_id);

        obj['lessons'] = parse_lessons (section_id);

        request_obj.push(obj);
    });
    return request_obj;
}

/**
 * Parse through all lessons and build lesson to section arrays.
 */
parse_lessons = function(section_id) {
    var section_id = section_id
    var lessons = [];

    jQuery('.course-section tbody select').each(function() {

        var parent_position = jQuery(this).parent().parent().parent().parent().parent().attr("id");
        var rowId = (jQuery('#' + parent_position  + ' .list_item').length) + 1;

        var lesson_obj = {};

        lesson_obj['lesson_id'] = jQuery(this).val();

        var position = jQuery(this).parent().parent().attr("data-order");

        position = position.substr(position.lastIndexOf("_") + 1);

        lesson_obj['position'] = position;

        if(jQuery(this).parent().parent().attr("data-section_id") == section_id) {
            lessons.push(lesson_obj);
        }

    });
    return lessons;
}

/**
 * Main update function, updates sections and lessons via ajax class.
 */
update_syllabus = function() {
	var request_obj = get_sections();

	    var data = { 'action':'update_syllabus', 'post_id' : jQuery('#syllabus').data("post_id"), 'sections' : request_obj };

	    var ajax = new Ajax('post', data, false);
	    ajax.update_syllabus();
}

/**
 * Returns array of all lessons
 */
get_lessons = function(section_id, section_position) {
	var ajax = new Ajax('post', {'action':'get_lessons'}, true);
	ajax.get_lessons(section_id, section_position);
}

/**
 * Creates new lesson tr element
 */
add_new_lesson = function(event){

	var section_id = jQuery(event.target).attr("data-section_id");


	if ( typeof( jQuery(event.target).attr("data-section") )  === undefined ) {
		 var section_position = 1; //default if no sectionid exists
	}
	else {
		var section_position = jQuery(event.target).attr("data-section");
	}

    get_lessons(section_id, section_position);
}

 /**
 * Generate lesson template
 */
lesson_template = function (lessons, section_id, section_position) {

		var row_id = (jQuery('#' + section_position  + ' .list_item').length) + 1;
		var select_class = 'list_item_' + row_id;
		var row_class = 'row_' + row_id;

		var numRows = jQuery('#' + section_position  + ' .dad-list').length;

		jQuery('<tr class="list_item" id="' + row_class + '" data-section_id="' + section_id + '" data-order="' + row_id + '"> \
			<td><select id="' + select_class + '" data-placeholder="Choose a Section" class="chosen-select lesson-select" \
			></select></td><td><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn"></i></td></tr>')
		.appendTo('#' + section_position + ' .dad-list tbody').hide().fadeIn(300);

		jQuery(select).change(function() { update_syllabus($(select).val()) });
		jQuery('#' + section_position + ' .dad-list tbody ' + '.lesson-select').change(function() { update_syllabus() });


		jQuery('.deleteBtn').click(function() {
		    var contentPanelId = jQuery(this).attr("class");
		    jQuery(this).parent().parent().remove();
	});


	for (var key in lessons) {

	    if (lessons.hasOwnProperty(key)) {
    		var select = jQuery('#' + section_position + ' #' + select_class);
    		var option = '<option value="' + lessons[key]['ID'] + '">' +  lessons[key]['post_title'] + '</option>'
    		jQuery(select).append(option);
	    }
	}
	jQuery(select).prepend('<option value="" selected disabled>Please select a lesson...</option>');
};

/**
 * Deletes the grandparent of the delete button
 */
delete_this = function(thisButton){
	jQuery(thisButton).parent().parent().remove();

	jQuery('.course-section').each( function(index) {
		jQuery( this ).attr('id', index + 1 )
		jQuery( this ).find('label').text('Section ' + (index + 1) + ':');
		jQuery( this ).find('a.add-lesson').attr('data-section', index + 1);

	});
	// var id = $('.course-section').length + 1;
 //        var sectionId = 'section_' + id;

	update_syllabus();
};

(function($){
	/**
	 * Create new section
	 */
	add_new_section = function () {
		var ajax = new Ajax( 'post', { 'action' : 'get_sections' }, false );
		ajax.get_sections();
	}

	/**
	 * Generate section template
	 */
	section_template = function (sections) {
		var response = sections;
	
        var id = $('.course-section').length + 1;
        var sectionId = 'section_' + id;

		// create section wrapper
		var section = document.createElement("div");
		section.setAttribute('class', 'course-section');
		section.setAttribute('id', id);

		// create title
		var	title = document.createElement('p');
		title.setAttribute('class', 'title');
		var label = document.createElement('label');
		label.setAttribute('class', 'order');
		$(label).text('Section ' + id + ':');

		// create select list and add all options
		var select = document.createElement('select');
		select.setAttribute('class', 'chosen-select chosen select section-select');
		select.setAttribute('data-placeholder', 'Select a Section');
		$(select).click(function() { reverse_selection ($(select))});
		$(select).change(function() { catch_duplicates($(select)) });
		$(select).attr("selectedIndex", -1);

		// create delete button
		var removeBtn = document.createElement('i');
		removeBtn.setAttribute('data-code', 'f153');
		removeBtn.setAttribute('class', 'dashicons dashicons-dismiss section-dismiss');
		$(removeBtn).click(function() { delete_this($(select)) });

		// create add lesson button
		var addLessonBtn = document.createElement('a');
		addLessonBtn.setAttribute('class', 'button button-primary add-lesson');

		$('#' + sectionId).data( "section", { ID: id } );
		$(addLessonBtn).attr("data-section", id);
		$(addLessonBtn).text('Add Lesson');
		//$(addLessonBtn).on('click', add_new_lesson());
		$(addLessonBtn).on('click', function(event){ add_new_lesson( event ) } );

		// create table framework
		var table = '<table class="wp-list-table widefat fixed posts dad-list"> \
		<thead><tr><th>Name</th><th></th></tr></thead> \
		<tfoot><tr><th>Name</th><th></th></tr> \
		</tfoot><tbody></tbody></table>';

		// populate select with sections.
		$(select).append('<option value="" selected disabled>Please select a section...</option>');
		for (var key in response) {
		    if (response.hasOwnProperty(key)) {
		    	var option = $('<option />').val(response[key]['ID']).text(response[key]['post_title']);
		    	$(select).append(option);
		    }
		}

		// put components together to build section block
		title.appendChild(label);
		title.appendChild(select);
		title.appendChild(removeBtn);
		section.appendChild(title);
		$(section).append(table);
		$(section).append(addLessonBtn);

    	$("#syllabus").hide().append(section).fadeIn(1000);
    	$(select).trigger("chosen:updated")

    	// grant table sorting functionality
    	lessons_sortable();
    }
})(jQuery);