<?php
/**
 * Single Student View: Courses Tab: Quiz View
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$quiz = new LLMS_Quiz( $quiz_id );
?>

<h3><a href="<?php echo esc_url( get_edit_post_link( $quiz_id ) ); ?>"><?php echo get_the_title( $quiz_id ); ?></a></h3>
<br>
<h4><?php _e( 'Quiz Summary', 'lifterlms' ); ?></h4>

<?php if ( $attempts ) : ?>

<table class="llms-table zebra">
	<thead>
		<tr>
			<th class="attempts"><?php _e( '# of Attempts', 'lifterlms' ); ?></th>
			<th class="grade"><?php _e( 'Best Grade', 'lifterlms' ); ?></th>
			<th class="remaining"><?php _e( 'Remaining Attempts', 'lifterlms' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="attempts"><?php echo count( $attempts ); ?></td>
			<td class="grade">
				<?php echo $best_attempt['grade']; ?>%
				(<?php $best_attempt['passed'] ? _e( 'Passed', 'lifterlms' ) : _e( 'Failed', 'lifterlms' ); ?>)
			</td>
			<td class="attempts"><?php echo $quiz->get_remaining_attempts_by_user( $student->get_id() ); ?></td>
		</tr>
	</tbody>
</table>
<br>
<h4><?php _e( 'All Attempts', 'lifterlms' ); ?></h4>


<?php foreach( $attempts as $attempt ) : ?>
<table class="llms-table zebra quiz-attempts">
	<thead>
		<tr>
			<th class="attempts"><?php _e( 'Attempt', 'lifterlms' ); ?></th>
			<th class="grade"><?php _e( 'Grade', 'lifterlms' ); ?></th>
			<th class="time"><?php _e( 'Time', 'lifterlms' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="attempts"><?php echo $attempt['attempt']; ?></td>
			<td class="grade">
				<?php echo $attempt['grade']; ?>%
				(<?php $attempt['passed'] ? _e( 'Passed', 'lifterlms' ) : _e( 'Failed', 'lifterlms' ); ?>)
			</td>
			<td class="time">
				<?php $start = strtotime( $attempt['start_date'] ); ?>
				<?php $end = strtotime( $attempt['end_date'] ); ?>
				<?php echo llms_get_date_diff( $start, $end ); ?>
				(<?php echo date_i18n( 'm/d/y h:i:sa', $start ); ?> &ndash; <?php echo date_i18n( 'm/d/y h:i:sa', $end ); ?>)
			</td>
		</tr>
		<tr>
			<td class="questions-table" colspan="3">
				<?php $table = new LLMS_AGBT_Questions( $attempt['questions'] ); echo $table->get_table_html(); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php endforeach; ?>

<?php else : ?>

	<p><?php _e( 'Student has not taken this quiz yet.', 'lifterlms' ); ?></p>

<?php endif; ?>
