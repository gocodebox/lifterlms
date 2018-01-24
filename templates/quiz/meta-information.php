<?php
/**
 * Single Quiz: Meta Information
 * @since    3.9.0
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;

$quiz = llms_get_post( $post );
$passing_percent = $quiz->get_passing_percent();
?>

<h2 class="llms-quiz-meta-title"><?php _e( 'Quiz Information', 'lifterlms' ); ?></h2>
<ul class="llms-quiz-meta-info">
	<?php if ( $passing_percent ) : ?>
	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Minimum Passing Grade: %s', 'lifterlms' ), '<span class="llms-pass-perc">' . $passing_percent . '%</span>' ); ?>
	</li>
	<?php endif; ?>

	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Remaining Attempts: %s', 'lifterlms' ), '<span class="llms-attempts">' . $quiz->get_remaining_attempts_by_user( get_current_user_id() ) . '</span>' ); ?>
	</li>

	<?php if ( $quiz->has_time_limit() ) : ?>
	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Time Limit: %s', 'lifterlms' ), '<span class="llms-time-limit">' . $quiz->get_time_limit_string() . '</span>' ); ?>
	</li>
	<?php endif; ?>
</ul>
