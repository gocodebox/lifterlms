<?php
/**
 * Add Custom User Fields to user admin panel screens
 * Applies to edit-user.php, user-new.php, & profile.php
 *
 * @since    2.7.0
 * @version  3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_User_Custom_Fields
 *
 * @since 2.7.0
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_User_Custom_Fields {

	private $fields = array();

	/**
	 * Constructor
	 *
	 * @since    2.7.0
	 * @version  3.13.0
	 */
	public function __construct() {

		// output custom fields on edit screens
		$field_actions = array(
			'show_user_profile',
			'edit_user_profile',
			'user_new_form',
		);
		foreach ( $field_actions as $action ) {
			add_action( $action, array( $this, 'output_custom_fields' ), 10, 1 );
			add_action( $action, array( $this, 'output_instructors_assistant_fields' ), 10, 1 );
		}

		// allow errors to be output before saving field data
		// save the data if no errors are encountered
		add_action( 'user_profile_update_errors', array( $this, 'add_errors' ), 10, 3 );

		// save data when a new user is created
		add_action( 'edit_user_created_user', array( $this, 'save' ) );

	}

	/**
	 * Validate custom fields
	 * During updates will save data
	 * Creation is saved during a different action
	 *
	 * @param    obj  &$errors  Instance of WP_Error
	 * @param    bool $update   true if updating a profile, false if a new user
	 * @param    obj  $user     Instance of WP_User for the user being updated
	 * @return   void
	 * @since    2.7.0
	 * @version  3.13.0
	 */
	public function add_errors( &$errors, $update, $user ) {

		$this->get_fields();

		$error = $this->validate_fields( $user );

		if ( $error ) {

			$errors->add( '', $error, '' );

			if ( $update ) {
				$this->save();
			}

			// don't save
			remove_action( 'edit_user_created_user', array( $this, 'save' ) );

			return;

		}

		// if updating, save here since there's no other save specific admin action (that I could find)
		if ( $update ) {
			$this->save( $user );
		}

	}

	/**
	 * Retrieve an associative array of custom fields and custom field data
	 *
	 * @return   array
	 * @since    2.7.0
	 * @version  3.13.0
	 */
	public function get_fields() {

		$fields = apply_filters(
			'lifterlms_get_user_custom_fields',
			array(

				'llms_billing_address_1' => array(
					'description' => '',
					'label'       => __( 'Billing Address 1', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_billing_address_2' => array(
					'description' => '',
					'label'       => __( 'Billing Address 2', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_billing_city'      => array(
					'description' => '',
					'label'       => __( 'Billing City', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_billing_state'     => array(
					'description' => '',
					'label'       => __( 'Billing State', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_billing_zip'       => array(
					'description' => '',
					'label'       => __( 'Billing Zip Code', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_billing_country'   => array(
					'description' => '',
					'label'       => __( 'Billing Country', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

				'llms_phone'             => array(
					'description' => '',
					'label'       => __( 'Phone', 'lifterlms' ),
					'required'    => false,
					'type'        => 'text',
					'value'       => '',
				),

			)
		);

		$this->fields = $fields;

		return $this->fields;

	}

	/**
	 * Load usermeta data into the array of fields retrieved from $this->get_fields
	 * meta data is added to the array under the key "value" for each field
	 * if no data is found for a particular field the value is still added as an empty string
	 *
	 * @param    mixed $user   Instance of WP_User or WP User ID
	 * @return   array
	 * @since    2.7.0
	 * @version  2.7.0
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
	 * @param    mixed $user   Instance of WP_User or WP User ID
	 * @return   void
	 * @since    2.7.0
	 * @version  3.24.0
	 */
	public function output_custom_fields( $user ) {

		if ( is_numeric( $user ) || is_a( $user, 'WP_User' ) ) {
			$this->get_fields_with_data( $user );
		} else {
			$this->get_fields();
		}

		llms_get_template(
			'admin/user-edit.php',
			array(
				'section_title' => __( 'LifterLMS Profile', 'lifterlms' ),
				'fields'        => $this->fields,
			)
		);

	}

	/**
	 * Add instructor parent fields for use when creating instructor's assistants
	 *
	 * @param    mixed $user   Instance of WP_User or WP User ID
	 * @return   void
	 * @since    3.13.0
	 * @version  3.23.0
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

		// only let admins & lms managers select the parent for an instructor's assistant
		if ( current_user_can( 'manage_lifterlms' ) ) {

			$users = get_users(
				array(
					'role__in' => array( 'administrator', 'lms_manager', 'instructor' ),
				)
			);
			?>
			<table class="form-table" id="llms-parent-instructors-table" style="display:none;">
				<tr class="form-field">
					<th scope="row"><label for="llms-parent-instructors"><?php _e( 'Parent Instructor(s)', 'lifterlms' ); ?></label></th>
					<td>
						<select class="regular-text" id="llms-parent-instructors" name="llms_parent_instructors[]" multiple="multiple">
							<?php foreach ( $users as $user ) : ?>
								<option value="<?php echo $user->ID; ?>"<?php selected( in_array( $user->ID, $selected ) ); ?>>
									<?php echo $user->display_name; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			<?php

			add_action( 'admin_print_footer_scripts', array( $this, 'output_instructors_assistant_scripts' ) );

			// this will be the case for Instructors only
			// show a hidden field with the current user's info
			// when saving it will only save if the created user's role is instructor's assistant
		} elseif ( 'add-new-user' === $user ) {
			echo '<input type="hidden" name="llms_parent_instructors[]" value="' . get_current_user_id() . '">';
		}

	}

	/**
	 * Output JS to handle user interaction with the instructor's parent field
	 * Display custom field ONLY when creating/editing an instructor's assistant
	 *
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
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
	 * @since    3.13.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @param    mixed $user  WP_User or WP_User ID
	 * @return   void
	 */
	public function save( $user ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing

		if ( is_numeric( $user ) ) {
			$user = new WP_User( $user );
			// an object that's not a WP_User gets passed in during updates
		} elseif ( isset( $user->ID ) ) {
			$user = new WP_User( $user->ID );
		}

		// saves custom fields
		foreach ( $this->fields as $field => $data ) {

			$value = apply_filters( 'lifterlms_save_custom_user_field_' . $field, llms_filter_input( INPUT_POST, $field, FILTER_SANITIZE_STRING ), $user, $field );
			update_user_meta( $user->ID, $field, $value );

		}

		// save instructor assistant's parent instructor
		if ( in_array( 'instructors_assistant', $user->roles ) && ! empty( $_POST['llms_parent_instructors'] ) ) {

			$instructor = llms_get_instructor( $user );
			$instructor->add_parent( llms_filter_input( INPUT_POST, 'llms_parent_instructors', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) );

		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

	}

	/**
	 * Validate custom fields
	 * By default only checks for valid as core fields don't have any special validation
	 * If adding custom fields, hook into the action run after required validation
	 * to add special validation rules for your field
	 *
	 * @since    2.7.0
	 *
	 * @param    mixed $user   Instance of WP_User or WP User ID
	 * @return   mixed          false if no validation errors, string (the error message) if validation errors occurred
	 */
	public function validate_fields( $user ) {

		// ensure there's no missing required fields
		foreach ( $this->fields as $field => $data ) {

			// return an error message for empty required fields
			if ( empty( $_POST[ $field ] ) && $data['required'] ) { // phpcs:disable WordPress.Security.NonceVerification.Missing

				return sprintf( __( 'Required field "%s" is missing.', 'lifterlms' ), $data['label'] );

			} else {

				/**
				 * Run custom validation against the field
				 * If filter function returns a truthy, validation will stop, fields will not be saved,
				 * and an error message will be displayed on screen
				 * This should return false or a string which will be used as the error message
				 *
				 * @since  2.7.0
				 */
				$error_msg = apply_filters( 'lifterlms_validate_custom_user_field_' . $field, false, $field, $user );

				if ( $error_msg ) {

					return $error_msg;

				}
			}
		}

		return false;

	}

}

return new LLMS_Admin_User_Custom_Fields();
