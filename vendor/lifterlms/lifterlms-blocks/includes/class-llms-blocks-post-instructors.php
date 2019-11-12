<?php
/**
 * Handle course & membership instructors data.
 *
 * @package  LifterLMS_Blocks/Classes
 * @since    1.0.0
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle course & membership instructors data.
 */
class LLMS_Blocks_Post_Instructors {

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_meta' ) );

	}

	/**
	 * Meta field update authorization callback.
	 *
	 * @param   bool   $allowed   Is the update allowed.
	 * @param   string $meta_key  Meta keyname.
	 * @param   int    $object_id WP Object ID (post,comment,etc)...
	 * @param   int    $user_id   WP User ID.
	 * @param   string $cap       requested capability.
	 * @param   array  $caps      user capabilities.
	 * @return  bool
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function authorize_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, 'edit_post', $object_id );
	}

	/**
	 * Retrieve instructor information for a give object.
	 *
	 * @param   array           $obj  Assoc. array of WP_Post data.
	 * @param   WP_REST_Request $request   Full details about the request.
	 * @return  WP_Error|object Object containing the meta values by name, otherwise WP_Error object.
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_callback( $obj, $request ) {

		$ret = array();

		$obj = llms_get_post( $obj['id'] );
		if ( $obj ) {
			$ret = $obj->instructors()->get_instructors( false );
			foreach ( $ret as &$instructor ) {
				$name    = '';
				$student = llms_get_student( $instructor['id'] );
				if ( $student ) {
					$name = $student->get_name();
				}
				$instructor['name'] = $name;
			}
		}
		return $ret;

	}

	/**
	 * Update instructor information for a given object.
	 *
	 * @param   string  $value  Instructor data to add to the object (JSON).
	 * @param   WP_Post $object WP_Post object.
	 * @param   string  $key    name of the field.
	 * @return  null|WP_Error
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function update_callback( $value, $object, $key ) {

		if ( ! current_user_can( 'edit_post', $object->ID ) ) {
			return new WP_Error(
				'rest_cannot_update',
				__( 'Sorry, you are not allowed to edit the object instructors.', 'lifterlms' ),
				array(
					'key'    => $name,
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$obj = llms_get_post( $object );
		if ( $obj ) {
			$obj->instructors()->set_instructors( $value );
		}

		return null;
	}

	/**
	 * Register custom meta fields.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_meta() {

		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {

			register_rest_field(
				$post_type,
				'instructors',
				array(
					'get_callback'    => array( $this, 'get_callback' ),
					'update_callback' => array( $this, 'update_callback' ),
					'schema'          => array(
						'description' => __( 'Instructor fields.', 'lifterlms' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'properties'  => array(),
						'arg_options' => array(
							'sanitize_callback' => null,
							'validate_callback' => null,
						),
					),
				)
			);

		}

	}

}

return new LLMS_Blocks_Post_Instructors();
