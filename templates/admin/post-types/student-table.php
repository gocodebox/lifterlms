<?php
/**
 * Students Metabox on admin panel
 * @since    3.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_admin() ) { exit; }
?>
<table class="llms-table zebra" id="llms-students-table">
	<thead>
		<tr>
			<th><?php _e( 'Name', 'lifterlms' ); ?></th>
			<th><?php _e( 'Status', 'lifterlms' ); ?></th>
			<th><?php _e( 'Enrollment Date', 'lifterlms' ); ?></th>
			<?php if ( 'course' === get_post_type( $post_id ) ) : ?>
				<th><?php _e( 'Progress', 'lifterlms' ); ?></th>
				<!-- <th><?php _e( 'Grade', 'lifterlms' ); ?></th> -->
				<th><?php _e( 'Last Completed Lesson', 'lifterlms' ); ?></th>
			<?php endif; ?>
			<th><?php _e( 'Enrollment Trigger', 'lifterlms' ); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php if ( $students['students'] ) : ?>
			<?php foreach ( $students['students'] as $sid ) : ?>
				<?php llms_get_template( 'admin/post-types/student-row.php', array(
					'post_id' => $post_id,
					'student' => new LLMS_Student( $sid ),
				) ); ?>
			<?php endforeach; ?>
		<?php else : ?>
			<tr><td colspan="7"><em><?php _e( 'No students found', 'lifterlms' ); ?></em></td></tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="7">
				<?php if ( $students['page'] > 1 ) : ?>
					<a class="button" href="<?php echo add_query_arg( 'llms-students', ( $students['page'] - 1 ), get_edit_post_link( $post_id ) ); ?>#lifterlms-students"><?php _e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>
				<?php if ( $students['more'] ) : ?>
					<a class="button" href="<?php echo add_query_arg( 'llms-students', ( $students['page'] + 1 ), get_edit_post_link( $post_id ) ); ?>#lifterlms-students"><?php _e( 'Next', 'lifterlms' ); ?></a>
				<?php endif; ?>
			</th>
		</tr>
	</tfoot>
</table>
