<?php
/**
 * Students Table
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>
<table class="llms-table zebra" id="llms-students-table">
	<thead>
		<tr>
			<?php foreach ( $cols as $class => $name ) : ?>
				<th class="<?php echo $class; ?>"><?php echo $name; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php if ( $students->get_results() ) : ?>
			<?php foreach ( $students->get_results() as $student ) : $student = new LLMS_Student( $student->ID ); ?>
				<tr>
					<?php foreach ( $cols as $class => $name ) : ?>
						<td class="<?php echo $class; ?>"><?php echo LLMS_Admin_Grade_Book::get_student_data( $student, $class ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr><td colspan="<?php echo count( $cols ); ?>"><em><?php _e( 'No students found', 'lifterlms' ); ?></em></td></tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="<?php echo count( $cols ); ?>">
				<?php if ( LLMS_Admin_Grade_Book::get_current_page() > 1 ) : ?>
					<a class="button" href="<?php echo LLMS_Admin_Grade_Book::get_prev_page_url(); ?>"><?php _e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>
				<?php if ( LLMS_Admin_Grade_Book::get_current_page() < $students->get( 'max_pages' ) ) : ?>
					<a class="button" href="<?php echo LLMS_Admin_Grade_Book::get_next_page_url(); ?>"><?php _e( 'Next', 'lifterlms' ); ?></a>
				<?php endif; ?>
			</th>
		</tr>
	</tfoot>
</table>
