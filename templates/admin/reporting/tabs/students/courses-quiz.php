<?php
/**
 * Single Student View: Courses Tab: Quiz View
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$quiz = new LLMS_Quiz( $quiz_id );
?>

<h2 class="llms-stab-title">
	<?php echo get_the_title( $quiz_id ); ?>
	<a href="<?php echo esc_url( get_edit_post_link( $quiz_id ) ); ?>"><span class="dashicons dashicons-admin-links"></span></a>
</h2>

<?php if ( $attempts ) : ?>

	<div class="llms-widget-row">

		<div class="llms-widget-1-4">
			<div class="llms-widget alt">
				<p class="llms-label"><?php _e( 'Status', 'lifterlms' ); ?></p>
				<h2><?php $best_attempt['passed'] ? _e( 'Passed', 'lifterlms' ) : _e( 'Failed', 'lifterlms' ); ?></h2>
			</div>
		</div>

		<div class="llms-widget-1-4">
			<div class="llms-widget alt">
				<p class="llms-label"><?php _e( 'Best Grade', 'lifterlms' ); ?></p>
				<h2><?php echo $best_attempt['grade']; ?>%</h2>
			</div>
		</div>

		<div class="llms-widget-1-4">
			<div class="llms-widget alt">
				<p class="llms-label"><?php _e( '# of Attempts', 'lifterlms' ); ?></p>
				<h2><?php echo count( $attempts ); ?></h2>
			</div>
		</div>

		<div class="llms-widget-1-4">
			<div class="llms-widget alt">
				<p class="llms-label"><?php _e( 'Remaining Attempts', 'lifterlms' ); ?></p>
				<h2><?php echo $quiz->get_remaining_attempts_by_user( $student->get_id() ); ?></h2>
			</div>
		</div>

	</div>

	<section class="llms-collapsible-group llms-quiz-attempts" id="llms-quiz-attempts">

	<?php foreach ( $attempts as $attempt ) : ?>

	<div class="llms-collapsible llms-quiz-attempt">

		<header class="llms-collapsible-header">
			<h3 class="d-1of6"><?php printf( __( 'Attempt %d', 'lifterlms' ), $attempt['attempt'] ); ?></h3>

			<div class="d-1of6">
				<?php _e( 'Grade', 'lifterlms' ); ?>:
				<?php echo $attempt['grade']; ?>%
				(<?php $attempt['passed'] ? _e( 'Passed', 'lifterlms' ) : _e( 'Failed', 'lifterlms' ); ?>)
			</div>
			<div class="d-1of6">
				<?php $start = strtotime( $attempt['start_date'] ); ?>
				<?php $end = strtotime( $attempt['end_date'] ); ?>
				<?php _e( 'Time', 'lifterlms' ); ?>:
				<?php if ( $attempt['end_date'] ) : ?>
					<?php echo llms_get_date_diff( $start, $end ); ?>
				<?php else : ?>
					<?php _e( 'Incomplete', 'lifterlms' ); ?>
				<?php endif; ?>
			</div>
			<div class="d-1of6">
				<?php _e( 'Start', 'lifterlms' ); ?>:
				<?php echo date_i18n( 'm/d/y h:i a', $start ); ?>
			</div>

			<div class="d-1of6">
				<?php _e( 'End', 'lifterlms' ); ?>:
				<?php if ( $attempt['end_date'] ) : ?>
					<?php echo date_i18n( 'm/d/y h:i a', $end ); ?>
				<?php else : ?>
					<?php _e( 'Incomplete', 'lifterlms' ); ?>
				<?php endif; ?>
			</div>

			<div class="d-1of6 d-right">
				<a class="llms-del-quiz-attempt tooltip" data-attempt="<?php echo $attempt['attempt']; ?>" data-lesson="<?php echo $attempt['assoc_lesson']; ?>" data-quiz="<?php echo $attempt['id']; ?>" data-user="<?php echo $attempt['user_id']; ?>" href="#llms-delete-quiz-attempt" title="<?php _e( 'Delete Attempt', 'lifterlms' ); ?>"><span class="dashicons dashicons-trash"></span></a>
				<span class="dashicons dashicons-arrow-down"></span>
				<span class="dashicons dashicons-arrow-up"></span>
			</div>
		</header>

		<section class="llms-collapsible-body">
			<?php
				$table = new LLMS_Table_Questions();
				$table->get_results( array(
					'questions' => $attempt['questions'],
				) );
				echo $table->get_table_html();
			?>
		</section>
	</div>
	<?php endforeach; ?>
	</section>

<?php else : ?>

	<p><?php _e( 'Student has not taken this quiz yet.', 'lifterlms' ); ?></p>

<?php endif; ?>
