<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;
$user_id = get_current_user_id();


$quiz_data = get_user_meta( get_current_user_id(), 'llms_quiz_data', true );
<<<<<<< HEAD
if ( $quiz_data ) {
$quiz->is_passing_score( $user_id );
$passing_percent = $quiz->get_passing_percent();
var_dump( $quiz_data );

=======
//var_dump( $quiz_data );
$start_date = $quiz->get_start_date( $user_id );
>>>>>>> hovo
$grade = $quiz->get_user_grade( $user_id );

$is_passing_score = $quiz->is_passing_score( $user_id );
$best_grade = $quiz->get_best_grade( $user_id );
$time = $quiz->get_total_time( $user_id );
//get quiz score
//

?>
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
	  
	  <div class="llms-progress-circle-count"><?php printf( __( '%s%' ), $grade ); ?></div>
	</div>

	<div class="llms-quiz-result-details">
		<ul>
		<li>
			<?php printf( __( 'Best Grade: %d%%', 'lifterlms' ), $grade ); ?></li>
		<h6><?php echo $start_date ?></h6>
		<li>Time Spent</li>
		</ul>
		<ul>
		<li>Last Attempt</li>
		</li>Oct 22, 2014</ul>
		<li>Time Spent</li>
		</ul>
	</div>
	<input type="hidden" id="llms-grade-value" name="llms_grade" value="<?php echo $grade; ?>" />

</div>




<div class="clear"></div>
<div class="llms-template-wrapper">
	<p class="llms-content-block">
		Your Score: <?php echo $grade ?>%
		<?php 
		if ( $is_passing_score ) {
			_e('Passed', 'lifterlms');
		}
		else {
			_e('Failed', 'lifterlms');
		}
		?>

	</p>
	Your best grade: <?php echo $best_grade; ?>
	Total time: <?php echo $time ?>
</div>
<?php } ?>





