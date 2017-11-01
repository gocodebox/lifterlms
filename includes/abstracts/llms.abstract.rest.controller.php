<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Rest API Controller
 * @author    LifterLMS
 * @category  API
 * @package   LifterLMS/API
 * @since     [version]
 * @version   [version]
 */
abstract class LLMS_Abstract_REST_Controller extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 * @var string
	 * @since [version]
	 * @since [version]
	 */
	protected $namespace = 'llms/v1';

	/**
	 * The base of this controller's route.
	 * @var string
	 * @since [version]
	 * @since [version]
	 */
	protected $rest_base = '';

	/**
	 * Default orderby enum options
	 * @var  array
	 * @since [version]
	 * @since [version]
	 */
	protected $orderby_enum = array( 'id' );

	/**
	 * Ensure the total number of items on a batch request does not exceed the maximum batch limit
	 * @param    array     $items  batch request data
	 * @return   bool|WP_Error
	 * @since    [version]
	 * @version  [version]
	 */
	public function check_batch_limit( $items ) {

		$limit = apply_filters( 'llms_rest_batch_items_limit', 100, $this->rest_base );

		$total = 0;
		// if ( ! empty( $items['create'] ) ) {
		// 	$total += count( $items['create'] );
		// }
		if ( ! empty( $items['update'] ) ) {
			$total += count( $items['update'] );
		}
		// if ( ! empty( $items['delete'] ) ) {
		// 	$total += count( $items['delete'] );
		// }

		if ( $total > $limit ) {
			return new WP_Error( 'llms_rest_request_entity_too_large', sprintf( __( 'Unable to accept more than %s items for this request.', 'lifterlms' ), $limit ), array( 'status' => 413 ) );
		}

		return true;

	}

	/**
	 * Retrieves the query params for the collections.
	 * @return  array
	 * @since   [version]
	 * @version [version]
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		$params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.', 'lifterlms' ),
			'type'               => 'string',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc' ),
			'validate_callback'  => 'rest_validate_request_arg',
		);
		$params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.', 'lifterlms' ),
			'type'               => 'string',
			'default'            => $this->orderby_enum[0],
			'enum'               => $this->orderby_enum,
			'validate_callback'  => 'rest_validate_request_arg',
		);

		return $params;

	}

}
