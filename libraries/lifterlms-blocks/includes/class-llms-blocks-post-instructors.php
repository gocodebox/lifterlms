<?php
/**
 * Handle course & membership instructors data.
 *
 * @package  LifterLMS_Blocks/Classes
 *
 * @since 1.0.0
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle course & membership instructors data.
 *
 * @since 1.0.0
 * @since 1.6.0 Automatically store course/membership instructor with `post_author` data when the post is created.
 * @since 1.7.1 Fix Core 5.3 compatibility issues with the `instructors` rest field.
 */
class LLMS_Blocks_Post_Instructors {

	/**
	 * Supported Post Types.
	 *
	 * @var array
	 */
	protected $post_types = array( 'course', 'llms_membership' );

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'save_post_course', array( $this, 'maybe_set_default_instructor' ), 50, 3 );
		add_action( 'save_post_llms_membership', array( $this, 'maybe_set_default_instructor' ), 50, 3 );

	}

	/**
	 * Meta field update authorization callback.
	 *
	 * @since   1.0.0
	 *
	 * @param   bool   $allowed   Is the update allowed.
	 * @param   string $meta_key  Meta keyname.
	 * @param   int    $object_id WP Object ID (post,comment,etc)...
	 * @param   int    $user_id   WP User ID.
	 * @param   string $cap       requested capability.
	 * @param   array  $caps      user capabilities.
	 * @return  bool
	 */
	public function authorize_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, 'edit_post', $object_id );
	}

	/**
	 * Retrieve instructor information for a give object.
	 *
	 * @since   1.0.0
	 *
	 * @param   array           $obj  Assoc. array of WP_Post data.
	 * @param   WP_REST_Request $request   Full details about the request.
	 * @return  WP_Error|object Object containing the meta values by name, otherwise WP_Error object.
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
	 * Automatically sets instructor data when a new course/membership is created.
	 *
	 * @since 1.6.0
	 *
	 * @link https://developer.wordpress.org/reference/hooks/save_post_post-post_type/
	 *
	 * @param int     $post_id WP_Post ID.
	 * @param WP_Post $post Post object.
	 * @param bool    $update Whether the save is an update (`true`) or a creation (`false`).
	 * @return bool
	 */
	public function maybe_set_default_instructor( $post_id, $post, $update ) {

		if ( $update || ! $post->post_author ) {
			return false;
		}

		$obj = llms_get_post( $post );
		if ( ! $obj || ! is_a( $obj, 'LLMS_Post_Model' ) || ! in_array( $obj->get( 'type' ), $this->post_types, true ) ) {
			return false;
		}

		remove_action( 'save_post_course', array( $this, 'maybe_set_instructors' ), 50, 3 );
		$obj->instructors()->set_instructors( array( array( 'id' => $post->post_author ) ) );

		return true;

	}

	/**
	 * Update instructor information for a given object.
	 *
	 * @since 1.0.0
	 * @since 1.7.1 Decode JSON prior to saving.
	 * @since 2.4.0 Fix access to non-existing variable when current user canno edit the course/membership.
	 *
	 * @param string  $value  Instructor data to add to the object (JSON).
	 * @param WP_Post $object WP_Post object.
	 * @param string  $key    Name of the field.
	 * @return null|WP_Error
	 */
	public function update_callback( $value, $object, $key ) {

		if ( ! current_user_can( 'edit_post', $object->ID ) ) {
			return new WP_Error(
				'rest_cannot_update',
				__( 'Sorry, you are not allowed to edit the object instructors.', 'lifterlms' ),
				array(
					'key'    => $key,
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$value = json_decode( $value, true );

		$obj = llms_get_post( $object );
		if ( $obj ) {
			$obj->instructors()->set_instructors( $value );
		}

		return null;
	}

	/**
	 * Register custom meta fields.
	 *
	 * @since 1.0.0
	 * @since 1.6.0 Use `$this->post_types` for loop.
	 * @since 1.7.1 Don't define a schema.
	 *
	 * @return void
	 */
	public function register_meta() {

		foreach ( $this->post_types as $post_type ) {

			register_rest_field(
				$post_type,
				'instructors',
				array(
					'get_callback'    => array( $this, 'get_callback' ),
					'update_callback' => array( $this, 'update_callback' ),
					'schema'          => null,
				)
			);

		}

	}

}

return new LLMS_Blocks_Post_Instructors();
