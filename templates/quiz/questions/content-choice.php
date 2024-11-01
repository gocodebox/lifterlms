<?php
/**
 * Choice Question Template
 *
 * @package LifterLMS/Templates
 *
 * @since 3.16.0
 * @since 7.8.0 Account for question answers.
 * @version 7.8.0
 *
 * @param $attempt  LLMS_Quiz_Attempt LLMS_Quiz_Attempt instance.
 * @param $question LLMS_Question     LLMS_Question instance.
 */

defined( 'ABSPATH' ) || exit;

$input_type      = ( 'yes' === $question->get( 'multi_choices' ) ) ? 'checkbox' : 'radio';
$question_answer = isset( $attempt ) && $attempt ? $attempt->get_question_answer( $question->get( 'id' ) ) : array();
?>

<ol class="llms-question-choices">
	<?php foreach ( $question->get_choices() as $choice ) : ?>
		<?php
		$answer = is_array( $question_answer ) ? in_array( $choice->get( 'id' ), $question_answer, true ) ? $choice->get( 'id' ) : null : null;
		?>
		<li class="llms-choice type--text" id="choice-wrapper-<?php echo esc_attr( $choice->get( 'id' ) ); ?>">
			<label for="choice-<?php echo esc_attr( $choice->get( 'id' ) ); ?>">
				<input id="choice-<?php echo esc_attr( $choice->get( 'id' ) ); ?>" name="question_<?php echo esc_attr( $question->get( 'id' ) ); ?>[]" type="<?php echo esc_attr( $input_type ); ?>" value="<?php echo esc_attr( $choice->get( 'id' ) ); ?>" <?php checked( $answer, $choice->get( 'id' ) ); ?>>
				<span class="llms-marker type--<?php echo esc_attr( $input_type ); ?>">
					<span class="iterator"><?php echo esc_html( $choice->get( 'marker' ) ); ?></span>
					<i class="fa fa-check"></i>
				</span>
				<p class="llms-choice-text"><?php echo esc_html( $choice->get( 'choice' ) ); ?></p>
			</label>
		</li>

	<?php endforeach; ?>
</ol>
