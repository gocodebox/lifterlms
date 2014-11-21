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
			<input type="radio" name="llms_option_1<" value="<?php echo $key; ?>"/>
			<?php echo $option; ?>
		</label>
	</div>
	<?php 
		endif;
	endforeach;
	?>
</div>


