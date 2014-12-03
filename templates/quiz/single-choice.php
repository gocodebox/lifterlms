<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $quiz, $question;

$options = $question->get_options();
$question_key = isset($quiz) ? $quiz->get_question_key : 0;
?>

<div class="llms-question-wrapper">
	<?php 
	foreach($options as $key => $value) :
		if (isset($value)) :
			$option = $value['option_text'];		
	?>
	<div class="llms-option_<?php echo $question_key; ?>">
		<label class="llms-question-label">
			<input type="radio" name="llms_option_selected" value="<?php echo $key; ?>"/>
			<input type="hidden" name="question_type" value="single_choice" />
			<input type="hidden" name="question_id" value="<?php echo $question->id ?>" />
			<?php echo $option; ?>
		</label>
	</div>
	<?php 
		endif;
	endforeach;
	?>
</div>


