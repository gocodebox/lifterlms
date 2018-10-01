<?php
/**
 * My Grades Template
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;
llms_print_notices();
?>

<div class="llms-sd-section llms-sd-grades">

	<?php do_action( 'llms_student_dashboard_before_my_grades' ); ?>

	<table class="llms-table">
		<thead>
			<tr>
				<th><?php _e( 'Course', 'lifterlms' ); ?></th>
				<th><?php _e( 'Progress', 'lifterlms' ); ?></th>
				<th><?php _e( 'Grade', 'lifterlms' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course ) : ?>
			<tr>
				<td><a href="<?php echo llms_get_endpoint_url( 'my-grades', $course->get( 'name' ) ); ?>"><?php echo $course->get( 'title' ); ?></a></td>
				<td><?php echo llms_get_progress_bar_html( $student->get_progress( $course->get( 'id' ) ) ); ?></td>
				<td><?php
					$grade = $student->get_grade( $course->get( 'id' ) );
					echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '&ndash;'; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>

	</table>

	<?php do_action( 'llms_student_dashboard_after_my_grades' ); ?>

</div>
