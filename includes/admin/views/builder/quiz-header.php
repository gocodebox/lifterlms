<?php
/**
 * Builder quiz model view
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-quiz-header-template">

	<h3 class="llms-headline llms-quiz-title">
		<?php _e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}">{{{ data.get( 'title' ) }}}</span>
	</h3>

	<div class="llms-headline llms-quiz-points">
		<?php _e( 'Total Points', 'lifterlms' ); ?>: <strong id="llms-quiz-total-points">{{{ data.get( '_points' ) }}}</strong>
	</div>

	<label class="llms-switch llms-quiz-status">
		<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
		<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
		<div class="llms-switch-slider"></div>
	</label>

	<div class="llms-action-icons">

		<a class="llms-action-icon tip--bottom-left" data-tip="<?php _e( 'Quiz Settings', 'lifterlms' ); ?>" href="#llms-quiz-settings" tabindex="-1">
			<i class="fa fa-cog" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Quiz Settings', 'lifterlms' ); ?></span>
		</a>

	</div>

	<div class="clear"></div>

	<section class="llms-quiz-settings<# if ( data.get( '_show_settings' ) ) { print( ' active' ); } #>">

		<div class="llms-settings-row">

			<div class="llms-editable-toggle-group">
				<div class="llms-editable-number tip--top-right" data-tip="<?php esc_attr_e( 'Minimum percentage of total points required to pass the quiz', 'lifterlms' ); ?>">
					<span class="llms-label"><?php _e( 'Passing Percentage', 'lifterlms' ); ?></span>
					<input class="llms-input standard" data-attribute="passing_percent" data-original-content="{{{ data.get( 'passing_percent' ) }}}" min="0" max="100" name="passing_percent" type="number" value="{{{ data.get( 'passing_percent' ) }}}">
				</div>
			</div>

			<div class="llms-editable-toggle-group">

				<label class="llms-switch">
					<span class="llms-label tip--top-right" data-tip="<?php esc_attr_e( 'Limit the maximum number of times a student can take this quiz', 'lifterlms' ); ?>"><?php _e( 'Limit Attempts', 'lifterlms' ); ?></span>
					<input type="checkbox" name="limit_attempts"<# if ( 'yes' === data.get( 'limit_attempts' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>

				<# if ( 'yes' === data.get( 'limit_attempts' ) ) { #>
					<div class="llms-editable-number">
						<input class="llms-input standard" data-attribute="allowed_attempts" data-original-content="{{{ data.get( 'allowed_attempts' ) }}}" min="1" max="999" name="allowed_attempts" type="number" value="{{{ data.get( 'allowed_attempts' ) }}}">
					</div>
				<# } #>

			</div>

			<div class="llms-editable-toggle-group">

				<label class="llms-switch">
					<span class="llms-label tip--top-left" data-tip="<?php esc_attr_e( 'Enforce a maximum number of minutes a student can spend on each attempt', 'lifterlms' ); ?>"><?php _e( 'Time Limit', 'lifterlms' ); ?></span>
					<input type="checkbox" name="limit_time"<# if ( 'yes' === data.get( 'limit_time' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>

				<# if ( 'yes' === data.get( 'limit_time' ) ) { #>
					<div class="llms-editable-number"">
						<input class="llms-input standard" data-attribute="time_limit" data-original-content="{{{ data.get( 'time_limit' ) }}}" min="1" max="360" name="time_limit" type="number" value="{{{ data.get( 'time_limit' ) }}}">
					</div>
				<# } #>

			</div>

		</div>

		<div class="llms-settings-row">

			<div class="llms-editable-toggle-group">
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Show Correct Answers', 'lifterlms' ); ?></span>
					<input type="checkbox" name="show_correct_answer"<# if ( 'yes' === data.get( 'show_correct_answer' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			</div>

			<div class="llms-editable-toggle-group">
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Randomize Question Order', 'lifterlms' ); ?></span>
					<input type="checkbox" name="random_questions"<# if ( 'yes' === data.get( 'random_questions' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			</div>

		</div>





<!-- 		<label class="llms-switch llms-quiz-enabled">
			<span class="llms-label"><?php _e( 'Enabled', 'lifterlms' ); ?></span>
			<input type="checkbox" name="parent.quiz_enabled"<# if ( 'yes' === data.get_parent().get( 'quiz_enabled' ) ) { print( ' checked' ) } #>>
			<div class="llms-switch-slider"></div>
		</label>
	-->

	</section>

</script>
