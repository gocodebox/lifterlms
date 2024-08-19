<?php
/**
 * LLMS_Admin_User_Custom_Fields class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 2.7.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add custom user fields to user admin panel screens
 *
 * Applies to edit-user.php, user-new.php, & profile.php.
 *
 * @since 2.7.0
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.15 Fix error encountered when errors encountered validating custom fields.
 */
class LLMS_Admin_User_Custom_Fields {

	private $fields = array();

	/**
	 * Constructor
	 *
	 * @since 2.7.0
	 * @since 3.13.0 Unknown.
	 * @since 4.14.0 Add personal options hook.
	 * @since 5.0.0 Custom fields (legacy), are now printed with priority 11 instead of 10.
	 * @return void
	 */
	public function __construct() {

		// Output custom fields on edit screens.
		$field_actions = array(
			'show_user_profile',
			'edit_user_profile',
			'user_new_form',
		);

		foreach ( $field_actions as $action ) {
			add_action( $action, array( $this, 'output_custom_fields' ), 11, 1 );
			add_action( $action, array( $this, 'output_instructors_assistant_fields' ), 10, 1 );
		}

		// Allow errors to be output before saving field data.
		// Save the data if no errors are encountered.
		add_action( 'user_profile_update_errors', array( $this, 'add_errors' ), 10, 3 );

		// Save data when a new user is created.
		add_action( 'edit_user_created_user', array( $this, 'save' ) );

		// Add personal options.
		add_action( 'personal_options', array( $this, 'output_personal_options' ) );
	}


	/**
	 * Validate custom fields
	 *
	 * During updates will save data, creation is saved during a different action.
	 *
	 * @since 2.7.0
	 * @since 3.13.0 Unknown.
	 * @since 3.37.15 Correctly pass `$user` to `$this->save()`.
	 *
	 * @param obj     $errors Instance of WP_Error, passed by reference.
	 * @param bool    $update `true` if updating a profile, `false` if a new user.
	 * @param WP_User $user   Instance of WP_User for the user being updated.
	 * @return void
	 */
	public function add_errors( &$errors, $update, $user ) {

		$this->get_fields();

		$error = $this->validate_fields( $user );

		if ( $error ) {

			$errors->add( '', $error, '' );

			if ( $update ) {
				$this->save( $user );
			}

			// Don't save.
			remove_action( 'edit_user_created_user', array( $this, 'save' ) );

			return;

		}

		// If updating, save here since there's no other save specific admin action (that I could find).
		if ( $update ) {
			$this->save( $user );
		}
	}

	/**
	 * Retrieve an associative array of custom fields and custom field data
	 *
	 * @since 2.7.0
	 * @since 3.13.0 Unknown.
	 * @since 5.0.0 Removed LLMS core fields and deprecate the filter usage.
	 *
	 * @return array
	 */
	public function get_fields() {

		$this->fields = apply_filters_deprecated(
			'lifterlms_get_user_custom_fields',
			array(
				array(),
			),
			'5.0.0',
			'llms_admin_profile_fields'
		);

		return $this->fields;
	}

	/**
	 * Load usermeta data into the array of fields retrieved from $this->get_fields
	 *
	 * Meta data is added to the array under the key "value" for each field.
	 *
	 * If no data is found for a particular field the value is still added as an empty string.
	 *
	 * @since 2.7.0
	 *
	 * @param WP_User|int $user Instance of WP_User or WP User ID
	 * @return array
	 */
	public function get_fields_with_data( $user ) {

		if ( is_numeric( $user ) ) {
			$user = new WP_User( $user );
		}

		$this->get_fields();

		foreach ( $this->fields as $field => $data ) {

			$this->fields[ $field ]['value'] = apply_filters( 'lifterlms_get_user_custom_field_value_' . $field, $user->get( $field ), $user, $field );

		}

		return $this->fields;
	}

