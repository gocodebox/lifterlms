<?php
/**
 * Certificate Options meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 3.37.12
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box Certificate Options class.
 *
 * Displays email settings meta box. Only displays on email post.
 *
 * @since 1.0.0
 * @since 3.1.0 Unknown.
 * @since 3.1.4 Unknown.
 * @since 3.37.12 Allow some fields to store values with quotes.
 */
class LLMS_Meta_Box_Email_Settings extends LLMS_Admin_Metabox {


	/**
	 * Configure the metabox settings.
	 *
	 * @since 3.0.0
	 * @since 3.1.4 Unknown.
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-email';
		$this->title    = __( 'Email Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_email',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 *
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Unknown.
	 * @since 3.37.12 Allow some fields to store values with quotes.
	 *
	 * @return array Array of metabox fields.
	 */
	public function get_fields() {

		$email_merge = array(
			'{student_email}' => __( 'Student Email', 'lifterlms' ),
			'{admin_email}'   => __( 'Admin Email', 'lifterlms' ),
		);

		return array(
			array(
				'title'  => 'Settings',
				'fields' => array(
					array(
						'type'       => 'text',
						'label'      => __( 'Email Subject', 'lifterlms' ),
						'desc'       => __( 'This will be used for the subject line of your email.', 'lifterlms' ) . llms_merge_code_button( '#' . $this->prefix . 'email_subject', false ),
						'id'         => $this->prefix . 'email_subject',
						'class'      => 'code input-full',
						'value'      => '',
						'desc_class' => 'd-all',
						'group'      => 'top',
						'sanitize'   => 'no_encode_quotes',
					),
					array(
						'type'       => 'text',
						'label'      => __( 'Email Heading', 'lifterlms' ),
						'desc'       => __( 'This is the heading for your email. It will display above the content.', 'lifterlms' ),
						'id'         => $this->prefix . 'email_heading',
						'class'      => 'code input-full',
						'value'      => '',
						'desc_class' => 'd-all',
						'group'      => 'bottom',
						'sanitize'   => 'no_encode_quotes',
					),
					array(
						'type'       => 'text',
						'label'      => __( 'Email To:', 'lifterlms' ),
						'desc'       => __( 'Separate multiple address with a comma.', 'lifterlms' ) . llms_merge_code_button( '#' . $this->prefix . 'email_to', false, $email_merge ),
						'default'    => '{student_email}',
						'id'         => $this->prefix . 'email_to',
						'class'      => 'code input-full',
						'required'   => true,
						'value'      => '',
						'desc_class' => 'd-all',
					),
					array(
						'type'       => 'text',
						'label'      => __( 'Email CC:', 'lifterlms' ),
						'desc'       => __( 'Separate multiple address with a comma.', 'lifterlms' ) . llms_merge_code_button( '#' . $this->prefix . 'email_cc', false, $email_merge ),
						'id'         => $this->prefix . 'email_cc',
						'class'      => 'code input-full',
						'value'      => '',
						'desc_class' => 'd-all',
					),
					array(
						'type'       => 'text',
						'label'      => __( 'Email BCC:', 'lifterlms' ),
						'desc'       => __( 'Separate multiple address with a comma.', 'lifterlms' ) . llms_merge_code_button( '#' . $this->prefix . 'email_bcc', false, $email_merge ),
						'id'         => $this->prefix . 'email_bcc',
						'class'      => 'code input-full',
						'value'      => '',
						'desc_class' => 'd-all',
					),
				),
			),
		);

	}

}
