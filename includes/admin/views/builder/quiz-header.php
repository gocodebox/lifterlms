<?php
/**
 * Builder quiz model header view
 * @since   3.16.0
 * @version 3.17.2
 */
?>
<script type="text/html" id="tmpl-llms-quiz-header-template">

	<h3 class="llms-headline llms-model-title">
		<?php _e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
	</h3>

	<div class="llms-headline llms-quiz-points">
		<?php _e( 'Total Points', 'lifterlms' ); ?>: <strong id="llms-quiz-total-points">{{{ data.get( '_points' ) }}}</strong>
	</div>

	<label class="llms-switch llms-model-status">
		<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
		<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
		<div class="llms-switch-slider"></div>
	</label>

	<div class="llms-action-icons">

		<# if ( ! data.has_temp_id() ) { #>
			<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_attr_e( 'Detach Quiz', 'lifterlms' ); ?>" href="#llms-detach-model">
				<i class="fa fa-chain-broken" aria-hidden="true"></i>
				<span class="screen-reader-text"><?php _e( 'Delete Quiz', 'lifterlms' ); ?></span>
			</a>
		<# } #>

		<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php _e( 'Delete Quiz', 'lifterlms' ); ?>" href="#llms-trash-model" tabindex="-1">
			<i class="fa fa-trash" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Delete Quiz', 'lifterlms' ); ?></span>
		</a>

		<a class="llms-action-icon tip--bottom-left" data-tip="<?php _e( 'Quiz Settings', 'lifterlms' ); ?>" href="#llms-model-settings" tabindex="-1">
			<i class="fa fa-cog" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Quiz Settings', 'lifterlms' ); ?></span>
		</a>

	</div>

	<div class="clear"></div>

	<section class="llms-model-settings<# if ( data.get( '_show_settings' ) ) { print( ' active' ); } #>">

		<?php do_action( 'llms_builder_quiz_before_settings' ); ?>

		<# if ( data.get( 'permalink' ) ) { #>
		<div class="llms-settings-row">
			<div class="llms-editable-toggle-group">
				<div class="llms-editable-toggle-group permalink">
					<span class="llms-label"><?php _e( 'Permalink', 'lifterlms' ); ?>:</span>
						<a target="_blank" href="{{{ data.get( 'permalink' ) }}}">{{{ data.get( 'permalink' ) }}}</a>
						<input class="llms-input permalink" data-attribute="name" data-original-content="{{{ data.get( 'name' ) }}}" data-type="permalink" name="name" type="text" value="{{{ data.get( 'name' ) }}}">
						<a class="llms-action-icon" href="#llms-edit-slug"><i class="fa fa-pencil" aria-hidden="true"></i></a>
				</div>
			</div>
		</div>
		<# } #>

		<div class="llms-settings-row">

			<div class="llms-editable-toggle-group">
				<span class="llms-label"><?php _e( 'Description', 'lifterlms' ); ?></span>
				<div class="llms-editable-editor">
					<textarea data-attribute="content" id="quiz-desc--{{{ data.get( 'id' ) }}}">{{{ data.get( 'content' ) }}}</textarea>
				</div>
			</div>

		</div>

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
					<div class="llms-editable-number">
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

		<?php do_action( 'llms_builder_quiz_after_settings' ); ?>

		<?php if ( get_theme_support( 'lifterlms-quizzes' ) ) :
			$layout = llms_get_quiz_theme_setting( 'layout' );
			if ( $layout ) :
				$layout_id = sprintf( '%1$s%2$s', isset( $layout['id_prefix'] ) ? $layout['id_prefix'] : '', $layout['id'] ); ?>
				<div class="llms-settings-row">

					<div class="llms-editable-toggle-group">

						<div class="llms-editable-select">

							<span class="llms-label"><?php echo $layout['name']; ?></span>

							<?php if ( 'select' === $layout['type'] ) : ?>
								<select name="<?php echo $layout['id']; ?>">
									<?php foreach ( $layout['options'] as $key => $name ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>"<# if ( data.get( '<?php echo $layout_id; ?>' ) === '<?php echo $key; ?>' ) { print( ' selected="selected"' ); } #>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php elseif ( 'image_select' === $layout['type'] ) : ?>
								<div class="llms-editable-img-select">
								<?php foreach ( $layout['options'] as $key => $src ) : ?>
									<label>
										<input name="<?php echo $layout['id']; ?>" type="radio" value="<?php echo esc_attr( $key ); ?>"<# if ( data.get( '<?php echo $layout_id; ?>' ) === '<?php echo $key; ?>' ) { print( ' checked="checked"' ); } #>>
										<span><img src="<?php echo esc_attr( $src ); ?>"></span>
									</label>
								<?php endforeach; ?>
								</div>
							<?php endif; ?>

						</div>

					</div>

				</div>
			<?php endif; ?>
		<?php endif; ?>

	</section>

</script>
