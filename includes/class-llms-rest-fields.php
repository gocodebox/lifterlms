<?php
/**
 * LLMS_Rest_Fields class
 *
 * @package LifterLMS/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers REST fields used by LifterLMS objects.
 *
 * @since 6.0.0
 */
class LLMS_REST_Fields {

	/**
	 * Constructor
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register' ) );
		add_filter( 'rest_prepare_llms_my_certificate', array( $this, 'remove_author_assign_link' ) );

	}

	/**
	 * Retrieves an array of data used to register fields for certificates.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function register() {

		if ( llms_is_block_editor_supported_for_certificates() ) {

			$this->register_fields_for_certificates();
			$this->register_fields_for_certificate_awards();
			$this->register_fields_for_certificate_templates();

		}

	}

	/**
	 * Register rest fields used for awarded certificates.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	private function register_fields_for_certificate_awards() {

		register_rest_field(
			'llms_my_certificate',
			'certificate_template',
			array(
				'schema'          => array(
					'description' => __( 'Certificate template ID.', 'lifterlms' ),
					'type'        => 'integer',
					'arg_options' => array(
						'validate_callback' => function( $value ) {
							return ! $value || 'llms_certificate' === get_post_type( $value );
						},
					),
				),
				'get_callback'    => function( $object ) {
					return wp_get_post_parent_id( $object['id'] );
				},
				'update_callback' => function( $value, $post ) {
					$update = array(
						'ID'          => $post->ID,
						'post_parent' => $value,
					);
					return wp_update_post( $update );
				},
			)
		);

	}

	/**
	 * Register rest fields used for certificate templates.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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

	/**
	 * Remove the author assign action link for llms_my_certificate REST responses.
	 *
	 * This is a hack put in place to prevent the default <PostAuthor> control component
	 * cannot be disabled in any other way I can find, the check in place on it determines
	 * if the control can be displayed based on the presence of this link in the REST response.
	 *
	 * Removing this probably isn't generally idea but I cannot conceive of any other way to handle this.
	 *
	 * @since 6.0.0
	 *
	 * @link https://github.com/WordPress/gutenberg/tree/trunk/packages/editor/src/components/post-author
	 *
	 * @param WP_REST_Response $res Rest response.
	 * @return WP_REST_Response
	 */
	public function remove_author_assign_link( $res ) {

		$res->remove_link( 'https://api.w.org/action-assign-author' );

		return $res;

	}

}

return new LLMS_REST_Fields();
