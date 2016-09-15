<?php
/**
 * Template for a single row in the table inside the Students Metabox on admin panel
 * @todo     add grades to table
 * @since    3.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_admin() ) { exit; }

$last_id = $student->get_last_completed_lesson( $post_id );
$trigger = $student->get_enrollment_trigger( $post_id );
$trigger_id = $student->get_enrollment_trigger_id( $post_id );
?>

<tr id="llms-student-id-<?php echo $student->get_id(); ?>">
	<td><a href="<?php echo get_edit_user_link( $student->get_id() ) ?>"><?php echo llms_trim_string( $student->display_name, 50 ); ?></a> &lt;<a href="mailto:<?php echo $student->user_email; ?>"><?php echo $student->user_email; ?></a>&gt;</td>
	<td><?php echo llms_get_enrollment_status_name( $student->get_enrollment_status( $post_id ) ); ?></td>
	<td><?php echo $student->get_enrollment_date( $post_id, 'enrolled', 'M d, Y' ); ?></td>
	<?php if ( 'course' === get_post_type( $post_id ) ) : ?>
		<td><?php echo $student->get_progress( $post_id, 'course' ); ?>%</td>
		<!-- <td><?php echo $student->get; _e( 'Grade', 'lifterlms' ); ?></td> -->
		<td>
			<?php if ( $last_id ) : ?>
				<a href="<?php echo get_edit_post_link( $last_id ); ?>"><?php echo llms_trim_string( get_the_title( $last_id ), 50 ); ?></a>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
	<?php endif; ?>
	<td>
		<?php if ( $trigger && false !== strpos( $trigger, 'order_' ) ) : ?>
			<a href="<?php echo get_edit_post_link( $trigger_id ); ?>"><?php printf( __( 'Order #%d', 'lifterlms' ), $trigger_id ); ?></a>
		<?php else : ?>
			<?php echo $trigger; ?>
		<?php endif; ?>
	</td>
	<td>
		<?php if ( $student->is_enrolled( $post_id ) ) : ?>
			<?php if ( ! $trigger_id ) : ?>
				<a class="llms-action-icon llms-remove-student" data-id="<?php echo $student->get_id(); ?>" href="#llms-student-remove"><span class="tooltip" title="<?php _e( 'Cancel Enrollment', 'lifterlms' ); ?>"><span class="dashicons dashicons-no"></span></span></a>
			<?php else : ?>
				<a class="llms-action-icon" href="<?php echo get_edit_post_link( $trigger_id ); ?>" target="_blank"><span class="tooltip" title="<?php _e( 'Visit the triggering order to manage this student\'s enrollment', 'lifterlms' ); ?>"><span class="dashicons dashicons-external"></span></span></a>
			<?php endif; ?>
		<?php else : ?>
			<a class="llms-action-icon llms-add-student" data-id="<?php echo $student->get_id(); ?>" href="#llms-student-remove"><span class="tooltip" title="<?php _e( 'Reactivate Enrollment', 'lifterlms' ); ?>"><span class="dashicons dashicons-update"></span></span></a>
		<?php endif; ?>
	</td>
</tr>
