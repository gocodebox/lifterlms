<?php
/**
 * Builder question view
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-question-template">

	<header class="llms-builder-header">

		<span class="llms-data-stamp {{{ data.get( 'question_type' ).get( 'id' ) }}} tip--top-right" data-tip="{{{ data.get_qid() }}} &ndash; {{{ data.get( 'question_type' ).get( 'name' ) }}}">
			<i class="fa fa-{{{ data.get( 'question_type' ).get( 'icon' ) }}}" aria-hidden="true"></i>
			<small>{{{ data.get_qid() }}}</small>
		</span>

		<h3 class="llms-headline llms-input-wrapper">
			<span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-formatting="b,i,u" data-original-content="{{{ data.get( 'title' ) }}}" data-placeholder="{{{ data.get( 'question_type' ).get( 'placeholder' ) }}}">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<div class="llms-action-icons">

			<# if ( ! data.get( '_expanded' ) ) { #>
				<a class="llms-action-icon expand--question tip--top-right" data-tip="<?php esc_attr_e( 'Expand question', 'lifterlms' ); ?>" href="#llms-expand" tabindex="-1">
					<i class="fa fa-plus-circle" aria-hidden="true"></i>
				</a>
			<# } else { #>
				<a class="llms-action-icon collapse--question tip--top-right" data-tip="<?php esc_attr_e( 'Collapse question', 'lifterlms' ); ?>" href="#llms-collapse" tabindex="-1">
					<i class="fa fa-minus-circle" aria-hidden="true"></i>
				</a>
			<# } #>

			<a class="llms-action-icon clone--question tip--top-right" data-tip="<?php esc_attr_e( 'Clone question', 'lifterlms' ); ?>" href="#llms-clone" tabindex="-1">
				<i class="fa fa-clone" aria-hidden="true"></i>
			</a>
			<a class="llms-action-icon danger delete--question tip--top-right" data-tip="<?php esc_attr_e( 'Delete question', 'lifterlms' ); ?>" href="#llms-trash" tabindex="-1">
				<i class="fa fa-trash" aria-hidden="true"></i>
			</a>
		</div>


		<div class="llms-question-points llms-editable-number tip--top-left" data-tip="{{{ data.get_points_percentage() }}}">
			<input class="llms-input two-digits" min="0" max="99" name="question_points" type="number" value="{{{ data.get( 'points' ) }}}" tabindex="-1"<# if ( ! data.get( 'question_type' ).get( 'points' ) ) { print( ' disabled'); }#>><small><?php _e( 'points', 'lifterlms' ); ?></small>
		</div>

	</header>

	<section class="llms-question-body <# if ( data.get( '_expanded' ) ) { print( ' active' ); }#>">

		<div class="llms-question-content">

			<# if ( 'yes' === data.get( 'description_enabled' ) ) { #>
				<div class="llms-editable-editor">
					<textarea data-attribute="content" id="question-desc--{{{ data.get( 'id' ) }}}">{{{ data.get( 'content' ) }}}</textarea>
				</div>
			<# } #>

			<# if ( 'yes' === data.get( 'image' ).get( 'enabled' ) ) { #>
				<div class="llms-editable-image">
					<# if ( data.get( 'image' ).get( 'src' ) ) { #>
						<div class="llms-image">
							<a class="llms-action-icon danger tip--top-left" data-attribute="image" data-tip="<?php esc_attr_e( 'Remove image', 'lifterlms' ); ?>" href="#llms-remove-image">
								<i class="fa fa-times-circle" aria-hidden="true"></i>
								<span class="screen-reader-text"><?php _e( 'Remove image', 'lifterlms' ); ?></span>
							</a>
							<img alt="<?php esc_attr_e( 'image preview', 'lifterlms' ); ?>" src="{{{ data.get( 'image' ).get( 'src' ) }}}">
						</div>
					<# } else { #>
						<button class="llms-element-button small llms-add-image" data-attribute="image" data-image-size="full">
							<span class="fa fa-picture-o"></span> <?php _e( 'Add Image', 'lifterlms' ); ?>
						</button>
					<# } #>
				</div>
			<# } #>

			<# if ( 'yes' === data.get( 'video_enabled' ) ) { #>
				<div class="llms-editable-video">
					<span class="llms-label"><?php _e( 'Video URL', 'lifterlms' ); ?>:</span>
					<span class="llms-input" contenteditable="true" data-attribute="video_src" data-original-content="{{{ data.get( 'video_src' ) }}}" data-placeholder="<?php esc_attr_e( 'https://', 'lifterlms' ); ?>" data-type="video">{{{ data.get( 'video_src' ) }}}</span>
					<span class="tip--top-<# if ( data.get( 'video_src' ) ) { print( 'left' ) } else { print( 'right' ) } #>" data-tip="<?php esc_attr_e( 'Use YouTube, Vimeo, or Wistia video URLS.', 'lifterlms' ); ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
				</div>
			<# } #>

		</div>

		<# if ( data.get( 'question_type' ).get( 'choices' ) ) { #>
			<ul class="llms-question-choices"></ul>
		<# } else if ( 'group' === data.get( 'question_type' ).get( 'id' ) ) { #>
			<ul class="llms-quiz-questions" data-empty-msg="<?php esc_attr_e( 'Drag a question here to add it to the group.', 'lifterlms' ); ?>"></ul>
		<# } #>

		<# if ( 'yes' === data.get( 'clarifications_enabled' ) ) { #>
			<div class="llms-editable-editor">
				<span class="llms-label"><?php _e( 'Result Clarifications', 'lifterlms' ); ?></span>
				<textarea data-attribute="clarifications" id="question-clarifications--{{{ data.get( 'id' ) }}}">{{{ data.get( 'clarifications' ) }}}</textarea>
			</div>
		<# } #>

		<div class="llms-question-features">
			<# if ( data.get( 'question_type' ).get( 'description' ) ) { #>
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Description', 'lifterlms' ); ?></span>
					<input type="checkbox" name="description_enabled"<# if ( 'yes' === data.get( 'description_enabled' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			<# } #>

			<# if ( data.get( 'question_type' ).get( 'image' ) ) { #>
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Image', 'lifterlms' ); ?></span>
					<input type="checkbox" name="image.enabled"<# if ( 'yes' === data.get( 'image' ).get( 'enabled' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			<# } #>

			<# if ( data.get( 'question_type' ).get( 'video' ) ) { #>
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Video', 'lifterlms' ); ?></span>
					<input type="checkbox" name="video_enabled"<# if ( 'yes' === data.get( 'video_enabled' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			<# } #>

			<# if ( data.get( 'question_type' ).get_multi_choices() ) { #>
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Multiple Correct Choices', 'lifterlms' ); ?></span>
					<input type="checkbox" name="multi_choices"<# if ( 'yes' === data.get( 'multi_choices' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			<# } #>

			<# if ( data.get( 'question_type' ).get( 'clarifications' ) ) { #>
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Result Clarifications', 'lifterlms' ); ?></span>
					<input type="checkbox" name="clarifications_enabled"<# if ( 'yes' === data.get( 'clarifications_enabled' ) ) { print( ' checked' ) } #>>
					<div class="llms-switch-slider"></div>
				</label>
			<# } #>

		</div>

	</section>

</script>
