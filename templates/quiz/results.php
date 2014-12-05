<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;
$user_id = get_current_user_id();

$passing_percent = $quiz->get_passing_percent();
$quiz_data = get_user_meta( get_current_user_id(), 'llms_quiz_data', true );
//var_dump( $quiz_data );

$grade = $quiz->get_user_grade( $user_id );
$is_passing_score = $quiz->is_passing_score( $user_id );

//get quiz score
?>
THIS SHOULD SHOW UP NOW!
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
</div>





