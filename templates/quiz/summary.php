<?php

	global $quiz;
	$user_id = get_current_user_id();
	$quiz_data = get_user_meta($user_id, 'llms_quiz_data', true );
	$quiz_session = LLMS()->session->get( 'llms_quiz' );

?>
<div class ="llms-template-wrapper quiz-summary">
	<div><a class="view-summary">View Summary</a></div>
	<div class = "accordion hidden">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php
	$congrats = ['Congrats!', 'Well Done!', 'Oh right !', 'You rock!', 'You rules !', 'You are the best !'];
	foreach ($quiz_session->questions as $key => $question) {
		$background = $question['correct'] ? 'right' : 'wrong';
		$icon = $question['correct'] ? 'llms-icon-checkmark' :  'llms-icon-close';
		$question_object = new LLMS_Question( $question['id']);
		$options = $question_object->get_options();
		$correct_option = $question_object->get_correct_option();
		?>
	    <div class="panel panel-default">
			<div class="panel-heading <?php echo $background ?>" role="tab" id="heading_ <?php echo $key ?>"
			 data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $key?>" aria-expanded="true"
			  aria-controls="collapse_<?php echo $key?>">
				<h4 class="panel-title">
				Question <?php echo $key + 1?>
   				<svg class="icon" role="img">
   					<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="http://localhost/wp-lms/wp-content/plugins/lifterlms/assets/svg/svg.svg#<?php echo $icon ?>">
   					</use>
   				</svg>
				</h4>
			</div>
			<div id="collapse_<?php echo $key ?>" class="panel-collapse collapse <?php echo $background . '-panel' ?>" role="tabpanel" aria-labelledby="heading_<?php echo $key ?>">
				<div class="panel-body">
				<p><?php echo $question_object->post->post_content ?> </p>
				<ul>
					<li>
						Your answer: <?php echo $options[$question['answer']]['option_text']; ?>						
					</li>
					<li>
						Correct answer: <?php echo $correct_option['option_text']; ?>
					</li>					
					<li>
					<?php
						if($question['correct']) {
							echo $congrats[rand(0,5)];
						} else {
							echo 'Description: ' . $correct_option['option_description'];
						}
					?>
					</li>
				</ul>		
				</div>
			</div>
		</div>
		<?php
	}
 ?>
		</div>
	</div>
</div>
