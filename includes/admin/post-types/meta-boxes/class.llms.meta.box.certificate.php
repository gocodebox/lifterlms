<?php
/**
 * Certificates meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 3.37.12
 */

defined( 'ABSPATH' ) || exit;

/**
 * Certificates meta box class
 *
 * @since 1.0.0
 * @since 3.17.4 Unknown.
 * @since 3.37.12 Allow the certificate title field to store text with quotes.
 */
class LLMS_Meta_Box_Certificate extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-certificate';
		$this->title    = __( 'Certificate Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_certificate',
		);
		$this->priority = 'high';

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
	 *
	 * @return array Array of metabox fields.
	 */
	public function get_fields() {

		return array(
			array(
				'title'  => 'General',
				'fields' => array(
					array(
						'label'      => __( 'Certificate Title', 'lifterlms' ),
						'desc'       => __( 'Enter a title for your certificate. EG: Certificate of Completion', 'lifterlms' ),
						'id'         => $this->prefix . 'certificate_title',
						'type'       => 'text',
						'section'    => 'certificate_meta_box',
						'class'      => 'code input-full',
						'desc_class' => 'd-all',
						'group'      => '',
						'value'      => '',
						'sanitize'   => 'no_encode_quotes',
					),
					array(
						'label'      => __( 'Background Image', 'lifterlms' ),
						'desc'       => __( 'Select an Image to use for the certificate.', 'lifterlms' ),
						'id'         => $this->prefix . 'certificate_image',
						'type'       => 'image',
						'section'    => 'certificate_meta_box',
						'class'      => 'certificate',
						'desc_class' => 'd-all',
						'group'      => '',
						'value'      => '',
					),
				),
			),
		);
	}

}
