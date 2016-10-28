<?php
/**
 * Single Student View: Courses Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$courses = $student->get_courses();

$cols = array(

	'id' => __( 'ID', 'lifterlms' ),
	'name' => __( 'Name', 'lifterlms' ),
	'completion' => __( 'Completion', 'lifterlms' ),

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
