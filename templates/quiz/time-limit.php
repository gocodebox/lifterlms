<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;

$time_limit = $quiz->get_time_limit();

if ( $time_limit ) {
?>
	<div class="clear"></div>
	<div class="llms-template-wrapper">
		<h4 class="llms-content-block">
			<?php printf( __( 'Time Limit: <span class="llms-content">%s</span>', 'lifterlms' ), LLMS_Date::convert_to_hours_minutes_string( $time_limit ) ); ?>
		</h4>
	</div>
<?php } ?>
