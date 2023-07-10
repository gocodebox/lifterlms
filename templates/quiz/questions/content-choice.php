<?php
/**
 * Choice Question Template
 *
 * @package LifterLMS/Templates
 *
 * @since 3.16.0
 * @since [version] Account for question answers.
 * @version [version]
 *
 * @param $attempt  LLMS_Quiz_Attempt LLMS_Quiz_Attempt instance.
 * @param $question LLMS_Question     LLMS_Question instance.
 */

defined( 'ABSPATH' ) || exit;

$input_type = ( 'yes' === $question->get( 'multi_choices' ) ) ? 'checkbox' : 'radio';
$answer     = $attempt ? $attempt->get_question_answer( $question->get( 'id' ) ) : [];
?>

<ol class="llms-question-choices">
	<?php foreach ( $question->get_choices() as $choice ) : ?>
		<?php
		$answer = is_array( $answer ) ? in_array( $choice->get( 'id' ), $answer, true ) ? $choice->get( 'id' ) : null : null;
		?>
		<li class="llms-choice type--text" id="choice-wrapper-<?php echo $choice->get( 'id' ); ?>">
			<label for="choice-<?php echo $choice->get( 'id' ); ?>">
				<input
					id="choice-<?php echo $choice->get( 'id' ); ?>"
					name="question_<?php echo $question->get( 'id' ); ?>[]"
					type="<?php echo $input_type; ?>"
					value="<?php echo $choice->get( 'id' ); ?>"
					<?php checked( $answer, $choice->get( 'id' ) ); ?>>
				<span class="llms-marker type--<?php echo $input_type; ?>">
					<span class="iterator"><?php echo $choice->get( 'marker' ); ?></span>
					<i class="fa fa-check"></i>
				</span>
				<p class="llms-choice-text"><?php echo $choice->get( 'choice' ); ?></p>
			</label>
		</li>

	<?php endforeach; ?>
</ol>
