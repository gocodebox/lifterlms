<?php
/**
 * Builder section model
 *
 * @since   3.16.0
 * @version 3.17.2
 */
?>
<script type="text/html" id="tmpl-llms-section-template">

	<span class="llms-drag-utility drag-section"></span>

	<header class="llms-builder-header">

		<h2 class="llms-headline">
			<span class="llms-input" contenteditable="true" data-attribute="title" data-original-content="{{{ data.title }}}" data-required="required">{{{ data.title }}}</span>
		</h2>

		<div class="llms-action-icons">

			<div class="llms-action-icons-left">

				<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
					<button class="llms-action-icon llms-trash-model trash--section danger tip--top-right" data-tip="<?php esc_attr_e( 'Delete section', 'lifterlms' ); ?>">
						<span class="fa fa-trash"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Delete section', 'lifterlms' ); ?></span>
					</button>
				<?php endif; ?>

			</div>

			<div class="llms-action-icons-right">

				<button class="llms-action-icon shift-up--section tip--top-right" data-tip="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>">
					<span class="fa fa-chevron-up"></span>
				</button>

				<button class="llms-action-icon shift-down--section tip--top-right" data-tip="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>">
					<span class="fa fa-chevron-down"></span>
				</button>

				<# if ( ! data._expanded ) { #>
					<button class="llms-action-icon expand tip--top-right" data-tip="<?php esc_attr_e( 'Expand section', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Expand section', 'lifterlms' ); ?>">
						<span class="fa fa-caret-down"></span>
					</button>
				<# } #>

				<# if ( data._expanded ) { #>
					<button class="llms-action-icon collapse tip--top-right" data-tip="<?php esc_attr_e( 'Collapse section', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Collapse section', 'lifterlms' ); ?>">
						<span class="fa fa-caret-up"></span>
					</button>
				<# } #>

			</div>

		</div>

	</header>

	<ul class="llms-lessons<# if ( data._expanded ) { #> expanded<# } #>" id="llms-lessons-{{{ data.id }}}"></ul>

	<# if ( data._expanded ) { #>
		<div class="llms-builder-footer">
			<button class="llms-button-secondary small new-lesson">
				<span class="fa fa-file"></span> <?php esc_html_e( 'Add New Lesson', 'lifterlms' ); ?>
			</button>
		</div>
	<# } #>

</script>
