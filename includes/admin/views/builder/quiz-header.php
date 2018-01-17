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
		<?php _e( 'Total Points', 'lifterlms' ); ?>: <strong id="llms-quiz-total-points">{{{ data.get( 'points' ) }}}</strong>
	</div>

	<label class="llms-switch llms-quiz-status">
		<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
		<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
		<div class="llms-switch-slider"></div>
	</label>

	<div class="llms-action-icons">

		<a class="llms-action-icon tip--bottom-right" data-tip="<?php _e( 'Quiz Settings', 'lifterlms' ); ?>" href="#llms-quiz-settings" tabindex="-1">
			<i class="fa fa-cog" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Quiz Settings', 'lifterlms' ); ?></span>
		</a>

	</div>

	<section class="llms-quiz-settings" style="display:none;">

		<label class="llms-switch llms-quiz-enabled">
			<span class="llms-label"><?php _e( 'Enabled', 'lifterlms' ); ?></span>
			<input type="checkbox" name="parent.quiz_enabled"<# if ( 'yes' === data.get_parent().get( 'quiz_enabled' ) ) { print( ' checked' ) } #>>
			<div class="llms-switch-slider"></div>
		</label>


	</section>

</script>
