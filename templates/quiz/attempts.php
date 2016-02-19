<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;
$user_id = get_current_user_id();
//get total attempts
//$attempts_left = $quiz->get_total_allowed_attempts();
$attempts_left = $quiz->get_remaining_attempts_by_user( $user_id )
//get total attempts used


//return total attempts left

?>
<div class="clear"></div>
<div class="llms-template-wrapper">
	<h4 class="llms-content-block">
		<?php printf( __( 'Attempts Remaining: <span class="llms-content llms-attempts">%s</span>', 'lifterlms' ), $attempts_left ); ?>
	</h4>
</div>


