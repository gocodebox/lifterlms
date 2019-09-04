<?php
/**
 * Builder question view
 *
 * @since   3.16.0
 * @version 3.27.0
 */
?>
<script type="text/html" id="tmpl-llms-question-template">

	<header class="llms-builder-header">

		<span class="llms-data-stamp {{{ data.get( 'question_type' ).get( 'id' ) }}} tip--top-right" data-tip="{{{ data.get_qid() }}} &ndash; {{{ data.get( 'question_type' ).get( 'name' ) }}}">
			<i class="fa fa-{{{ data.get( 'question_type' ).get( 'icon' ) }}}" aria-hidden="true"></i>
			<small>{{{ data.get_qid() }}}</small>
		</span>

		<h3 class="llms-headline llms-input-wrapper">
			<div class="llms-editable-title llms-input-formatting" data-attribute="title" data-formatting="bold,italic,underline" data-placeholder="{{{ data.get( 'question_type' ).get( 'placeholder' ) }}}">{{{ data.get( 'title' ) }}}</div>
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

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon detach--question danger tip--top-right" data-tip="<?php esc_attr_e( 'Detach question', 'lifterlms' ); ?>" href="#llms-detach-model">
					<span class="fa fa-chain-broken"></span>
				</a>
			<# } #>

			<a class="llms-action-icon danger delete--question tip--top-right" data-tip="<?php esc_attr_e( 'Delete question', 'lifterlms' ); ?>" href="#llms-trash" tabindex="-1">
				<i class="fa fa-trash" aria-hidden="true"></i>
			</a>
		</div>


		<div class="llms-question-points llms-editable-number tip--top-left" data-tip="{{{ data.get_points_percentage() }}}">
			<input class="llms-input two-digits" min="0" max="99" name="question_points" type="number" value="{{{ data.get( 'points' ) }}}" tabindex="-1"<# if ( ! data.get( 'question_type' ).get( 'points' ) ) { print( ' disabled'); }#>><small><?php _e( 'points', 'lifterlms' ); ?></small>
		</div>

	</header>

	<section class="llms-question-body <# if ( data.get( '_expanded' ) ) { print( ' active' ); }#>">

		<div class="llms-question-features">

			<?php do_action( 'llms_builder_question_before_features' ); ?>

			<div class="llms-settings-row">
				<# if ( data.get( 'question_type' ).get( 'description' ) ) { #>
					<div class="llms-editable-toggle-group">
						<label class="llms-switch">
							<span class="llms-label"><?php _e( 'Description', 'lifterlms' ); ?></span>
							<input type="checkbox" name="description_enabled"<# if ( 'yes' === data.get( 'description_enabled' ) ) { print( ' checked' ) } #>>
							<div class="llms-switch-slider"></div>
						</label>

						<# if ( 'yes' === data.get( 'description_enabled' ) ) { #>
							<div class="llms-editable-editor">
								<textarea data-attribute="content" id="question-desc--{{{ data.get( 'id' ) }}}">{{{ data.get( 'content' ) }}}</textarea>
							</div>
						<# } #>
					</div>

					<# if ( 'yes' === data.get( 'description_enabled' ) ) { #>
						<div class="llms-breaker"></div>
					<# } #>
				<# } #>

				<# if ( data.get( 'question_type' ).get( 'image' ) ) { #>
					<div class="llms-editable-toggle-group">
						<label class="llms-switch">
							<span class="llms-label"><?php _e( 'Image', 'lifterlms' ); ?></span>
							<input type="checkbox" name="image.enabled"<# if ( 'yes' === data.get( 'image' ).get( 'enabled' ) ) { print( ' checked' ) } #>>
							<div class="llms-switch-slider"></div>
						</label>

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
					</div>
				<# } #>

				<# if ( data.get( 'question_type' ).get( 'video' ) ) { #>
					<div class="llms-editable-toggle-group">
						<label class="llms-switch">
							<span class="llms-label"><?php _e( 'Video', 'lifterlms' ); ?></span>
							<input type="checkbox" name="video_enabled"<# if ( 'yes' === data.get( 'video_enabled' ) ) { print( ' checked' ) } #>>
							<div class="llms-switch-slider"></div>
						</label>

						<# if ( 'yes' === data.get( 'video_enabled' ) ) { #>
							<div class="llms-editable-video tip--top-right" data-tip="<?php esc_attr_e( 'Use YouTube, Vimeo, or Wistia video URLS.', 'lifterlms' ); ?>">
								<input class="llms-input standard" data-attribute="video_src" data-original-content="{{{ data.get( 'video_src' ) }}}" placeholder="<?php esc_attr_e( 'https://', 'lifterlms' ); ?>" data-type="video" name="video_src" value="{{{ data.get( 'video_src' ) }}}">
							</div>
						<# } #>
					</div>
				<# } #>
			</div>

			<?php do_action( 'llms_builder_question_after_features' ); ?>

		</div>

		<# if ( data.get( 'question_type' ).get( 'choices' ) ) { #>
			<div class="llms-question-choices-wrapper">

				<header class="llms-question-choices-list-header">

					<span class="llms-label"><?php _e( 'Choices', 'lifterlms' ); ?></span>

					<# if ( data.get( 'question_type' ).get_multi_choices() && data.get( 'question_type' ).get_choice_selectable() ) { #>
						<label class="llms-switch">
							<span class="llms-label"><?php _e( 'Multiple Correct Choices', 'lifterlms' ); ?></span>
							<input type="checkbox" name="multi_choices"<# if ( 'yes' === data.get( 'multi_choices' ) ) { print( ' checked' ) } #>>
							<div class="llms-switch-slider"></div>
						</label>
					<# } #>

				</header>

				<ul class="llms-question-choices<# if ( 'yes' === data.get( 'multi_choices' ) ) { print( ' multi-choices' ) } #>"></ul>
			</div>
		<# } else if ( 'group' === data.get( 'question_type' ).get( 'id' ) ) { #>
			<ul class="llms-quiz-questions" data-empty-msg="<?php esc_attr_e( 'Drag a question here to add it to the group.', 'lifterlms' ); ?>"></ul>
		<# } #>

		<div class="llms-question-features">

			<div class="llms-settings-row">
				<# if ( data.get( 'question_type' ).get( 'clarifications' ) ) { #>
					<div class="llms-editable-toggle-group">
						<label class="llms-switch">
							<span class="llms-label"><?php _e( 'Result Clarifications', 'lifterlms' ); ?></span>
							<input type="checkbox" name="clarifications_enabled"<# if ( 'yes' === data.get( 'clarifications_enabled' ) ) { print( ' checked' ) } #>>
							<div class="llms-switch-slider"></div>
						</label>
						<# if ( 'yes' === data.get( 'clarifications_enabled' ) ) { #>
							<div class="llms-editable-editor">
								<textarea data-attribute="clarifications" id="question-clarifications--{{{ data.get( 'id' ) }}}">{{{ data.get( 'clarifications' ) }}}</textarea>
							</div>
						<# } #>
					</div>
				<# } #>
			</div>

		</div>

	</section>

</script>
