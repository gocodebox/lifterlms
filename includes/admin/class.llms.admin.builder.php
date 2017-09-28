<?php
/**
 * Course Builder
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Builder {

	/**
	 * A terrible Rest API for the course builder
	 * @shame    gimme a break pls
	 * @param    array     $request  $_REQUEST
	 * @return   array
	 * @since    [version]
	 * @version  [version]
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

				// reorder sectioes or lessons
				if ( 'collection' === $request['object_type'] && in_array( $request['data_type'], array( 'section', 'lesson' ) ) ) {

					foreach ( $request['models'] as $model ) {
						$object = llms_get_post( $model['id'] );
						$object->set( 'order', $model['order'] );
						// additionally save lessons parent
						if ( 'lesson' === $request['data_type'] ) {
							$object->set( 'parent_section', $model['section_id'] );
						}
					}
				} elseif ( 'model' === $request['object_type'] ) {

					$id = ( false === strpos( $request['model']['id'], '_temp_' ) ) ? absint( $request['model']['id'] ) : 'new';

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

						if ( 'new' === $id ) {
							$lesson->set( 'parent_course', $request['course_id'] );
							$lesson->set( 'parent_section', $request['model']['section_id'] );
							$lesson->set( 'order', $request['model']['order'] );
						} else {
							$lesson->set( 'title', $request['model']['title'] );
						}

						wp_send_json( self::get_lesson( $lesson->get( 'id' ), false, true ) );

					} elseif ( 'course' === $request['data_type'] ) {

						$course = new LLMS_Course( $id );
						$course->set( 'title', $request['model']['title'] );

					}
				}// End if().

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
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
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
	 * Retrieve Quiz data
	 * @param    int        $quiz_id            WP Post ID of a quiz
	 * @param    boolean    $include_questions  if true, includes question data
	 * @return   array
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
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
					<ul class="llms-sections" id="llms-sections"></ul>
				</section>

				<aside id="llms-builder-tools" class="llms-builder-tools">

					<h2 class="llms-tools-headline"><?php _e( 'Course Elements', 'lifterlms' ); ?></h2>

					<ul class="llms-tools-list llms-add-items">

						<li>
							<button class="llms-add-item" id="llms-new-section" data-model="section">
								<span class="fa fa-puzzle-piece"></span> <?php _e( 'Section', 'lifterlms' ); ?>
							</button>
						</li>

						<li>
							<button class="llms-add-item" id="llms-new-lesson" data-model="lesson">
								<span class="fa fa-file"></span> <?php _e( 'Lesson', 'lifterlms' ); ?>
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
				'id' => absint( $course_id ),
				'edit_url' => current_user_can( 'edit_course', $course_id ) ? get_edit_post_link( $course_id ) : '',
				'view_url' => get_permalink( $course_id ),
				'title' => get_the_title( $course_id ),
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
	 * @since    [version]
	 * @version  [version]
	 */
	private static function templates( $course_id ) {

		$lesson_icons = array(
			'free' => array(
				'active' => 'is_free',
				'icon' => 'unlock',
				'text_default' => esc_attr__( 'Enrolled students only', 'lifterlms' ),
				'text_active' => esc_attr__( 'Publicly available', 'lifterlms' ),
			),
			'prerequisite' => array(
				'active' => 'prerequisite',
				'icon' => 'link',
				'text_default' => esc_attr__( 'No prerequisite', 'lifterlms' ),
				'text_active' => sprintf( esc_attr__( 'Prerequisite: %s', 'lifterlms' ), '<%- prerequisite.title %>' ),
			),
			'drip' => array(
				'active' => 'drip_method',
				'icon' => 'calendar',
				'text_default' => esc_attr__( 'No drip delay', 'lifterlms' ),
				'text_active' => '
					<% print( LLMS.l10n.translate( "Drip delay" ) + ": " ) %>
					<% if ( "date" === drip_method ) { print( date_available ) } %>
					<% if ( "start" === drip_method ) { print( days_before_available + " " + LLMS.l10n.translate( "days after course start date" ) ) } %>
					<% if ( "enrollment" === drip_method ) { print( days_before_available + " " + LLMS.l10n.translate( "days after enrollment" ) ) } %>
				',
			),
			'quiz' => array(
				'active' => 'quiz',
				'icon' => 'question-circle',
				'text_default' => esc_attr__( 'No quiz', 'lifterlms' ),
				'text_active' => sprintf( esc_attr__( 'Quiz: %s', 'lifterlms' ), '<%- quiz.title %>' ),
			),
			'content' => array(
				'active' => 'has_content',
				'icon' => 'file-text-o',
				'text_default' => esc_attr__( 'No content', 'lifterlms' ),
				'text_active' => esc_attr__( 'Has content', 'lifterlms' ),
			),
		);

		?>

		<script type="text/template" id="llms-course-template">
			<h1 class="llms-headline">
				<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="<%= title %>" type="text"><%= title %></span>
			</h1>
			<div class="llms-action-icons">
				<% if ( edit_url ) { %>
					<a class="llms-action-icon" href="<%= edit_url %>"><span class="fa fa-pencil"></span></a>
				<% } %>
				<a class="llms-action-icon" href="<%= view_url %>"><span class="fa fa-external-link"></span></a>
			</div>
		</script>

		<script type="text/template" id="llms-section-template">
			<header class="llms-builder-header">
				<span class="llms-drag-utility drag-section"></span>
				<h2 class="llms-headline">
					<?php echo get_post_type_object( 'section' )->labels->singular_name; ?> <%= order %>:
					<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="<%= title %>" type="text"><%= title %></span>
				</h2>

				<div class="llms-action-icons">

					<a class="llms-action-icon expand" data-title-default="<?php esc_attr_e( 'Expand section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-plus-circle"></span>
					</a>
					<a class="llms-action-icon collapse" data-title-default="<?php esc_attr_e( 'Collapse section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-minus-circle"></span>
					</a>

					<% if ( 1 !== order ) { %>
						<a class="llms-action-icon shift-up" data-title-default="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
							<span class="fa fa-caret-square-o-up"></span>
						</a>
					<% } %>
					<% if ( this.model.collection && this.model.collection.length !== order ) { %>
						<a class="llms-action-icon shift-down" data-title-default="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
							<span class="fa fa-caret-square-o-down"></span>
						</a>
					<% } %>

					<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
						<a class="llms-action-icon trash" data-title-default="<?php esc_attr_e( 'Delete Section', 'lifterlms' ); ?>" href="#llms-trash">
							<span class="fa fa-trash"></span>
						</a>
					<?php endif; ?>

				</div>

			</header>
			<ul class="llms-lessons"></ul>
		</script>

		<script type="text/html" id="llms-lesson-template">
			<header class="llms-builder-header">
				<span class="llms-drag-utility drag-lesson"></span>
				<h3 class="llms-headline">
					<?php echo get_post_type_object( 'lesson' )->labels->singular_name; ?> <%= order %>:
					<span class="llms-input llms-editable-title" contenteditable="true" data-original-content="<%= title %>" type="text"><%= title %></span>
				</h3>

				<div class="llms-action-icons">

					<% if ( edit_url ) { %>
						<a class="llms-action-icon" data-title-default="<?php esc_attr_e( 'Edit lesson settings', 'lifterlms' ); ?>" href="<%= edit_url %>">
							<span class="fa fa-pencil"></span>
						</a>
					<% } %>
					<a class="llms-action-icon" data-title-default="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="<%= view_url %>">
						<span class="fa fa-external-link"></span>
					</a>

					<% if ( 1 !== order ) { %>
						<a class="llms-action-icon shift-up" data-title-default="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
							<span class="fa fa-caret-square-o-up"></span>
						</a>
					<% } %>
					<% if ( this.model.collection && this.model.collection.length !== order ) { %>
						<a class="llms-action-icon shift-down" data-title-default="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
							<span class="fa fa-caret-square-o-down"></span>
						</a>
					<% } %>


					<% if ( 1 !== this.model.get_section().get( 'order' ) ) { %>
						<a class="llms-action-icon section-prev" data-title-default="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>" href="#llms-section-change">
							<span class="fa fa-arrow-circle-o-up"></span>
						</a>
					<% } %>

					<% if ( ! this.model.get_section().is_last() ) { %>
						<a class="llms-action-icon section-next" data-title-default="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>" href="#llms-section-change">
							<span class="fa fa-arrow-circle-o-down"></span>
						</a>
					<% } %>

					<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
						<a class="llms-action-icon trash" data-title-default="<?php esc_attr_e( 'Delete Lesson', 'lifterlms' ); ?>" href="#llms-trash">
							<span class="fa fa-trash"></span>
						</a>
					<?php endif; ?>

				</div>

			</header>

			<div class="llms-info-icons">
			<?php foreach ( $lesson_icons as $icon => $info ) : ?>
				<span class="llms-info-icon<% <?php echo $info['active']; ?> ? print( ' active' ) : print( '' ) %>" data-title-active="<?php echo $info['text_active']; ?>" data-title-default="<?php echo $info['text_default']; ?>">
					<i class="fa fa-<?php echo $info['icon']; ?>" aria-hidden="true"></i>
				</span>
			<?php endforeach; ?>
			</div>

		</script>
		<?php
	}

}
