<?php
/**
 * Favorite Actions
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Favorite class
 *
 * @since [version]
 */
class LLMS_Controller_Favorite {

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'handle_favorite_form' ) );
		add_action( 'init', array( $this, 'handle_unfavorite_form' ) );

		add_action( 'llms_trigger_object_favorite', array( $this, 'mark_favorite' ), 10, 4 );

	}

	/**
	 * Retrieve a object ID from form data for the mark favorite / unfavorite forms
	 *
	 * @since [version]
	 *
	 * @param String $action Form action, either "favorite" or "unfavorite".
	 * @return int|null Returns `null` when either required post fields are missing or if the object_id is non-numeric, int (object id) on success.
	 */
	private function get_object_id_from_form_data( $action ) {

		if ( ! llms_verify_nonce( '_wpnonce', 'mark_' . $action, 'POST' ) ) {
			return null;
		}

		$submitted = llms_filter_input( INPUT_POST, 'mark_' . $action );
		$object_id = llms_filter_input( INPUT_POST, 'mark-' . $action );

		// Required fields.
		if ( is_null( $submitted ) || is_null( $object_id ) ) {
			return null;
		}

		$object_id = absint( $object_id );

		// Invalid lesson ID.
		if ( ! $object_id || ! is_numeric( $object_id ) ) {

			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );
			return null;

		}

		return $object_id;

	}

	/**
	 * Mark Object as favorite
	 *
	 * + Favorite Object form post.
	 * + Marks Object as favorite.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function handle_favorite_form() {

		$object_id   = $this->get_object_id_from_form_data( 'favorite' );
		$object_type = llms_filter_input( INPUT_POST, 'type' );

		if ( is_null( $object_id ) ) {
			return;
		}

		/**
		 * Filter to modify the user id instead of current logged in user id.
		 *
		 * @since [version]
		 *
		 * @param int  $user_id User id to mark lesson as favorite.
		 */
		$user_id = apply_filters( 'llms_object_favorite_user_id', get_current_user_id() );

		/**
		 * Action triggered for saving the favorite object to it's own postmeta.
		 *
		 * @since [version]
		 *
		 * @param int       $user_id     User ID who is marking object as favorite.
		 * @param int       $object_id   Object ID (Lesson, Course or Instructor).
		 * @param string    $object_type  Object description string (Lesson, Course or Instructor).
		 */
		do_action( 'llms_trigger_object_favorite', $user_id, $object_id, $object_type );

	}

	/**
	 * Mark Object as unfavorite
	 *
	 * + Unfavorite Object form post.
	 * + Marks Object as Unfavorite.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function handle_unfavorite_form() {

		$object_id   = $this->get_object_id_from_form_data( 'unfavorite' );
		$object_type = llms_filter_input( INPUT_POST, 'type' );

		if ( is_null( $object_id ) ) {
			return;
		}

		/**
		 * Filter to modify the user id instead of current logged in user id.
		 *
		 * @since [version]
		 *
		 * @param int  $user_id User id to mark lesson as favorite.
		 */
		$user_id = apply_filters( 'llms_object_unfavorite_user_id', get_current_user_id() );

		// Mark unfavorite.
		llms_mark_unfavorite( $user_id, $object_id, $object_type );

	}

	/**
	 * Handle favoriting of object via `llms_trigger_object_favorite` action
	 *
	 * @since [version]
	 *
	 * @param int    $user_id       User ID.
	 * @param int    $object_id     Object ID.
	 * @param string $object_type   Object description string (Lesson, Course or Instructor).
	 * @param array  $args          Optional arguments.
	 * @return void
	 */
	public function mark_favorite( $user_id, $object_id, $object_type = '', $args = array() ) {

		if ( llms_allow_lesson_completion( $user_id, $object_id, $object_type, $args ) ) {

			llms_mark_favorite( $user_id, $object_id, $object_type );

		}

	}

}

return new LLMS_Controller_Favorite();
