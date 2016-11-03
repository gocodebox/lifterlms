<?php
/**
 * Single Student View: Courses Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>

<?php if ( empty( $_GET['course_id'] ) ) : ?>

	<?php
		$courses = $student->get_courses();

		$cols = array(

			'id' => __( 'ID', 'lifterlms' ),
			'name' => __( 'Name', 'lifterlms' ),
			'grade' => __( 'Grade', 'lifterlms' ),
			'progress' => __( 'Progress', 'lifterlms' ),
			'completed' => __( 'Completed', 'lifterlms' ),

		);
	?>

	<table class="llms-table zebra" id="llms-students-courses-table">
		<thead>
			<tr>
			<?php foreach ( $cols as $name => $title ) : ?>
				<th class="<?php echo $name; ?>"><?php echo $title; ?></th>
			<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $courses['results'] as $cid ) : $course = new LLMS_Course( $cid ); ?>
				<tr>
					<?php foreach ( $cols as $name => $title ) : ?>
						<td class="<?php echo $name; ?>"><?php echo LLMS_Admin_Grade_Book::get_course_data( $course, $student, $name ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php else : ?>

	<?php $course = new LLMS_Course( intval( $_GET['course_id'] ) ); ?>

	<h3><a href="<?php echo esc_url( get_edit_post_link( $course->get( 'id' ) ) ); ?>"><?php echo $course->get( 'title' ); ?></a></h3>

	<p>
		<?php printf( __( 'Progress: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'progress' ) ); ?> |
		<?php printf( __( 'Grade: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'grade' ) ); ?> |
		<?php printf( __( 'Completed: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'completed' ) ); ?>
	</p>

	<?php
		$cols = array(

			'id' => __( 'ID', 'lifterlms' ),
			'name' => __( 'Lesson Name', 'lifterlms' ),
			'quiz' => __( 'Quiz', 'lifterlms' ),
			'grade' => __( 'Grade', 'lifterlms' ),
			'completion' => __( 'Completed', 'lifterlms' ),

		);
	?>

	<table class="llms-table zebra" id="llms-students-courses-table">
		<thead>
			<tr>
			<?php foreach ( $cols as $name => $title ) : ?>
				<th class="<?php echo $name; ?>"><?php echo $title; ?></th>
			<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>

			<?php foreach ( $course->get_lessons() as $lesson ) : ?>
				<tr>
					<?php foreach ( $cols as $name => $title ) : ?>
						<td class="<?php echo $name; ?>"><?php echo LLMS_Admin_Grade_Book::get_lesson_data( $lesson, $student, $name ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<footer class="llms-gb-footer">
		<a href="<?php echo esc_url( add_query_arg( 'student_id', $student->get_id(), admin_url( 'admin.php?page=llms-grade-book' ) ) ); ?>"><?php _e( 'Back to all courses', 'lifterlms' ); ?></a>
	</footer>

<?php endif; ?>

