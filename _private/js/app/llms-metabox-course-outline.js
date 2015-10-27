/* global LLMS, $ */
/* jshint strict: false */

/**
 * Front End Quiz Class
 * Applies only to post type quiz
 * @type {Object}
 */
LLMS.MB_Course_Outline = {

	/**
	 * init
	 * loads class methods
	 */
	init: function() {

		if ($('#llms_post_edit_type').length ) {
			if ($('#llms_post_edit_type').val() === 'course') {
				this.bind();
			}
		}

	},
	alreadySubmitted: false,
	/**
	 * Bind Method
	 * Handles dom binding on load
	 */
	bind: function() {
		var _this = this;

		$( '.llms-modal-cancel' ).click(function(e) {
			e.preventDefault();
			$(window).trigger('build');
		});

		$(document).ready(function() {
			$('.llms-chosen-select').chosen({width: '100%'});
		});

		//hack to resize excerpt and content editor size.
		//There is a WP but where passing the css_options to wp_editor
		//does not work.
		$('.tab-link').on('click', function() {
			$( '#content_ifr' ).css('height', '300px');
			$( '#excerpt_ifr' ).css('height', '300px');
		});

		//show / hide prereq lesson select based on setting
		if ( $('#_has_prerequisite').attr('checked') ) {
			$('.llms-prereq-top').addClass('top');
			$('.llms-prereq-bottom').show();

		} else {
			$('.llms-prereq-bottom').hide();
		}
		$('#_has_prerequisite').change(function() {
			if ( $('#_has_prerequisite').attr('checked') ) {
				$('.llms-prereq-top').addClass('top');
				$('.llms-prereq-bottom').show();

			} else {
				$('.llms-prereq-top').removeClass('top');
				$('.llms-prereq-bottom').hide();
			}
		});

		//generic modal call
		$('a.llms-modal').click(function() {
			$('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	closed: function() {
	        		_this.alreadySubmitted = false;
	        	}
	        });
		});

		//add new lesson modal
	    $('a.llms-modal-new-lesson-link').click(function(){
	        $('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	open: function() {
	        		_this.getSections();
					$( '#llms_create_lesson' ).find('input[value="Create Lesson"]').removeProp('disabled');
	        	},
	        	closed: function() {
	        		_this.alreadySubmitted = false;
	        	}
	        });
	    });

	    //add existing lesson modal
	    $('a.llms-modal-existing-lesson-link').click(function(){
	        $('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	open: function() {
	        		_this.getSections();
	        		_this.getLessons();
	        	},
	        	closed: function() {
	        		_this.alreadySubmitted = false;
	        	}
	        });
	    });

		this.setup_course();

		$(window).click(function(e) {
			if (e.target.id !== 'llms-outline-add' && $('#llms-outline-add').hasClass('clicked') ) {
				$('#llms-outline-menu').css('display', 'none');
				reset();
			}
		});

		function reset() {
			$('#llms-outline-add').removeClass('clicked');
            $('#llms-outline-add').addClass('bt');
            $('#llms-outline-menu').removeClass('fade-in');
			$('#triangle').show();
		}

		$(window).scroll(function() {
			if($('#llms-outline-add').hasClass('clicked')) {
					$('#triangle').hide();
					var popover = $('#llms-outline-menu'),
					top = -($('#llms-outline-add').offset().top) - 81 +
					$(window).scrollTop() + ($(window).height() / 2);
					popover.css('top', top);
			}
		});

	    $('#llms-outline-add').click(function(e) {
			e.preventDefault();
			var popover = $('#llms-outline-menu');
			if ($(this).hasClass('bt')) {
				if($(this).offset().top - $(window).scrollTop() < 200) {
					popover.css('top', '43px');
					if($(window).width() < 851) {
						popover.find('#triangle').css('left', '164px');
							popover.css('top', '57px');
							popover.css('left', '-138px');
							popover.css('bottom', '15px');
					}
				} else {
					popover.css('top', '');
						if($(window).width() < 851) {
							popover.css('top', '-54px');
								var left = $(window).width() < 400 ?  -Math.abs($(window).width() / 2) : -242;
								left += 'px';
								popover.css('left', left);
							popover.css('bottom', '15px');
							popover.find('#triangle').css('left', '227px');
						}
				}
	            $(this).removeClass('bt');
	            $(this).addClass('clicked');
				popover.addClass('fade-in');
				popover.css('display', 'block');
	        } else {
	            $(this).removeClass('clicked');
	            $(this).addClass('bt');
	            popover.removeClass('fade-in');
				popover.css('display', 'none');
				popover.find('#triangle').show();
	        }
	    });

	    $('#tooltip_menu a').click(function(e) {
			var popover = $('#llms-outline-menu');
			popover.removeClass('fade-in');
			popover.css('display', 'none');
			e.preventDefault();
	    });

	    $('a.tooltip').click(function(e) {
	        e.preventDefault();
	    });

		//sortable
		$( '.llms-lesson-tree' ).sortable({
			connectWith: '.llms-lesson-tree',
			axis 		: 'y',
	    	placeholder : 'placeholder',
	    	cursor		: 'move',
	    	forcePlaceholderSize:true,
	    	stop: function() {

	    		_this.resortLessons();
	    	}

		}).disableSelection();

		//add section row js functionality
		_this.addSectionRowFunctionality();

		//add lesson row js functionality
		_this.addLessonRowFunctionality();

		//section form submit
		$( '#llms_create_section' ).on( 'submit', function(e) {
			e.preventDefault();
			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});
			if(_this.alreadySubmitted === false) {
				_this.alreadySubmitted = true;
				_this.createSection( values );
			}
		});

		//new lesson form submit
		$( '#llms_create_lesson' ).on( 'submit', function(e) {
			e.preventDefault();
			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});
			if(_this.alreadySubmitted === false) {
				_this.alreadySubmitted = true;
				_this.createLesson( values );
			}

		});

		//add existing lesson form submit
		$( '#llms_add_existing_lesson' ).on( 'submit', function(e) {
			e.preventDefault();

			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});
			if(_this.alreadySubmitted === false) {
				_this.alreadySubmitted = true;
				_this.addExistingLesson( values );
			}

		});

		//update lesson title
		$( '#llms_edit_lesson' ).on( 'submit', function(e) {
			e.preventDefault();

			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});

			_this.updateLesson( values );

		});

		//update section title
		$( '#llms_edit_section' ).on( 'submit', function(e) {
			e.preventDefault();

			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});

			_this.updateSection( values );

		});

		//update lesson title
		$( '#llms_delete_section' ).on( 'submit', function(e) {
			e.preventDefault();

			var values = {};
			$.each($(this).serializeArray(), function (i, field) {
			    values[field.name] = field.value;
			});

			_this.deleteSection( values );
		});
	},
	resortSections: function() {

		var section_tree = {};

		$( '.llms-section' ).each( function(i) {
			i++;

			//update the sections to display the new order
			$(this).find('[name="llms_section_order[]"]').val(i);
			$(this).find('.llms-section-order').html(i);

			var id = $(this).find('[name="llms_section_id[]"]').val();

			//add section id and order to section tree object
			section_tree[id] = i;
			//update the new order in the database

		});

		LLMS.MB_Course_Outline.updateSectionOrder( section_tree );

	},

	updateSectionOrder: function( section_tree ) {
		console.log(section_tree);
		LLMS.Ajax.call({
	    	data: {
	    		action: 'update_section_order',
				sections: section_tree
	    	},
	    	beforeSend: function() {
	    	},
	    	success: function(r) {
	    		console.log(r);
	    		if ( r.success === true ) {
	    		}
	    	}
	    });
	},

	updateLessonOrder: function( lesson_tree ) {
		console.log(lesson_tree);
		LLMS.Ajax.call({
	    	data: {
	    		action: 'update_lesson_order',
				lessons: lesson_tree
	    	},
	    	beforeSend: function() {
	    	},
	    	success: function(r) {
	    		console.log(r);

	    		if ( r.success === true ) {
	    		}
	    	}
	    });
	},

	resortLessons: function() {

		var lesson_tree = {};

		$( '.llms-lesson-tree' ).each( function() {

			//loop through all lessons and set order
			$(this).find( '.llms-lesson').each( function(i) {
    			i++;

    			//set parent section
    			var parentSection = $(this).parent().parent().find('[name="llms_section_id[]"]').val();
    			// alert(parentSection);
    			$(this).find('[name="llms_lesson_parent_section[]"]').val(parentSection);

    			//set the new order
    			$(this).find('[name="llms_lesson_order[]"]').val(i);
    			$(this).find('.llms-lesson-order').html(i);
    			console.log(parentSection);

    			//save parent section and order to object
    			var id = $(this).find('[name="llms_lesson_id[]"]').val();
    			lesson_tree[id] = {
    				parent_section : parentSection,
    				order : i
    			};

    		});
		});

		LLMS.MB_Course_Outline.updateLessonOrder( lesson_tree );

	},

	createSection: function( values ) {
	    LLMS.Ajax.call({
	    	data: {
	    		action: 'create_section',
				title: values.llms_section_name

	    	},
	    	beforeSend: function() {
	    	},
	    	success: function(r) {
	    		console.log(r);

	    		if ( r.success === true ) {

	    			$('#llms_course_outline_sort').append(r.data);
	    			$(window).trigger('build');
	    			LLMS.MB_Course_Outline.addSectionRowFunctionality();

	    			//clear form
	    			$( '#llms_create_section' ).each(function(){
					    this.reset();
					});
	    		}
	    	}
	    });
	},

	addSectionRowFunctionality: function() {

		//lesson sortable functionality
		$( '.llms-lesson-tree' ).sortable({
			connectWith: '.llms-lesson-tree',
			axis 		: 'y',
	    	placeholder : 'placeholder',
	    	cursor		: 'move',
	    	forcePlaceholderSize:true,
	    	stop: function() {

	    		LLMS.MB_Course_Outline.resortLessons();
	    	}

		}).disableSelection();

		$( '#llms_course_outline_sort' ).sortable({
			connectWith: '.sortablewrapper',
			axis 		: 'y',
	    	placeholder : 'placeholder',
	    	cursor		: 'move',
	    	forcePlaceholderSize:true,
	    	stop: function() {
	    		LLMS.MB_Course_Outline.resortSections();
	    	}
		}).disableSelection();

		//edit section modal
	    $('a.llms-edit-section-link').click(function(){
	    	var _that = $(this);
	        $('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	open: function() {
	        		var section_id = _that.parent().parent().find('[name="llms_section_id[]"]').val();
	        		LLMS.MB_Course_Outline.getSection(section_id);
	        	}
	        });
	    });

	    //delete section modal
	    $('a.llms-delete-section-link').click(function(){
	    	var _that = $(this);
	        $('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	open: function() {

	        		var section_id = _that.parent().parent().find('[name="llms_section_id[]"]').val();
	        		$('#llms-section-delete-id').val(section_id);
	        	}
	        });
	    });
	},

	addLessonRowFunctionality: function() {

		//edit lesson modal
	    $('a.llms-edit-lesson-link').click(function(){
	    	var _that = $(this);
	        $('#' + $(this).attr('data-modal_id') ).topModal( {
	        	title: $(this).attr('data-modal_title'),
	        	open: function() {
	        		var lesson_id = _that.parent().parent().parent().find('[name="llms_lesson_id[]"]').val();
	        		LLMS.MB_Course_Outline.getLesson(lesson_id);
	        	}
	        });
	    });

		//update lesson title
		$( '.llms-remove-lesson-link' ).on( 'click', function(e) {
			e.preventDefault();

			var lesson_id = $(this).parent().parent().parent().find('[name="llms_lesson_id[]"]').val();

			LLMS.MB_Course_Outline.removeLesson( lesson_id );
		});
	},

	createLesson: function( values ) {
	    LLMS.Ajax.call({
	    	data: {
	    		action: 'create_lesson',
				title: values.llms_lesson_name,
				excerpt: values.llms_lesson_excerpt,
				section_id: values.llms_section
	    	},
	    	beforeSend: function() {
	    	},
	    	success: function(r) {
	    		console.log(r);

	    		if ( r.success === true ) {

	    			//find the correct section and attach lesson
	    			$( '.llms-section' ).each( function() {

						var input_value = $(this).find('[name="llms_section_id[]"]').val();
						console.log(input_value);
						if ( input_value === values.llms_section ) {
							console.log('found one');
							console.log($(this));
							console.log($(this).find('.llms_lesson_tree'));
							$(this).find( '#llms_section_tree_' + values.llms_section ).append(r.data);
						}

					});

	    			//close modal window
	    			$(window).trigger('build');
	    			LLMS.MB_Course_Outline.addLessonRowFunctionality();

	    			//clear form
	    			$( '#llms_create_lesson' ).each(function(){
						this.reset();
					});
				}
			},
		});
	},

	addExistingLesson: function( values ) {

	    LLMS.Ajax.call({
	    	data: {
	    		action: 'add_lesson_to_course',
	    		lesson_id: values.llms_lesson,
				section_id: values.llms_section
	    	},
	    	beforeSend: function() {
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			$( '.llms-section' ).each( function() {

						var input_value = $(this).find('[name="llms_section_id[]"]').val();
						console.log(input_value);
						if ( input_value === values.llms_section ) {
							$(this).find( '#llms_section_tree_' + values.llms_section ).append(r.data);
						}

					});

	    			//close modal window
	    			$(window).trigger('build');
	    			LLMS.MB_Course_Outline.addLessonRowFunctionality();

	    			$( '#llms_add_existing_lesson' ).each(function(){
						this.reset();
					});
				}
			}
		});
	},

	getSections: function() {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'get_course_sections',
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			$('#llms-section-select').empty();

					$.each(r.data, function(key, value) {
						//append a new option for each result
						var newOption = $('<option value="' + value.ID + '">' + value.post_title + '</option>');
						$('#llms-section-select').append(newOption);
					});

					// refresh option list
					$('#llms-section-select').trigger('chosen:updated');
	    		}
	    	}
	    });
	},

	getSection: function( section_id ) {
		console.log(section_id);
		LLMS.Ajax.call({
	    	data: {
	    		action: 'get_course_section',
	    		section_id: section_id
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

					$('#llms-section-edit-name').val(r.data.post.post_title);
					$('#llms-section-edit-id').val(r.data.id);
	    		}
	    	}
	    });
	},

	getLesson: function( lesson_id ) {
console.log(lesson_id);
		LLMS.Ajax.call({
	    	data: {
	    		action: 'get_course_lesson',
	    		lesson_id: lesson_id
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

					$('#llms-lesson-edit-name').val(r.data.post.post_title);
					$('#llms-lesson-edit-excerpt').val(r.data.post.post_excerpt);
					$('#llms-lesson-edit-id').val(r.data.id);
	    		}
	    	}
	    });
	},

	updateSection: function( values ) {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'update_course_section',
	    		section_id: values.llms_section_edit_id,
	    		title: values.llms_section_edit_name
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			//find and update section title in tree
	    			//find the correct section and attach lesson
	    			$( '.llms-section' ).each( function() {

						var input_value = $(this).find('[name="llms_section_id[]"]').val();
						console.log(input_value);
						if ( input_value === values.llms_section_edit_id ) {
							console.log('found one');
							console.log($(this));
							console.log($(this).find('.llms-section-title'));
							$(this).find( '.llms-section-title' ).html(r.data.title);
						}

					});

					$(window).trigger('build');

					//clear form
					$( '#llms_edit_section' ).each(function(){
					    this.reset();
					});
	    		}
	    	}
	    });
	},

	updateLesson: function( values ) {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'update_course_lesson',
	    		lesson_id: values.llms_lesson_edit_id,
	    		title: values.llms_lesson_edit_name,
	    		excerpt: values.llms_lesson_edit_excerpt
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			//find the correct lesson and update the title and description
	    			$( '.llms-lesson' ).each( function() {

						var input_value = $(this).find('[name="llms_lesson_id[]"]').val();
						console.log(input_value);
						if ( input_value === values.llms_lesson_edit_id ) {
							$(this).find( '.llms-lesson-title' ).html(r.data.title.title);
							$(this).find( '.llms-lesson-excerpt' ).html(r.data.excerpt.post_excerpt);
						}

					});

					$(window).trigger('build');

					$( '#llms_edit_lesson' ).each(function(){
					    this.reset();
					});
	    		}
	    	}
	    });
	},

	removeLesson: function( lesson_id ) {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'remove_course_lesson',
	    		lesson_id: lesson_id,
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			//find the correct lesson and remove it
	    			$( '.llms-lesson' ).each( function() {

						var input_value = $(this).find('[name="llms_lesson_id[]"]').val();
						console.log(input_value);
						if ( input_value === lesson_id ) {
							$(this).remove();
							LLMS.MB_Course_Outline.resortLessons();
						}

					});
	    		}
	    	}
	    });
	},

	deleteSection: function( values ) {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'delete_course_section',
	    		section_id: values.llms_section_delete_id,
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {

	    			//find the correct lesson and remove it
	    			$( '.llms-section' ).each( function() {

						var input_value = $(this).find('[name="llms_section_id[]"]').val();
						console.log(input_value);
						if ( input_value === values.llms_section_delete_id ) {
							$(this).remove();
							LLMS.MB_Course_Outline.resortSections();
						}

					});

					$(window).trigger('build');
	    		}
	    	}
	    });
	},

	getLessons: function() {

		LLMS.Ajax.call({
	    	data: {
	    		action: 'get_lesson_options_for_select',
	    	},
	    	success: function(r) {

	    		if ( r.success === true ) {
	    			$('#llms-lesson-select').empty();

					$.each(r.data, function(key, value) {
						//append a new option for each result
						var newOption = $('<option value="' + key + '">' + value + '</option>');
						$('#llms-lesson-select').append(newOption);

					});

					// refresh option list
					$('#llms-lesson-select').trigger('chosen:updated');
	    		}
	    	}
	    });
	},

	/**
	 * Initial Course setup
	 * displays modal window
	 * User enters course name
	 * Submit adds title to course and saves course as draft.
	 * @return {[type]} [description]
	 */
	setup_course: function() {

		//only run this function on new posts of type course
		var $R = LLMS.Rest,
			new_post = ['post-new.php'],
			post_type = 'course',
			query_vars = $R.get_query_vars();

		if ( $R.is_path(new_post) && query_vars.post_type === post_type ) {
			$(document).ready(function() {
				$('#pop1').topModal( {
		        	title: 'Create New Course'
		        });
			});

			//on submit set course title and save post as draft
		    $( '#llms-create-course-submit').click(function(e) {

		    	$('#title').val( $('#llms-course-name').val() );
		    	$('#save-post').click();
		    	////save for later when you want to close a modal
		    	// $('#TB_window').fadeOut();
		    	// self.parent.tb_remove();
	    		e.preventDefault();
		    });
	    }
	}
};
