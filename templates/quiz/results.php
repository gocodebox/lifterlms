<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;
$user_id = get_current_user_id();


$quiz_data = get_user_meta( get_current_user_id(), 'llms_quiz_data', true );

if ( $quiz_data ) {
$quiz->is_passing_score( $user_id );
$passing_percent = $quiz->get_passing_percent();

$start_date = $quiz->get_start_date( $user_id );

$grade = $quiz->get_user_grade( $user_id );

$is_passing_score = $quiz->is_passing_score( $user_id );
$best_grade = $quiz->get_best_grade( $user_id );
$time = $quiz->get_total_time( $user_id );

$start_date = date('M d, Y', strtotime( $quiz->get_start_date( $user_id ) ) );

$best = $quiz->get_best_quiz_attempt( $user_id );
$best_time = $quiz->get_total_time( $user_id, $best );
?>

<div class="clear"></div>
<div class="llms-template-wrapper">
	<div class="llms-quiz-results">
	<h3>Quiz Results</h3>

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
		  
		  <div class="llms-progress-circle-count"><?php printf( __( '%s%%' ), $grade ); ?></div>
		</div>

		<div class="llms-quiz-result-details">
			<ul>
				<li>
					<h4><?php printf( __( 'Your Score: %d%%', 'lifterlms' ), $grade ); ?></h4>
					<h5 class="llms-content-block">
						<?php 
						if ( $is_passing_score ) {
							_e('Passed', 'lifterlms');
						}
						else {
							_e('Failed', 'lifterlms');
						}
						?>
					</h5>
					<h6><?php printf( __( '%d / %d correct answers', 'lifterlms' ), $quiz->get_correct_answers_count( $user_id ), $quiz->get_question_count() ); ?></h6>
					<h6><?php printf( __( 'Date: <span class="llms_content_block">%s</span>', 'lifterlms' ), $start_date ); ?></h6>
					<h6><?php printf( __( 'Total time: %s', 'lifterlms' ), $time ); ?></h6>
				</li>
			</ul>
			<ul>
				<li>
					<h4><?php printf( __( 'Best Score: %d%%', 'lifterlms' ), $best_grade ); ?></h4>
					<h5>
						<?php 
						if ( $is_passing_score ) {
							_e('Passed', 'lifterlms');
						}
						else {
							_e('Failed', 'lifterlms');
						}
						?>
					</h5>
					<h6><?php printf( __( '%d / %d correct answers', 'lifterlms' ), $quiz->get_correct_answers_count( $user_id, $best ), $quiz->get_question_count() ); ?></h6>
					<h6><?php printf( __( 'Date: <span class="llms_content_block">%s</span>', 'lifterlms' ), $start_date ); ?></h6>
					<h6><?php printf( __( 'Total time: %s', 'lifterlms' ), $best_time ); ?></h6>
				</li>
			</ul>
		</div>
		<input type="hidden" id="llms-grade-value" name="llms_grade" value="<?php echo $grade; ?>" />

	</div>
</div>
<?php } ?>





