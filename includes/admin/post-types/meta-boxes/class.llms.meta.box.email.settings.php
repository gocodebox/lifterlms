<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Certificate Options
*
* displays email settings metabox. only dislays on email post
*/
class LLMS_Meta_Box_Email_Settings extends LLMS_Admin_Metabox {


	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-email';
		$this->title = __( 'Email Settings', 'lifterlms' );
		$this->screens = array(
			'llms_email',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 * @return array [md array of metabox fields]
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {

		return array(
			array(
				'title' 	=> 'Settings',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> __( 'Email Subject', 'lifterlms' ),
						'desc' 		=> __( 'This will be used for the subject line of your email. The Subject allows mergefields.', 'lifterlms' ),
						'id' 		=> $this->prefix .'email_subject',
						'class' 	=> 'code',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> 'top',
					),
					array(
						'type'		=> 'text',
						'label'		=> __( 'Email Heading', 'lifterlms' ),
						'desc' 		=> __( 'This is the heading for your email. It will display above the content.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'email_heading',
						'class' 	=> 'code',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> 'bottom',
					),
					array(
						'type'		=> 'custom-html',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> '',
						'class' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> 'bottom',
						'value' 	=> '<p>' . __( 'Use the text editor above to add content to your certificate. You can include any of the following merge fields.', 'lifterlms' ) . '
										<br>{site_title}
										<br>{user_login}
										<br>{site_url}
										<br>{first_name}
										<br>{last_name}
										<br>{email_address}
										<br>{current_date}
										</p>',
					),
				),
			),
		);

	}

}
