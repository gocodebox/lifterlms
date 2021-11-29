<?php
/**
 * LLMS_Rest_Fields class
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers REST fields used by LifterLMS objects.
 *
 * @since [version]
 */
class LLMS_REST_Fields {

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register' ) );

	}

	/**
	 * Retrieves an array of data used to register fields for certificates.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_fields_for_certificates() {

		return array(
			'size'  => array(
				'description' => __( 'Certificate size.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_merge(
					array_keys( llms_get_certificate_sizes() ),
					array( 'CUSTOM' ),
				),
			),
			'width' => array(
				'description' => __( 'Certificate width.', 'lifterlms' ),
				'type'        => 'number',
			),
			'height' => array(
				'description' => __( 'Certificate height.', 'lifterlms' ),
				'type'        => 'number',
			),
			'unit' => array(
				'description' => __( 'Certificate sizing unit applied to the width and height properties.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_units() ),
			),
			'orientation'  => array(
				'description' => __( 'Certificate orientation.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_orientations() ),
			),
			'margins'  => array(
				'description' => __( 'Certificate margins.', 'lifterlms' ),
				'type'        => 'array',
				'minItems'    => 4,
				'maxItems'    => 4,
				'items'       => array(
					'type' => 'number',
				),
			),
			'background'  => array(
				'description' => __( 'Certificate background color.', 'lifterlms' ),
				'type'        => 'string',
			),
		);

	}

	/**
	 * Register the REST fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register() {

		$this->register_fields_for_certificates();

	}

	/**
	 * Register fields for certificate post types.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function register_fields_for_certificates() {

		foreach ( $this->get_fields_for_certificates() as $key => $schema ) {

			$schema['context'] = array( 'view', 'edit' );

			register_rest_field( array( 'llms_certificate', 'llms_my_certificate' ), "certificate_{$key}", array(
				'schema'          => $schema,
				'get_callback'    => function( $object ) use ( $key ) {
					$cert = llms_get_certificate( $object['id'], true );
					$func = "get_{$key}";
					return $cert->$func();
				},
				'update_callback' => function( $value, $post ) use ( $key ) {
					$cert = llms_get_certificate( $post->ID, true );
					return $cert->set( $key, $value );
				},
			) );

		}

	}

}

return new LLMS_REST_Fields();
