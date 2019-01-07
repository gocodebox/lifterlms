<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Class
 * Settings field Factory
 * @since    1.0.0
 * @version  3.24.0
 */
class LLMS_Admin_Settings {

	/**
	* Settings array
	* @access private
	* @var array
	*/
	private static $settings = array();

	/**
	* Errors array
	* @access private
	* @var array
	*/
	private static $errors   = array();

	/**
	* Messages array
	* @access private
	* @var array
	*/
	private static $messages = array();

	/**
	* Inits $settings and includes settings base class.
	*
	* @return self::$settings array
	*/
	public static function get_settings_tabs() {

		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( 'settings/class.llms.settings.page.php' );

			$settings[] = include( 'settings/class.llms.settings.general.php' );
			$settings[] = include( 'settings/class.llms.settings.courses.php' );
			$settings[] = include( 'settings/class.llms.settings.memberships.php' );
			$settings[] = include( 'settings/class.llms.settings.accounts.php' );
			$settings[] = include( 'settings/class.llms.settings.checkout.php' );
			$settings[] = include( 'settings/class.llms.settings.engagements.php' );
			$settings[] = include( 'settings/class.llms.settings.notifications.php' );
			$settings[] = include( 'settings/class.llms.settings.integrations.php' );

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

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'lifterlms-settings' ) ) {
			die( __( 'Whoa! something went wrong there!. Please refresh the page and retry.', 'lifterlms' ) );
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

		if ( sizeof( self::$errors ) > 0 ) {

			foreach ( self::$errors as $error ) {
				echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {

			foreach ( self::$messages as $message ) {
				echo '<div class="updated"><p><strong>' . $message . '</strong></p></div>';
			}
		}
	}

	/**
	* Settings Page output tabs
	*
	* @return void
	*/
	public static function output() {

		global $current_tab;

		do_action( 'lifterlms_settings_start' );

		self::get_settings_tabs();

		$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );

		if ( ! empty( $_POST ) ) {
			self::save(); }

		if ( ! empty( $_GET['llms_error'] ) ) {
			self::set_error( stripslashes( $_GET['llms_error'] ) ); }

		if ( ! empty( $_GET['llms_message'] ) ) {
			self::set_message( stripslashes( $_GET['llms_message'] ) ); }

		self::display_messages_html();

		$tabs = apply_filters( 'lifterlms_settings_tabs_array', array() );

		include 'views/html.admin.settings.php';

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

	    	// skip item if no field type is set
			if ( ! isset( $field['type'] ) ) {
				continue; }

			// output the field
	    	self::output_field( $field );

		}
	}


	/**
	 * Output fields
	 * @param    array  $field  array of field settings
	 * @return   void
	 * @version  3.24.0
	 */
	public static function output_field( $field ) {

		// set missing values with defaults
		$field = self::set_field_defaults( $field );

		$custom_attributes_field = array_key_exists( 'custom_attributes', $field ) ? $field['custom_attributes'] : array();
		// setup custom attributes
			$custom_attributes = self::format_field_custom_attributes( $custom_attributes_field );

		// setup field description and tooltip
		// this will return an associative array of with the keys "description" and "tooltip"
		extract( self::set_field_descriptions( $field ) );

		// allow using value not retrieved via this class
		if ( isset( $field['value'] ) ) {
			$option_value = $field['value'];
		} else {
			// get the option value
			$option_value = self::get_option( $field['id'], $field['default'] );
		}

		$disabled_class = ( isset( $field['disabled'] ) && true === $field['disabled'] ) ? 'llms-disabled-field' : '';

		// Switch based on type
		switch ( $field['type'] ) {

			// Section Titles
			case 'title':

				if ( ! empty( $field['title'] ) ) {

					echo '<p class="llms-label">' . esc_html( $field['title'] ) . '</p>';

				}
				if ( ! empty( $field['desc'] ) ) {

					echo '<p class="llms-description">' . wpautop( wptexturize( wp_kses_post( $field['desc'] ) ) ) . '</p>';

				}

				echo '<table class="form-table">' . "\n\n";

				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) );

				}
			break;

			case 'table':
				echo '<tr valign="top" class="' . $disabled_class . '"><td>';

					$field['table']->get_results();
					echo $field['table']->get_table_html();

				echo '</td></tr>';
			break;

			case 'subtitle':
				if ( ! empty( $field['title'] ) ) {
				    echo '<tr valign="top" class="' . $disabled_class . '"><td colspan="2">
				    	<h3 class="llms-subtitle">' . $field['title'] . '</h3>';
				    if ( ! empty( $field['desc'] ) ) {
				    	echo '<p>' . $field['desc'] . '</p>';
				    }
				    echo '</tr></td>';
				}
			break;

			case 'desc':
				if ( ! empty( $field['desc'] ) ) {
					echo '<th colspan="2" style="font-weight: normal;">' . wpautop( wptexturize( wp_kses_post( $field['desc'] ) ) ) . '</th>';
				}

			break;

			case 'custom-html':
				if ( ! empty( $field['value'] ) ) {
				    echo '<tr valign="top" class="' . $disabled_class . '"><td colspan="2">' . $field['value'] . '</tr></td>';
				}
			break;

			case 'custom-html-no-wrap':
				if ( ! empty( $field['value'] ) ) {
				    echo $field['value'];
				}
			break;

			case 'sectionstart':
				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_before' );

					echo '<div class="llms-widget-full ' . $field['class'] . '">';
					echo '<div class="llms-widget">';

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_start' );

				}
			break;

			case 'sectionend':
				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_end' );

				}

				echo '</table>';
				echo '</div>';
				echo '</div>';

				if ( ! empty( $field['id'] ) ) {

					do_action( 'lifterlms_settings_' . sanitize_title( $field['id'] ) . '_after' );

				}
			break;

			case 'button':

				$name = isset( $field['name'] ) ? $field['name'] : 'save';

				echo '<tr valign="top" class="' . $disabled_class . '"><th>
            		<label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['title'] ) . '</label>
						' . $tooltip . '
            	</th>';

				echo '<td class="forminp forminp-' . sanitize_title( $field['type'] ) . '">';
				echo '<div id="llms-form-wrapper">';
				echo $description . '<br><br>';
				echo '<input name="' . $name . '" class="llms-button-primary" type="submit" value="' . esc_attr( $field['value'] ) . '" />';
				echo '</div>';
				echo '</td></tr>';
				//get_submit_button( 'Filter Results', 'primary', 'llms_search', true, array( 'id' => 'llms_analytics_search' ) );
			break;

			case 'hidden':
				echo '<th></th>';
				echo '<td><input type="hidden"
					name="' . esc_attr( $field['id'] ) . '"
					id="' . esc_attr( $field['id'] ) . '"
					value="' . esc_attr( $field['value'] ) . '">';
			break;

			case 'text':
			case 'email':
			case 'number':
			case 'password':

				$type 			= $field['type'];
				$class 			= '';

				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="<?php echo esc_attr( $type ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?> <?php echo isset( $field['after_html'] ) ? $field['after_html'] : ''; ?>
					</td>
				</tr><?php
			break;

			// Textarea
			case 'textarea':

				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
						<textarea
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							><?php echo esc_textarea( $option_value );  ?></textarea>
						<?php echo $description; ?>
					</td>
				</tr><?php
			break;

			case 'wpeditor':
				$editor_settings = isset( $field['editor_settings'] ) ? $field['editor_settings'] : array();
				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
						<?php wp_editor( $option_value, $field['id'], $editor_settings ); ?>
						<?php echo $description; ?>
					</td>
				</tr><?php
			break;

			// Select boxes
			case 'select' :
			case 'multiselect' :
				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
						<select
							name="<?php echo esc_attr( $field['id'] ); ?><?php if ( 'multiselect' == $field['type'] ) { echo '[]'; } ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							<?php if ( 'multiselect' == $field['type'] ) { echo 'multiple="multiple"'; } ?>
							>
	                    	<?php
							foreach ( $field['options'] as $key => $val ) {

								// convert an array from llms_make_select2_post_array()
								if ( is_array( $val ) ) {
									$key = $val['key'];
									$val = $val['title'];
								}

								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php

								if ( is_array( $option_value ) ) {
									selected( in_array( $key, $option_value ), true );
								} else {
									selected( $option_value, $key );
								}

								?>><?php echo $val ?></option>
								<?php
							}
		                    ?>
					   </select>
						<?php echo $description; ?>
					</td>
				</tr><?php
			break;

			// Radio inputs
			case 'radio' :

				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
						<fieldset>
							<?php echo $description; ?>
							<ul>
							<?php
							foreach ( $field['options'] as $key => $val ) {
								?>
								<li>
									<label><input
										name="<?php echo esc_attr( $field['id'] ); ?>"
										value="<?php echo $key; ?>"
										type="radio"
										style="<?php echo esc_attr( $field['css'] ); ?>"
										class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); ?>
										<?php checked( $key, $option_value ); ?>
										/> <?php echo $val ?></label>
		                        	</li>
		                        	<?php
							}
							?>
							</ul>
						</fieldset>
					</td>
				</tr><?php
			break;

			// Checkbox input
			case 'checkbox' :

				$visbility_class = array();

				if ( ! isset( $field['hide_if_checked'] ) ) {
					$field['hide_if_checked'] = false;
				}
				if ( ! isset( $field['show_if_checked'] ) ) {
					$field['show_if_checked'] = false;
				}
				if ( 'yes' == $field['hide_if_checked'] || 'yes' == $field['show_if_checked'] ) {
					$visbility_class[] = 'hidden_option';
				}
				if ( 'option' == $field['hide_if_checked'] ) {
					$visbility_class[] = 'hide_options_if_checked';
				}
				if ( 'option' == $field['show_if_checked'] ) {
					$visbility_class[] = 'show_options_if_checked';
				}
				if ( ! isset( $field['checkboxgroup'] ) || 'start' == $field['checkboxgroup'] ) {
					?>
	            		<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?> <?php echo $disabled_class; ?>">
							<th><?php echo esc_html( $field['title'] ) ?></th>
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
						<legend class="screen-reader-text"><span><?php echo esc_html( $field['title'] ) ?></span></legend>
					<?php
				}

				?>
					<label for="<?php echo $field['id'] ?>">
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="checkbox"
							value="1"
							<?php checked( $option_value, 'yes' ); ?>
							<?php echo implode( ' ', $custom_attributes ); ?>
						/> <?php echo $description ?>
					</label> <?php echo $tooltip; ?>
				<?php

				if ( ! isset( $field['checkboxgroup'] ) || 'end' == $field['checkboxgroup'] ) {
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

				$type 			= $field['type'];
				$class 			= '';

				if ( $option_value ) {
					// media lib object ID
					if ( is_numeric( $option_value ) ) {
						$size = isset( $field['image_size'] ) ? $field['image_size'] : 'medium';
						$attachment = wp_get_attachment_image_src( $option_value, $size );
						$src = $attachment[0];
					} else {
						// raw img src
						$src = $option_value;
					}
				} else {
					$src = '';
				}

				?><tr valign="top" class="<?php echo $disabled_class; ?>">
					<th>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<?php echo $tooltip; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">

						<img class="llms-image-field-preview" src="<?php echo $src; ?>">
						<button class="llms-button-secondary llms-image-field-upload" data-id="<?php echo esc_attr( $field['id'] ); ?>" type="button">
							<span class="dashicons dashicons-admin-media"></span>
							<?php _e( 'Upload', 'lifterlms' ); ?>
						</button>
						<button class="llms-button-danger llms-image-field-remove<?php echo ( ! $src ) ? ' hidden' : '' ?>" data-id="<?php echo esc_attr( $field['id'] ); ?>" type="button">
							<span class="dashicons dashicons-no"></span>
						</button>
						<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="hidden"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?> <?php echo isset( $field['after_html'] ) ? $field['after_html'] : ''; ?>
					</td>
				</tr><?php
			break;

			// Single page selects
			case 'single_select_page' :

				$args = array(
					'name' => $field['id'],
					'id' => $field['id'],
					'sort_column' => 'menu_order',
					'sort_order' => 'ASC',
					'show_option_none' => ' ',
					'class' => $field['class'],
					'echo' => false,
					'selected' => absint( self::get_option( $field['id'] ) ),
				);

				if ( isset( $field['args'] ) ) {
					$args = wp_parse_args( $field['args'], $args );
				}

				?><tr valign="top" class="single_select_page">
					<th><?php echo esc_html( $field['title'] ) ?> <?php echo $tooltip; ?></th>
					<td class="forminp">
			        	<?php echo str_replace( ' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'lifterlms' ) . "' style='" . $field['css'] . "' class='" . $field['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
			        </td>
			   	</tr><?php
			break;

			// Single page selects
			case 'single_select_membership' :

				$args = array(
					'posts_per_page' 	=> -1,
					'post_type' 		=> 'llms_membership',
					'nopaging' 			=> true,
					'post_status'   	=> 'publish',
					'class'				=> $field['class'],
					'selected'			=> absint( self::get_option( $field['id'] ) ),
				);
				$posts = get_posts( $args );

				if ( isset( $field['args'] ) ) {
					$args = wp_parse_args( $field['args'], $args );
				}

				?><tr valign="top" class="single_select_membership">
					<th><?php echo esc_html( $field['title'] ) ?> <?php echo $tooltip; ?></th>
					<td class="forminp">
	                    <select class="<?php echo $args['class']; ?>" style="<?php echo $field['css']; ?>" name="lifterlms_membership_required" id="lifterlms_membership_required">
	                    	<option value=""> <?php _e( 'None', 'lifterlms' ); ?></option>
		                    <?php foreach ( $posts as $post ) : setup_postdata( $post );
								if ( $args['selected'] == $post->ID ) {
									$selected = 'selected';
								} else {
									$selected = '';
								}
		                    ?>
						    <option value="<?php echo $post->ID; ?>" <?php echo $selected; ?> ><?php echo $post->post_title ?></option>
						<?php endforeach; ?>
						</select>
			        </td>
			   	</tr><?php
			break;

			// Default: run an action
			default:

				do_action( 'lifterlms_admin_field_' . $field['type'], $field, $option_value, $description, $tooltip, $custom_attributes );

			break;
		}// End switch().

	}



	/**
	 * Add and set default values for a field when looping
	 * @param array  $value   associative array of field data
	 * @return array          associative array of field data
	 *
	 * @since 1.4.5
	 */
	public static function set_field_defaults( $value = array() ) {

		if ( ! isset( $value['id'] ) ) {
			$value['id'] = ''; }
		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : ''; }
		if ( ! isset( $value['class'] ) ) {
			$value['class'] = ''; }
		if ( ! isset( $value['css'] ) ) {
			$value['css'] = ''; }
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = ''; }
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = ''; }
		if ( ! isset( $value['desc_tooltip'] ) ) {
			$value['desc_tooltip'] = false; }

		return $value;

	}


	/**
	 * Setup a field's tooltip and description based on supplied values
	 * @param    array  $field  associative array of field data
	 * @return   array          associatve array containing field description and tooltip HTML
	 * @since    1.4.5
	 * @version  3.24.0
	 */
	public static function set_field_descriptions( $field = array() ) {

		if ( true === $field['desc_tooltip'] ) {

			$description = '';
			$tooltip = $field['desc'];

		} elseif ( ! empty( $field['desc_tooltip'] ) ) {

			$description = $field['desc'];
			$tooltip = $field['desc_tooltip'];

		} elseif ( ! empty( $field['desc'] ) ) {
			$description = $field['desc'];
			$tooltip = '';
		} else {

			$description = '';
			$tooltip = '';

		}

		if ( $description && in_array( $field['type'], array( 'radio' ) ) ) {

			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';

		} elseif ( $description && in_array( $field['type'], array( 'checkbox' ) ) ) {

			$description = wp_kses_post( $description );
		} elseif ( $description ) {

			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $tooltip && in_array( $field['type'], array( 'checkbox' ) ) ) {

			$tooltip = '<p class="description">' . $tooltip . '</p>';

		} elseif ( $tooltip ) {

			$tooltip = '<img class="help_tooltip" data-tooltip="' . esc_attr( $tooltip ) . '" src="' . LLMS()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

		}

		return array(
			'description' => $description,
			'tooltip'     => $tooltip,
		);

	}

	/**
	 * Formats an associative array of custom field attributes as an array of HTML strings
	 * @param  array  $attributes   associative array of attributes
	 * @return array
	 *
	 * @since  1.4.5
	 */
	public static function format_field_custom_attributes( $attributes = array() ) {

		// Custom attribute handling
		$custom_attributes = array();
		foreach ( $attributes as $attribute => $attribute_value ) {

			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

		}

		return $custom_attributes;

	}


	/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option
	 * @return string
	 * @version  3.7.5
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Array value
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key
			$option_name = current( array_keys( $option_array ) );

			// Get value
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
	 * Loops though the lifterlms options array and outputs each field.
	 * @param    array $settings Opens array to output
	 * @return   bool
	 * @since    1.0.0
	 * @version  3.17.5
	 */
	public static function save_fields( $settings ) {
	    if ( empty( $_POST ) ) {
	    	return false; }

	    // Options to update will be stored here
	    $update_options = array();

	    // Loop options and get values to save
	    foreach ( $settings as $value ) {

	    	if ( ! isset( $value['id'] ) ) {
	    		continue; }

	    	$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

	    	// Get the option name
	    	$option_value = null;

	    	switch ( $type ) {

		    	// Standard types
		    	case 'checkbox' :

		    		// ooboi this is gross
		    		if ( strstr( $value['id'], '[' ) ) {
		    			parse_str( $value['id'], $option_data );
		    			$main_option_names = array_keys( $option_data );
		    			$main_option_vals = array_keys( $option_data[ $main_option_names[0] ] );
		    			if ( isset( $_POST[ $main_option_names[0] ] ) && in_array( $main_option_vals[0], array_keys( $_POST[ $main_option_names[0] ] ) ) ) {
		    				$option_value = 'yes';
		    			} else {
		    				$option_value = 'no';
		    			}
		    		} elseif ( isset( $_POST[ $value['id'] ] ) ) {
		    			$option_value = 'yes';
		            } else {
		            	$option_value = 'no';
		            }

		    	break;

		    	case 'textarea' :
		    	case 'wpeditor' :

			    	if ( isset( $_POST[ $value['id'] ] ) ) {
			    		$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
		            } else {
		                $option_value = '';
		            }

		    	break;

		    	case 'password':
		    	case 'text' :
		    	case 'email':
	            case 'number':
		    	case 'select' :
		    	case 'single_select_page' :
		    	case 'single_select_membership' :
		    	case 'radio' :
		    	case 'hidden' :
		    	case 'image' :

					if ( isset( $_POST[ $value['id'] ] ) ) {
		            	$option_value = llms_clean( stripslashes( $_POST[ $value['id'] ] ) );
		            } else {
		                $option_value = '';
		            }

		            if ( isset( $value['sanitize'] ) && 'slug' === $value['sanitize'] ) {
		            	$option_value = sanitize_title( $option_value );
		            }

		    	break;

		    	case 'multiselect' :

			    	if ( isset( $_POST[ $value['id'] ] ) ) {
			    		foreach ( $_POST[ $value['id'] ] as $k => $v ) {

			    			$_POST[ $value['id'] ][ $k ] = llms_clean( stripslashes( $v ) );
			    		}
			    		$option_value = $_POST[ $value['id'] ];

			    	} else {
			    		$option_value = '';
			    	}
		    	break;

		    	// Custom handling
		    	default :

		    		do_action( 'lifterlms_update_option_' . $type, $value );

		    	break;

	    	}// End switch().

	    	if ( ! is_null( $option_value ) ) {
		    	// Check if option is an array
				if ( strstr( $value['id'], '[' ) ) {

					parse_str( $value['id'], $option_array );

		    		// Option name is first key
		    		$option_name = current( array_keys( $option_array ) );

		    		// Get old option value
		    		if ( ! isset( $update_options[ $option_name ] ) ) {
		    			 $update_options[ $option_name ] = get_option( $option_name, array() ); }

		    		if ( ! is_array( $update_options[ $option_name ] ) ) {
		    			$update_options[ $option_name ] = array(); }

		    		// Set keys and value
		    		$key = key( $option_array[ $option_name ] );

		    		$update_options[ $option_name ][ $key ] = $option_value;

					// Single value
				} else {
					$update_options[ $value['id'] ] = $option_value;
				}
			}

	    	// Custom handling
	    	do_action( 'lifterlms_update_option', $value );
	    }// End foreach().

	    // Now save the options
	    foreach ( $update_options as $name => $value ) {

	    	update_option( $name, $value );

	    }

	    return true;
	}

}