	/**
	 * Output custom field data fields as HTML inputs
	 *
	 * @since 2.7.0
	 * @since 3.24.0 Unknown.
	 * @since 5.0.0 Do not include user-edit template if no fields to show.
	 *
	 * @param WP_User|int $user Instance of WP_User or WP User ID.
	 * @return void
	 */
	public function output_custom_fields( $user ) {

		if ( is_numeric( $user ) || is_a( $user, 'WP_User' ) ) {
			$this->get_fields_with_data( $user );
		} else {
			$this->get_fields();
		}

		if ( empty( $this->fields ) ) {
			return;
		}

		llms_get_template(
			'admin/user-edit.php',
			array(
				'section_title' => __( 'LifterLMS Profile (legacy fields)', 'lifterlms' ),
				'fields'        => $this->fields,
			)
		);
	}

	/**
	 * Output personal option fields
	 *
	 * Currently adds a single option row for controlling auto-save behavior on the course builder.
	 *
	 * @since 4.14.0
	 *
	 * @param WP_User $user Viewed user object.
	 * @return void
	 */
	public function output_personal_options( $user ) {

		if ( ! user_can( $user, 'edit_courses' ) ) {
			return;
		}

		$autosave = get_user_option( 'llms_builder_autosave', $user->ID );
		$autosave = empty( $autosave ) ? 'no' : $autosave;

		?>
		<tr class="llms-builder-autosave llms-builder-autosave-wrap">
			<th scope="row"><?php esc_html_e( 'Course Builder Autosave', 'lifterlms' ); ?></th>
			<td>
				<label for="llms_builder_autosave">
					<input name="llms_builder_autosave" type="checkbox" id="llms_builder_autosave" value="yes"<?php checked( 'yes', $autosave ); ?>>
					<?php esc_html_e( 'Automatically save changes when using the course builder', 'lifterlms' ); ?>
				</label><br>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add instructor parent fields for use when creating instructor's assistants
	 *
	 * @since 3.13.0
	 * @since 3.23.0 Unknown.
	 * @since 3.37.15 Use strict comparisons.
	 *
	 * @param WP_User|int $user Instance of WP_User or WP User ID
	 * @return void
	 */
	public function output_instructors_assistant_fields( $user ) {

		if ( is_numeric( $user ) || is_a( $user, 'WP_User' ) ) {
			$instructor = llms_get_instructor( $user );
			$selected   = $instructor->get( 'parent_instructors' );
			if ( empty( $selected ) && ! is_array( $selected ) ) {
				$selected = array();
			}
		} else {
			$selected = array( get_current_user_id() );
		}

		$selected = array_map( 'absint', $selected );

		// Only let admins & lms managers select the parent for an instructor's assistant.
		if ( current_user_can( 'manage_lifterlms' ) ) {

			$users = get_users(
				array(
					'role__in' => array( 'administrator', 'lms_manager', 'instructor' ),
				)
			);
			?>
			<table class="form-table" id="llms-parent-instructors-table" style="display:none;">
				<tr class="form-field">
					<th scope="row"><label for="llms-parent-instructors"><?php esc_html_e( 'Parent Instructor(s)', 'lifterlms' ); ?></label></th>
					<td>
						<select class="regular-text" id="llms-parent-instructors" name="llms_parent_instructors[]" multiple="multiple">
							<?php foreach ( $users as $user ) : ?>
								<option value="<?php echo esc_attr( $user->ID ); ?>"<?php selected( in_array( $user->ID, $selected, true ) ); ?>>
									<?php echo esc_html( $user->display_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			<?php

			add_action( 'admin_print_footer_scripts', array( $this, 'output_instructors_assistant_scripts' ) );

		} elseif ( 'add-new-user' === $user ) {
			/**
			 * This will be the case for Instructors only:
			 *
			 * Show a hidden field with the current user's info
			 *
			 * When saving it will only save if the created user's role is instructor's assistant.
			 */
			echo '<input type="hidden" name="llms_parent_instructors[]" value="' . esc_attr( get_current_user_id() ) . '">';
		}
	}

	/**
	 * Output JS to handle user interaction with the instructor's parent field
	 *
	 * Display custom field ONLY when creating/editing an instructor's assistant.
	 *
	 * @since 3.13.0
	 *
	 * @return void
	 */
	public function output_instructors_assistant_scripts() {
		?>
		<script>
			( function( $ ) {
				var $role = $( '#role' ),
					$parent = $( '#llms-parent-instructors-table' );
				$role.closest( '.form-table' ).after( $parent );
				$role.on( 'change', function() {
					if ( 'instructors_assistant' === $( this ).val() ) {
						$parent.show();
					} else {
						$parent.hide();
					}
				} ).trigger( 'change' );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Save custom field data for a user
	 *
	 * @since 3.13.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.37.15 Use strict comparisons.
	 * @since 4.14.0 Save builder autosave personal options.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param WP_User|int|obj $user User object or id.
	 * @return void
	 */
	public function save( $user ) {

		if ( is_numeric( $user ) ) {

			// Numeric ID is passed in during creations.
			$user   = new WP_User( $user );
			$action = 'create';

		} elseif ( isset( $user->ID ) ) {

			// An object that's not a WP_User gets passed in during updates.
			$user   = new WP_User( $user->ID );
			$action = 'update';
		}

		// Saves custom fields.
		foreach ( $this->fields as $field => $data ) {

			$value = apply_filters( 'lifterlms_save_custom_user_field_' . $field, llms_filter_input_sanitize_string( INPUT_POST, $field ), $user, $field );
			update_user_meta( $user->ID, $field, $value );

		}

		// Save instructor assistant's parent instructor.
		if ( in_array( 'instructors_assistant', $user->roles, true ) && ! empty( $_POST['llms_parent_instructors'] ) ) { // phpcs:disable WordPress.Security.NonceVerification.Missing

			$instructor = llms_get_instructor( $user );
			$instructor->add_parent( llms_filter_input( INPUT_POST, 'llms_parent_instructors', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) );

		}

		// Save personal options.
		if ( user_can( $user, 'edit_courses' ) && 'create' !== $action ) {
			$autosave = empty( $_POST['llms_builder_autosave'] ) ? 'no' : 'yes';
			update_user_meta( $user->ID, 'llms_builder_autosave', $autosave );
		}
	}

	/**
	 * Validate custom fields
	 *
	 * By default only checks for valid as core fields don't have any special validation.
	 *
	 * If adding custom fields, hook into the action run after required validation
	 * to add special validation rules for your field.
	 *
	 * @since 2.7.0
	 *
	 * @param WP_User|int $user Instance of WP_User or WP User ID.
	 * @return string|bool `false` if no validation errors or the error message (as a sttring) if validation errors occurred.
	 */
	public function validate_fields( $user ) {

		// Ensure there's no missing required fields.
		foreach ( $this->fields as $field => $data ) {

			// Return an error message for empty required fields.
			if ( empty( $_POST[ $field ] ) && $data['required'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				return sprintf( __( 'Required field "%s" is missing.', 'lifterlms' ), $data['label'] );

			} else {

				/**
				 * Run custom validation against the field
				 *
				 * If filter function returns a truthy, validation will stop, fields will not be saved,
				 * and an error message will be displayed on screen.
				 *
				 * This should return `false` or a string which will be used as the error message.
				 *
				 * @since 2.7.0
				 *
				 * @param boolean     $error_message The error message when validation issues are encountered. Return `false` when no validation issues.
				 * @param string      $field         Field id.
				 * @param WP_User|int $user          Instance of WP_User or WP User ID.
				 */
				$error_msg = apply_filters( "lifterlms_validate_custom_user_field_{$field}", false, $field, $user );

				if ( $error_msg ) {

					return $error_msg;

				}
			}
		}

		return false;
	}
}

return new LLMS_Admin_User_Custom_Fields();
