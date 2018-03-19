<?php
/**
 * Builder lesson settings template
 * @since   [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-lesson-settings-template">

	<header class="llms-model-header" id="llms-lesson-header">

		<h3 class="llms-headline llms-model-title">
			<?php _e( 'Title', 'lifterlms' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<label class="llms-switch llms-model-status">
			<span class="llms-label"><?php _e( 'Published', 'lifterlms' ); ?></span>
			<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
			<div class="llms-switch-slider"></div>
		</label>

		<div class="llms-action-icons">

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon tip--bottom-left" data-tip="<?php esc_attr_e( 'Open WordPress Editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}" target="_blank">
					<i class="fa fa-wordpress" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php _e( 'Open WordPress Editor', 'lifterlms' ); ?></span>
				</a>

				<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach-model">
					<i class="fa fa-chain-broken" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php _e( 'Detach Lesson', 'lifterlms' ); ?></span>
				</a>
			<# } #>

			<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php _e( 'Delete Lesson', 'lifterlms' ); ?>" href="#llms-trash-model" tabindex="-1">
				<i class="fa fa-trash" aria-hidden="true"></i>
				<span class="screen-reader-text"><?php _e( 'Delete Lesson', 'lifterlms' ); ?></span>
			</a>

		</div>

	</header>

	<section class="llms-model-settings active">

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
				<span class="llms-label"><?php _e( 'Video Embed URL', 'lifterlms' ); ?></span>
				<div class="llms-editable-video tip--top-right" data-tip="<?php esc_attr_e( 'Use YouTube, Vimeo, or Wistia video URLS.', 'lifterlms' ); ?>">
					<input class="llms-input standard" data-attribute="video_embed" data-original-content="{{{ data.get( 'video_embed' ) }}}" placeholder="<?php esc_attr_e( 'https://', 'lifterlms' ); ?>" data-type="video" name="video_embed" value="{{{ data.get( 'video_embed' ) }}}">
				</div>
			</div>

			<div class="llms-editable-toggle-group">
				<span class="llms-label"><?php _e( 'Audio Embed URL', 'lifterlms' ); ?></span>
				<div class="llms-editable-video tip--top-right" data-tip="<?php esc_attr_e( 'Use SoundCloud or Spotify audio URLS.', 'lifterlms' ); ?>">
					<input class="llms-input standard" data-attribute="audio_embed" data-original-content="{{{ data.get( 'audio_embed' ) }}}" placeholder="<?php esc_attr_e( 'https://', 'lifterlms' ); ?>" data-type="video" name="audio_embed" value="{{{ data.get( 'audio_embed' ) }}}">
				</div>
			</div>

		</div>

		<div class="llms-settings-row">
			<div class="llms-editable-toggle-group">
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Free Lesson', 'lifterlms' ); ?></span>
					<input type="checkbox" name="free_lesson"{{{ _.checked( 'yes', data.get( 'free_lesson' ) ) }}}>
					<div class="llms-switch-slider"></div>
				</label>
			</div>
		</div>

		<# if ( ! data.is_first_in_course() ) { #>
		<div class="llms-settings-row">

			<div class="llms-editable-toggle-group">
				<label class="llms-switch">
					<span class="llms-label"><?php _e( 'Prerequisite', 'lifterlms' ); ?></span>
					<input type="checkbox" name="has_prerequisite"{{{ _.checked( 'yes', data.get( 'has_prerequisite' ) ) }}}>
					<div class="llms-switch-slider"></div>
				</label>

				<# if ( 'yes' === data.get( 'has_prerequisite' ) ) { #>
					<div class="llms-editable-select">
						<select name="prerequisite">
							<# data.get_course().get( 'sections' ).each( function( section, si ) { #>
								<# if ( si <= data.get_parent().collection.indexOf( data.get_parent() ) ) { #>
								<optgroup label="{{{ LLMS.l10n.replace( 'Section %1$d: %2$s', { '%1$d': section.get( 'order' ), '%2$s': section.get( 'title' ) } ) }}}">
									<# section.get( 'lessons' ).each( function( lesson, li ) { #>
										<# if ( si !== data.get_parent().collection.indexOf( data.get_parent() ) || li < data.collection.indexOf( data ) ) { #>
										<option value="{{{ lesson.get( 'id' ) }}}"{{{ _.selected( lesson.get( 'id' ), data.get( 'prerequisite' ) ) }}}>
											{{{ LLMS.l10n.replace( 'Lesson %1$d: %2$s', { '%1$d': lesson.get( 'order' ), '%2$s': lesson.get( 'title' ) } ) }}}
										</option>
										<# } #>
									<# } ); #>
								</optgroup>
								<# } #>
							<# } ); #>
						</select>
					</div>
				<# } #>
			</div>

		</div>
		<# } #>

		<div class="llms-settings-row">

			<div class="llms-editable-toggle-group drip-group">
				<div class="llms-editable-select drip-method">
					<span class="llms-label"><?php _e( 'Drip Method', 'lifterlms' ); ?></span>
					<select name="drip_method">
						<option value=""{{{ _.selected( '', data.get( 'drip_method' ) ) }}}><?php esc_html_e( 'None', 'lifterlms' ); ?></option>
						<option value="date"{{{ _.selected( 'date', data.get( 'drip_method' ) ) }}}><?php esc_html_e( 'On a specific date', 'lifterlms' ); ?></option>
						<option value="enrollment"{{{ _.selected( 'enrollment', data.get( 'drip_method' ) ) }}}><?php esc_html_e( '# of days after course enrollment', 'lifterlms' ); ?></option>
						<# if ( 'yes' === data.get_course().get( 'start_date' ) ) { #>
						<option value="start"{{{ _.selected( 'start', data.get( 'drip_method' ) ) }}}><?php esc_html_e( '# of days after course start date', 'lifterlms' ); ?></option>
						<# } #>
						<# if ( 'yes' === data.get( 'has_prerequisite' ) ) { #>
						<option value="prerequisite"{{{ _.selected( 'prerequisite', data.get( 'drip_method' ) ) }}}><?php esc_html_e( '# of days after prerequisite lesson completion', 'lifterlms' ); ?></option>
						<# } #>
					</select>
				</div>

				<# if ( -1 !== _.indexOf( [ 'enrollment', 'start', 'prerequisite' ], data.get( 'drip_method' ) ) ) { #>
					<div class="llms-editable-number drip-availability">
						<span class="llms-label"><?php _e( '# of days', 'lifterlms' ); ?></span>
						<input class="llms-input standard" data-attribute="days_before_available" data-original-content="{{{ data.get( 'days_before_available' ) }}}" min="0" name="days_before_available" type="number" value="{{{ data.get( 'days_before_available' ) }}}">
					</div>
				<# } else if ( 'date' === data.get( 'drip_method' ) ) { #>
					<div class="llms-editable-date drip-availability">
						<span class="llms-label"><?php _e( 'Date', 'lifterlms' ); ?></span>
						<input class="llms-input standard" data-attribute="date_available" data-date-timepicker="false" data-date-format="Y-m-d" data-original-content="{{{ data.get( 'date_available' ) }}}" name="date_available" type="text" value="{{{ data.get( 'date_available' ) }}}">
					</div>
					<div class="llms-editable-date drip-availability">
						<span class="llms-label"><?php _e( 'Time', 'lifterlms' ); ?></span>
						<input class="llms-input standard" data-attribute="time_available" data-date-datepicker="false" data-date-format="h:i A" data-original-content="{{{ data.get( 'time_available' ) }}}" name="time_available" type="text" value="{{{ data.get( 'time_available' ) }}}">
					</div>
				<# } #>
			</div>

		</div>

	</section>

</script>
