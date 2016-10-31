<?php
/**
 * Students Table
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

if ( isset( $_GET['order'] ) ) {
	$order = ( 'ASC' === $_GET['order'] ) ? 'DESC' : 'ASC';
} else {
	$order = 'ASC';
}

$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
?>
<table class="llms-table zebra" id="llms-students-table">
	<thead>
		<tr>
			<?php foreach ( $cols as $id => $data ) : ?>
				<th class="<?php echo $id; ?>">
					<?php if ( $data['sortable'] ) : ?>
						<a class="<?php echo $order; ?><?php echo ( $orderby === $id ) ? ' active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'order' => $order, 'orderby' => $id ) ) ); ?>">
							<?php echo $data['title']; ?>
							<span class="dashicons dashicons-arrow-up asc"></span>
							<span class="dashicons dashicons-arrow-down desc"></span>
						</a>
					<?php else: ?>
						<?php echo $data['title']; ?>
					<?php endif; ?>
				</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php if ( $students->get_results() ) : ?>
			<?php foreach ( $students->get_results() as $student ) : $student = new LLMS_Student( $student->ID ); ?>
				<tr>
					<?php foreach ( array_keys( $cols ) as $id ) : ?>
						<td class="<?php echo $id; ?>"><?php echo LLMS_Admin_Grade_Book::get_student_data( $student, $id ); ?></td>
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
