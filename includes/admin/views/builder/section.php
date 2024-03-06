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
					<a class="llms-action-icon trash--section danger tip--top-right" data-tip="<?php esc_attr_e( 'Delete Section', 'lifterlms' ); ?>" href="#llms-trash-model">
						<span class="fa fa-trash"></span>
					</a>
				<?php endif; ?>

			</div>

			<div class="llms-action-icons-right">

				<a class="llms-action-icon shift-up--section tip--top-right" data-tip="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
					<span class="fa fa-caret-square-o-up"></span>
				</a>

				<a class="llms-action-icon shift-down--section tip--top-right" data-tip="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
					<span class="fa fa-caret-square-o-down"></span>
				</a>

				<# if ( ! data._expanded ) { #>
					<a class="llms-action-icon expand tip--top-right" data-tip="<?php esc_attr_e( 'Expand section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-plus-circle"></span>
					</a>
				<# } #>

				<# if ( data._expanded ) { #>
					<a class="llms-action-icon collapse tip--top-right" data-tip="<?php esc_attr_e( 'Collapse section', 'lifterlms' ); ?>" href="#llms-toggle">
						<span class="fa fa-minus-circle"></span>
					</a>
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
