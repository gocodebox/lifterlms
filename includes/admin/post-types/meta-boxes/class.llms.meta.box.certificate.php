<?php
/**
 * Certificates meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version [version]
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

	use LLMS_Trait_Earned_Engagement_Meta_Box;

	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 * @since [version] Show metabox in `llms_my_certificate` post type as well.
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-certificate';
		$this->title    = __( 'Certificate Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_certificate',
			'llms_my_certificate',
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
	 * @since [version] Handle specific fields for earned engaegments post types.
	 *
	 * @return array Array of metabox fields.
	 */
	public function get_fields() {

		$fields = array(
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
		);

		$fields = $this->add_earned_engagement_fields( $fields );

		return array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => $fields,
			),
		);
	}

}
