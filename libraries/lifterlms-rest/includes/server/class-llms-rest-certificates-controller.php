<?php
/**
 * REST Certificates Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Certificates_Controller class.
 *
 * Manages certificate templates (the `llms_certificate` post type).
 *
 * @since [version]
 */
class LLMS_REST_Certificates_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_certificate';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'certificates';

	/**
	 * Check if a given request has access to read items.
	 *
	 * Unlike courses or lessons, certificate templates are not public content:
	 * reading them requires template editing capabilities.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( get_post_type_object( $this->post_type )->cap->edit_posts ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Checks if a certificate template can be read.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $object The certificate template object.
	 * @return bool Whether the template can be read.
	 */
	protected function check_read_permission( $object ) {
		return current_user_can( 'edit_post', $object->get( 'id' ) );
	}

	/**
	 * Get object.
	 *
	 * The `llms_certificate` post type has no dedicated post model: core itself models
	 * templates with `LLMS_User_Certificate` (see `llms_get_certificate()`), so we do the same.
	 *
	 * @since [version]
	 *
	 * @param int|WP_Post $id Object ID or already retrieved WP_Post.
	 * @return LLMS_User_Certificate|WP_Error
	 */
	protected function get_object( $id ) {

		$post = get_post( $id );
		if ( ! $post || $this->post_type !== $post->post_type ) {
			return llms_rest_not_found_error();
		}

		return llms_get_certificate( $post, true );
	}

	/**
	 * Create an LLMS_User_Certificate wrapping a certificate template post.
	 *
	 * @since [version]
	 *
	 * @param array $object_args Object args.
	 * @return LLMS_User_Certificate|WP_Error
	 */
	protected function create_llms_post( $object_args ) {

		// The model's default db post type is `llms_my_certificate`: force the template post type.
		$object_args['post_type'] = $this->post_type;

		$object = new LLMS_User_Certificate( 'new', $object_args );
		if ( ! $object || ! $object->get( 'id' ) ) {
			return llms_rest_not_found_error();
		}

		return $this->get_object( $object->get( 'id' ) );
	}

	/**
	 * Prepares a single certificate template for create or update.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error Array of template args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = parent::prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$schema = $this->get_item_schema();

		if ( isset( $request['sequential_id'] ) && ! empty( $request['id'] ) ) {
			$current_id = llms_get_certificate_sequential_id( $request['id'] );
			if ( absint( $request['sequential_id'] ) < $current_id ) {
				return llms_rest_bad_request_error(
					sprintf(
						// Translators: %d = the current next sequential ID.
						__( 'The sequential_id must be greater than or equal to the current next sequential ID (%d).', 'lifterlms' ),
						$current_id
					)
				);
			}
		}

		/**
		 * Filters a certificate template before it is inserted via the REST API.
		 *
		 * @since [version]
		 *
		 * @param array           $prepared_item Array of item properties prepared for database.
		 * @param WP_REST_Request $request       Full details about the request.
		 * @param array           $schema        The item schema.
		 */
		return apply_filters( 'llms_rest_pre_insert_llms_certificate', $prepared_item, $request, $schema );
	}

	/**
	 * Updates additional information not handled by the main post insert.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $certificate   Certificate template object.
	 * @param WP_REST_Request       $request       Full details about the request.
	 * @param array                 $schema        The item schema.
	 * @param array                 $prepared_item Prepared item array.
	 * @param bool                  $creating      Optional. Whether we're in creation or update phase. Default true (create).
	 * @return bool|WP_Error True on success or false if nothing to update, WP_Error object if something went wrong during the update.
	 */
	protected function update_additional_object_fields( $certificate, $request, $schema, $prepared_item, $creating = true ) {

		$updated = false;

		if ( isset( $request['certificate_title'] ) ) {
			$certificate->set( 'certificate_title', sanitize_text_field( $request['certificate_title'] ) );
			$updated = true;
		}

		if ( isset( $request['sequential_id'] ) ) {
			$certificate->set( 'sequential_id', absint( $request['sequential_id'] ) );
			$updated = true;
		}

		foreach ( array( 'size', 'unit', 'orientation', 'background' ) as $prop ) {
			if ( isset( $request[ $prop ] ) ) {
				$certificate->set( $prop, sanitize_text_field( $request[ $prop ] ) );
				$updated = true;
			}
		}

		foreach ( array( 'width', 'height' ) as $prop ) {
			if ( isset( $request[ $prop ] ) ) {
				$certificate->set( $prop, (float) $request[ $prop ] );
				$updated = true;
			}
		}

		if ( isset( $request['margins'] ) ) {
			$certificate->set( 'margins', array_map( 'floatval', (array) $request['margins'] ) );
			$updated = true;
		}

		return $updated;
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $certificate Certificate template object.
	 * @param WP_REST_Request       $request     Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $certificate, $request ) {

		$data = parent::prepare_object_for_response( $certificate, $request );

		$data['certificate_title'] = $certificate->get( 'certificate_title' );
		$data['sequential_id']     = llms_get_certificate_sequential_id( $certificate->get( 'id' ) );
		$data['size']              = $certificate->get_size();
		$data['width']             = $certificate->get_width();
		$data['height']            = $certificate->get_height();
		$data['unit']              = $certificate->get_unit();
		$data['orientation']       = $certificate->get_orientation();
		$data['margins']           = $certificate->get_margins();
		$data['background']        = $certificate->get_background();

		return $data;
	}

	/**
	 * Get the Certificate template's schema, conforming to JSON Schema.
	 *
	 * @since [version]
	 *
	 * @return array Item schema data.
	 */
	protected function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		// Certificate templates don't expose these post props.
		unset(
			$schema['properties']['excerpt'],
			$schema['properties']['comment_status'],
			$schema['properties']['ping_status'],
			$schema['properties']['password'],
			$schema['properties']['menu_order']
		);

		$schema['properties']['title']['description'] = __( 'The certificate template title, used for administrative purposes.', 'lifterlms' );

		$template_properties = array(
			'certificate_title' => array(
				'description' => __( 'The title displayed on certificates awarded from this template.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'sequential_id'     => array(
				'description' => __( 'The next sequential ID used when awarding a certificate from this template. Must be greater than or equal to the current next sequential ID.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'size'              => array(
				'description' => __( 'The certificate size.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_merge(
					array_keys( llms_get_certificate_sizes() ),
					array( 'CUSTOM' )
				),
				'context'     => array( 'view', 'edit' ),
			),
			'width'             => array(
				'description' => __( 'The certificate width. Only used when size is CUSTOM.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
			),
			'height'            => array(
				'description' => __( 'The certificate height. Only used when size is CUSTOM.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
			),
			'unit'              => array(
				'description' => __( 'The certificate sizing unit applied to the width and height properties.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_units() ),
				'context'     => array( 'view', 'edit' ),
			),
			'orientation'       => array(
				'description' => __( 'The certificate orientation.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_certificate_orientations() ),
				'context'     => array( 'view', 'edit' ),
			),
			'margins'           => array(
				'description' => __( 'The certificate margins: top, right, bottom, and left.', 'lifterlms' ),
				'type'        => 'array',
				'minItems'    => 4,
				'maxItems'    => 4,
				'items'       => array(
					'type' => 'number',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'background'        => array(
				'description' => __( 'The certificate background color, as a CSS color string.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
		);

		$schema['properties'] = array_merge( $schema['properties'], $template_properties );

		return $schema;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $object  Object data.
	 * @param WP_REST_Request       $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		unset( $links['content'] );

		$links['awarded_certificates'] = array(
			'href' => add_query_arg(
				'template',
				$object->get( 'id' ),
				rest_url( sprintf( '/%1$s/awarded-certificates', $this->namespace ) )
			),
		);

		return $links;
	}
}
