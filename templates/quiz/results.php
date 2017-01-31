<?php
/**
 * Quiz Results Template
 * @since    1.0.0
 * @version  3.2.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;
$user_id = get_current_user_id();

$quiz_data = get_user_meta( $user_id, 'llms_quiz_data', true );
$quiz_session = LLMS()->session->get( 'llms_quiz' );

if ( $quiz->get_total_attempts_by_user( $user_id ) ) {

	$grade = $quiz->get_user_grade( $user_id );
	$quiz->is_passing_score( $user_id, $grade );
	$passing_percent = $quiz->get_passing_percent();

	$start_date = $quiz->get_start_date( $user_id );


	$is_passing_score = $quiz->is_passing_score( $user_id, $grade );
	$best_grade = $quiz->get_best_grade( $user_id );
	$time = $quiz->get_total_time( $user_id );
	$start_date = date_i18n( 'M d, Y', strtotime( $quiz->get_start_date( $user_id ) ) );

	$best = $quiz->get_best_quiz_attempt( $user_id );
	$best_time = $quiz->get_total_time( $user_id, $best );
	$best_passing = $quiz->is_passing_score( $user_id, $best );
	?>

	<div class="clear"></div>
	<div class="llms-template-wrapper">
		<div class="llms-quiz-results">
		<h3><?php _e( 'Quiz Results', 'lifterlms' ); ?></h3>

			<?php
			//determine if grade, best grade or none should be shown.
			if (isset( $grade ) && isset( $best_grade )) :
			 	$graph_grade = empty( $grade ) ? $best_grade : $grade;
			?>
				<input type="hidden" id="llms-grade-value" name="llms_grade" value="<?php echo $graph_grade; ?>" />
				<div class="llms-progress-circle">
				  <svg>
			      <g>
			         <circle cx="-40" cy="40" r="68" class="llms-background-circle" transform="translate(50,50) rotate(-90)"  />
			      </g>
				    <g>
				      <circle cx="-40" cy="40" r="68" class="llms-animated-circle" transform="translate(50,50) rotate(-90)"  />
				    </g>
				    <g>
				     <circle cx="40" cy="40" r="63" transform="translate(50,50)"  />
				    </g>
				  </svg>

				  <div class="llms-progress-circle-count"><?php echo $graph_grade; ?>%</div>
				</div>

			<?php endif; ?>

			<div class="llms-quiz-result-details">

				<?php //if ($grade) : ?>
				<ul>
					<li>
						<h4><?php printf( __( 'Your Score: %s', 'lifterlms' ), $grade ); ?>%</h4>
						<h5 class="llms-content-block">
							<?php
							if ( $is_passing_score ) {
								echo apply_filters( 'lifterlms_quiz_passed_text', __( 'Passed', 'lifterlms' ) );
							} else {
								echo apply_filters( 'lifterlms_quiz_failed_text', __( 'Failed', 'lifterlms' ) );
							}
							?>
						</h5>
						<h6><?php printf( __( '%1$d / %2$d correct answers', 'lifterlms' ), $quiz->get_correct_answers_count( $user_id ), $quiz->get_question_count() ); ?></h6>
						<h6><?php printf( __( 'Date: <span class="llms_content_block">%s</span>', 'lifterlms' ), $start_date ); ?></h6>
						<h6><?php printf( __( 'Total time: %s', 'lifterlms' ), $time ); ?></h6>

						<?php if ($quiz->show_quiz_results()) { ?>
							<a class="view-summary"><?php _e( 'View Summary', 'lifterlms' ); ?></a>
						<?php } ?>

					</li>
					</li>
				</ul>
				<?php //endif; ?>

				<?php //if ($best_grade ) ) : ?>
				<ul>
					<li>
						<h4><?php printf( __( 'Best Score: %1$d%', 'lifterlms' ), $best_grade ); ?>%</h4>
						<h5>
							<?php
							if ( $best_passing ) {
								echo apply_filters( 'lifterlms_quiz_passed_text', __( 'Passed', 'lifterlms' ) );
							} else {
								echo apply_filters( 'lifterlms_quiz_failed_text', __( 'Failed', 'lifterlms' ) );
							}
							?>
						</h5>
						<h6><?php printf( __( '%1$d / %1$d correct answers', 'lifterlms' ), $quiz->get_correct_answers_count( $user_id, $best ), $quiz->get_question_count() ); ?></h6>
						<h6><?php printf( __( 'Date: <span class="llms_content_block">%s</span>', 'lifterlms' ), $start_date ); ?></h6>
						<h6><?php printf( __( 'Total time: %s', 'lifterlms' ), $best_time ); ?></h6>
					</li>
				</ul>
				<?php //endif; ?>

			</div>

		</div>
	</div>
<?php } ?>
