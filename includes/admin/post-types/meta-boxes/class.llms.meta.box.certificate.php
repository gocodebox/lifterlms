<?php
/**
 * Certificates meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Certificate template meta box class.
 *
 * @since 1.0.0
 * @since 3.37.12 Allow the certificate title field to store text with quotes.
 */
class LLMS_Meta_Box_Certificate extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 * @since 6.0.0 Renamed from "Certificate Settings" to "Settings".
	 *              Moved to the side context with default priority.
	 *
	 * @return void
	 */
	public function configure() {

		$this->id            = 'lifterlms-certificate';
		$this->title         = __( 'Settings', 'lifterlms' );
		$this->screens       = array(
			'llms_certificate',
		);
		$this->priority      = 'default';
		$this->context       = 'side';
		$this->callback_args = array(
			'__back_compat_meta_box' => true,
		);

	}

	/**
	 * Builds array of metabox options.
	 *
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @since 1.0.0
	 * @since 3.17.4 Unknown.
	 * @since 3.37.12 Allow the certificate title field to store text with quotes.
	 * @since 6.0.0 Remove the background image option (in favor of featured image metabox).
	 *              Expose the "Next Sequential ID" option.
	 *
	 * @return array Array of metabox fields.
	 */
	public function get_fields() {

		$next_id = llms_get_certificate_sequential_id( $this->post->ID );

		$fields = array(
			array(
				'label'      => __( 'Certificate Title', 'lifterlms' ),
				'id'         => $this->prefix . 'certificate_title',
				'type'       => 'text',
				'class'      => 'input-full',
				'desc_class' => 'd-all',
				'sanitize'   => 'no_encode_quotes',
			),
			array(
				'label'      => __( 'Next Sequential ID', 'lifterlms' ),
				'id'         => $this->prefix . 'sequential_id',
				'type'       => 'number',
				'class'      => 'input-full',
				'desc_class' => 'd-all',
				'value'      => $next_id,
				'min'        => $next_id,
				'step'       => 1,
			),
		);

		return array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => $fields,
			),
		);
	}

}
