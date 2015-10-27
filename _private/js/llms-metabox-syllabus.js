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

    //disable selections on page load
	$('.section-select').attr('disabled', 'disbaled');
	$('.lesson-select').attr('disabled', 'disbaled');

    // check for duplicate sections on select change
    $('.section-select').change(function() {
 		//catch_duplicates(this);
 		//$(this).attr('disabled', 'disbaled');
    });

	// calls ajax update function on lesson change
    $('.lesson-select').change(function() {
    	update_syllabus();
    	//$(this).attr('disabled', 'disbaled');
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
	sections_sortable();
	order_lessons();
	

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
	});

	var promise = wait();
	promise.done(update_syllabus);
	var section_position = jQuery(element).parent().parent().attr('id');
	get_associated_lessons(new_value, section_position);
}

function wait() {
	var deferred = jQuery.Deferred();

	setTimeout(function() {
		deferred.resolve();
	}, 2500);

	return deferred.promise();
}

/**
 * Click event function to append a new row to the tasks table
 */
lessons_sortable = function() {

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
		},
        update		: function(event, ui) {
            var start_pos = ui.item.data('start_pos');
            var end_pos = jQuery(ui.item).index();

            jQuery(ui.item).attr("data-order", end_pos);
            order_lessons();
            update_syllabus();
        } 
    });
}

order_lessons = function() {
	
	jQuery('.course-section').each( function(index) {
		var section_position = jQuery(this).attr('id');
    	jQuery(this).find('.list_item').each( function(index) {
    		jQuery(this).attr("data-order", index + 1);
    		jQuery(this).attr("id", 'row_' + section_position + '_' + (index + 1));
    	});
    });
    jQuery('.course-section').each( function(index) {
    	var section_position = jQuery(this).attr('id');
    	jQuery(this).find('.lesson-select').each( function(index) {
    		jQuery(this).attr("data-order", index + 1);
    		jQuery(this).attr("id", 'list_item_' + section_position + '_' + (index + 1));
    	});
    });
	jQuery('.course-section').each( function(index) {
		console.log(index);
		var section_position = jQuery(this).attr('id');
		jQuery(this).find('.section-select').each( function(index) {
			console.log('-------------------------------');
			console.log(section_position)
			jQuery(this).attr("id", 'section_item_' + section_position);
		});
	});
}

/**
 * Click event function to append a new row to the tasks table
 */
