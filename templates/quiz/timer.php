<?php
/**
 * Single Quiz: Timer
 * @since    1.0.0
 * @version  3.9.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $quiz;

if ( ! ($quiz instanceof LLMS_Quiz ) ) {
	$quiz = new LLMS_Quiz( $post->ID ); }

$time_limit = $quiz->get_time_limit();

if ( ! empty( $time_limit ) ) :
?>
	<div id="llms-quiz-timer">
		<input type="hidden" id="set-time" value="<?php echo $time_limit; ?>"/>

		<div id="countdown">

			<div id='tiles' class="color-full"></div>
			<div class="countdown-label"><p>Time Remaining</p></div>
		</div>
	</div>
<?php endif; ?>
