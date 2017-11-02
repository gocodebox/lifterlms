<?php
/**
 * Quiz Results Template
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$student = llms_get_student();
$attempt = isset( $_GET['attempt_key'] ) ? $student->quizzes()->get_attempt_by_key( $_GET['attempt_key'] ) : false;
$siblings = isset( $_GET['attempt_key'] ) ? $student->quizzes()->get_sibling_attempts_by_key( $_GET['attempt_key'] ) : false;
if ( ! $attempt || in_array( $attempt->get_status(), array( 'new', 'in-progress' ) ) ) {
	$show_result = false;
} else {
	$show_result = true;
	$quiz = $attempt->get_quiz();
	$donut_class = array( $attempt->get( 'passed' ) ? 'passing' : 'failing' );
}
?>

<div class="clear"></div>
<div class="llms-quiz-results">

	<?php if ( $show_result ) : ?>
		<h2 class="llms-quiz-results-title"><?php printf( __( 'Attempt #%d Results', 'lifterlms' ), $attempt->get( 'attempt' ) ); ?></h2>

		<aside class="llms-quiz-results-aside">
			<?php echo llms_get_donut( $attempt->get( 'grade' ), $attempt->l10n( 'passed' ), 'default', $donut_class ); ?>
		</aside>

		<section class="llms-quiz-results-main">
			<ul class="llms-quiz-meta-info">
				<li class="llms-quiz-meta-item"><?php printf( __( 'Status: %s', 'lifterlms' ), $attempt->l10n( 'passed' ) ); ?></li>
				<li class="llms-quiz-meta-item"><?php printf( __( 'Grade: %s', 'lifterlms' ), round( $attempt->get( 'grade' ), 2 ) . '%' ); ?></li>
				<li class="llms-quiz-meta-item"><?php printf( __( 'Correct Answers: %1$d / %2$d', 'lifterlms' ), $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'questions' ) ); ?></li>
				<li class="llms-quiz-meta-item"><?php printf( __( 'Completed: %s', 'lifterlms' ), $attempt->get_date( 'start' ) ); ?></li>
				<li class="llms-quiz-meta-item"><?php printf( __( 'Total time: %s', 'lifterlms' ), $attempt->get_time() ); ?></li>
				<?php if ( $quiz->show_quiz_results() ) : ?>
					<li class="llms-quiz-meta-item"><a class="view-summary" href="#"><?php _e( 'View Summary', 'lifterlms' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</section>

		<?php if ( $quiz->show_quiz_results() ) : ?>
			<?php llms_get_template( 'quiz/summary.php' ); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $siblings && count( $siblings ) > 1 ) : ?>
		<section class="llms-quiz-results-history">
			<h2 class="llms-quiz-results-title"><?php _e( 'View Previous Attempts', 'lifterlms' ); ?></h2>
			<select id="llms-quiz-attempt-select">
				<option value="">-- <?php _e( 'Select an Attempt', 'lifterlms' ); ?> --</option>
				<?php foreach ( $siblings as $sibling ) :
					$sibling = new LLMS_Quiz_Attempt( $sibling );
					if ( in_array( $sibling->get_status(), array( 'new', 'in-progress' ) ) ) {
						continue;
					} elseif ( $attempt && $attempt->get( 'attempt' ) == $sibling->get( 'attempt' ) ) {
						continue;
					}
					?>
					<option value="<?php echo esc_url( $sibling->get_permalink() ); ?>">
						<?php // translators: 1: attempt number; 2: grade percentage; 3: pass/fail text ?>
						<?php printf( __( 'Attempt #%1$d - %2$s (%3$s)', 'lifterlms' ), $sibling->get( 'attempt' ), round( $sibling->get( 'grade' ), 2 ) . '%', $sibling->l10n( 'passed' ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</section>
	<?php endif; ?>

</div>
