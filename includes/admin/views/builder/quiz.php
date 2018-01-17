<?php
/**
 * Builder quiz model view
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-quiz-template">

	<# if ( _.isEmpty( data ) ) { #>

		<div class="llms-quiz-empty">
			<p><?php _e( 'There\'s no quiz associated with this lesson.', 'lifterlms' ); ?></p>
			<button class="llms-element-button" id="llms-enable-quiz" type="button">
				<?php _e( 'Create a Quiz', 'lifterlms' ); ?>
				<i class="fa fa-plus" aria-hidden="true"></i>
			</button>
		</div>

	<# } else { #>

		<header class="llms-quiz-header" id="llms-quiz-header"></header>

		<ul class="llms-quiz-questions" data-empty-msg="<?php esc_attr_e( 'Click "Add Question" below to start building your quiz!', 'lifterlms' ); ?>" id="llms-quiz-questions"></ul>

		<button class="llms-element-button small right llms-show-question-bank" id="llms-show-question-bank">
			<?php _e( 'Add Question', 'lifterlms' ); ?>
			<i class="fa fa-plus-circle" aria-hidden="true"></i>
		</button>

		<div class="llms-quiz-tools" id="llms-quiz-tools">

<!-- 			<div class="llms-quiz-tools-search">
				<label>
					<i class="fa fa-search" aria-hidden="true"></i>
					<input id="llms-question-bank-filter" placeholder="<?php esc_attr_e( 'Filter', 'lifterlms' ); ?>" type="search">
				</label>
			</div> -->

			<ul class="llms-question-bank" id="llms-question-bank"></ul>

		</div>

	<# } #>

</script>
