<?php
/**
 * Builder quiz model view
 *
 * @since   3.16.0
 * @version 3.17.6
 */
?>
<script type="text/html" id="tmpl-llms-quiz-template">

	<# if ( _.isEmpty( data ) ) { #>

		<div class="llms-quiz-empty">

			<p><?php esc_html_e( 'There\'s no quiz associated with this lesson.', 'lifterlms' ); ?></p>

			<button class="llms-element-button" id="llms-new-quiz" type="button">
				<?php esc_html_e( 'Create New Quiz', 'lifterlms' ); ?>
				<i class="fa fa-file" aria-hidden="true"></i>
			</button>

			<br>

			<button class="llms-element-button" id="llms-existing-quiz" type="button">
				<?php esc_html_e( 'Add Existing Quiz', 'lifterlms' ); ?>
				<i class="fa fa-file-text" aria-hidden="true"></i>
			</button>

		</div>

	<# } else { #>

		<header class="llms-model-header" id="llms-lesson-header">

			<h3 class="llms-headline llms-model-title">
				<?php esc_html_e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
			</h3>

			<div class="llms-headline llms-quiz-points">
				<?php esc_html_e( 'Total Points', 'lifterlms' ); ?>: <strong id="llms-quiz-total-points">{{{ data.get( '_points' ) }}}</strong>
			</div>

			<label class="llms-switch llms-model-status">
				<span class="llms-label"><?php esc_html_e( 'Published', 'lifterlms' ); ?></span>
				<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
				<div class="llms-switch-slider"></div>
			</label>

			<div class="llms-action-icons">

				<# if ( ! data.has_temp_id() ) { #>
					<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_attr_e( 'Detach Quiz', 'lifterlms' ); ?>" href="#llms-detach-model">
						<i class="fa fa-chain-broken" aria-hidden="true"></i>
						<span class="screen-reader-text"><?php esc_html_e( 'Detach Quiz', 'lifterlms' ); ?></span>
					</a>
				<# } #>

				<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_html_e( 'Delete Quiz', 'lifterlms' ); ?>" href="#llms-trash-model" tabindex="-1">
					<i class="fa fa-trash" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php esc_html_e( 'Delete Quiz', 'lifterlms' ); ?></span>
				</a>

			</div>

		</header>

		<?php do_action( 'llms_builder_quiz_before_settings' ); ?>

		<div id="llms-quiz-settings-fields"></div>

		<?php do_action( 'llms_builder_quiz_after_settings' ); ?>

		<ul class="llms-quiz-questions" data-empty-msg="<?php esc_attr_e( 'Click "Add Question" below to start building your quiz!', 'lifterlms' ); ?>" id="llms-quiz-questions"></ul>

		<footer class="llms-quiz-footer">

			<button class="llms-element-button secondary small right bulk-toggle" data-action="collapse" id="llms-question-collapse-all">
				<?php esc_html_e( 'Collapse All', 'lifterlms' ); ?>
				<i class="fa fa-minus-circle"></i>
			</button>

			<button class="llms-element-button secondary small right bulk-toggle" data-action="expand" id="llms-question-expand-all">
				<?php esc_html_e( 'Expand All', 'lifterlms' ); ?>
				<i class="fa fa-plus-circle"></i>
			</button>

			<button class="llms-element-button small right llms-show-question-bank" id="llms-show-question-bank">
				<?php esc_html_e( 'Add Question', 'lifterlms' ); ?>
				<i class="fa fa-plus-circle" aria-hidden="true"></i>
			</button>

		</footer>

		<div class="llms-quiz-tools" id="llms-quiz-tools">

			<ul class="llms-question-bank" id="llms-question-bank"></ul>

		</div>

	<# } #>

</script>
