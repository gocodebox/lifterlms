/**
 * LifterLMS Students Metabox Functions
 * @since  3.0.0
 * @version  3.0.0
 */
;( function( $, undefined ) {

	window.llms = window.llms || {};

	var MetaboxStudents = function() {

		/**
		 * Initialize
		 * @return void
		 * @since  3.0.0
		 * @version  3.0.0
		 */
		this.init = function() {

			var screens = [ 'course', 'llms_membership' ];

			if ( window.llms.post.post_type && -1 !== screens.indexOf( window.llms.post.post_type ) ) {

				this.$metabox = $( '#lifterlms-students' );

				this.bind();
			}

		};

		/**
		 * Bind dom events
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.bind = function() {

			var self = this;

			this.$metabox.on( 'click', 'a.llms-remove-student', function() {
				self.remove_student( $( this ) );
			} );

			this.$metabox.on( 'click', 'a.llms-add-student', function() {
				self.add_student( $( this ) );
			} );

			$( '#llms-add-student-select' ).llmsStudentsSelect2( { multiple: true } );

			$( '#llms-enroll-students' ).on( 'click', function() {
				self.enroll_students( $( this ) );
			} );

		};

		/**
		 * Add a Student
		 * @param    obj  $el  jQuery selector of the add button
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.add_student = function( $el ) {
			this.update_student_enrollment( $el.attr( 'data-id' ), 'add' );
		};

		this.enroll_students = function( $el ) {

			var self = this,
				page = $el.attr( 'data-page' ),
				$select = $( '#llms-add-student-select' ),
				ids = $select.val(),
				$container = this.$metabox.find( '.llms-metabox-students-add-new' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'bulk_enroll_students',
					page: page,
					student_ids: ids,
				},
				beforeSend: function( xhr ) {
					self.$metabox.find( '.llms-error' ).remove();
					if ( ! ids ) {
						$el.before( '<span class="llms-error">' + LLMS.l10n.translate( 'Please select a student to enroll' ) + '</span>' );
						xhr.abort();
						LLMS.Spinner.stop( $container );
					}
				},
				success: function( r ) {

					if ( r.success && r.data ) {
						$( '#llms-students-table' ).replaceWith( r.data );
						$select.val( '' ).trigger( 'change' );
					} else {
						$el.before( '<span class="llms-error">' + r.message + '</span>' );
					}

					LLMS.Spinner.stop( $container );
				},
			} );



		};

		/**
		 * Remove a Student
		 * @param    obj  $el  jQuery selector of the add button
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.remove_student = function( $el ) {
			this.update_student_enrollment( $el.attr( 'data-id' ), 'remove' );
		};

		/**
		 * Execute AJAX call, add spinners, update html view
		 * @param    int      id      student id
		 * @param    string   status  new status [add|remove]
		 * @return   void
		 * @since    3.0.0
		 * @version  3.0.0
		 */
		this.update_student_enrollment = function( id, status ) {

			var self = this,
				$container = this.$metabox.find( '.llms-metabox-students-enrollments' );

			LLMS.Spinner.start( $container );

			window.LLMS.Ajax.call( {
				data: {
					action: 'update_student_enrollment',
					status: status,
					student_id: id,
				},
				beforeSend: function() {
					self.$metabox.find( 'p.error' ).remove();
				},
				success: function( r ) {

					if ( r.success && r.data ) {
						$( '#llms-student-id-' + id ).replaceWith( r.data );
					} else {
						self.$metabox.find( '.llms-metabox-students-enrollments' ).prepend( '<p class="error">' + r.message + '</p>' );
					}

					LLMS.Spinner.stop( $container );
				},
			} );

		};


		// go
		this.init();

	};

	// initalize the object
	window.llms.MetaboxStudents = new MetaboxStudents();

} )( jQuery );



















// jQuery(document).ready(function($) {
//     jQuery(".add-student-select").select2({
//         width: '100%',
//         multiple: true,
//         allowClear: true,
//         maximumSelectionLength: 10,
//         placeholder: "Select a student",
//         ajax: {
//             url: "admin-ajax.php",
//             method: 'POST',
//             dataType: 'json',
//             delay: 250,
//             data: function (params) {
//                 return {
//                     term: params.term, // search term
//                     page: params.page,
//                     action: 'get_students',
//                     postId: jQuery('#post_ID').val(),
//                 };
//             },
//             processResults: function (data) {
//                 return {
//                     results: $.map(data.items, function (item) {
//                         return {
//                             text: item.name,
//                             id: item.id
//                         }
//                     })
//                 };
//             },
//             cache: true,
//         },
//     });

//     jQuery(".remove-student-select").select2({
//         width: '100%',
//         multiple: true,
//         maximumSelectionLength: 10,
//         placeholder: "Select a student",
//         ajax: {
//             url: "admin-ajax.php",
//             method: 'POST',
//             dataType: 'json',
//             delay: 250,
//             data: function (params) {
//                 return {
//                     term: params.term, // search term
//                     page: params.page,
//                     action: 'get_enrolled_students',
//                     postId: jQuery('#post_ID').val(),
//                 };
//             },
//             processResults: function (data) {
//                 return {
//                     results: $.map(data.items, function (item) {
//                         return {
//                             text: item.name,
//                             id: item.id
//                         }
//                     })
//                 };
//             },
//             cache: true,
//         }
//     });
// });
