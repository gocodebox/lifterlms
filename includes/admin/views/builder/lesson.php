<?php
/**
 * Builder lesson model view
 * @since   3.16.0
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

			<a class="llms-action-icon edit-lesson tip--top-right" data-tip="<?php esc_attr_e( 'Edit lesson', 'lifterlms' ); ?>" href="#llms-lesson-settings">
				<span class="fa fa-cog"></span>
			</a>

			<a class="llms-action-icon edit-quiz tip--top-right" data-tip="<?php esc_attr_e( 'Edit quiz', 'lifterlms' ); ?>" href="#llms-lesson-settings">
				<span class="fa fa-question-circle"></span>
			</a>

			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'Open WordPress editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}" target="_blank">
					<span class="fa fa-pencil"></span>
				</a>
			<# } #>

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="{{{ data.get( 'permalink' ) }}}" target="_blank">
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
				<a class="llms-action-icon detach--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach-model">
					<span class="fa fa-chain-broken"></span>
				</a>
			<# } #>

			<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
				<a class="llms-action-icon trash--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Trash Lesson', 'lifterlms' ); ?>" href="#llms-trash-model">
					<span class="fa fa-trash"></span>
				</a>
			<?php endif; ?>

		</div>

	</header>

	<ul class="llms-info-list">

		<li class="llms-info-item tip--top-right<# if ( 'yes' === data.get( 'free_lesson' ) ) { print( ' active') } #>" data-tip="<?php esc_attr_e( 'Enrolled students only', 'lifterlms' ); ?>" data-tip-active="<?php esc_attr_e( 'Free Lesson', 'lifterlms' ); ?>" >
			<# if ( 'yes' === data.get( 'free_lesson' ) ) { #>
				<i class="fa fa-unlock"></i>
			<# } else { #>
				<i class="fa fa-lock"></i>
			<# } #>
		</li>

		<li class="llms-info-item tip--top-right<# if ( 'yes' === data.get( 'has_prerequsite' ) ) { print( ' active') } #>" data-tip="<?php esc_attr_e( 'No prerequsite', 'lifterlms' ); ?>" data-tip-active="<?php esc_attr_e( 'Prerequsite Enabled', 'lifterlms' ); ?>">
			<i class="fa fa-link"></i>
		</li>

		<li class="llms-info-item tip--top-right<# if ( data.get( 'drip_method' ) ) { print( ' active') } #>" data-tip="<?php esc_attr_e( 'No prerequsite', 'lifterlms' ); ?>" data-tip-active="<?php esc_attr_e( 'Drip Enabled', 'lifterlms' ); ?>">
			<i class="fa fa-calendar"></i>
		</li>

		<li class="llms-info-item tip--top-right<# if ( 'yes' === data.get( 'quiz_enabled' ) ) { print( ' active') } #>" data-tip="<?php esc_attr_e( 'No quiz', 'lifterlms' ); ?>"<# if ( 'yes' === data.get( 'quiz_enabled' ) ) { #> data-tip-active="<?php printf( esc_attr__( 'Quiz: %s', 'lifterlms' ), "{{{ data.get( 'quiz' ).get( 'title' ) }}}" ); ?>"<# } #>>
			<i class="fa fa-question-circle"></i>
		</li>

		<li class="llms-info-item tip--top-right<# if ( data.get( 'content' ) ) { print( ' active') } #>" data-tip="<?php esc_attr_e( 'No content', 'lifterlms' ); ?>" data-tip-active="<?php esc_attr_e( 'Has content', 'lifterlms' ); ?>">
			<i class="fa fa-file-text-o"></i>
		</li>

	</ul>

</script>
