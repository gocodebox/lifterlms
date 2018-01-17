<?php
/**
 * Builder lesson model view
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-lesson-template">

	<span class="llms-drag-utility drag-lesson"></span>

	<header class="llms-builder-header">
		<h3 class="llms-headline">
			<?php echo get_post_type_object( 'lesson' )->labels->singular_name; ?> {{{ data.get( 'order' ) }}}:
			<span class="llms-input" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" type="text">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<div class="llms-action-icons">

			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'Edit lesson settings', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}">
					<span class="fa fa-pencil"></span>
				</a>
			<# } #>

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="{{{ data.get( 'permalink' ) }}}">
					<span class="fa fa-external-link"></span>
				</a>
			<# } #>

			<a class="llms-action-icon shift-up--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
				<span class="fa fa-caret-square-o-up"></span>
			</a>

			<a class="llms-action-icon shift-down--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
				<span class="fa fa-caret-square-o-down"></span>
			</a>

			<a class="llms-action-icon section-prev tip--top-right" data-tip="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>" href="#llms-section-change">
				<span class="fa fa-arrow-circle-o-up"></span>
			</a>

			<a class="llms-action-icon section-next tip--top-right" data-tip="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>" href="#llms-section-change">
				<span class="fa fa-arrow-circle-o-down"></span>
			</a>

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon detach--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach">
					<span class="fa fa-chain-broken"></span>
				</a>
			<# } #>

			<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
				<a class="llms-action-icon trash--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Trash Lesson', 'lifterlms' ); ?>" href="#llms-trash">
					<span class="fa fa-trash"></span>
				</a>
			<?php endif; ?>

		</div>

	</header>

	<# if ( 'yes' === data.get( 'quiz_enabled' ) ) { #>
		<div class="llms-quiz">
			<i class="fa fa-question-circle" aria-hidden="true"></i> <?php _e( 'Quiz:', 'lifterlms' ); ?> {{{ data.get( 'quiz' ).get( 'title' ) }}}
		</div>
	<# } #>

</script>
