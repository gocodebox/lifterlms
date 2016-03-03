<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Certificate Options
*
* displays email settings metabox. only dislays on email post
*/
class LLMS_Meta_Box_Email_Settings extends LLMS_Admin_Metabox {

	public static $prefix = '_';

	/**
	 * outputs the Meta Box on the page
	 * @param object $post Global WP Post Object
	 * @param array The array returned by the local metabox_options() function
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$email_subject = get_post_meta( $post->ID, '_email_subject', true );
		$email_heading = get_post_meta( $post->ID, '_email_heading', true );

		$meta_fields_email_settings = array(
			array(
				'title' 	=> 'Settings',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'Email Subject',
						'desc' 		=> 'This will be used for the subject line of your email. The Subject allows mergefields.',
						'id' 		=> self::$prefix .'email_subject',
						'class' 	=> 'code',
						'value' 	=> $email_subject,
						'desc_class' => 'd-all',
						'group' 	=> 'top',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Email Heading',
						'desc' 		=> 'This is the heading for your email. It will display above the content.',
						'id' 		=> self::$prefix . 'email_heading',
						'class' 	=> 'code',
						'value' 	=> $email_heading,
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
						'value' 	=> '<p>Use the text editor above to add content to your email.
										You can include any of the following merge fields to give the email a personal touch.
										<br>{site_title}
										<br>{user_login}
										<br>{site_url}
										<br>{first_name}
										<br>{last_name}
										<br>{email_address}
										<br>{current_date}</p>
									',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_email_settings' )) {
			//Add Fields to the email settings Meta Box
			$meta_fields_email_settings = apply_filters( 'llms_meta_fields_email_settings', $meta_fields_email_settings );
		}

		return $meta_fields_email_settings;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_email_subject'] ) ) {
			//update email subject textbox
			$subject = ( llms_clean( $_POST['_email_subject'] ) );
			update_post_meta( $post_id, '_email_subject', ( $subject === '' ) ? '' : $subject );

		}

		if ( isset( $_POST['_email_heading'] ) ) {
			//update heading textbox
			$heading = ( llms_clean( $_POST['_email_heading'] ) );
			update_post_meta( $post_id, '_email_heading', ( $heading === '' ) ? '' : $heading );

		}
	}

}
