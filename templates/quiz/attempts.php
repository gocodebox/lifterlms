<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;

//get total attempts
$attempts_left = $quiz->get_total_allowed_attempts();

//get total attempts used


//return total attempts left

?>
<div class="clear"></div>
<div class="llms-template-wrapper">
	<p class="llms-content-block">
		<?php printf( __('Attempts Remaining: <span class="llms-content llms-attempts">%s</span>', 'lifterlms'), $attempts_left ); ?>
	</p>
</div>