sections_sortable = function() {

    jQuery('#syllabus').sortable({
    	items		: '.course-section',
    	axis 		: 'y',
    	placeholder : "sortable-placeholder section-placeholder",
 		cursor		: "move",
        start 		: function(event, ui) {
			var start_pos = ui.item.index();
			ui.item.data('start_pos', start_pos);
		},
        update		: function(event, ui) {
            var start_pos = ui.item.data('start_pos');
            var end_pos = jQuery(ui.item).index();

            jQuery(ui.item).attr("data-order", end_pos);
            jQuery('.course-section').each( function(index) {
				jQuery( this ).attr('id', index + 1 )
				jQuery( this ).find('label').text('Section ' + (index + 1) + ':');
				jQuery( this ).find('a.add-lesson').attr('data-section', index + 1);

			});
			order_lessons();
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
	console.log('update syllabus called');
	var request_obj = get_sections();

	    var data = { 'action':'update_syllabus', 'post_id' : jQuery('#syllabus').data("post_id"), 'sections' : request_obj };

	    var ajax = new Ajax('post', data, false);
	    ajax.update_syllabus();
}

/**
 * Find lessons associated with selected section
 */
get_associated_lessons = function(section_id, section_position) {
	console.log('get_associated_lessons called');
	var ajax = new Ajax('post', {'action':'get_associated_lessons', 'section_id' : section_id, 'section_position' : section_position }, true);
	ajax.get_associated_lessons(section_id, section_position);
}

add_associated_lessons = function(response, section_id, section_position) {
	console.log('add_associated_lessons triggered');
	console.log(response);
	console.log(section_id);
	console.log(section_position);
	associated_lesson_template(response, section_id, section_position)
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
	var select_class = 'list_item_' + section_position + '_' + row_id;
	var row_class = 'row_' + section_position + '_' + row_id;

	var numRows = jQuery('#' + section_position  + ' .dad-list').length;

	jQuery('<tr class="list_item" id="' + row_class + '" data-section_id="' + section_id + '" data-order="' + row_id + '"> \
		<td><select data-type="lesson" id="' + select_class + '" data-placeholder="Choose a Section" class="chosen-select lesson-select" \
		></select></td><td><i class="fa fa-bars llms-fa-move-lesson"></i><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn"></i></td></tr>')
	.appendTo('#' + section_position + ' .dad-list tbody').hide().fadeIn(300);

	jQuery(select).change(function() { update_syllabus(jQuery(select).val()); });
	jQuery('#' + section_position + ' .dad-list tbody ' + '.lesson-select').change(function() { get_edit_link(jQuery(this)); update_syllabus(); jQuery(this).attr('disabled', 'disabled'); });

	jQuery('.deleteBtn').click(function() {
	    var contentPanelId = jQuery(this).attr("class");
	    jQuery(this).parent().parent().remove();
	    order_lessons();
	    update_syllabus();
	});


	for (var key in lessons) {

	    if (lessons.hasOwnProperty(key)) {
    		var select = jQuery('#' + section_position + ' #' + select_class);
    		var option = '<option value="' + lessons[key]['ID'] + '">' +  lessons[key]['post_title'] + '</option>'
    		jQuery(select).append(option);
    		//jQuery(select).prepend('<option value="" selected disabled>Please select a lesson...</option>');
	    }
	}
	jQuery(select).prepend('<option value="" selected disabled>Please select a lesson...</option>');
};

get_edit_link = function(element) {
	console.log('get edit link called');
	console.log(element);
	var lesson_id = jQuery(element).val();
	var element_id = jQuery(element).attr('id');
	console.log('element_id');
	console.log(element_id);
	var type = jQuery(element).attr('data-type');

	console.log(lesson_id);
	var ajax = new Ajax('post', {'action':'get_lesson', 'lesson_id' : lesson_id}, true);
	ajax.get_lesson(lesson_id, element_id, type);
}
add_edit_link = function(post, lesson_id, row_id, type) {
	console.log('add_edit_link called');
	console.log(lesson_id);
	console.log(post);
	console.log(row_id);
	console.log('type');
	console.log(type);
	//var td = jQuery('#' + row_id).parent().next();

	var edit_link = document.createElement('a');
	edit_link.setAttribute('href', encodeURI(post.edit_url));

	var edit_icon = document.createElement('i');
	edit_icon.setAttribute('class', 'fa fa-pencil-square-o llms-fa-edit-lesson');

	edit_link.appendChild(edit_icon);
	// put components together to build section block
	if (type == 'lesson') {
		
		jQuery('#' + row_id).parent().next().append(edit_link);
	}
	if (type == 'section') {
		console.log('trying to append the emelemnt')
		jQuery('#' + row_id).parent().append(edit_link);
	}
}

 /**
 * Generate lesson template
 */
associated_lesson_template = function (lessons, section_id, section_position) {
	for (var key in lessons) {

		var row_id = (jQuery('#' + section_position  + ' .list_item').length) + 1;
		var select_class = 'list_item_' + section_position + '_' + row_id;
		var row_class = 'row_' + section_position + '_' + row_id;

		var numRows = jQuery('#' + section_position  + ' .dad-list').length;

		jQuery('<tr class="list_item" id="' + row_class + '" data-section_id="' + section_id + '" data-order="' + row_id + '"> \
			<td><select id="' + select_class + '" data-placeholder="Choose a Section" class="chosen-select lesson-select" disabled="disabled" \
			></select></td><td><a href="' + lessons[key]['edit_url'] + '"><i class="fa fa-pencil-square-o llms-fa-edit-lesson"></i></a> \
			<i class="fa fa-bars llms-fa-move-lesson"></i><i data-code="f153" class="dashicons dashicons-dismiss deleteBtn"></i></td></tr>')
		.appendTo('#' + section_position + ' .dad-list tbody').hide().fadeIn(300);

		jQuery(select).change(function() { update_syllabus(jQuery(select).val()); });
		jQuery('#' + section_position + ' .dad-list tbody ' + '.lesson-select').change(function() { get_edit_link(jQuery(this)); update_syllabus(); jQuery(this).attr('disabled', 'disbaled'); });
		jQuery('.deleteBtn').click(function() {
		    var contentPanelId = jQuery(this).attr("class");
		    jQuery(this).parent().parent().remove();
		    order_lessons();
		    update_syllabus();
		});

	    if (lessons.hasOwnProperty(key)) {
			var select = jQuery('#' + section_position + ' #' + select_class);
			
			var option = '<option selected value="' + lessons[key]['ID'] + '">' +  lessons[key]['post_title'] + '</option>'
			jQuery(select).append(option);
	    }
	}
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
 	order_lessons();
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
		select.setAttribute('data-type', 'section');
		select.setAttribute('id', 'section_item_' + id);
		
		$(select).click(function() { reverse_selection ($(select))});
		$(select).change(function() { get_edit_link(jQuery(this)); catch_duplicates($(select)); $(this).attr('disabled', 'disbaled'); });
		$(select).attr("selectedIndex", -1);

		// create delete button
		var removeBtn = document.createElement('i');
		removeBtn.setAttribute('data-code', 'f153');
		removeBtn.setAttribute('class', 'dashicons dashicons-dismiss section-dismiss');
		$(removeBtn).click(function() { delete_this($(select)) });
		
		var moveBtn = document.createElement('i');
		moveBtn.setAttribute('class', 'fa fa-bars llms-fa-move-lesson');

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
		title.appendChild(moveBtn);
		section.appendChild(title);
		$(section).append(table);
		$(section).append(addLessonBtn);

    	$("#syllabus").hide().append(section).fadeIn(1000);
    	$(select).trigger("chosen:updated")

    	// grant table sorting functionality
    	lessons_sortable();
    }
})(jQuery);