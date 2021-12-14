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
			'size'        => array(
				'description' => __( 'Certificate size.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_merge(
					array_keys( llms_get_certificate_sizes() ),
					array( 'CUSTOM' )
				),
			),
			'width'       => array(
				'description' => __( 'Certificate width.', 'lifterlms' ),
				'type'        => 'number',
			),
			'height'      => array(
				'description' => __( 'Certificate height.', 'lifterlms' ),
				'type'        => 'number',
			),
			'unit'        => array(
				'description' => __( 'Certificate sizing unit applied to the width and height properties.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_units() ),
			),
			'orientation' => array(
				'description' => __( 'Certificate orientation.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_orientations() ),
			),
			'margins'     => array(
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
		$this->register_fields_for_certificate_awards();
		$this->register_fields_for_certificate_templates();

	}

	/**
	 * Registers rest fields user for awarded certificates.
	 *
	 * This provides a REST field in place of the default WP Core author field. Since the post type
	 * doesn't support `author` the field isn't returned by the REST API so we add a custom field,
	 * `user`, in it's place.
	 *
	 * We don't want to enable `author` support for this as the author selection interface only supports
	 * authors returned by the `?who=authors` which doesn't satisfy our needs. And there's no way I can find
	 * to disable the default UI if we do enable `author` post type support.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function register_fields_for_certificate_awards() {

		register_rest_field(
			'llms_my_certificate',
			'user',
			array(
				'schema'          => array(
					'description' => __( 'User ID of the user who earned the certificate.', 'lifterlms' ),
					'type'        => 'integer',
					'arg_options' => array(
						'validate_callback' => function( $value, $request ) {
							return false !== get_userdata( (int) $value );
						},
					),
				),
				'get_callback'    => function( $object ) {
					$cert = llms_get_certificate( $object['id'], true );
					return $cert ? $cert->get( 'author' ) : null;
				},
				'update_callback' => function( $value, $post ) {
					$cert = llms_get_certificate( $post->ID, true );
					return $cert ? $cert->set( 'author', $value ) : null;
				},
			)
		);

	}

	/**
	 * Register rest fields used for certificate templates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function register_fields_for_certificate_templates() {

		register_rest_field(
			'llms_certificate',
			'certificate_title',
			array(
				'schema'          => array(
					'description' => __( 'Certificate title.', 'lifterlms' ),
					'type'        => 'string',
				),
				'get_callback'    => function( $object ) {
					$cert = llms_get_certificate( $object['id'], true );
					return $cert ? $cert->get( 'certificate_title' ) : null;
				},
				'update_callback' => function( $value, $post ) {
					$cert = llms_get_certificate( $post->ID, true );
					return $cert ? $cert->set( 'certificate_title', $value ) : null;
				},
			)
		);

		register_rest_field(
			'llms_certificate',
			'certificate_sequential_id',
			array(
				'schema'          => array(
					'description' => __( 'Next sequential ID.', 'lifterlms' ),
					'type'        => 'integer',
					'arg_options' => array(
						'validate_callback' => function( $value, $request ) {
							return (int) $value >= llms_get_certificate_sequential_id( $request['id'] );
						},
					),
				),
				'get_callback'    => function( $object ) {
					return llms_get_certificate_sequential_id( $object['id'] );
				},
				'update_callback' => function( $value, $post ) {
					$cert = llms_get_certificate( $post->ID, true );
					return $cert ? $cert->set( 'sequential_id', $value ) : null;
				},
			)
		);

	}

	/**
	 * Register fields for template and earned certificates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function register_fields_for_certificates() {

		foreach ( $this->get_fields_for_certificates() as $key => $schema ) {

			$schema['context'] = array( 'view', 'edit' );

			register_rest_field(
				array( 'llms_certificate', 'llms_my_certificate' ),
				"certificate_{$key}",
				array(
					'schema'          => $schema,
					'get_callback'    => function( $object ) use ( $key ) {
						$cert = llms_get_certificate( $object['id'], true );
						$func = "get_{$key}";
						return $cert ? $cert->$func() : null;
					},
					'update_callback' => function( $value, $post ) use ( $key ) {
						$cert = llms_get_certificate( $post->ID, true );
						return $cert ? $cert->set( $key, $value ) : null;
					},
				)
			);

		}

	}

}

return new LLMS_REST_Fields();
