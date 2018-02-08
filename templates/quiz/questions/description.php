<?php
/**
 * Single Question description template
 * @since    3.16.0
 * @version  3.16.6
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $question->has_description() ) {
	return;
}
?>

<div class="llms-question-description"><?php echo $question->get_description(); ?></div>
