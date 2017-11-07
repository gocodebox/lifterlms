<?php
/**
 * Course Builder
 * @since    3.13.0
 * @version  3.14.8
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Builder {

	private static $search_term = '';

	/**
	 * A terrible Rest API for the course builder
	 * @shame    gimme a break pls
	 * @param    array     $request  $_REQUEST
	 * @return   array
	 * @since    3.13.0
	 * @version  3.14.8
	 */
	public static function handle_ajax( $request ) {

		// @todo do some real error handling here
		if ( ! $request['course_id'] || ! current_user_can( 'edit_course', $request['course_id'] ) ) {
			return array();
		}

		switch ( $request['action_type'] ) {

			case 'delete':

				if ( ! current_user_can( 'delete_course', $request['course_id'] ) ) {
					return array(); // @todo better error handling here
				}

				if ( 'model' === $request['object_type'] ) {

					if ( in_array( $request['data_type'], array( 'section', 'lesson' ) ) ) {

						$obj = llms_get_post( $request['model']['id'] );

						// make sure sections are empty before deleting
						if ( 'section' === $obj->type && $obj->get_lessons( 'ids' ) ) {
							return array(); // @todo error handling
						}

						wp_delete_post( $request['model']['id'], true );

					}
				}

			break;

			case 'read':

				if ( 'section' === $request['data_type'] && 'collection' === $request['object_type'] ) {

					$course = llms_get_post( $request['course_id'] );
					$sections = array();
					foreach ( $course->get_sections( 'ids' ) as $section_id ) {
						array_push( $sections, self::get_section( $section_id, true ) );
					}
					return $sections;

				}

			break;

			case 'update':

				// reorder sections or lessons
				if ( 'collection' === $request['object_type'] && in_array( $request['data_type'], array( 'section', 'lesson' ) ) ) {

					if ( isset( $request['models'] ) ) {

						foreach ( $request['models'] as $model ) {
							$object = llms_get_post( $model['id'] );
							$object->set( 'order', $model['order'] );
							// additionally save lessons parent
							if ( 'lesson' === $request['data_type'] ) {
								$object->set( 'parent_section', $model['section_id'] );
							}
						}
					}
				} elseif ( 'model' === $request['object_type'] ) {

					// new item
					if ( false !== strpos( $request['model']['id'], '_temp_' ) ) {

						$id = 'new';

						// clone (lessons only)
					} elseif ( false !== strpos( $request['model']['id'], '_clone_' ) ) {

						$orig_id = preg_replace( '/[^0-9]/', '', $request['model']['id'] );
						$orig = llms_get_post( $orig_id );
						$id = $orig->clone_post();

						// regular update
					} else {

						$id = absint( $request['model']['id'] );

					}

					// create new / update existing sections/lessons
					if ( 'section' === $request['data_type'] ) {

						$section = new LLMS_Section( $id, $request['model']['title'] );

						if ( 'new' === $id ) {
							$section->set( 'parent_course', $request['course_id'] );
							$section->set( 'order', $request['model']['order'] );
						} else {
							$section->set( 'title', $request['model']['title'] );
						}

						wp_send_json( self::get_section( $section->get( 'id' ), false ) );

					} elseif ( 'lesson' === $request['data_type'] ) {

						$lesson = new LLMS_Lesson( $id, $request['model']['title'] );

						$lesson->set( 'parent_section', $request['model']['section_id'] );
						$lesson->set( 'order', $request['model']['order'] );

						if ( 'new' !== $id ) {
							$lesson->set( 'title', $request['model']['title'] );
							if ( ! $lesson->has_modified_slug() ) {
								$lesson->set( 'name', sanitize_title( $request['model']['title'] ) );
							}
						}

						// detach the lesson
						if ( '' === $request['model']['section_id'] ) {
							$lesson->set( 'parent_course', '' );
						} else {
							$lesson->set( 'parent_course', $request['course_id'] );
						}

						wp_send_json( self::get_lesson( $lesson->get( 'id' ), false, true ) );

					} elseif ( 'course' === $request['data_type'] ) {

						$course = new LLMS_Course( $id );
						$course->set( 'title', $request['model']['title'] );

					}// End if().
				}// End if().

			break;

			case 'search':
				$page = isset( $request['page'] ) ? $request['page'] : 1;
				$term = isset( $request['term'] ) ? sanitize_text_field( $request['term'] ) : '';
				wp_send_json( self::get_existing_lessons( absint( $request['course_id'] ), $term, $page ) );
			break;

		}// End switch().

		return array();

	}

	/**
	 * Do post locking stuff on the builder
	 * Locking the course edit main screen will lock the builder and vice versa... probably need to find a way
	 * to fix that but for now this'll work just fine and if you're unhappy about it, well, sorry...
	 *
	 * @param    int     $course_id  WP Post ID
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private static function handle_post_locking( $course_id ) {

		if ( ! wp_check_post_lock( $course_id ) ) {
			$active_post_lock = wp_set_post_lock( $course_id );
		}

		?><input type="hidden" id="post_ID" value="<?php echo absint( $course_id ); ?>"><?php

if ( ! empty( $active_post_lock ) ) {
	?><input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" /><?php
}

		add_filter( 'get_edit_post_link', array( __CLASS__, 'modify_take_over_link' ), 10, 3 );
		add_action( 'admin_footer', '_admin_notice_post_locked' );

	}

	/**
	 * Retrieve lesson data
	 * @param    int        $lesson_id        WP Post ID of a lesson
	 * @param    boolean    $include_quizzes  if true, include quiz data
	 * @param    boolean    $include_meta     if true, include meta data
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public static function get_lesson( $lesson_id, $include_quizzes = false, $include_meta = true ) {

		$lesson = llms_get_post( $lesson_id );

		$data = array(
			'id' => $lesson->get( 'id' ),
			'title' => $lesson->get( 'title' ),
			'order' => $lesson->get( 'order' ),
			'section_id' => $lesson->get( 'parent_section' ),
		);

		if ( $include_meta ) {

			$quiz_id = $lesson->get( 'assigned_quiz' );

			$data = array_merge( $data, array(
				'is_free' => $lesson->is_free(),
				'prerequisite' => $lesson->has_prerequisite() ? self::get_lesson( $lesson->get( 'prerequisite' ), false, false ) : false,
				'drip_method' => $lesson->get( 'drip_method' ),
				'days_before_available' => $lesson->get( 'days_before_available' ),
				'date_available' => $lesson->get( 'date_available' ),
				'quiz' => $quiz_id ? self::get_quiz( $quiz_id ) : false,
				'has_content' => $lesson->get( 'content' ) ? true : false,
				'edit_url' => current_user_can( 'edit_lesson', $lesson_id ) ? get_edit_post_link( $lesson_id ) : '',
				'view_url' => get_permalink( $lesson_id ),
			) );

		}

		// if ( $include_quizzes ) {}

		return $data;

	}

	/**
	 * Retrieve a list of lessons the current user is allowed to clone/attach
	 * Used for ajax searching to add existing lessons
	 * @param    int        $course_id    WP Post ID of the course
	 * @param    string     $search_term  optional search term (searches post_title)
	 * @param    integer    $page         page, used when paginating search results
	 * @return   array
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	private static function get_existing_lessons( $course_id, $search_term = '', $page = 1 ) {

		$lessons = array(
			'orphan' => array(
				'children' => array(),
				'text' => esc_attr__( 'Attach Orphaned Lesson', 'lifterlms' ),
			),
			'dupe' => array(
				'children' => array(),
				'text' => esc_attr__( 'Clone Existing Lesson', 'lifterlms' ),
			),
		);

		$args = array(
			'order' => 'ASC',
			'orderby' => 'post_title',
			'paged' => $page,
			'post_status' => array( 'publish', 'draft', 'pending' ),
			'post_type' => 'lesson',
			'posts_per_page' => 50,
		);

		if ( ! current_user_can( 'manage_options' ) ) {

			$instructor = llms_get_instructor();
			$parents = $instructor->get( 'parent_instructors' );
			if ( ! $parents ) {
				$parents = array();
			}

			$args['author__in'] = array_unique( array_merge(
				array( get_current_user_id() ),
				$instructor->get_assistants(),
				$parents
			) );

		}

		self::$search_term = $search_term;
		add_filter( 'posts_where', array( __CLASS__, 'get_existing_lessons_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'get_existing_lessons_where' ), 10, 2 );

		$lessons = array();

		if ( $query->have_posts() ) {

			foreach ( $query->posts as $post ) {

				$lesson = llms_get_post( $post );

				if ( $lesson->is_orphan() ) {
					$id = $post->ID;
					$text = sprintf( '%1$s (#%2$d)', $post->post_title, $post->ID );
				} else {
					$id = 'lesson_clone_' . $post->ID;
					$text = sprintf( '[CLONE] %1$s (#%2$d)', $post->post_title, $post->ID );
				}

				$lessons[] = array(
					'id' => $id,
					'text' => $text,
					'title' => $post->post_title,
				);

			}
		}

		$ret = array(
			'results' => $lessons,
			'pagination' => array(
				'more' => ( $page < $query->max_num_pages ),
			),
		);

		return $ret;

	}

	/**
	 * Search lessons by search term during existing lesson lookups
	 * @param    string     $where      existing sql where clause
	 * @param    obj        $wp_query   WP_Query
	 * @return   string
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	public function get_existing_lessons_where( $where, $wp_query ) {

		if ( self::$search_term ) {
			global $wpdb;
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE "%' . esc_sql( $wpdb->esc_like( self::$search_term ) ) . '%"';
		}

		return $where;

	}

	/**
	 * Tutorial steps data
	 * @return   [type]     [description]
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private static function get_tutorial_steps() {
		return array(
			array(
				'el' => '#llms-new-section',
				'title' => __( 'Create a Section', 'lifterlms' ),
				'placement' => 'left',
				'content_main' => __( 'Sections are the organizational building blocks of a course. A course can be made up of one or more sections and each of these sections contains at least one lesson.', 'lifterlms' ),
				'content_action' => __( 'Add a section by clicking the "New Section" button on the right.', 'lifterlms' ),
			),
			array(
				'el' => '#llms-builder-tools footer',
				'title' => __( 'Auto-Saves and Save Status', 'lifterlms' ),
				'placement' => 'top',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Everything is saved automatically but watch the status indicator to ensure your content is saved before leaving the builder!', 'lifterlms' ),
			),
			array(
				'el' => '#llms-new-lesson',
				'title' => __( 'Create a Lesson', 'lifterlms' ),
				'placement' => 'left',
				'content_main' => __( 'Great! Now that you have a section you can start adding lessons to it. Lessons will contain the main content of your course. In a lesson you can add text, video, image, and other types of content.', 'lifterlms' ),
				'content_action' => __( 'Add a lesson by clicking the "New Lesson" button on the right.', 'lifterlms' ),
			),
			array(
				'el' => '.llms-sections .llms-section:first-child .llms-drag-utility',
				'title' => __( 'Reorder Sections', 'lifterlms' ),
				'placement' => 'bottom',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Use drag handles to drag and drop sections and reorder them.', 'lifterlms' ),
			),
			array(
				'el' => '.llms-sections .llms-section:first-child > .llms-builder-header .llms-editable-title',
				'title' => __( 'Rename a Section', 'lifterlms' ),
				'placement' => 'bottom',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Click on the title of any section to edit the title. When finished, hit the "Enter" key to save or press "Esc" to quit editing and revert to the original title.', 'lifterlms' ),
			),
			array(
				'el' => '.llms-sections .llms-section:first-child .llms-lessons .llms-drag-utility',
				'title' => __( 'Reorder Lessons within a section', 'lifterlms' ),
				'placement' => 'bottom',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Use drag handles on a lesson to drag and drop lessons within a section and reorder them. When you have multilpe sections you can also move lessons into another section.', 'lifterlms' ),
			),
			array(
				'el' => '.llms-sections .llms-section:first-child .llms-lessons .llms-editable-title',
				'title' => __( 'Rename a Lesson', 'lifterlms' ),
				'placement' => 'bottom',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Click on the title of a lesson to rename it in the same way you can rename sections!', 'lifterlms' ),
			),
			array(
				'el' => '#llms-expand-all',
				'title' => __( 'Expand and Collapse', 'lifterlms' ),
				'placement' => 'left',
				'buttons' => array(
					'next' => __( 'Next', 'lifterlms' ),
				),
				'content_main' => __( 'Use these expand and collapse buttons to open and close all the sections in the course in one click.', 'lifterlms' ),
			),
			array(
				'el' => '#llms-builder-tools',
				'title' => __( 'Build on!', 'lifterlms' ),
				'placement' => 'left',
				'buttons' => array(
					'next' => __( 'Finish!', 'lifterlms' ),
				),
				'content_main' => __( 'That\'s all! To finish building your course you\'ll want to finish your outline with more sections and lessons. When you\'re satisfied use the pencil icons to leave the builder and start adding content to your lessons.', 'lifterlms' ),
			),

		);
	}

	/**
	 * Retrieve Quiz data
	 * @param    int        $quiz_id            WP Post ID of a quiz
	 * @param    boolean    $include_questions  if true, includes question data
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public static function get_quiz( $quiz_id, $include_questions = false ) {

		$quiz = new LLMS_QQuiz( $quiz_id );
		$data = array(
			'id' => $quiz->get( 'id' ),
			'title' => $quiz->get( 'title' ),
		);

		// if ( $include_questions ) {}

		return $data;

	}

	/**
	 * Retrieve an array of data for a section
	 * @param    int      $section_id       WP Post ID of the section
	 * @param    bool     $include_lessons  if true, includes children lesson data
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public static function get_section( $section_id, $include_lessons ) {

		$section = llms_get_post( $section_id );

		$data = array(
			'id' => $section->get( 'id' ),
			'title' => $section->get( 'title' ),
			'order' => $section->get( 'order' ),
		);

		if ( $include_lessons ) {
			$data['lessons'] = array();
			foreach ( $section->get_lessons( 'ids' ) as $lesson_id ) {
				array_push( $data['lessons'], self::get_lesson( $lesson_id ) );
			}
		}

		return $data;

	}

	/**
	 * Modify the "Take Over" link on the post locked modal to send users to the builder when taking over a course
	 * @param    string     $link     default post edit link
	 * @param    int        $post_id  WP Post ID of the course
	 * @param    string     $context  display context
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public static function modify_take_over_link( $link, $post_id, $context ) {

		return add_query_arg( array(
			'page' => 'llms-course-builder',
			'course_id' => $post_id,
		), admin_url( 'admin.php' ) );

	}

	/**
	 * Output the page content
	 * @return   void
	 * @since    3.13.0
	 * @version  3.14.8
	 */
	public static function output() {

		global $post;

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : null;
		if ( ! $course_id || ( $course_id && 'course' !== get_post_type( $course_id ) ) ) {
			_e( 'Invalid course ID', 'lifterlms' );
			return;
		}

		$post = get_post( $course_id );

		if ( ! current_user_can( 'edit_course', $course_id ) ) {
			_e( 'You cannot edit this course!', 'lifterlms' );
			return;
		}
		?>

		<div class="wrap lifterlms llms-course-builder">

			<div class="llms-builder-inside">

			<?php do_action( 'llms_before_course_builder', $course_id ); ?>

			<header class="llms-builder-page-header" id="llms-course-info"></header>

			<div class="llms-builder-main">

				<section class="llms-course-syllabus llms-course" id="llms-course-syllabus">
					<div class="llms-builder-tutorial" id="llms-builder-tutorial"></div>
					<ul class="llms-sections" id="llms-sections"></ul>
				</section>

				<aside id="llms-builder-tools" class="llms-builder-tools">

					<h2 class="llms-tools-headline"><?php _e( 'Course Elements', 'lifterlms' ); ?></h2>

					<ul class="llms-tools-list llms-add-items">

						<li>
							<button class="llms-tool-button llms-add-item" id="llms-new-section" data-model="section">
								<span class="fa fa-puzzle-piece"></span> <?php _e( 'Section', 'lifterlms' ); ?>
							</button>
						</li>

						<li>
							<button class="llms-tool-button llms-add-item" id="llms-new-lesson" data-model="lesson">
								<span class="fa fa-file"></span> <?php _e( 'New Lesson', 'lifterlms' ); ?>
							</button>
						</li>

						<li>
							<button class="llms-tool-button" id="llms-existing-lesson" data-model="lesson">
								<span class="fa fa-file-text"></span> <?php _e( 'Existing Lesson', 'lifterlms' ); ?>
							</button>
						</li>

					</ul>

					<h2 class="llms-tools-headline"><?php _e( 'Tools', 'lifterlms' ); ?></h2>

					<ul class="llms-tools-list llms-utilities">

						<li>
							<a class="llms-utility bulk-toggle" href="#llms-bulk-toggle" data-action="expand" id="llms-expand-all">
								<span class="fa fa-plus-circle"></span>
								<?php _e( 'Expand All', 'lifterlms' ); ?>
							</a>
						</li>

						<li>
							<a class="llms-utility bulk-toggle" href="#llms-bulk-toggle" data-action="collapse" id="llms-collapse-all">
								<span class="fa fa-minus-circle"></span>
								<?php _e( 'Collapse All', 'lifterlms' ); ?>
							</a>
						</li>

					</ul>


					<footer>
						<h5 class="save-status" data-status="complete" id="save-status">
							<span class="unsaved"><?php _e( 'You have unsaved changes', 'lifterlms' ); ?></span>
							<span class="saving"><i id="llms-spinner-el"></i><?php _e( 'Saving changes...', 'lifterlms' ); ?></span>
						</h5>
					</footer>

				</aside>

			</div>

			<?php self::templates( $course_id ); ?>

			<script>window.llms_builder = <?php echo json_encode( array(
				'course' => array(
					'id' => absint( $course_id ),
					'edit_url' => current_user_can( 'edit_course', $course_id ) ? get_edit_post_link( $course_id ) : '',
					'view_url' => get_permalink( $course_id ),
					'title' => get_the_title( $course_id ),
				),
				'tutorial' => self::get_tutorial_steps(),
			) ); ?></script>

			<?php do_action( 'llms_after_course_builder', $course_id ); ?>

			</div>

		</div>

		<?php
		self::handle_post_locking( $course_id );

	}

	/**
	 * Output underscore template HTML
	 * @param    int   $course_id   WP_Post ID of the course
	 * @return   void
	 * @since    3.13.0
	 * @version  3.14.4
	 */
	private static function templates( $course_id ) {

		$lesson_icons = array(
			'free' => array(
				'active' => 'data.is_free',
				'icon' => 'unlock',
				'text_default' => esc_attr__( 'Enrolled students only', 'lifterlms' ),
				'text_active' => esc_attr__( 'Publicly available', 'lifterlms' ),
			),
			'prerequisite' => array(
				'active' => 'data.prerequisite',
				'icon' => 'link',
				'text_default' => esc_attr__( 'No prerequisite', 'lifterlms' ),
				'text_active' => sprintf( esc_attr__( 'Prerequisite: %s', 'lifterlms' ), '{{ data.prerequisite.title }}' ),
			),
			'drip' => array(
				'active' => 'data.drip_method',
				'icon' => 'calendar',
				'text_default' => esc_attr__( 'No drip delay', 'lifterlms' ),
				'text_active' => '
					<# print( LLMS.l10n.translate( "Drip delay" ) + ": " ) #>
					<# if ( "date" === data.drip_method ) { print( data.date_available ) } #>
					<# if ( "start" === data.drip_method ) { print( data.days_before_available + " " + LLMS.l10n.translate( "days after course start date" ) ) } #>
					<# if ( "enrollment" === data.drip_method ) { print( data.days_before_available + " " + LLMS.l10n.translate( "days after enrollment" ) ) } #>
				',
			),
			'quiz' => array(
				'active' => 'data.quiz',
				'icon' => 'question-circle',
				'text_default' => esc_attr__( 'No quiz', 'lifterlms' ),
				'text_active' => sprintf( esc_attr__( 'Quiz: %s', 'lifterlms' ), '{{ data.quiz.title }}' ),
			),
			'content' => array(
				'active' => 'data.has_content',
				'icon' => 'file-text-o',
				'text_default' => esc_attr__( 'No content', 'lifterlms' ),
				'text_active' => esc_attr__( 'Has content', 'lifterlms' ),
			),
		);
		?>

		<script type="text/html" id="tmpl-llms-course-template">
			<h1 class="llms-headline">
				<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="{{{ data.title }}}" type="text">{{{ data.title }}}</span>
			</h1>
			<div class="llms-action-icons">
				<# if ( data.edit_url ) { #>
					<a class="llms-action-icon" href="{{{ data.edit_url }}}"><span class="fa fa-pencil"></span></a>
				<# } #>
				<a class="llms-action-icon" href="{{{ data.view_url }}}"><span class="fa fa-external-link"></span></a>
			</div>
		</script>

		<script type="text/html" id="tmpl-llms-section-template">
			<header class="llms-builder-header">
				<span class="llms-drag-utility drag-section"></span>
				<h2 class="llms-headline">
					<?php echo get_post_type_object( 'section' )->labels->singular_name; ?> {{{ data.order }}}:
					<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="{{{ data.title }}}" type="text">{{{ data.title }}}</span>
				</h2>

				<div class="llms-action-icons">

					<a class="llms-action-icon expand" data-title-default="<?php esc_attr_e( 'Expand section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-plus-circle"></span>
					</a>
					<a class="llms-action-icon collapse" data-title-default="<?php esc_attr_e( 'Collapse section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-minus-circle"></span>
					</a>

					<a class="llms-action-icon shift-up" data-title-default="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
						<span class="fa fa-caret-square-o-up"></span>
					</a>

					<a class="llms-action-icon shift-down" data-title-default="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
						<span class="fa fa-caret-square-o-down"></span>
					</a>

					<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
						<a class="llms-action-icon trash danger" data-title-default="<?php esc_attr_e( 'Delete Section', 'lifterlms' ); ?>" href="#llms-trash">
							<span class="fa fa-trash"></span>
						</a>
					<?php endif; ?>

				</div>

			</header>
			<ul class="llms-lessons"></ul>
		</script>

		<script type="text/html" id="tmpl-llms-builder-tutorial-template">

			<h2 class="llms-headline"><?php _e( 'Drop a section here to get started!', 'lifterlms' ); ?></h2>
			<div class="llms-tutorial-buttons">
				<a class="llms-button-primary large" href="#llms-start-tut" id="llms-start-tut">
					<?php _e( 'Show Me How', 'lifterlms' ); ?>
					<i class="fa fa-magic" aria-hidden="true"></i>
				</a>
				<a class="llms-button-secondary large" href="https://lifterlms.com/docs/using-course-builder/" target="_blank">
					<?php _e( 'Read the Docs', 'lifterlms' ); ?>
					<i class="fa fa-book" aria-hidden="true"></i>
				</a>
			</div>

		</script>

		<script type="text/html" id="tmpl-llms-lesson-template">
			<header class="llms-builder-header">
				<span class="llms-drag-utility drag-lesson"></span>
				<h3 class="llms-headline">
					<?php echo get_post_type_object( 'lesson' )->labels->singular_name; ?> {{{ data.order }}}:
					<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="{{{ data.title }}}" type="text">{{{ data.title }}}</span>
				</h3>

				<div class="llms-action-icons">

					<# if ( data.edit_url ) { #>
						<a class="llms-action-icon" data-title-default="<?php esc_attr_e( 'Edit lesson settings', 'lifterlms' ); ?>" href="{{{ data.edit_url }}}">
							<span class="fa fa-pencil"></span>
						</a>
					<# } #>
					<a class="llms-action-icon" data-title-default="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="{{{ data.view_url }}}">
						<span class="fa fa-external-link"></span>
					</a>

					<a class="llms-action-icon shift-up" data-title-default="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
						<span class="fa fa-caret-square-o-up"></span>
					</a>

					<a class="llms-action-icon shift-down" data-title-default="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
						<span class="fa fa-caret-square-o-down"></span>
					</a>

					<a class="llms-action-icon section-prev" data-title-default="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>" href="#llms-section-change">
						<span class="fa fa-arrow-circle-o-up"></span>
					</a>

					<a class="llms-action-icon section-next" data-title-default="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>" href="#llms-section-change">
						<span class="fa fa-arrow-circle-o-down"></span>
					</a>

					<a class="llms-action-icon detach danger" data-title-default="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach">
						<span class="fa fa-chain-broken"></span>
					</a>

					<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
						<a class="llms-action-icon trash danger" data-title-default="<?php esc_attr_e( 'Delete Lesson', 'lifterlms' ); ?>" href="#llms-trash">
							<span class="fa fa-trash"></span>
						</a>
					<?php endif; ?>

				</div>

			</header>

			<div class="llms-info-icons">
			<?php foreach ( $lesson_icons as $icon => $info ) : ?>
				<span class="llms-info-icon<# <?php echo $info['active']; ?> ? print( ' active' ) : print( '' ) #>" data-title-active="<?php echo $info['text_active']; ?>" data-title-default="<?php echo $info['text_default']; ?>">
					<i class="fa fa-<?php echo $info['icon']; ?>" aria-hidden="true"></i>
				</span>
			<?php endforeach; ?>
			</div>

		</script>

		<?php
	}

}
