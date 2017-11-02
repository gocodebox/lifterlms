<?php
/**
 * Single Quiz: Single Choice Question
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post, $quiz, $question;

if ( ! $quiz ) {

	$quiz = new LLMS_Quiz( $post->ID );

}
if ( ! $question ) {

	$question = new LLMS_Question( $post->ID );

}

$quiz_obj = $quiz;
$options = $question->get_options();
$question_key = isset( $quiz ) ? $quiz->get_question_key : 0;

$quiz_session = LLMS()->session->get( 'llms_quiz' );
$quiz = $quiz_session;

$answer = '';
if ( ! empty( $quiz_session->questions ) ) {
	foreach ( $quiz_session->questions as $q ) {

		if ( $q['id'] == $question->id ) {
			$answer = $q['answer'];

		}
	}
}
?>
<div class="clear"></div>
<div class="llms-question-wrapper">
	<?php
	if ( $quiz_obj->get_show_random_answers() ) {
		llms_shuffle_assoc( $options );
	}

	foreach ( $options as $key => $value ) :
		if ( isset( $value ) ) :
			$option = $value['option_text'];


			if ( (int) $answer === (int) $key ) {
				$checked = 'checked';
			} else {
				$checked = '';
			}

	?>
	<div class="llms-option_<?php echo $question_key; ?>">
		<label class="llms-question-label">
			<input type="radio" name="llms_option_selected" value="<?php echo $key; ?>" <?php echo $checked; ?>/>
			<input type="hidden" name="question_type" value="single_choice" />
			<input type="hidden" name="question_id" value="<?php echo $question->id ?>" />
			<?php echo wp_kses_post( $option ); ?>
		</label>
	</div>
	<?php
		endif;
	endforeach;
	?>
</div>


