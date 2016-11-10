<?php
/**
 * Single Student View: Courses Tab: Single Course View
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$table = new LLMS_Table_Student_Course();
$table->get_results( array(
	'course_id' => $_GET['course_id'],
	'student' => $student,
) );
echo $table->get_table_html();
return;
?>



<p>
	<?php printf( __( 'Progress: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'progress' ) ); ?> |
	<?php printf( __( 'Grade: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'grade' ) ); ?> |
	<?php printf( __( 'Completed: %s', 'lifterlms' ), LLMS_Admin_Grade_Book::get_course_data( $course, $student, 'completed' ) ); ?>
</p>
