<?php
/**
 * My Grades Template
 * @since    3.24.0
 * @version  3.24.0
 */
defined( 'ABSPATH' ) || exit;
llms_print_notices();
?>

<div class="llms-sd-section llms-sd-grades">

	<?php do_action( 'llms_student_dashboard_before_my_grades' ); ?>

	<table class="llms-table">
		<thead>
			<tr>
				<th><?php _e( 'Course', 'lifterlms' ); ?></a></th>
				<th><?php _e( 'Enrollment Date', 'lifterlms' ); ?></a></th>
				<th><?php _e( 'Progress', 'lifterlms' ); ?></th>
				<th><?php _e( 'Grade', 'lifterlms' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course ) : ?>
			<tr>
				<td><a href="<?php echo llms_get_endpoint_url( 'my-grades', $course->get( 'name' ) ); ?>"><?php echo $course->get( 'title' ); ?></a></td>
				<td><?php echo $student->get_enrollment_date( $course->get( 'id' ) ); ?></td>
				<td><?php echo llms_get_progress_bar_html( $student->get_progress( $course->get( 'id' ) ) ); ?></td>
				<td><?php
					$grade = $student->get_grade( $course->get( 'id' ) );
					echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '&ndash;'; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>

		<tfoot>
			<tr>
				<td class="llms-table-navigation" colspan="2">
					<?php if ( 1 !== $pagination['current'] || $pagination['max'] !== $pagination['current'] ) : ?>
					<nav class="llms-pagination">
					<?php echo paginate_links( array(
						'base'         => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
						'format'       => '?page=%#%',
						'total'        => $pagination['max'],
						'current'      => $pagination['current'],
						'prev_next'    => true,
						'prev_text'    => '« ' . __( 'Previous', 'lifterlms' ),
						'next_text'    => __( 'Next', 'lifterlms' ) . ' »',
						'type'         => 'list',
					) ); ?>
					</nav>
					<?php endif; ?>
				</td>
				<td class="llms-table-sort" colspan="2">
					<form action="<?php echo esc_url( llms_get_endpoint_url( 'my-grades' ) ); ?>" method="GET">
						<label for="llms-sd-table-sort"><?php _e( 'Sort: ', 'lifterlms' ); ?></label>
						<select name="sort" id="llms-sd-table-sort">
							<option value="date_desc" <?php selected( 'date_desc', $sort ); ?>><?php esc_attr_e( 'Enrollment Date (Most Recent)', 'lifterlms' ); ?></option>
							<option value="date_asc" <?php selected( 'date_asc', $sort ); ?>><?php esc_attr_e( 'Enrollment Date (Oldest)', 'lifterlms' ); ?></option>
							<option value="title_asc" <?php selected( 'title_asc', $sort ); ?>><?php esc_attr_e( 'Course Title (A-Z)', 'lifterlms' ); ?></option>
							<option value="title_desc" <?php selected( 'title_desc', $sort ); ?>><?php esc_attr_e( 'Course Title (Z-A)', 'lifterlms' ); ?></option>
						</select>
						<button class="llms-button-secondary small" type="submit"><?php _e( 'Update', 'lifterlms' ); ?></button>
					</form>
				</td>
		</tfoot>

	</table>

	<?php do_action( 'llms_student_dashboard_after_my_grades' ); ?>

</div>
