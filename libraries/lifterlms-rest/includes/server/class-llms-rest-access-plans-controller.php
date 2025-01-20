<?php
/**
 * REST Access Plans Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.18
 * @version 1.0.0-beta.27
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Access_Plans_Controller class
 *
 * @since 1.0.0-beta.18
 */
class LLMS_REST_Access_Plans_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_access_plan';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'access-plans';

	/**
	 * Get the Access Plan's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta.27 Do not fire the llms_rest_access_plan_item_schema filter, it'll be fired in `LLMS_REST_Controller::filter_item_schema()`.
	 *
	 * @return array
	 */
	public function get_item_schema_base() {

		$schema = (array) parent::get_item_schema_base();

		// Post properties to unset.
		$properties_to_unset = array(
			'comment_status',
			'excerpt',
			'featured_media',
			'password',
			'ping_status',
			'slug',
			'status',
		);

		foreach ( $properties_to_unset as $to_unset ) {
			unset( $schema['properties'][ $to_unset ] );
		}

		// The content is not required.
		unset( $schema['properties']['content']['required'] );

		$access_plan_properties = require LLMS_REST_API_PLUGIN_DIR . 'includes/server/schemas/schema-access-plans.php';

		$schema['properties'] = array_merge(
			$schema['properties'],
			$access_plan_properties
		);

		return $schema;

	}

	/**
	 * Retrieves the query params for the objects collection
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['post_id'] = array(
			'description'       => __( 'Retrieve access plans for a specific list of one or more posts. Accepts a course/membership id or comma separated list of course/membership ids.', 'lifterlms' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}

	/**
	 * Retrieves an array of arguments for the delete endpoint
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return array Delete endpoint arguments.
	 */
	public function get_delete_item_args() {
		return array();
	}

	/**
	 * Whether the delete should be forced
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the delete should be forced, false otherwise.
	 */
	protected function is_delete_forced( $request ) {
		return true;
	}

	/**
	 * Whether the trash is supported
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return bool True if the trash is supported, false otherwise.
	 */
	protected function is_trash_supported() {
		return false;
	}

	/**
	 * Check if a given request has access to create an item
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		$can_create = parent::create_item_permissions_check( $request );

		// If current user cannot create the item because of authorization, check if the current user can edit the "parent" course/membership.
		$can_create = $this->related_product_permissions_check( $can_create, $request );

		return is_wp_error( $can_create ) ? $can_create : $this->allow_request_when_access_plan_limit_not_reached( $request );
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta.20 Call to private method `block_request_when_access_plan_limit` replaced with a call to the new `allow_request_when_access_plan_limit_not_reached` method.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {

		$can_update = parent::update_item_permissions_check( $request );

		// If current user cannot edit the item because of authorization, check if the current user can edit the "parent" course/membership.
		$can_update = $this->related_product_permissions_check( $can_update, $request );

		return is_wp_error( $can_update ) ? $can_update : $this->allow_request_when_access_plan_limit_not_reached( $request );

	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {

		$can_delete = parent::delete_item_permissions_check( $request );

		// If current user cannot delete the item because of authorization, check if the current user can edit the "parent" course/membership.
		return $this->related_product_permissions_check( $can_delete, $request );

	}

	/**
	 * Prepare links for the request
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param LLMS_Access_Plan $access_plan LLMS Access Plan instance.
	 * @param WP_REST_Request  $request     Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $access_plan, $request ) {

		$links = parent::prepare_links( $access_plan, $request );
		unset( $links['content'] );

		$links['post'] = array(
			'href' => rest_url(
				sprintf(
					'%s/%s/%s',
					'llms/v1',
					'course' === $access_plan->get_product_type() ? 'courses' : 'memberships',
					$access_plan->get( 'product_id' )
				)
			),
		);

		// Membership restrictions.
		if ( $access_plan->has_availability_restrictions() ) {
			$links['restrictions'] = array(
				'href' => rest_url(
					sprintf(
						'%s/%s?include=%s',
						'llms/v1',
						'memberships',
						implode( ',', $access_plan->get_array( 'availability_restrictions' ) )
					)
				),
			);
		}

		/**
		 * Filters the access plan's links.
		 *
		 * @since 1.0.0-beta.18
		 *
		 * @param array            $links       Links for the given access plan.
		 * @param LLMS_Access_Plan $access_plan LLMS Access Plan instance.
		 */
		return apply_filters( 'llms_rest_access_plan_links', $links, $access_plan );

	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta.20 Fixed return format of the `access_expires` property.
	 *                      Fixed sale date properties.
	 * @since 1.0.0-beta-24 Fixed `availability_restrictions` never returned.
	 *
	 * @param LLMS_Access_Plan $access_plan LLMS Access Plan instance.
	 * @param WP_REST_Request  $request     Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $access_plan, $request ) {

		$data    = parent::prepare_object_for_response( $access_plan, $request );
		$context = $request->get_param( 'context' );

		// Price.
		$data['price'] = $access_plan->is_free() ? 0 : $access_plan->get_price( 'price', array(), 'float' );

		// Access expiration.
		$data['access_expiration'] = $access_plan->get( 'access_expiration' );

		// Access expires date.
		if ( 'limited-date' === $data['access_expiration'] || 'edit' === $context ) {
			$data['access_expires'] = $access_plan->get_date( 'access_expires', 'Y-m-d H:i:s' );
		}

		// Access length and period.
		if ( 'limited-period' === $data['access_expiration'] || 'edit' === $context ) {
			$data['access_length'] = $access_plan->get( 'access_length' );
			$data['access_period'] = $access_plan->get( 'access_period' );
		}

		// Availability restrictions, only returned for courses.
		if ( 'course' === $access_plan->get_product_type() ) {
			$data['availability_restrictions'] = $access_plan->has_availability_restrictions()
				?
				array_map( 'absint', $access_plan->get_array( 'availability_restrictions' ) )
				:
				array();
		}

		// Enroll text.
		$data['enroll_text'] = $access_plan->get_enroll_text();

		// Frequency.
		$data['frequency'] = $access_plan->get( 'frequency' );

		// Length and period.
		if ( 0 < $data['frequency'] || 'edit' === $context ) {
			$data['length'] = $access_plan->get( 'length' );
			$data['period'] = $access_plan->get( 'period' );
		}

		// Post ID.
		$data['post_id'] = $access_plan->get( 'product_id' );

		// Redirect forced.
		if ( ! empty( $data['availability_restrictions'] ) || 'edit' === $context ) {
			$data['redirect_forced'] = llms_parse_bool( $access_plan->get( 'checkout_redirect_forced' ) );
		}

		// Redirect type.
		$data['redirect_type'] = $access_plan->get( 'checkout_redirect_type' );

		// Redirect page.
		if ( 'page' === $data['redirect_type'] || 'edit' === $context ) {
			$data['redirect_page'] = $access_plan->get( 'checkout_redirect_page' );
		}

		// Redirect url.
		if ( 'url' === $data['redirect_type'] || 'edit' === $context ) {
			$data['redirect_url'] = $access_plan->get( 'checkout_redirect_url' );
		}

		// Permalink.
		$data['permalink'] = $access_plan->get_checkout_url( false );

		// Sale enabled.
		$data['sale_enabled'] = llms_parse_bool( $access_plan->get( 'on_sale' ) );

		// Sale start/end and price.
		if ( $data['sale_enabled'] || 'edit' === $context ) {
			$data['sale_date_start'] = $access_plan->get_date( 'sale_start', 'Y-m-d H:i:s' );
			$data['sale_date_end']   = $access_plan->get_date( 'sale_end', 'Y-m-d H:i:s' );
			$data['sale_price']      = $access_plan->get_price( 'sale_price', array(), 'float' );
		}

		// SKU.
		$data['sku'] = $access_plan->get( 'sku' );

		// Trial.
		$data['trial_enabled'] = $access_plan->has_trial();

		if ( $data['trial_enabled'] || 'edit' === $context ) {
			$data['trial_length'] = $access_plan->get( 'trial_length' );
			$data['trial_period'] = $access_plan->get( 'trial_period' );
			$data['trial_price']  = $access_plan->get_price( 'trial_price', array(), 'float' );
		}

		// Visibility.
		$data['visibility'] = $access_plan->get_visibility();

		/**
		 * Filters the access plan data for a response.
		 *
		 * @since 1.0.0-beta.18
		 *
		 * @param array            $data        Array of lesson properties prepared for response.
		 * @param LLMS_Access_Plan $access_plan LLMS Access Plan instance.
		 * @param WP_REST_Request  $request     Full details about the request.
		 */
		$data = apply_filters( 'llms_rest_prepare_access_plan_object_response', $data, $access_plan, $request );

		return $data;
	}

	/**
	 * Format query arguments to retrieve a collection of objects
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $query_args ) ) {
			return $query_args;
		}

		// Filter by post ID.
		if ( ! empty( $request['post_id'] ) ) {
			$query_args = array_merge(
				$query_args,
				array(
					'meta_query' => array(
						array(
							'key'     => '_llms_product_id',
							'value'   => $request['post_id'],
							'compare' => 'IN',
						),
					),
				)
			);
		}

		return $query_args;
	}

	/**
	 * Prepares a single post for create or update
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error Array of llms post args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = parent::prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$schema = $this->get_item_schema();

		// Enroll text.
		if ( ! empty( $schema['properties']['enroll_text'] ) && isset( $request['enroll_text'] ) ) {
			$prepared_item['enroll_text'] = $request['enroll_text'];
		}

		// Post id.
		if ( ! empty( $schema['properties']['post_id'] ) && isset( $request['post_id'] ) ) {
			$prepared_item['product_id'] = $request['post_id'];
		}

		// SKU.
		if ( ! empty( $schema['properties']['sku'] ) && isset( $request['sku'] ) ) {
			$prepared_item['sku'] = $request['sku'];
		}

		/**
		 * Filters the access plan data before inserting in the db
		 *
		 * @since 1.0.0-beta.18
		 *
		 * @param array           $prepared_item Array of access plan item properties prepared for database.
		 * @param WP_REST_Request $request       Full details about the request.
		 * @param array           $schema        The item schema.
		 */
		$prepared_item = apply_filters( 'llms_rest_pre_insert_access_plan', $prepared_item, $request, $schema );

		return $prepared_item;
	}

	/**
	 * Updates an existing single LLMS_Access_Plan in the database.
	 *
	 * This method should be used for access plan properties that require the access plan id in order to be saved in the database.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta-24 Fixed reference to a non-existent schema property: visibiliy in place of visibility.
	 *                      Fixed issue that prevented updating the access plan `redirect_forced` property.
	 *                      Better handling of the availability_restrictions.
	 * @since 1.0.0-beta.25 Allow updating meta with the same value as the stored one.
	 *
	 * @param LLMS_Access_Plan $access_plan   LLMS Access Plan instance.
	 * @param WP_REST_Request  $request       Full details about the request.
	 * @param array            $schema        The item schema.
	 * @param array            $prepared_item Array.
	 * @param bool             $creating      Optional. Whether we're in creation or update phase. Default true (create).
	 * @return bool|WP_Error True on success or false if nothing to update, WP_Error object if something went wrong during the update.
	 */
	protected function update_additional_object_fields( $access_plan, $request, $schema, $prepared_item, $creating = true ) {

		$error = new WP_Error();

		// Will contain the properties to set.
		$to_set = array();

		// Access expiration.
		if ( ! empty( $schema['properties']['access_expiration'] ) && isset( $request['access_expiration'] ) ) {
			$to_set['access_expiration'] = $request['access_expiration'];
		}

		// Access expires.
		if ( ! empty( $schema['properties']['access_expires'] ) && isset( $request['access_expires'] ) ) {
			$access_expires           = rest_parse_date( $request['access_expires'] );
			$to_set['access_expires'] = empty( $access_expires ) ? '' : date_i18n( 'Y-m-d H:i:s', $access_expires );
		}

		// Access length.
		if ( ! empty( $schema['properties']['access_length'] ) && isset( $request['access_length'] ) ) {
			$to_set['access_length'] = $request['access_length'];
		}

		// Access period.
		if ( ! empty( $schema['properties']['access_period'] ) && isset( $request['access_period'] ) ) {
			$to_set['access_period'] = $request['access_period'];
		}

		// Redirect.
		if ( ! empty( $schema['properties']['redirect_type'] ) && isset( $request['redirect_type'] ) ) {
			$to_set['checkout_redirect_type'] = $request['redirect_type'];
		}

		// Redirect page.
		if ( ! empty( $schema['properties']['redirect_page'] ) && isset( $request['redirect_page'] ) ) {
			$redirect_page = get_post( $request['redirect_page'] );
			if ( $redirect_page && is_a( $redirect_page, 'WP_Post' ) ) {
				$to_set['checkout_redirect_page'] = $request['redirect_page']; // maybe allow only published pages?
			}
		}

		// Redirect url.
		if ( ! empty( $schema['properties']['redirect_url'] ) && isset( $request['redirect_url'] ) ) {
			$to_set['checkout_redirect_url'] = $request['redirect_url'];
		}

		// Price.
		if ( ! empty( $schema['properties']['price'] ) && isset( $request['price'] ) ) {
			$to_set['price'] = $request['price'];
		}

		// Sale enabled.
		if ( ! empty( $schema['properties']['sale_enabled'] ) && isset( $request['sale_enabled'] ) ) {
			$to_set['on_sale'] = $request['sale_enabled'] ? 'yes' : 'no';
		}

		// Sale dates.
		if ( ! empty( $schema['properties']['sale_date_start'] ) && isset( $request['sale_date_start'] ) ) {
			$sale_date_start      = rest_parse_date( $request['sale_date_start'] );
			$to_set['sale_start'] = empty( $sale_date_start ) ? '' : date_i18n( 'Y-m-d H:i:s', $sale_date_start );
		}

		if ( ! empty( $schema['properties']['sale_date_end'] ) && isset( $request['sale_date_end'] ) ) {
			$sale_date_end      = rest_parse_date( $request['sale_date_end'] );
			$to_set['sale_end'] = empty( $sale_date_end ) ? '' : date_i18n( 'Y-m-d H:i:s', $sale_date_end );
		}
		// Sale price.
		if ( ! empty( $schema['properties']['sale_price'] ) && isset( $request['sale_price'] ) ) {
			$to_set['sale_price'] = $request['sale_price'];
		}

		// Trial enabled.
		if ( ! empty( $schema['properties']['trial_enabled'] ) && isset( $request['trial_enabled'] ) ) {
			$to_set['trial_offer'] = $request['trial_enabled'] ? 'yes' : 'no';
		}

		// Trial Length.
		if ( ! empty( $schema['properties']['trial_length'] ) && isset( $request['trial_length'] ) ) {
			$to_set['trial_length'] = $request['trial_length'];
		}
		// Trial Period.
		if ( ! empty( $schema['properties']['trial_period'] ) && isset( $request['trial_period'] ) ) {
			$to_set['trial_period'] = $request['trial_period'];
		}
		// Trial price.
		if ( ! empty( $schema['properties']['trial_price'] ) && isset( $request['trial_price'] ) ) {
			$to_set['trial_price'] = $request['trial_price'];
		}

		// Availability restrictions.
		// If access plan related post type is not a course, set availability to 'open' and clean the `availability_restrictions` array.
		if ( 'course' !== $access_plan->get_product_type() ) {
			$to_set['availability']              = 'open';
			$to_set['availability_restrictions'] = array();
		} elseif ( ! empty( $schema['properties']['availability_restrictions'] ) && isset( $request['availability_restrictions'] ) ) {
			$to_set['availability_restrictions'] = $request['availability_restrictions'];
			// If availability restrictions supplied is not empty, set `availability` to 'members'.
			$to_set['availability'] = ! empty( $to_set['availability_restrictions'] ) ? 'members' : 'open';
		}

		// Redirect forced.
		if ( ! empty( $schema['properties']['redirect_forced'] ) && isset( $request['redirect_forced'] ) ) {
			$to_set['checkout_redirect_forced'] = $request['redirect_forced'] ? 'yes' : 'no';
		}

		// Frequency.
		if ( ! empty( $schema['properties']['frequency'] ) && isset( $request['frequency'] ) ) {
			$to_set['frequency'] = $request['frequency'];
		}

		// Length.
		if ( ! empty( $schema['properties']['length'] ) && isset( $request['length'] ) ) {
			$to_set['length'] = $request['length'];
		}
		// Period.
		if ( ! empty( $schema['properties']['period'] ) && isset( $request['period'] ) ) {
			$to_set['period'] = $request['period'];
		}

		$this->handle_props_interdependency( $to_set, $access_plan, $creating );

		// Visibility.
		if ( ! empty( $schema['properties']['visibility'] ) && isset( $request['visibility'] ) ) {
			$visibility = $access_plan->set_visibility( $request['visibility'] );
			if ( is_wp_error( $visibility ) ) {
				return $visibility;
			}
		}

		// Set bulk.
		if ( ! empty( $to_set ) ) {
			$update = $access_plan->set_bulk( $to_set, true, true );
			if ( is_wp_error( $update ) ) {
				$error = $update;
			}
		}

		if ( $error->errors ) {
			return $error;
		}

		return ! empty( $to_set ) || ! empty( $visibility );
	}

	/**
	 * Handle properties interdependency
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param array            $to_set      Array of properties to be set.
	 * @param LLMS_Access_Plan $access_plan LLMS Access Plan instance.
	 * @param bool             $creating    Whether we're in creation or update phase.
	 * @return void
	 */
	private function handle_props_interdependency( &$to_set, $access_plan, $creating ) {

		// Access Plan properties as saved in the db.
		$saved_props = $access_plan->toArray();

		$this->add_subordinate_props( $to_set, $saved_props, $creating );

		$this->unset_subordinate_props( $to_set, $saved_props );

	}

	/**
	 * Add all the properties which need to be set as consequence of another setting
	 *
	 * These properties must be compared to the saved value before updating, because if equal they will produce an error(see update_post_meta()).
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta-24 Cast `price` property to float.
	 * @since 1.0.0-beta.25 Allow updating meta with the same value as the stored one.
	 *
	 * @param array $to_set      Array of properties to be set.
	 * @param array $saved_props Array of LLMS_Access_Plan properties as saved in the db.
	 * @param bool  $creating    Whether we're in creation or update phase.
	 * @return void
	 */
	private function add_subordinate_props( &$to_set, $saved_props, $creating ) {

		$subordinate_props = array();

		// Merge new properties to set and saved props.
		$props = wp_parse_args( $to_set, $saved_props );

		// Paid plan.
		if ( $props['price'] > 0 ) {

			$subordinate_props['is_free'] = 'no';

			// One-time (no trial).
			if ( 0 === $props['frequency'] ) {
				$subordinate_props['trial_offer'] = 'no';
			}
		} else {

			$subordinate_props['is_free']     = 'yes';
			$subordinate_props['price']       = 0.0;
			$subordinate_props['frequency']   = 0;
			$subordinate_props['on_sale']     = 'no';
			$subordinate_props['trial_offer'] = 'no';

		}

		$to_set = array_merge( $to_set, $subordinate_props );

	}

	/**
	 * Remove all the properties that do not need to be set, based on other properties
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @param array $to_set      Array of properties to be set.
	 * @param array $saved_props Array of LLMS_Access_Plan properties as saved in the db.
	 * @return void
	 */
	private function unset_subordinate_props( &$to_set, $saved_props ) {

		// Merge new properties to set and saved props.
		$props = wp_parse_args( $to_set, $saved_props );

		// No need to create/update recurring props when it's a 1-time payment.
		if ( 0 === $props['frequency'] ) {
			unset( $to_set['length'], $to_set['period'] );
		}

		// No need to create/update trial props when no trial enabled.
		if ( ! llms_parse_bool( $props['trial_offer'] ) ) {
			unset( $to_set['trial_price'], $to_set['trial_length'], $to_set['trial_period'] );
		}

		// No need to create/update sale props when not on sale.
		if ( ! llms_parse_bool( $props['on_sale'] ) ) {
			unset( $to_set['sale_price'], $to_set['sale_end'], $to_set['sale_start'] );
		}

		// Unset redirect props based on redirect settings.
		if ( 'url' === $props['checkout_redirect_type'] ) {
			unset( $to_set['checkout_redirect_page'] );
		} elseif ( 'page' === $props['checkout_redirect_type'] ) {
			unset( $to_set['checkout_redirect_url'] );
		} else {
			unset( $to_set['checkout_redirect_url'], $to_set['checkout_redirect_page'] );
		}

		// Unset expiration props based on expiration settings.
		if ( 'lifetime' === $props['access_expiration'] ) {
			unset( $to_set['access_expires'], $to_set['access_length'], $to_set['access_period'] );
		} elseif ( 'limited-date' === $props['access_expiration'] ) {
			unset( $to_set['access_length'], $to_set['access_period'] );
		} elseif ( 'limited-period' === $props['access_expiration'] ) {
			unset( $to_set['access_expires'] );
		}
	}

	/**
	 * Check if the current user, who has no permissions to manipulate the access plan post, can edit its related product.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta.20 Made sure either we're creating or updating prior to check related product's permissions.
	 *
	 * @param boolean|WP_Error $has_permissions Whether or not the current user has the permission to manipulate the resource.
	 * @param WP_REST_Request  $request         Full details about the request.
	 * @return boolean|WP_Error
	 */
	private function related_product_permissions_check( $has_permissions, $request ) {

		if ( llms_rest_is_authorization_required_error( $has_permissions ) ) {

			// `id` required on "reading/updating", `post_id` required on "creating".
			if ( empty( $request['id'] ) && empty( $request['post_id'] ) ) {
				return $has_permissions;
			}

			$product_id = isset( $request['id'] ) /* not creation */ ? $this->get_object( (int) $request['id'] )->get( 'product_id' ) : (int) $request['post_id'];

			$product_post_type_object = get_post_type_object( get_post_type( $product_id ) );

			if ( current_user_can( $product_post_type_object->cap->edit_post, $product_id ) ) {
				$has_permissions = true;
			}
		}

		return $has_permissions;
	}

	/**
	 * Allow request when the access plan limit per product is not reached.
	 *
	 * @since 1.0.0-beta.20
	 * @since 1.0.0-beta-24 Made sure we can update an access plan of a product even if its access plan limit has already been reached.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error
	 */
	private function allow_request_when_access_plan_limit_not_reached( $request ) {

		// `id` required on "reading/updating", `post_id` required on "creating".
		if ( empty( $request['id'] ) && empty( $request['post_id'] ) ) {
			return true;
		}

		$product_id           = isset( $request['post_id'] ) ? $request['post_id'] : $this->get_object( (int) $request['id'] )->get( 'product_id' );
		$product              = new LLMS_Product( $product_id );
		$limit                = $product->get_access_plan_limit();
		$product_access_plans = $product->get_access_plans( false, false );
		// Check whether we're updating an access plan, and whether this access plan was already a destination's product access plan,
		// otherwise we're either creating an access plan or moving the access plans from a product to a different one.
		$updating_product_access_plan = ! empty( $request['id'] ) && ! empty( $product_access_plans ) && in_array( $request['id'], wp_list_pluck( $product_access_plans, 'id' ), true );

		if ( ! $updating_product_access_plan && count( $product_access_plans ) >= $limit ) {

			return llms_rest_bad_request_error(
				sprintf(
					// Translators: %1$d = access plans limit per product, %2$s access plan post type plural name, %3$s product post type singular name.
					__( 'Only %1$d %2$s allowed per %3$s', 'lifterlms' ),
					$limit,
					strtolower( get_post_type_object( $this->post_type )->labels->name ),
					strtolower( get_post_type_object( get_post_type( $product_id ) )->labels->singular_name )
				)
			);

		}

		return true;
	}

}
