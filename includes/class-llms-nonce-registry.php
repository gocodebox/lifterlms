<?php
/**
 * LLMS_Nonce_Registry class
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create, manage, and verify nonces used throughout LifterLMS
 *
 * @since [version]
 */
class LLMS_Nonce_Registry {

	/**
	 * Create a nonce string by form ID
	 *
	 * This is a wrapper for `wp_create_nonce()` that creates a registered nonce using the form's ID.
	 *
	 * @since [version]
	 *
	 * @param string $id The form's ID.
	 * @return string|boolean The nonce string or `false` if the nonce isn't registered by the given ID.
	 */
	public static function create( $id ) {
		$nonce = self::get_by_form_id( $id );
		return $nonce ? wp_create_nonce( $nonce['action'] ) : false;
	}

	/**
	 * Retrieve a nonce by form action or ID
	 *
	 * @since [version]
	 *
	 * @param string $identifier The form action or ID.
	 * @param string $field      Field to retrieve by. Either "id" or "action".
	 * @return array|boolean Returns the nonce object or `false` if not found.
	 */
	public static function get( $identifier, $field = 'id' ) {

		$nonce = false;

		switch ( $field ) {
			case 'id':
				$nonce = self::get_by_form_action( $identifier );
				break;

			case 'action':
				$nonce = self::get_by_form_id( $identifier );
				break;
		}

		return $nonce;

	}


	/**
	 * Retrieve all registered nonces
	 *
	 * @since [version]
	 *
	 * @return array[] Array of nonce arrays.
	 */
	private static function get_all() {

		$nonces = array(
			'account' => array(
				'field'  => '_llms_update_person_nonce',
				'action' => 'llms_update_person',
			),
			'checkout' => array(
				'field'  => '_llms_checkout_nonce',
				'action' => 'create_pending_order',
			),
			'login' => array(
				'field'  => '_llms_register_person_nonce',
				'action' => 'llms_login_user',
			),
			'registration' => array(
				'field'  => '_llms_register_person_nonce',
				'action' => 'llms_register_person',
			),
		);

		/**
		 * Filter the registered nonces
		 *
		 * @since [version]
		 *
		 * @param array[] $nonces Array of nonce arrays.
		 */
		return apply_filters( 'llms_registered_nonces', $nonces );

	}

	/**
	 * Retrieve a registered nonce array based on the form's action
	 *
	 * @since [version]
	 *
	 * @param array $form_action The form action.
	 * @return array|boolean Returns the nonce object or `false` if not found.
	 */
	private static function get_by_form_action( $form_action ) {

		$id = self::get_form_id( $form_action );
		if ( $id ) {
			return self::get_by_form_id( $id );
		}
		return false;

	}

	/**
	 * Retrieve a registered nonce array based on the form's ID
	 *
	 * @since [version]
	 *
	 * @param array $form_id The form ID.
	 * @return array|boolean Returns the nonce object or `false` if not found.
	 */
	private static function get_by_form_id( $form_id ) {

		$nonces = self::get_all();
		return isset( $nonces[ $form_id ] ) ? $nonces[ $form_id ] : false;

	}

	/**
	 * Retrieve or echo the HTML for a nonce field and form action
	 *
	 * This is a wrapper for `wp_nonce_field()` that creates and retrieves the HTML
	 * using the form's ID.
	 *
	 * It additionally adds a hidden input for the form's "action" which can be used to
	 * verify the nonce later.
	 *
	 * @since [version]
	 *
	 * @param string  $id      The form's ID.
	 * @param boolean $referer Whether or not to add a referer field.
	 * @param boolean $echo    If `true`, will output the HTML.
	 * @return string The HTML for the fields. Returns an empty string if the form isn't registered.
	 */
	public static function get_fields( $id, $referer = true, $echo = true ) {

		$nonce = self::get_by_form_id( $id );
		if ( ! $nonce ) {
			return '';
		}

		$fields = wp_nonce_field( $nonce['action'], $nonce['field'], $referer, false );
		$action = self::get_form_action( $id );

		if ( $action ) {
			$fields .= '<input type="hidden" name="action" value="' . $action . '" />';
		}

		if ( $echo ) {
			echo $fields;
		}

		return $fields;

	}

	/**
	 * Retrieve the action of a form based on the form ID
	 *
	 * @since [version]
	 *
	 * @param string $form_id The form ID.
	 * @return string The form's action.
	 */
	private static function get_form_action( $form_id ) {
		$map = self::get_form_actions_map();
		return isset( $map[ $form_id ] ) ? $map[ $form_id ] : false;
	}

	/**
	 * Retrieve an array mapping form IDs to the form's action
	 *
	 * @since [version]
	 *
	 * @return array Each array key is the ID of the form and the value is the form's action.
	 */
	private static function get_form_actions_map() {

		$map = array(
			 'account'      => 'llms_update_person',
			 'checkout'     => 'create_pending_order',
			 'login'        => 'llms_login_user',
			 'registration' => 'llms_register_person',
		);

		/**
		 * Filters the form actions map
		 *
		 * @since [version]
		 *
		 * @param array $map Form actions map.
		 */
		return apply_filters( 'llms_nonce_registry_form_actions_map', $map );

	}

	/**
	 * Retrieve the ID of a form based on the form action
	 *
	 * @since [version]
	 *
	 * @param string $form_action The form action.
	 * @return string|boolean Returns the form ID or `false` if not found.
	 */
	private static function get_form_id( $form_action ) {

		$map = array_flip( self::get_form_actions_map() );
		return isset( $map[ $form_action ] ) ? $map[ $form_action ] : false;

	}

	/**
	 * Verify a request
	 *
	 * Looks up the nonce using the form's action and attempts nonce verification.
	 *
	 * @since [version]
	 *
	 * @param string $request_method Form submission request method, either "POST" or "GET".
	 * @return null|boolean|int Returns `null` if the action wasn't submitted with the request or no form can be
	 *                          found for the given action.
	 *                          Returns `false` if the nonce is invalid.
	 *                          Returns `1` if the nonce is valid and generated between 0-12 hours ago.
	 *                          Returns `2` if the nonce is valid and generated between 12-24 hours ago.
	 */
	public static function verify_request( $request_method = 'POST' ) {

		$input = 'POST' === $request_method ? 'INPUT_POST' : 'INPUT_GET';

		$action = llms_filter_input( constant( $input ), 'action', FILTER_SANITIZE_STRING );
		if ( ! $action ) {
			return null;
		}

		$nonce = self::get_by_form_action( $action );
		if ( ! $nonce ) {
			return null;
		}

		return llms_verify_nonce( $nonce['field'], $nonce['action'], $request_method );

	}



}
