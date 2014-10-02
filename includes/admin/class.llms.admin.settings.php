<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Settings' ) ) :

/**
* Admin Settings Class
*
* TODO: description
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
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
			$settings[] = include( 'settings/class.llms.settings.accounts.php' );
			$settings[] = include( 'settings/class.llms.settings.checkout.php' );
			$settings[] = include( 'settings/class.llms.settings.gateways.php' );

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
				echo '<div class="error"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}

		} elseif ( sizeof( self::$messages ) > 0 ) {

			foreach ( self::$messages as $message ) {
				echo '<div class="updated"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
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

	    if ( ! empty( $_POST ) )
	    	self::save();

	    if ( ! empty( $_GET['llms_error'] ) )
	    	self::set_error( stripslashes( $_GET['llms_error'] ) );

	    if ( ! empty( $_GET['llms_message'] ) )
	    	self::set_message( stripslashes( $_GET['llms_message'] ) );

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
	    foreach ( $settings as $value ) {

	    	if ( ! isset( $value['type'] ) ) 
	    		continue;
	    	if ( ! isset( $value['id'] ) ) 
	    		$value['id'] = '';
	    	if ( ! isset( $value['title'] ) ) 
	    		$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
	    	if ( ! isset( $value['class'] ) ) 
	    		$value['class'] = '';
	    	if ( ! isset( $value['css'] ) ) 
	    		$value['css'] = '';
	    	if ( ! isset( $value['default'] ) ) 
	    		$value['default'] = '';
	    	if ( ! isset( $value['desc'] ) ) 
	    		$value['desc'] = '';
	    	if ( ! isset( $value['desc_tooltip'] ) ) 
	    		$value['desc_tooltip'] = false;

	    	// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {

				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {

					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

				}
			}

			if ( $value['desc_tooltip'] === true ) {

				$description = '';
				$tooltip = $value['desc'];

			} 

			elseif ( ! empty( $value['desc_tooltip'] ) ) {

				$description = $value['desc'];
				$tooltip = $value['desc_tooltip'];

			} 

			elseif ( ! empty( $value['desc'] ) ) {

				$description = $value['desc'];
				$tooltip = '';
			} 

			else {

				$description = $tooltip = '';

			}

			if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {

				$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';

			}

			elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {

				$description =  wp_kses_post( $description );
			} 

			elseif ( $description ) {

				$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
			}

			if ( $tooltip && in_array( $value['type'], array( 'checkbox' ) ) ) {

				$tooltip = '<p class="description">' . $tooltip . '</p>';

			} elseif ( $tooltip ) {

				$tooltip = '<img class="help_tooltip" data-tooltip="' . esc_attr( $tooltip ) . '" src="' . LLMS()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

			}

			// Switch based on type
	        switch( $value['type'] ) {

	        	// Section Titles
	            case 'title':

	            	if ( ! empty( $value['title'] ) ) {

	            		echo '<h3>' . esc_html( $value['title'] ) . '</h3>';

	            	}
	            	if ( ! empty( $value['desc'] ) ) {

	            		echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );

	            	}

	            	echo '<table class="form-table">'. "\n\n";

	            	if ( ! empty( $value['id'] ) ) {

	            		do_action( 'lifterlms_settings_' . sanitize_title( $value['id'] ) );

	            	}
	            break;

	            case 'sectionend':
	            	if ( ! empty( $value['id'] ) ) {

	            		do_action( 'lifterlms_settings_' . sanitize_title( $value['id'] ) . '_end' );

	            	}

	            	echo '</table>';

	            	if ( ! empty( $value['id'] ) ) {

	            		do_action( 'lifterlms_settings_' . sanitize_title( $value['id'] ) . '_after' );

	            	}
	            break;

	            case 'text':
	            case 'email':
	            case 'number':

	            	$type 			= $value['type'];
	            	$class 			= '';
	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top">
						<th>
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<input
	                    		name="<?php echo esc_attr( $value['id'] ); ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		type="<?php echo esc_attr( $type ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		value="<?php echo esc_attr( $option_value ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
	                    		<?php echo implode( ' ', $custom_attributes ); ?>
	                    		/> <?php echo $description; ?>
	                    </td>
	                </tr><?php
	            break;

	            // Textarea
	            case 'textarea':

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top">
						<th>
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<?php echo $description; ?>

	                        <textarea
	                        	name="<?php echo esc_attr( $value['id'] ); ?>"
	                        	id="<?php echo esc_attr( $value['id'] ); ?>"
	                        	style="<?php echo esc_attr( $value['css'] ); ?>"
	                        	class="<?php echo esc_attr( $value['class'] ); ?>"
	                        	<?php echo implode( ' ', $custom_attributes ); ?>
	                        	><?php echo esc_textarea( $option_value );  ?></textarea>
	                    </td>
	                </tr><?php
	            break;

	            // Select boxes
	            case 'select' :
	            case 'multiselect' :

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top">
						<th>
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<select
	                    		name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
	                    		<?php echo implode( ' ', $custom_attributes ); ?>
	                    		<?php if ( $value['type'] == 'multiselect' ) echo 'multiple="multiple"'; ?>
	                    		>
		                    	<?php
			                        foreach ( $value['options'] as $key => $val ) {
			                        	?>
			                        	<option value="<?php echo esc_attr( $key ); ?>" <?php

				                        	if ( is_array( $option_value ) )
				                        		selected( in_array( $key, $option_value ), true );
				                        	else
				                        		selected( $option_value, $key );

			                        	?>><?php echo $val ?></option>
			                        	<?php
			                        }
			                    ?>
	                       </select> <?php echo $description; ?>
	                    </td>
	                </tr><?php
	            break;

	            // Radio inputs
	            case 'radio' :

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top">
						<th>
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<fieldset>
	                    		<?php echo $description; ?>
	                    		<ul>
	                    		<?php
	                    			foreach ( $value['options'] as $key => $val ) {
			                        	?>
			                        	<li>
			                        		<label><input
				                        		name="<?php echo esc_attr( $value['id'] ); ?>"
				                        		value="<?php echo $key; ?>"
				                        		type="radio"
					                    		style="<?php echo esc_attr( $value['css'] ); ?>"
					                    		class="<?php echo esc_attr( $value['class'] ); ?>"
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

					$option_value    = self::get_option( $value['id'], $value['default'] );
					$visbility_class = array();

	            	if ( ! isset( $value['hide_if_checked'] ) ) {
	            		$value['hide_if_checked'] = false;
	            	}
	            	if ( ! isset( $value['show_if_checked'] ) ) {
	            		$value['show_if_checked'] = false;
	            	}
	            	if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes' ) {
	            		$visbility_class[] = 'hidden_option';
	            	}
	            	if ( $value['hide_if_checked'] == 'option' ) {
	            		$visbility_class[] = 'hide_options_if_checked';
	            	}
	            	if ( $value['show_if_checked'] == 'option' ) {
	            		$visbility_class[] = 'show_options_if_checked';
	            	}

	            	if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
	            		?>
		            		<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
								<th><?php echo esc_html( $value['title'] ) ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
	            	} else {
	            		?>
		            		<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
	            		<?php
	            	}

	            	if ( ! empty( $value['title'] ) ) {
	            		?>
	            			<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
	            		<?php
	            	}

	            	?>
						<label for="<?php echo $value['id'] ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $option_value, 'yes'); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description ?>
						</label> <?php echo $tooltip; ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
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

	            // Image width settings
	            case 'image_width' :

	            	$width 	= self::get_option( $value['id'] . '[width]', $value['default']['width'] );
	            	$height = self::get_option( $value['id'] . '[height]', $value['default']['height'] );
	            	$crop 	= checked( 1, self::get_option( $value['id'] . '[crop]', $value['default']['crop'] ), false );

	            	?><tr valign="top">
						<th><?php echo esc_html( $value['title'] ) ?> <?php echo $tooltip; ?></th>
	                    <td class="forminp image_width_settings">

	                    	<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

	                    	<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" <?php echo $crop; ?> /> <?php _e( 'Hard Crop?', 'lifterlms' ); ?></label>

	                    	</td>
	                </tr><?php
	            break;

	            // Single page selects
	            case 'single_select_page' :

	            	$args = array( 'name'				=> $value['id'],
	            				   'id'					=> $value['id'],
	            				   'sort_column' 		=> 'menu_order',
	            				   'sort_order'			=> 'ASC',
	            				   'show_option_none' 	=> ' ',
	            				   'class'				=> $value['class'],
	            				   'echo' 				=> false,
	            				   'selected'			=> absint( self::get_option( $value['id'] ) )
	            				   );

	            	if( isset( $value['args'] ) )
	            		$args = wp_parse_args( $value['args'], $args );

	            	?><tr valign="top" class="single_select_page">
	                    <th><?php echo esc_html( $value['title'] ) ?> <?php echo $tooltip; ?></th>
	                    <td class="forminp">
				        	<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'lifterlms' ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
				        </td>
	               	</tr><?php
	            break;

	            // Default: run an action
	            default:
	            	do_action( 'lifterlms_admin_field_' . $value['type'], $value );
	            break;
	    	}
		}
	}

		/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option
	 * @return string
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

			if ( isset( $option_values[ $key ] ) )
				$option_value = $option_values[ $key ];
			else
				$option_value = null;

		// Single value
		} else {
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) )
			$option_value = array_map( 'stripslashes', $option_value );
		elseif ( ! is_null( $option_value ) )
			$option_value = stripslashes( $option_value );

		return $option_value === null ? $default : $option_value;
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the lifterlms options array and outputs each field.
	 *
	 * @access public
	 * @param array $settings Opens array to output
	 * @return bool
	 */
	public static function save_fields( $settings ) {

	    if ( empty( $_POST ) )
	    	return false;

	    // Options to update will be stored here
	    $update_options = array();

	    // Loop options and get values to save
	    foreach ( $settings as $value ) {

	    	if ( ! isset( $value['id'] ) )
	    		continue;

	    	$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

	    	// Get the option name
	    	$option_value = null;

	    	switch ( $type ) {

		    	// Standard types
		    	case "checkbox" :

		    		if ( isset( $_POST[ $value['id'] ] ) ) {
		    			$option_value = 'yes';
		            } else {
		            	$option_value = 'no';
		            }

		    	break;

		    	case "textarea" :

			    	if ( isset( $_POST[$value['id']] ) ) {
			    		$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
		            } else {
		                $option_value = '';
		            }

		    	break;

		    	case "text" :
		    	case 'email':
	            case 'number':
		    	case "select" :
		    	case "single_select_page" :
		    	case 'radio' :


		       if ( isset( $_POST[$value['id']] ) ) {
	            	$option_value = llms_clean( stripslashes( $_POST[ $value['id'] ] ) );
	            } else {
	                $option_value = '';
	            }


		    	break;

		    	case "image_width" :

			    	if ( isset( $_POST[$value['id'] ]['width'] ) ) {

		              	$update_options[ $value['id'] ]['width']  = llms_clean( stripslashes( $_POST[ $value['id'] ]['width'] ) );
		              	$update_options[ $value['id'] ]['height'] = llms_clean( stripslashes( $_POST[ $value['id'] ]['height'] ) );

						if ( isset( $_POST[ $value['id'] ]['crop'] ) )
							$update_options[ $value['id'] ]['crop'] = 1;
						else
							$update_options[ $value['id'] ]['crop'] = 0;

		            } else {
		            	$update_options[ $value['id'] ]['width'] 	= $value['default']['width'];
		            	$update_options[ $value['id'] ]['height'] 	= $value['default']['height'];
		            	$update_options[ $value['id'] ]['crop'] 	= $value['default']['crop'];
		            }

		    	break;

		    	// Custom handling
		    	default :

		    		do_action( 'lifterlms_update_option_' . $type, $value );

		    	break;

	    	}

	    	if ( ! is_null( $option_value ) ) {
		    	// Check if option is an array
				if ( strstr( $value['id'], '[' ) ) {

					parse_str( $value['id'], $option_array );

		    		// Option name is first key
		    		$option_name = current( array_keys( $option_array ) );

		    		// Get old option value
		    		if ( ! isset( $update_options[ $option_name ] ) )
		    			 $update_options[ $option_name ] = get_option( $option_name, array() );

		    		if ( ! is_array( $update_options[ $option_name ] ) )
		    			$update_options[ $option_name ] = array();

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
	    }

	    // Now save the options
	    foreach( $update_options as $name => $value )
	    	update_option( $name, $value );

	    return true;
	}



}

endif;