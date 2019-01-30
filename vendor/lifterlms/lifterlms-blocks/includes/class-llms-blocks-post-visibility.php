<?php
/**
 * Handle course & membership catalog visibility data data.
 *
 * @package  LifterLMS_Blocks/Classes
 * @since    1.3.0
 * @version  1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks_Post_Visibility class.
 */
class LLMS_Blocks_Post_Visibility {

	/**
	 * Constructor.
	 *
	 * @since   1.3.0
	 * @version  1.3.0
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
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function authorize_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, 'edit_post', $object_id );
	}

	/**
	 * Retrieve visibility information for a give object.
	 *
	 * @param   array           $obj  Assoc. array of WP_Post data.
	 * @param   WP_REST_Request $request   Full details about the request.
	 * @return  WP_Error|string Visibility term slug or WP_Error object.
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function get_callback( $obj, $request ) {

		$ret = array();

		$obj = new LLMS_Product( $obj['id'] );
		if ( $obj ) {
			$ret = $obj->get_catalog_visibility();
		}
		return $ret;

	}

	/**
	 * Update visibility information for a given object.
	 *
	 * @param   string  $value  new visibility status value.
	 * @param   WP_Post $object WP_Post object.
	 * @param   string  $key    name of the field.
	 * @return  null|WP_Error
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function update_callback( $value, $object, $key ) {

		if ( ! current_user_can( 'edit_post', $object->ID ) ) {
			return new WP_Error(
				'rest_cannot_update',
				__( 'Sorry, you are not allowed to edit the object visibility.', 'lifterlms' ),
				array(
					'key'    => $name,
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$obj = new LLMS_Product( $object->ID );
		if ( $obj ) {
			$obj->set_catalog_visibility( $value );
		}

		return null;
	}

	/**
	 * Register custom meta fields.
	 *
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function register_meta() {

		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {

			register_rest_field(
				$post_type,
				'visibility',
				array(
					'get_callback'    => array( $this, 'get_callback' ),
					'update_callback' => array( $this, 'update_callback' ),
					'schema'          => array(
						'description' => __( 'Post visibility.', 'lifterlms' ),
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

return new LLMS_Blocks_Post_Visibility();
