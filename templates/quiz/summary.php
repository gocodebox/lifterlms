<?php

use LLMS\Users\User;

global $quiz;

$quiz_session = LLMS()->session->get( 'llms_quiz' );

$user = new User();

$last_attempt = $quiz->get_users_last_attempt( $user );

$user_id = get_current_user_id();

$quiz_data = get_user_meta( $user_id, 'llms_quiz_data', true );

?>

<div class ="llms-template-wrapper quiz-summary">

	<div class = "accordion hidden">

		<div class="panel-group collapsed" id="accordion" role="tablist" aria-multiselectable="true">

		<?php

		foreach ( (array) $last_attempt['questions'] as $key => $question) {
			$background = $question['correct'] ? 'right' : 'wrong';

			$icon = $question['correct'] ? 'llms-icon-checkmark' :  'llms-icon-close';

			$question_object = new LLMS_Question( $question['id'] );

			$options = $question_object->get_options();

			$correct_option = $question_object->get_correct_option();

			?>

			<div class="panel panel-default">

				<div class="panel-heading <?php echo $background ?>" role="tab" id="heading_ <?php echo $key ?>"
				 data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $key?>" aria-expanded="true"
				  aria-controls="collapse_<?php echo $key?>">

					<h4 class="panel-title">

						<?php echo LLMS_Language::output( 'Question' . ($key + 1) ); ?>

						<?php echo LLMS_Svg::get_icon( $icon, 'Lesson', 'Lesson', 'tree-icon' ); ?>

					</h4>

				</div>

				<div id="collapse_<?php echo $key ?>" class="panel-collapse collapse <?php echo $background . '-panel' ?>" role="tabpanel" aria-labelledby="heading_<?php echo $key ?>">

					<div class="panel-body">

					<p>
						<?php
						echo do_shortcode( $question_object->post->post_content );
						?>
						</p>

						<div class="clear"></div>
						<br>

						<ul>
						<?php if ( is_array( $options[ $question['answer'] ] ) && array_key_exists( 'option_text', $options[ $question['answer'] ] ) ) {
							?>

							<li>
								<span class="llms-quiz-summary-label user-answer">
								<?php echo LLMS_Language::output( 'Your answer:' . wp_kses_post( $options[ $question['answer'] ]['option_text'] ) ); ?>
								</span>
							</li>

							<?php } ?>

							<?php

							if ($quiz->show_correct_answer()) {
								echo '<li><span class="llms-quiz-summary-label correct-answer">';
									echo 'Correct answer: ' . wp_kses_post( $correct_option['option_text'] );
								echo '</span></li>';
							}

							if ($question['correct']) {
								if ($quiz->show_description_right_answer()) {
									if (array_key_exists( 'option_description', $options[ $question['answer'] ] )) {
										echo '<li><span class="llms-quiz-summary-label clarification">' .
											LLMS_Language::output( 'Clarification: ' . wpautop( $options[ $question['answer'] ]['option_description'] ) )
										. '</span></li>';
									}
								}
							} else {
								if ($quiz->show_description_wrong_answer()) {
									if (array_key_exists( 'option_description', $options[ $question['answer'] ] )) {
										echo '<li><span class="llms-quiz-summary-label clarification">' .
											LLMS_Language::output( 'Clarification: ' . wpautop( $options[ $question['answer'] ]['option_description'] ) )
										. '</span></li>';
									}
								}
							}
							?>

						</ul>

					</div>

				</div>

			</div>

		<?php } ?>

		</div>

	</div>

</div>
