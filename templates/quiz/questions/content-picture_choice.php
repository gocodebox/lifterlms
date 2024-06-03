<?php
/**
 * Picture choice question template.
 *
 * @package LifterLMS/Templates
 *
 * @since 3.16.0
 * @since 5.9.0 Use `llms-flex-cols` in favor of `llms-cols` for arranging choices in columns.
 * @version 3.16.0
 *
 * @var LLMS_Quiz_Attempt $attempt  Current quiz attempt object.
 * @var LLMS_Question     $question Question object.
 */

defined( 'ABSPATH' ) || exit;

$input_type = ( 'yes' === $question->get( 'multi_choices' ) ) ? 'checkbox' : 'radio';
$choices    = $question->get_choices();
$cols       = llms_get_picture_choice_question_cols( count( $choices ) );
?>

<ol class="llms-question-choices llms-flex-cols">
	<?php foreach ( $choices as $choice ) : ?>

		<li class="llms-choice type--picture llms-col-<?php echo absint( $cols ); ?>" id="choice-wrapper-<?php echo $choice->get( 'id' ); ?>">
			<label for="choice-<?php echo $choice->get( 'id' ); ?>">
				<input id="choice-<?php echo $choice->get( 'id' ); ?>" name="question_<?php echo $question->get( 'id' ); ?>[]" type="<?php echo $input_type; ?>" value="<?php echo $choice->get( 'id' ); ?>">
				<span class="llms-marker type--<?php echo $input_type; ?>">
					<span class="iterator"><?php echo $choice->get( 'marker' ); ?></span>
					<i class="fa fa-check"></i>
				</span>
				<div class="llms-choice-image"><?php echo $choice->get_image(); ?></div>
			</label>
		</li>

	<?php endforeach; ?>
</ol>

