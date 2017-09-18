<?php
/**
 * Course Builder
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Course_Builder {

	private static function get_settings( $setting = null ) {

		$settings = apply_filters( 'llms_course_builder_get_settings', array(
			'initial_sections' => 5,
			'initial_lessons' => 3,
		) );

		if ( $setting && isset( $settings[ $setting ] ) ) {
			return $settings[ $setting ];
		}

		return $settings;

	}



	public static function handle_ajax( $request ) {

		switch ( $request['method'] ) {

			case 'load_item':

				$data = array();
				$type = null;

				switch ( get_post_type( $request['id'] ) ) {
					case 'section':
						$data = self::get_section( $request['id'], true );
						$type = 'section';
					break;
				}

				return array(
					'item' => $data,
					'item_type' => $type
				);

			break;

			case 'load_sections' :



			break;

			case 'save_edits':

				$obj = llms_get_post( $request['id'] );
				$obj->set( $request['field'], $request['value'] );

				return array(
					'value' => $obj->get( $request['field'] ),
				);

			break;

		}

		return array();
	}

	private static function get_course( $course_id ) {

		$threshold = self::get_settings( 'initial_sections' );

		$course = llms_get_post( $course_id );
		$sections = array();

		$i = 1;
		foreach ( $course->get_sections( 'ids' ) as $section_id ) {

			if ( $i <= $threshold ) {

				$section_data = self::get_section( $section_id, true );

			} else {

				$section_data = array(
					'id' => $section_id,
				);

			}

			array_push( $sections, $section_data );

			$i++;
		}

		$data = array(
			'id' => $course->get( 'id' ),
			'title' => $course->get( 'title' ),
			'sections' => $sections,
		);

		return $data;

	}

	public static function get_lesson( $lesson_id, $include_quizzes = false, $include_meta = true ) {

		$lesson = llms_get_post( $lesson_id );

		$data = array(
			'id' => $lesson->get( 'id' ),
			'title' => $lesson->get( 'title' ),
			'order' => $lesson->get( 'order' ),
		);

		if ( $include_meta ) {
			$data = array_merge( $data, array(
				'is_free' => $lesson->is_free(),
				'prerequisite' => $lesson->has_prerequisite() ? self::get_lesson( $lesson->get( 'prerequisite' ), false, false ) : false,
				'drip_method' => $lesson->get( 'drip_method' ),
				'days_before_available' => $lesson->get( 'days_before_available' ),
				'date_available' => $lesson->get( 'date_available' ),
				'quiz' => $lesson->get( 'assigned_quiz' ),
				'has_content' => $lesson->get( 'content' ) ? true : false,
			) );
		}

		if ( $include_quizzes ) {
		}

		return $data;

	}

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
	 * Output the page content
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public static function output() {

		// if ( current_user_can( 'edit_course' ) ) {

		// }

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : null;
		if ( $course_id && 'course' !== get_post_type( $course_id ) ) {
			$course_id = null;
		}
		$page_title = $course_id ? get_the_title( $course_id ) : __( 'Error', 'lifterlms' );
		?>

		<div class="wrap lifterlms llms-course-builder">

			<h1><?php echo $page_title; ?></h1>

			<?php do_action( 'llms_before_course_builder', $course_id ); ?>

			<?php if ( ! $course_id ) : ?>
				<p><?php _e( 'Invalid course ID', 'lifterlms' ); ?>
			<?php else: ?>

				<div class="llms-builder-main">

					<section class="llms-course-syllabus llms-course" id="llms-course-syllabus">
						<?php // self::output_course( $course_id ); ?>
						<ul class="llms-sections" id="llms-sections"></ul>

						<footer>

							<button class="llms-button-secondary small" id="llms-builder-load-sections" data-type="sections"><?php _e( 'Load more', 'lifterlms' ); ?></button>

						</footer>

					</section>

					<aside class="llms-builder-tools">

						<a href="#llms-toggle-all" data-action="open"><?php _e( 'Open All', 'lifterlms' ); ?></a>
						<a href="#llms-toggle-all" data-action="close"><?php _e( 'Close All', 'lifterlms' ); ?></a>

						<footer>
							<h6 class="save-status" id="save-status">
								<span class="unsaved"><?php _e( 'You have unsaved changes', 'lifterlms' ); ?></span>
								<span class="saving"><i id="llms-spinner-el"></i><?php _e( 'Saving changes...', 'lifterlms' ); ?></span>
							</h6>
							<button class="llms-button-primary full" disabled="disabled" type="button"><?php _e( 'Save', 'lifterlms' ); ?></button>
						</footer>

					</aside>

				</div>

				<?php self::templates(); ?>

				<script id="llms-course-object">window.llms_course = <?php echo json_encode( self::get_course( $course_id ) ); ?></script>

			<?php endif; ?>

			<?php do_action( 'llms_after_course_builder', $course_id ); ?>

		</div>

		<?php

	}

	private static function output_course( $course_id ) {

		$course = llms_get_post( $course_id );

		echo '<ul class="llms-sections">';
		foreach ( $course->get_sections() as $i => $section ) {
			self::output_part( $section, $i + 1, false );
		}
		echo '</ul>';

	}

	private static function output_info_icons() {

		$icons = array(
			'free' => array(
				'active' => false,
				'icon' => 'usd',
				'text' => esc_attr__( 'Enrollment required', 'lifterlms' ),
			),
			'prerequisite' => array(
				'active' => false,
				'icon' => 'lock',
				'text' => esc_attr__( 'No prerequisite', 'lifterlms' ),
			),
			'drip' => array(
				'active' => false,
				'icon' => 'calendar',
				'text' => esc_attr__( 'No drip delay', 'lifterlms' ),
			),
			'quiz' => array(
				'active' => false,
				'icon' => 'question-circle',
				'text' => esc_attr__( 'No quiz', 'lifterlms' ),
			),
			'content' => array(
				'active' => false,
				'icon' => 'file-text-o',
				'text' => esc_attr__( 'No content', 'lifterlms' ),
			),
		);
		?>
		<div class="llms-info-icons">
			<?php foreach ( $icons as $item => $data ) : ?>
				<span class="<?php printf( 'llms-info-icon tooltip info--%1$s', $item ); ?>" title="<?php echo esc_attr( $data['text'] ); ?>">
					<i class="fa <?php printf( 'fa-%s', $data['icon'] ); ?>" aria-hidden="true"></i>
				</span>
			<?php endforeach; ?>
		</div>
		<?php
	}

	private static function output_part( $obj, $number, $expand = false ) {

		if ( is_numeric( $obj ) ) {
			$obj = llms_get_post( $obj );
		}

		$type = $obj->get( 'type' );
		?>

		<li class="<?php printf( 'llms-%s', $type ); ?>" data-id="<?php echo $obj->get( 'id' ); ?>" data-order="<?php echo $number; ?>" id="<?php printf( 'llms-%1$s-%2$d', $type, $obj->get( 'id' ) ); ?>">

			<header>

				<span class="llms-drag-utility <?php printf( 'drag-%s', $type ); ?>"></span>

				<h2><?php printf( '%1$s %2$d: <span class="llms-inline-edit-wrap" data-llms-editable="title">%3$s</span>', $obj->get_post_type_label(), $number, $obj->get( 'title' ) ); ?></h2>

				<div class="llms-action-icons">

					<?php if ( 'section' === $type ) : ?>
						<a class="llms-action-icon collapse" href="#llms-toggle">
							<span class="fa fa-caret-up"></span>
						</a>

						<a class="llms-action-icon expand" href="#llms-toggle">
							<span class="fa fa-caret-down"></span>
						</a>
					<?php endif; ?>

				</div>
			</header>

			<?php if ( 'lesson' === $type ) : ?>
				<?php self::output_info_icons( $obj ); ?>
			<?php endif; ?>

			<?php if ( 'section' === $type ) : ?>
				<ul class="llms-lessons">
				<?php if ( $expand ) :
					$lessons = $obj->get_lessons(); ?>
					<?php if ( $lessons ) : ?>
						<?php foreach ( $lessons as $i => $lesson ) : ?>
							<?php self::output_part( $lesson, $i + 1 ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
				</ul>
			<?php endif; ?>

		</li>

		<?php
	}


	private static function templates() {
		?>
		<script type="text/html" id="tmpl-llms-section-template">
			<li class="llms-builder-item llms-section" data-id="{{data.id}}" data-loaded="no" data-order="{{data.order}}" id="llms-section-{{data.id}}">
				<header class="llms-builder-header">

					<span class="llms-drag-utility drag-section"></span>

					<h2><?php printf( '%s {{data.order}}: <span class="llms-inline-edit-wrap" data-llms-editable="title">{{data.title}}</span>', get_post_type_object( 'section' )->labels->singular_name ); ?></h2>

					<div class="llms-action-icons">
						<a class="llms-action-icon collapse" href="#llms-toggle"><span class="fa fa-caret-up"></span></a>
						<a class="llms-action-icon expand" href="#llms-toggle"><span class="fa fa-caret-down"></span></a>
					</div>
				</header>
				<ul class="llms-lessons"></ul>
			</li>
		</script>
		<script type="text/html" id="tmpl-llms-lesson-template">
			<li class="llms-builder-item llms-lesson" data-id="{{data.id}}" data-order="{{data.order}}" id="llms-lesson-{{data.id}}">
				<header class="llms-builder-header">

					<span class="llms-drag-utility drag-lesson"></span>

					<h2><?php printf( '%s {{data.order}}: <span class="llms-inline-edit-wrap" data-llms-editable="title">{{data.title}}</span>', get_post_type_object( 'lesson' )->labels->singular_name ); ?></h2>
<!-- 								<div class="llms-action-icons">
						<a class="llms-action-icon collapse" href="#llms-toggle"><span class="fa fa-caret-up"></span></a>
						<a class="llms-action-icon expand" href="#llms-toggle"><span class="fa fa-caret-down"></span></a>
					</div> -->
				</header>
				<?php self::output_info_icons(); ?>
			</li>
		</script>
		<?php
	}

}
