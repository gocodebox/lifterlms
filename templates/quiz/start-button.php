<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;
$user_id = get_current_user_id();

if ( $quiz ) {
	$attempts = $quiz->get_remaining_attempts_by_user( $user_id );
}
?>

<div class="clear"></div>
<div class="llms-button-wrapper" id="quiz-start-button">

<?php

if ( empty( $quiz ) || $attempts === 'unlimited' || $attempts > 0 || $quiz->get_end_date( $user_id ) == '' ) :
?>
	<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data"> 
	 	<?php do_action( 'lifterlms_before_start_quiz' ); ?>
		<input id="llms-user" name="llms-user_id" type="hidden" value="<?php echo $user_id; ?>"/>
	 	<input id="llms-quiz" name="llms-quiz_id" type="hidden" value="<?php echo $quiz->id; ?>"/>
	 	<input id="llms_start_quiz" type="button" class="button" name="llms_start_quiz" value="<?php _e('Start Quiz', 'lifterlms'); ?>" />
	 	<input type="hidden" name="action" value="llms_start_quiz" />

	 	<?php wp_nonce_field( 'llms_start_quiz' ); ?>
		<?php do_action( 'lifterlms_after_start_quiz' ); ?>
	</form>

<?php


else :
	_e('<p>You are not able take this quiz</p>', 'lifterlms');
endif;
?>
</div>




