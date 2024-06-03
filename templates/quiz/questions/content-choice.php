<?php
/**
 * Choice Question Template
 *
 * @package LifterLMS/Templates
 *
 * @since    3.16.0
 * @version  3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

defined( 'ABSPATH' ) || exit;

$input_type = ( 'yes' === $question->get( 'multi_choices' ) ) ? 'checkbox' : 'radio';
?>

<ol class="llms-question-choices">
	<?php foreach ( $question->get_choices() as $choice ) : ?>

		<li class="llms-choice type--text" id="choice-wrapper-<?php echo esc_attr( $choice->get( 'id' ) ); ?>">
			<label for="choice-<?php echo esc_attr( $choice->get( 'id' ) ); ?>">
				<input id="choice-<?php echo esc_attr( $choice->get( 'id' ) ); ?>" name="question_<?php echo esc_attr( $question->get( 'id' ) ); ?>[]" type="<?php echo esc_attr( $input_type ); ?>" value="<?php echo esc_attr( $choice->get( 'id' ) ); ?>">
				<span class="llms-marker type--<?php echo esc_attr( $input_type ); ?>">
					<span class="iterator"><?php echo esc_html( $choice->get( 'marker' ) ); ?></span>
					<i class="fa fa-check"></i>
				</span>
				<p class="llms-choice-text"><?php echo esc_html( $choice->get( 'choice' ) ); ?></p>
			</label>
		</li>

	<?php endforeach; ?>
</ol>
