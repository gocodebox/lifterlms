<?php
/**
 * REST Controller for Webhooks.
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.3
 * @version 1.0.0-beta.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Webhooks_Controller class.
 *
 * @since 1.0.0-beta.3
 */
class LLMS_REST_Webhooks_Controller extends LLMS_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'webhooks';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'name',
		'created',
		'updated',
	);

	/**
	 * Check if the authenticated user can perform the request action.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return boolean
	 */
	protected function check_permissions() {
		return current_user_can( 'manage_lifterlms_webhooks' ) ? true : llms_rest_authorization_required_error();
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Insert the prepared data into the database.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param array           $prepared Prepared item data.
	 * @param WP_REST_Request $request Request object.
	 * @return obj Object Instance of object from $this->get_object().
	 */
	protected function create_object( $prepared, $request ) {

		return LLMS_REST_API()->webhooks()->create( $prepared );

	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Delete the object.
	 *
	 * Note: we do not return 404s when the resource to delete cannot be found. We assume it's already been deleted and respond with 204.
	 * Errors returned by this method should be any error other than a 404!
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param obj             $object Instance of the object from $this->get_object().
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error true when the object is removed, WP_Error on failure.
	 */
	protected function delete_object( $object, $request ) {

		return $object->delete();

	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		$params['status'] = array(
			'description' => __( 'Include only webhooks matching a specific status.', 'lifterlms' ),
			'type'        => 'string',
			'enum'        => array_keys( LLMS_REST_API()->webhooks()->get_statuses() ),
		);

		return $params;

	}

	/**
	 * Get the Webhook's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return array
	 */
	public function get_item_schema() {

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'api_key',
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Webhook ID.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'         => array(
					'description' => __( 'Friendly, human-readable name or description.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'status'       => array(
					'description' => __( 'The status of the webhook.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'enum'        => array_keys( LLMS_REST_API()->webhooks()->get_statuses() ),
					'default'     => 'disabled',
				),
				'topic'        => array(
					'description' => __( 'The webhook topic', 'lifterlms' ),
					'type'        => 'string',
					'required'    => true,
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'validate_callback' => array( LLMS_REST_API()->webhooks(), 'is_topic_valid' ),
					),
				),
				'delivery_url' => array(
					'description' => __( 'The webhook payload delivery URL.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'secret'       => array(
					'description' => __( 'An optional secret key used to generate the delivery signature.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'created'      => array(
					'description' => __( 'Creation date. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'updated'      => array(
					'description' => __( 'Date last modified. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'resource'     => array(
					'description' => __( 'The parsed resource from the `topic`.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'event'        => array(
					'description' => __( 'The parsed event from the `topic`.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'hooks'        => array(
					'description' => __( 'List of WordPress action hook associated with the webhook.', 'lifterlms' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param obj             $query Objects query result.
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return array {
	 *     Array of pagination information.
	 *
	 *     @type int $current_page Current page number.
	 *     @type int $total_results Total number of results.
	 *     @type int $total_pages Total number of results pages.
	 * }
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		return array(
			'current_page'  => $query->get( 'page' ),
			'total_results' => $query->found_results,
			'total_pages'   => $query->max_pages,
		);

	}

	/**
	 * Retrieve An Webhook object by ID.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param int  $id Webhook ID.
	 * @param bool $hydrate If true, pulls all key data from the database on instantiation.
	 * @return WP_Error|LLMS_REST_API_Key
	 */
	protected function get_object( $id, $hydrate = true ) {

		if ( ! is_numeric( $id ) ) {
			$id = $this->get_object_id( $id );
		}

		$key = LLMS_REST_API()->webhooks()->get( $id, $hydrate );
		return $key ? $key : llms_rest_not_found_error();

	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_User_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		return new LLMS_REST_Webhooks_Query( $prepared );

	}

	/**
	 * Retrieve an array of objects from the result of $this->get_objects_query().
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param obj $query Objects query result.
	 * @return obj[]
	 */
	protected function get_objects_from_query( $query ) {

		return $query->get_results();

	}

	/**
	 * Map request keys to database keys for insertion.
	 *
	 * Array keys are the request fields (as defined in the schema) and
	 * array values are the database fields.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return array
	 */
	protected function map_schema_to_database() {

		$map = parent::map_schema_to_database();

		// Not inserted/read via database calls.
		unset( $map['resource'], $map['event'], $map['hooks'] );

		return $map;

	}

	/**
	 * Prepare an object for response.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param LLMS_Abstract_User_Data $object User object.
	 * @param WP_REST_Request         $request Request object.
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $request ) {

		$prepared = parent::prepare_object_for_response( $object, $request );

		$prepared['id']       = absint( $prepared['id'] );
		$prepared['resource'] = $object->get_resource();
		$prepared['event']    = $object->get_event();
		$prepared['hooks']    = $object->get_hooks();
		$prepared['created']  = mysql_to_rfc3339( $prepared['created'] );
		$prepared['updated']  = mysql_to_rfc3339( $prepared['updated'] );

		return $prepared;

	}

	/**
	 * Update an Webhook
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$prepared = $this->prepare_item_for_database( $request );
		$key      = LLMS_REST_API()->webhooks()->update( $prepared );
		if ( is_wp_error( $request ) ) {
			$request->add_data( array( 'status' => 400 ) );
			return $request;
		}

		$response = $this->prepare_item_for_response( $key, $request );

		return $response;

	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

}
