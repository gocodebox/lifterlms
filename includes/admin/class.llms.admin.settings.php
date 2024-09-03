<?php
/**
 * Admin Settings and fields
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 1.0.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin settings and fields class
 *
 * @since 1.0.0
 * @since 3.29.0 Unknown.
 * @since 3.34.4 Add "keyval" field for displaying custom html next to a setting key.
 * @since 3.35.0 Sanitize input data.
 * @since 3.35.1 Fix saving issue.
 * @since 3.35.2 Don't strip tags on editor and textarea fields that allow HTML.
 * @since 3.37.9 Add option for fields to show an asterisk for required fields.
 * @since 4.2.0 Use dashicons for tooltip icon display.
 */
class LLMS_Admin_Settings {

	/**
	 * Settings array
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Errors array
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Messages array
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Instantiates setting page objects, if not already done, and returns them.
	 *
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return LLMS_Settings_Page[] self::$settings
	 */
	public static function get_settings_tabs() {

		if ( empty( self::$settings ) ) {
			$settings = array();

			$settings[] = include 'settings/class.llms.settings.general.php';
			$settings[] = include 'settings/class.llms.settings.courses.php';
			$settings[] = include 'settings/class.llms.settings.memberships.php';
			$settings[] = include 'settings/class.llms.settings.accounts.php';
			$settings[] = include 'settings/class.llms.settings.checkout.php';
			$settings[] = include 'settings/class.llms.settings.engagements.php';
			$settings[] = include 'settings/class.llms.settings.notifications.php';
			$settings[] = include 'settings/class.llms.settings.integrations.php';

			/**
			 * Allow 3rd parties to add or remove setting pages.
			 *
			 * @since 1.0.0
			 *
			 * @param LLMS_Settings_Page[] $settings Setting page objects.
			 */
			self::$settings = apply_filters( 'lifterlms_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save method. Saves all fields on current tab
	 *
	 * @return void
	 */
	public static function save() {

		global $current_tab;
		if ( isset( $_POST['_wpnonce'] ) && ! llms_verify_nonce( '_wpnonce', 'lifterlms-settings' ) ) {
			die( esc_html__( 'Whoa! something went wrong there!. Please refresh the page and retry.', 'lifterlms' ) );
		}

		do_action( 'lifterlms_settings_save_' . $current_tab );
		do_action( 'lifterlms_update_options_' . $current_tab );
		do_action( 'lifterlms_update_options' );

		self::set_message( __( 'Your settings have been saved.', 'lifterlms' ) );

		do_action( 'lifterlms_settings_saved' );
		do_action( 'lifterlms_settings_saved_' . $current_tab );
	}

	/**
	 * set message to messages array
	 *
	 * @param string $message
	 * @return void
	 */
	public static function set_message( $message ) {
		self::$messages[] = $message;
	}

	/**
	 * set message to messages array
	 *
	 * @param string $message
	 * @return void
	 */
	public static function set_error( $message ) {
		self::$errors[] = $message;
	}

	/**
	 * display messages in settings
	 *
	 * @return void
	 */
	public static function display_messages_html() {

		if ( count( self::$errors ) > 0 ) {

			foreach ( self::$errors as $error ) {
				echo '<div class="error"><p><strong>' . wp_kses_post( $error ) . '</strong></p></div>';
			}
		} elseif ( count( self::$messages ) > 0 ) {

			foreach ( self::$messages as $message ) {
				echo '<div class="updated"><p><strong>' . wp_kses_post( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings Page output tabs
	 *
	 * @since 1.0.0
	 * @since 3.29.0 Unknown.
	 * @since 3.35.0 Sanitize `$_GET` data.
	 * @since 3.35.1 Fix issue causing data to be saved on every page load.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public static function output() {

		global $current_tab;

		do_action( 'lifterlms_settings_start' );

		self::get_settings_tabs();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce is checked in self::save().
		$current_tab = empty( $_GET['tab'] ) ? 'general' : llms_filter_input_sanitize_string( INPUT_GET, 'tab' );

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is checked in self::save().
		if ( ! empty( $_POST ) ) {
			self::save();
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing.

		$err = llms_filter_input_sanitize_string( INPUT_GET, 'llms_error' );
		if ( $err ) {
			self::set_error( $err );
		}

		$msg = llms_filter_input_sanitize_string( INPUT_GET, 'llms_message' );
		if ( $msg ) {
			self::set_message( $msg );
		}

		self::display_messages_html();

		$tabs = apply_filters( 'lifterlms_settings_tabs_array', array() );

		include 'views/settings.php';
	}

	/**
	 * Output fields for settings tabs. Dynamically generates fields.
	 *
	 * Needs to be refactored! Sets up all of the fields..gross...
	 *
	 * @return void
	 */
	public static function output_fields( $settings ) {

		foreach ( $settings as $field ) {

			// Skip item if no field type is set.
			if ( ! isset( $field['type'] ) ) {
				continue; }

			// Output the field.
			self::output_field( $field );

		}
	}


	/**
	 * Output fields
	 *
	 * @since Unknown.
	 * @since 3.29.0 Unknown.
	 * @since 3.34.4 Add "keyval" field for displaying custom html next to a setting key.
	 * @since 3.37.9 Add option for fields to show an asterisk for required fields.
	 * @since 5.0.2 Pass any option value sanitized as a "slug" through `urldecode()` prior to displaying it.
	 * @since 7.0.0 Add `$after_html` to all field types.
	 *
	 * @param array $field {
	 *     Array of field settings.
	 *
	 *     @type string $id                The setting ID. Used as the from element's `name` and `id` attributes and
	 *                                     automatically correspond to an option key using the WP options API.
	 *     @type string $type              The field type. Accepts: 'title', 'table', 'subtitle', 'desc', 'custom-html',
	 *                                     'custom-html-no-wrap', 'sectionstart', 'sectionend', 'button', 'hidden', 'keyval',
	 *                                     'text', 'email', 'number', 'password', 'textarea', 'wpeditor', 'select', 'multiselect',
	 *                                     'radio', 'checkbox', 'image', 'single_select_page', 'single_select_membership'.
	 *     @type string $title             The title / name of the option as displayed to the user.
	 *     @type string $name              For "button" fields only: used as HTML `name` attribute. If not supplied the default
	 *                                     value "save" will be used. For other field types used as a fallback for `$title` if
	 *                                     no value is supplied.
	 *     @type string $class             A space-separated list of CSS class names to apply the setting form element (the
	 *                                     `<input>`, `<select>` etc...).
	 *     @type string $css               An inline CSS style string.
	 *     @type string $default           The default value of the setting.
	 *     @type string $desc              The setting's description.
	 *     @type bool   $desc_tooltip      If `true`, displays `$desc` in a hoverable tooltip.
	 *     @type string $value             The value of the setting. If supplied this will override the automatic setting retrieval
	 *                                     using `get_option( $id, $default )`.
	 *     @type array  $custom_attributes An associative array of custom HTML attributes to be added to the form element (the
	 *                                     `<input>`, `<select>` etc...).
	 *     @type bool   $disabled          If `true` adds the `llms-disabled-field` class to the settings field wrapper.
	 *     @type bool   $required          If `true`, text, email, number, and password fields will require user input.
	 *     @type string $secure_option     The name of settings secure option equivalent. If specified, the fields value will be
	 *                                     automatically removed from the database and the value will be masked when displayed on
	 *                                     on screen. See {@see llms_get_secure_option()} for more information.
	 *     @type string $sanitize          Automatically apply the specified sanitization to the value before storing and outputting
	 *                                     the stored value. Supported filters:
	 *                                       + "slug": Uses `sanitize_title()` on the value when storing and `urldecode()` when displaying.
	 *     @type string $after_html        Additional HTML to add after the field's form element.
	 *     @type array  $editor_settings   Used with "wpeditor" field type only. An array of options to pass to `wp_editor()` as the `$settings` argument.
	 *     @type array  $options           For "select", "multiselect", and "radio" fields, an array of key/value pairs where the
	 *                                     key is the setting value stored in database and the value is the setting label displayed
	 *                                     on screen.
	 * }
	 * @return void
	 */
	public static function output_field( $field ) {

		// Set missing values with defaults.
		$field = self::set_field_defaults( $field );

		// Setup custom attributes.
		$custom_attributes = self::format_field_custom_attributes( $field['custom_attributes'] ?? array() );

		// Setup field description and tooltip.
		extract( self::set_field_descriptions( $field ) );
		$description .= ' ' . $field['after_html'];

		// Get the field value.
		$option_value = isset( $field['value'] ) ? $field['value'] : self::get_option( $field['id'], $field['default'] );

		// Setup the disabled CSS class.
		$disabled_class = ( isset( $field['disabled'] ) && true === $field['disabled'] ) ? 'llms-disabled-field' : '';

		// Switch based on type.
		switch ( $field['type'] ) {

			// Section Titles.
			case 'title':
				if ( ! empty( $field['title'] ) ) {
					echo '<p class="llms-label">' . esc_html( $field['title'] ) . '</p>';
				}
				if ( ! empty( $field['desc'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in wp_kses_post()
					echo '<p class="llms-description">' . wpautop( wptexturize( wp_kses_post( $field['desc'] ) ) ) . '</p>';
				}

				echo '<table class="form-table">' . "\n\n";

				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) );

				}
				break;

			case 'table':
				echo '<tr valign="top" class="' . esc_attr( $disabled_class ) . '"><td>';

					$field['table']->get_results();
					$field['table']->output_table_html();

				echo '</td></tr>';
				break;

			case 'subtitle':
				if ( ! empty( $field['title'] ) ) {
					echo '<tr valign="top" class="' . esc_attr( $disabled_class ) . '"><td colspan="2">
				    	<h3 class="llms-subtitle">' . esc_html( $field['title'] ) . '</h3>';
					if ( ! empty( $field['desc'] ) ) {
						echo '<p>' . wp_kses_post( $field['desc'] ) . '</p>';
					}
					echo '</tr></td>';
				}
				break;

			case 'desc':
				if ( ! empty( $field['desc'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in wp_kses_post()
					echo '<th colspan="2" style="font-weight: normal;">' . wpautop( wptexturize( wp_kses_post( $field['desc'] ) ) ) . '</th>';
				}

				break;

			case 'custom-html':
				if ( ! empty( $field['value'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in value / template file..
					echo '<tr valign="top" class="' . esc_attr( $disabled_class ) . '"><td colspan="2">' . $field['value'] . '</tr></td>';
				}
				break;

			case 'custom-html-no-wrap':
				if ( ! empty( $field['value'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in value / template file..
					echo $field['value'];
				}
				break;

			case 'sectionstart':
				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_before' );

					echo '<div class="llms-setting-group ' . esc_attr( $field['class'] ) . '">';

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_start' );

				}
				break;

			case 'sectionend':
				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_end' );

				}

				echo '</table>';
				echo '</div>';

				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_after' );

				}
				break;

			case 'button':
				$name = isset( $field['name'] ) ? $field['name'] : 'save';
				echo '<tr valign="top" class="' . esc_attr( $disabled_class ) . '"><th><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['title'] ) . '</label>';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tooltip escaped in set_field_descriptions().
				echo $tooltip;
				echo '</th>';

				echo '<td class="forminp forminp-' . esc_attr( sanitize_title( $field['type'] ) ) . '">';
				echo '<div id="llms-form-wrapper">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $description escaped in set_field_descriptions().
				echo $description . '<br><br>';
				echo '<input name="' . esc_attr( $name ) . '" class="llms-button-primary" type="submit" value="' . esc_attr( $field['value'] ) . '" />';
				echo '</div>';
				echo '</td></tr>';
				// phpcs:ignore -- commented out code
				// get_submit_button( 'Filter Results', 'primary', 'llms_search', true, array( 'id' => 'llms_analytics_search' ) );
				break;

			case 'hidden':
				echo '<th></th>';
				echo '<td><input type="hidden"
					name="' . esc_attr( $field['id'] ) . '"
					id="' . esc_attr( $field['id'] ) . '"
					value="' . esc_attr( $field['value'] ) . '">';
				break;

			case 'keyval':
				?><tr valign="top">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions(). ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<div id="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['value'] ); ?></div>
					</td>
				</tr>
				<?php

				break;

			case 'text':
			case 'email':
			case 'number':
			case 'password':
				$type     = $field['type'];
				$class    = '';
				$required = ! empty( $field['required'] );

				$secure_val   = isset( $field['secure_option'] ) ? llms_get_secure_option( $field['secure_option'], false ) : false;
				$option_value = ( false !== $secure_val ) ? str_repeat( '*', strlen( $secure_val ) ) : $option_value;

				// Ensure slugs with non-latin characters are not displayed as urlencoded strings.
				if ( ! empty( $field['sanitize'] ) && 'slug' === $field['sanitize'] ) {
					$option_value = urldecode( $option_value );
				}

				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>">
							<?php echo esc_html( $field['title'] ); ?>
							<?php echo $required ? '<span class="llms-required">*</span>' : ''; ?>
						</label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions(). ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="<?php echo esc_attr( $type ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo $secure_val ? 'disabled="disabled"' : ''; ?>
							<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
							<?php echo $required ? 'required="required"' : ''; ?>
							/> <?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			// Textarea.
			case 'textarea':
				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<textarea
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
							><?php echo esc_textarea( $option_value ); ?></textarea>
						<?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			case 'wpeditor':
				$editor_settings = isset( $field['editor_settings'] ) ? $field['editor_settings'] : array();
				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<?php wp_editor( $option_value, $field['id'], $editor_settings ); ?>
						<?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			// Select boxes.
			case 'select':
			case 'multiselect':
				$field_name = esc_attr( $field['id'] );
				if ( 'multiselect' === $field['type'] ) {
					$field_name .= '[]';
				}
				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<select
							name="<?php echo esc_attr( $field_name ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
							<?php
							if ( 'multiselect' === $field['type'] ) {
								echo 'multiple="multiple"'; }
							?>
							>
							<?php
							foreach ( $field['options'] as $key => $val ) {

								// Convert an array from llms_make_select2_post_array().
								if ( is_array( $val ) ) {
									$key = $val['key'];
									$val = $val['title'];
								}

								?>
								<option value="<?php echo esc_attr( $key ); ?>"
								<?php
								if ( is_array( $option_value ) ) {
									selected( in_array( $key, $option_value ), true );
								} else {
									selected( $option_value, $key );
								}
								?>
								><?php echo wp_kses_post( $val ); ?></option>
								<?php
							}
							?>
							</select>
						<?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			// Radio inputs.
			case 'radio':
				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
						<fieldset>
							<?php echo wp_kses_post( $description ); ?>
							<ul>
							<?php
							foreach ( $field['options'] as $key => $val ) {
								?>
								<li>
									<label><input
										name="<?php echo esc_attr( $field['id'] ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										type="radio"
										style="<?php echo esc_attr( $field['css'] ); ?>"
										class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
										<?php checked( $key, $option_value ); ?>
										/> <?php echo esc_html( $val ); ?></label>
									</li>
									<?php
							}
							?>
							</ul>
						</fieldset>
					</td>
				</tr>
				<?php
				break;

			// Checkbox input.
			case 'checkbox':
				$visbility_class = array();

				if ( ! isset( $field['hide_if_checked'] ) ) {
					$field['hide_if_checked'] = false;
				}
				if ( ! isset( $field['show_if_checked'] ) ) {
					$field['show_if_checked'] = false;
				}
				if ( 'yes' === $field['hide_if_checked'] || 'yes' === $field['show_if_checked'] ) {
					$visbility_class[] = 'hidden_option';
				}
				if ( 'option' === $field['hide_if_checked'] ) {
					$visbility_class[] = 'hide_options_if_checked';
				}
				if ( 'option' === $field['show_if_checked'] ) {
					$visbility_class[] = 'show_options_if_checked';
				}
				if ( ! isset( $field['checkboxgroup'] ) || 'start' === $field['checkboxgroup'] ) {
					?>
						<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?> <?php echo esc_attr( $disabled_class ); ?>">
							<th><?php echo esc_html( $field['title'] ); ?></th>
							<td class="forminp forminp-checkbox">
								<fieldset>
					<?php
				} else {
					?>
						<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
					<?php
				}

				if ( ! empty( $field['title'] ) ) {
					?>
						<legend class="screen-reader-text"><span><?php echo esc_html( $field['title'] ); ?></span></legend>
					<?php
				}

				?>
					<label for="<?php echo esc_attr( $field['id'] ); ?>">
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="checkbox"
							value="1"
							<?php checked( $option_value, 'yes' ); ?>
							<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
						/> <?php echo wp_kses_post( $description ); ?>
					</label> <?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
				<?php

				if ( ! isset( $field['checkboxgroup'] ) || 'end' === $field['checkboxgroup'] ) {
					?>
								</fieldset>
							</td>
						</tr>
					<?php
				} else {
					?>
						</fieldset>
					<?php
				}
				break;

			case 'image':
				$type  = $field['type'];
				$class = '';

				if ( $option_value ) {
					// Media lib object ID.
					if ( is_numeric( $option_value ) ) {
						$size       = isset( $field['image_size'] ) ? $field['image_size'] : 'medium';
						$attachment = wp_get_attachment_image_src( $option_value, $size );
						$src        = $attachment[0];
					} else {
						// Raw img src.
						$src = $option_value;
					}
				} else {
					$src = '';
				}

				?>
				<tr valign="top" class="<?php echo esc_attr( $disabled_class ); ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?>
					</th>
					<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">

						<img class="llms-image-field-preview" src="<?php echo esc_url( $src ); ?>">
						<button class="llms-button-secondary llms-image-field-upload" data-id="<?php echo esc_attr( $field['id'] ); ?>" type="button">
							<span class="dashicons dashicons-admin-media"></span>
							<?php esc_html_e( 'Upload', 'lifterlms' ); ?>
						</button>
						<button class="llms-button-danger llms-image-field-remove<?php echo ( ! $src ) ? ' hidden' : ''; ?>" data-id="<?php echo esc_attr( $field['id'] ); ?>" type="button">
							<span class="dashicons dashicons-no"></span>
						</button>
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="hidden"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in format_field_custom_attributes. ?>
							/> <?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			// Single page selects.
			case 'single_select_page':
				$args = array(
					'name'             => $field['id'],
					'id'               => $field['id'],
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'class'            => $field['class'],
					'echo'             => false,
					'selected'         => absint( self::get_option( $field['id'] ) ),
				);

				if ( isset( $field['args'] ) ) {
					$args = wp_parse_args( $field['args'], $args );
				}

				?>
				<tr valign="top" class="single_select_page">
					<th><?php echo esc_html( $field['title'] ); ?> <?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions.?></th>
					<td class="forminp">
						<?php 
						// PHPCS ignore reason: This is a dropdown and the output is escaped in wp_dropdown_pages.
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page&hellip;', 'lifterlms' ) . "' style='" . esc_attr( $field['css'] ) . "' class='" . esc_attr( $field['class'] ) . "' id=", wp_dropdown_pages( $args ) ); ?>
						<?php echo wp_kses_post( $description ); ?>
					</td>
				</tr>
				<?php
				break;

			// Single page selects.
			case 'single_select_membership':
				$args  = array(
					'posts_per_page' => -1,
					'post_type'      => 'llms_membership',
					'nopaging'       => true,
					'post_status'    => 'publish',
					'class'          => $field['class'],
					'selected'       => absint( self::get_option( $field['id'] ) ),
				);
				$posts = get_posts( $args );

				if ( isset( $field['args'] ) ) {
					$args = wp_parse_args( $field['args'], $args );
				}

				?>
				<tr valign="top" class="single_select_membership">
					<th><?php echo esc_html( $field['title'] ); ?> <?php echo $tooltip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in set_field_descriptions. ?></th>
					<td class="forminp">
						<select class="<?php echo esc_attr( $args['class'] ); ?>" style="<?php echo esc_attr( $field['css'] ); ?>" name="lifterlms_membership_required" id="lifterlms_membership_required">
							<option value=""> <?php esc_html_e( 'None', 'lifterlms' ); ?></option>
							<?php
							foreach ( $posts as $post ) :
								setup_postdata( $post );
								if ( $args['selected'] === $post->ID ) {
									$selected = 'selected';
								} else {
									$selected = '';
								}
								?>
							<option value="<?php echo esc_attr( $post->ID ); ?>" <?php echo esc_attr( $selected ); ?> ><?php echo esc_html( $post->post_title ); ?></option>
						<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<?php
				break;

			// Default: run an action.
			default:
				do_action( 'lifterlms_admin_field_' . $field['type'], $field, $option_value, $description, $tooltip, $custom_attributes );

				break;
		}
	}

	/**
	 * Add and set default values for a field when looping
	 *
	 * @since 1.4.5
	 * @since 7.0.0 Use `wp_parse_args()` to simplify method logic & add `after_html` default.
	 *
	 * @param array $field Associative array of field data, {@see LLMS_Admin_Settings::output_field()} for a full description.
	 * @return array
	 */
	public static function set_field_defaults( $field = array() ) {

		$field = wp_parse_args(
			$field,
			array(
				'id'           => '',
				'title'        => $field['name'] ?? '',
				'class'        => '',
				'css'          => '',
				'default'      => '',
				'desc'         => '',
				'desc_tooltip' => '',
				'after_html'   => '',
			)
		);

		return $field;
	}

	/**
	 * Setup a field's tooltip and description based on supplied values
	 *
	 * @since 1.4.5
	 * @since 3.24.0 Unknown.
	 * @since 4.2.0 Use a dashicon in place of image for tooltip icon.
	 *
	 * @param array $field Associative array of field data.
	 * @return array {
	 *     Associative array containing field description and tooltip HTML.
	 *
	 *     @type string $description Description element HTML.
	 *     @type string $tooltip     Tooltip element HTML.
	 * }
	 */
	public static function set_field_descriptions( $field = array() ) {

		$description = '';
		$tooltip     = '';

		if ( true === $field['desc_tooltip'] ) {

			$description = '';
			$tooltip     = $field['desc'];

		} elseif ( ! empty( $field['desc_tooltip'] ) ) {

			$description = $field['desc'];
			$tooltip     = $field['desc_tooltip'];

		} elseif ( ! empty( $field['desc'] ) ) {

			$description = $field['desc'];
			$tooltip     = '';

		}

		if ( $description && in_array( $field['type'], array( 'radio' ), true ) ) {

			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';

		} elseif ( $description && in_array( $field['type'], array( 'checkbox' ), true ) ) {

			$description = wp_kses_post( $description );

		} elseif ( $description ) {

			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		if ( $tooltip && in_array( $field['type'], array( 'checkbox' ), true ) ) {

			$tooltip = '<p class="description">' . $tooltip . '</p>';

		} elseif ( $tooltip ) {

			$position = isset( $field['tooltip_position'] ) ? $field['tooltip_position'] : 'top-right';
			$tooltip  = '<span class="llms-help-tooltip tip--' . esc_attr( $position ) . '" data-tip="' . esc_attr( $tooltip ) . '"><span class="dashicons dashicons-editor-help"></span></span>';

		}

		return compact( 'description', 'tooltip' );
	}

	/**
	 * Formats an associative array of custom field attributes as an array of HTML strings
	 *
	 * @param  array $attributes   associative array of attributes
	 * @return array
	 *
	 * @since  1.4.5
	 */
	public static function format_field_custom_attributes( $attributes = array() ) {

		// Custom attribute handling.
		$custom_attributes = array();
		foreach ( $attributes as $attribute => $attribute_value ) {

			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

		}

		return $custom_attributes;
	}


	/**
	 * Get a setting from the settings API.
	 *
	 * @since Unknown
	 * @since 3.7.5 Unknown
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $default     Optional default value.
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Array value.
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key.
			$option_name = current( array_keys( $option_array ) );

			// Get value.
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			} else {
				$option_value = null;
			}
		} else {
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = array_map( 'stripslashes', $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return null === $option_value ? $default : $option_value;
	}

	/**
	 * Save admin fields.
	 *
	 * Loops through a LifterLMS settings field options array and saves the values via `update_option()`.
	 *
	 * @since 1.0.0
	 * @since 3.29.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 3.35.2 Don't strip tags on editor and textarea fields that allow HTML.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.0.0 Add handling for array fields for standard input types.
	 *              Account for the `maxlength` input text and textarea attribute.
	 *
	 * @param array $settings Opens array to output
	 * @return boolean
	 */
	public static function save_fields( $settings ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is checked in self::save().
		if ( empty( $_POST ) ) {
			return false;
		}

		// Options to update will be stored here.
		$update_options = array();

		// Loop options and get values to save.
		foreach ( $settings as $field ) {

			if ( ! isset( $field['id'] ) ) {
				continue;
			}

			$type = isset( $field['type'] ) ? sanitize_title( $field['type'] ) : '';

			// Remove secure options from the database.
			if ( isset( $field['secure_option'] ) && llms_get_secure_option( $field['secure_option'] ) ) {
				delete_option( $field['id'] );
				continue;
			}

			// Get the option name.
			$option_value = null;

			// Determines if the option value is an array.
			$is_array_option = false !== strpos( $field['id'], '[' );

			switch ( $type ) {

				case 'checkbox':
					if ( $is_array_option ) {
						$option_value = self::get_array_field_posted_value( $field['id'] ) ? 'yes' : 'no';
					} elseif ( isset( $_POST[ $field['id'] ] ) ) {
						$option_value = 'yes';
					} else {
						$option_value = 'no';
					}
					break;

				case 'textarea':
				case 'wpeditor':
					if ( isset( $_POST[ $field['id'] ] ) ) {
						$option_value = wp_kses_post( trim( llms_filter_input( INPUT_POST, $field['id'], FILTER_DEFAULT ) ) );
					} else {
						$option_value = '';
					}
					break;

				case 'password':
				case 'text':
				case 'email':
				case 'number':
				case 'select':
				case 'single_select_page':
				case 'single_select_membership':
				case 'radio':
				case 'hidden':
				case 'image':
					if ( $is_array_option ) {
						$option_value = self::get_array_field_posted_value( $field['id'] );
					} elseif ( isset( $_POST[ $field['id'] ] ) ) {
						$option_value = llms_filter_input_sanitize_string( INPUT_POST, $field['id'] );
					} else {
						$option_value = '';
					}

					if ( isset( $field['sanitize'] ) && 'slug' === $field['sanitize'] ) {
						$option_value = sanitize_title( $option_value );
					}

					break;

				case 'multiselect':
					if ( isset( $_POST[ $field['id'] ] ) ) {
						$option_value = llms_filter_input_sanitize_string( INPUT_POST, $field['id'], array( FILTER_REQUIRE_ARRAY ) );
					} else {
						$option_value = '';
					}
					break;

				default:
					/**
					 * Action run for external field types.
					 *
					 * @since Unknown
					 * @deprecated 7.0.0 Use `llms_update_option_{$type}` filter hook instead.
					 *
					 * @param type $arg Description.
					 */
					do_action_deprecated( "lifterlms_update_option_{$type}", array( $field ), '7.0.0' );

			}

			// Special treatment for the 'maxlength' attribute.
			if ( in_array( $type, array( 'text', 'textarea' ), true ) && isset( $field['custom_attributes']['maxlength'] ) ) {
				$option_value = llms_trim_string( $option_value, (int) $field['custom_attributes']['maxlength'], '' );
			}

			/**
			 * Filters the value of a settings field after it has been parsed and sanitized
			 * and before it is saved to the database.
			 *
			 * The dynamic portion of this hook, `{$type}` refers to the setting field type:
			 * email, text, checkbox, etc...
			 *
			 * @since 7.0.0
			 *
			 * @param string|null $option_value The sanitized option value or `null`.
			 * @param array       $field        The settings field array.
			 */
			$option_value = apply_filters( "llms_update_option_{$type}", $option_value, $field );

			if ( ! is_null( $option_value ) ) {

				if ( $is_array_option ) {

					parse_str( $field['id'], $option_array );

					// Option name is first key.
					$option_name = current( array_keys( $option_array ) );

					// Get old option value.
					if ( ! isset( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = get_option( $option_name, array() );
					}

					if ( ! is_array( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = array();
					}

					// Set keys and value.
					$key = key( $option_array[ $option_name ] );

					$update_options[ $option_name ][ $key ] = $option_value;

				} else {
					$update_options[ $field['id'] ] = $option_value;
				}
			}

			/**
			 * Action run prior to the update of a LifterLMS setting field option.
			 *
			 * An update isn't guaranteed after this action if the method's logic can't
			 * find a valid posted valued to persist to the database.
			 *
			 * @since Unknown
			 *
			 * @param array $field The admin setting field array to be updated.
			 */
			do_action( 'lifterlms_update_option', $field );

		}

		// Now save the options.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return true;
	}

	/**
	 * Retrieves the posted value for an array type setting field.
	 *
	 * @since 7.0.0
	 *
	 * @param string $id The field ID, eg: "my_setting[field_one]".
	 * @return string Returns the (sanitized) posted value or an empty string if it wasn't posted.
	 */
	private static function get_array_field_posted_value( $id ) {

		parse_str( $id, $parsed_id );
		$opt_id  = key( $parsed_id );
		$opt_key = key( $parsed_id[ $opt_id ] );
		$posted  = llms_filter_input_sanitize_string( INPUT_POST, $opt_id, array( FILTER_REQUIRE_ARRAY ) );

		return $posted[ $opt_key ] ?? '';
	}
}
