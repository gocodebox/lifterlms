<?php
/**
 * Award Engagements submit meta box.
 *
 * Heavily based on WordPress `post_submit_meta_box()`.
 *
 * @package LifterLMS/Admin/Views/Metaboxes
 *
 * @since 6.0.0
 * @version 6.0.0
 *
 * @property WP_Post $engagement    WP_Post instance of the engagement.
 * @property int     $engagement_id WP_Post ID of the engagement.
 * @property string  $action        The action being performed.
 * @property bool    $can_publish   Whether the current user can publish the engagement.
 * @property string  $fields        Meta box fields such as student information ones.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="submitbox llms-award-engagement-submitbox" id="submitpost">
	<div id="minor-publishing">
		<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key. ?>
		<div style="display:none;">
			<?php submit_button( __( 'Save', 'lifterlms' ), '', 'save' ); ?>
		</div>
		<div class="clear"></div>

		<div id="misc-publishing-actions">
			<div class="misc-pub-section misc-pub-post-status">
				<?php esc_html_e( 'Status:', 'lifterlms' ); ?>
				<span id="post-status-display">
					<?php
					switch ( $engagement->post_status ) {
						case 'publish':
							esc_html_e( 'Awarded', 'lifterlms' );
							break;
						case 'future':
							esc_html_e( 'Scheduled', 'lifterlms' );
							break;
						case 'draft':
						case 'auto-draft':
							esc_html_e( 'Draft', 'lifterlms' );
							break;
					}
					?>
				</span>
				<?php if ( 'publish' === $engagement->post_status || $can_publish ) : /* Select needed because the core js takes the current status from this, when changing the publishing time */ ?>
					<div id="post-status-select" class="hidden">
					<select name="post_status" id="post_status">
						<?php if ( 'publish' === $engagement->post_status ) : ?>
							<option<?php selected( $engagement->post_status, 'publish' ); ?> value='publish'><?php esc_html_e( 'Awarded', 'lifterlms' ); ?></option>
						<?php elseif ( 'future' === $engagement->post_status ) : ?>
							<option<?php selected( $engagement->post_status, 'future' ); ?> value='future'><?php esc_html_e( 'Scheduled', 'lifterlms' ); ?></option>
						<?php else : ?>
							<option<?php selected( $engagement->post_status, 'auto-draft' ); ?> value='draft'><?php esc_html_e( 'Draft', 'lifterlms' ); ?></option>
						<?php endif; ?>
					</select>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		/* translators: Award box date string. 1: Date, 2: Time. See https://www.php.net/manual/datetime.format.php */
		$date_string = __( '%1$s at %2$s', 'lifterlms' );
		/* translators: Award box date format, see https://www.php.net/manual/datetime.format.php */
		$date_format = _x( 'M j, Y', 'award box date format', 'lifterlms' );
		/* translators: Award box time format, see https://www.php.net/manual/datetime.format.php */
		$time_format = _x( 'H:i', 'award box time format', 'lifterlms' );

		if ( 0 !== $engagement_id ) {
			if ( 'future' === $engagement->post_status ) { // Scheduled for awarding at a future date.
				/* translators: Engagement date information. %s: Date on which the engagement is currently scheduled to be awarded. */
				$stamp = __( 'Scheduled for: %s', 'lifterlms' );
			} elseif ( 'publish' === $engagement->post_status ) { // Already awarded.
				/* translators: Post date information. %s: Date on which the engagement was awarded. */
				$stamp = __( 'Awarded on: %s', 'lifterlms' );
			} elseif ( '0000-00-00 00:00:00' === $engagement->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
				$stamp = __( 'Award <b>immediately</b>', 'lifterlms' );
			} elseif ( llms_current_time( 'U', true ) < strtotime( $engagement->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
				/* translators: Post date information. %s: Date on which the post is to be awarded. */
				$stamp = __( 'Schedule for: %s', 'lifterlms' );
			} else { // Draft, 1 or more saves, date specified.
				/* translators: Post date information. %s: Date on which the post is to be awarded. */
				$stamp = __( 'Award on: %s', 'lifterlms' );
			}
			$date = sprintf(
				$date_string,
				date_i18n( $date_format, strtotime( $engagement->post_date ) ),
				date_i18n( $time_format, strtotime( $engagement->post_date ) )
			);
		} else { // Draft (no saves, and thus no date specified).
			$stamp = __( 'Award <b>immediately</b>', 'lifterlms' );
			$date  = sprintf(
				$date_string,
				date_i18n( $date_format, strtotime( llms_current_time( 'mysql' ) ) ),
				date_i18n( $time_format, strtotime( llms_current_time( 'mysql' ) ) )
			);
		}

		if ( $can_publish ) : // Contributors don't get to choose the date of awarding.
			?>
			<div class="misc-pub-section curtime misc-pub-curtime">
				<span id="timestamp">
					<?php echo wp_kses_post( sprintf( $stamp, '<b>' . $date . '</b>' ) ); ?>
				</span>
				<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button">
					<span aria-hidden="true"><?php esc_html_e( 'Edit', 'lifterlms' ); ?></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Edit date and time', 'lifterlms' ); ?></span>
				</a>
				<fieldset id="timestampdiv" class="hide-if-js">
					<legend class="screen-reader-text"><?php esc_html_e( 'Date and time', 'lifterlms' ); ?></legend>
					<?php touch_time( ( 'edit' === $action ), 1 ); ?>
				</fieldset>
			</div>
			<?php
		endif;
		?>
		<div class="clear"></div>
	</div>
	<ul id="misc-fields" class="misc-pub-section" style="margin-top:0">
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $fields;
		?>
	</ul>
	<div id="major-publishing-actions">
		<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $engagement_id ) ) {
				$delete_text = __( 'Delete permanently', 'lifterlms' );
				?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $engagement_id, '', true ); ?>"><?php echo esc_html( $delete_text ); ?></a>
				<?php
			}
			?>
		</div>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if ( ! in_array( $engagement->post_status, array( 'publish', 'future' ), true ) || 0 === $engagement_id ) {
				if ( $can_publish ) :
					if ( ! empty( $engagement->post_date_gmt ) && llms_current_time( 'U', true ) < strtotime( $engagement->post_date_gmt . ' +0000' ) ) :
						?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php echo esc_attr_x( 'Schedule', 'post action/button label', 'lifterlms' ); ?>" />
						<?php submit_button( _x( 'Schedule', 'post action/button label', 'lifterlms' ), 'primary large', 'publish', false ); ?>
						<?php
					else :
						?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Award', 'lifterlms' ); ?>" />
						<?php submit_button( __( 'Award', 'lifterlms' ), 'primary large', 'publish', false ); ?>
						<?php
					endif;
				endif;
			} else {
				?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'lifterlms' ); ?>" />
				<?php submit_button( __( 'Update', 'lifterlms' ), 'primary large', 'save', false, array( 'id' => 'publish' ) ); ?>
				<?php
			}
			?>
		</div>
		<div class="clear"></div>
	</div>
</div>
