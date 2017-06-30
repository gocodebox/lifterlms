<?php
/**
 * Single Quiz: Summary accordion
 * @since    1.0.0
 * @version  3.10.0
 */

$student = llms_get_student();
$attempt = isset( $_GET['attempt_key'] ) ? $student->quizzes()->get_attempt_by_key( $_GET['attempt_key'] ) : false;
$quiz = $attempt->get_quiz();
?>

<div class ="llms-template-wrapper quiz-summary">

	<div class = "accordion hidden">

		<div class="panel-group collapsed" id="accordion" role="tablist" aria-multiselectable="true">

		<?php foreach ( $attempt->get( 'questions' ) as $key => $question ) :
			$background = $question['correct'] ? 'right' : 'wrong';
			$icon = $question['correct'] ? 'llms-icon-checkmark' :  'llms-icon-close';
			$question_object = new LLMS_Question( $question['id'] );
			$options = $question_object->get_options();
			$correct_option = $question_object->get_correct_option();
			$answer = isset( $options[ $question['answer'] ] ) ? $options[ $question['answer'] ] : array(
				'option_text' => __( 'No answer selected', 'lifterlms' ),
			);
			?>

			<div class="panel panel-default">

				<div class="panel-heading <?php echo $background ?>" role="tab" id="heading_ <?php echo $key ?>"
				 data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $key?>" aria-expanded="true"
				  aria-controls="collapse_<?php echo $key?>">

					<h4 class="panel-title">

						<?php echo sprintf( __( 'Question', 'lifterlms' ), ($key + 1) ); ?>

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
						<?php if ( is_array( $answer ) && array_key_exists( 'option_text', $answer ) ) {
							?>

							<li>
								<span class="llms-quiz-summary-label user-answer">
								<?php echo sprintf( __( 'Your answer: %s', 'lifterlms' ), wp_kses_post( $answer['option_text'] ) ); ?>
								</span>
							</li>

							<?php } ?>

							<?php

							if ( $quiz->show_correct_answer() ) {
								echo '<li><span class="llms-quiz-summary-label correct-answer">';
									echo sprintf( __( 'Correct answer: %s', 'lifterlms' ), wp_kses_post( $correct_option['option_text'] ) );
								echo '</span></li>';
							}

							if ( $question['correct'] ) {
								if ( $quiz->show_description_right_answer() ) {
									if ( is_array( $answer ) && array_key_exists( 'option_description', $answer ) ) {
										echo '<li><span class="llms-quiz-summary-label clarification">' .
											sprintf( __( 'Clarification: %s', 'lifterlms' ), wpautop( $answer['option_description'] ) )
										. '</span></li>';
									}
								}
							} else {
								if ( $quiz->show_description_wrong_answer() ) {
									if ( is_array( $answer ) && array_key_exists( 'option_description', $answer ) ) {
										echo '<li><span class="llms-quiz-summary-label clarification">' .
											sprintf( __( 'Clarification: %s', 'lifterlms' ), wpautop( $answer['option_description'] ) )
										. '</span></li>';
									}
								}
							}
							?>

						</ul>

					</div>

				</div>

			</div>

		<?php endforeach; ?>
		</div>

	</div>

</div>
