<?php
/**
 * Single Question: Wrapper Start
 *
 * @package LifterLMS/Templates
 *
 * @since    1.0.0
 * @version  3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="llms-question-wrapper type--<?php echo $question->get( 'question_type' ); ?>" data-id="<?php echo $question->get( 'id' ); ?>" data-type="<?php echo $question->get( 'question_type' ); ?>" id="llms-question-<?php echo $question->get( 'id' ); ?>">
