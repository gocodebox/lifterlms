<?php
/**
 * Engagement Email
 *
 * @package LifterLMS/Emails/Classes
 *
 * @since 1.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagement Email Class
 *
 * Generates emails and sends to user. Triggered from an engagement.
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Email_Engagement extends LLMS_Email {

	/**
	 * Email identifier
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $id = 'engagement';

	/**
	 * @since 3.8.0
	 * @var WP_User
	 */
	public $student;

	/**
	 * Initialize all variables
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @param array $args Associative array of engagement args.
	 * @return void
	 */
	public function init( $args ) {

		$this->student    = new WP_User( $args['person_id'] );
		$this->email_post = get_post( $args['email_id'] );

		$this->add_merge_data(
			array(
				'{user_login}'    => stripslashes( $this->student->user_login ),
				'{first_name}'    => stripslashes( $this->student->first_name ),
				'{last_name}'     => stripslashes( $this->student->last_name ),
				'{email_address}' => stripslashes( $this->student->user_email ),
				'{site_url}'      => get_permalink( llms_get_page_id( 'myaccount' ) ),
				'{current_date}'  => date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ),
			)
		);

		// Setup subject, headline, & body.
		$this->body    = $this->email_post->post_content;
		$this->subject = get_post_meta( $this->email_post->ID, '_llms_email_subject', true );
		$this->heading = get_post_meta( $this->email_post->ID, '_llms_email_heading', true );

		// Setup all the recipients.
		foreach ( array( 'to', 'cc', 'bcc' ) as $type ) {

			$list = get_post_meta( $this->email_post->ID, '_llms_email_' . $type, true );

			// Fall back to student email for existing emails with no definition.
			if ( ! $list && 'to' === $type ) {
				$list = '{student_email}';
			}

			if ( ! $list ) {
				continue;
			}

			foreach ( $this->merge_emails( $list ) as $email ) {
				$this->add_recipient( $email, $type );
			}
		}

	}

	/**
	 * Handles email merge codes that can be used in the to, cc, and bcc fields
	 *
	 * @since 3.1.0
	 * @since 3.8.0 Unknown.
	 *
	 * @param string $list Unmerged, comma-separated list of emails
	 * @return array
	 */
	private function merge_emails( $list ) {

		$codes = array(
			'{student_email}',
			'{admin_email}',
		);

		$addresses = array(
			$this->student->ID,
			get_option( 'admin_email' ),
		);

		$merged = str_replace( $codes, $addresses, $list );
		$array  = explode( ',', $merged );
		return array_map( 'trim', $array );

	}

	/**
	 * Send email
	 *
	 * @since 5.0.0
	 *
	 * @return boolean
	 */
	public function send() {

		add_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );

		$ret = parent::send();

		remove_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );

		return $ret;

	}

	/**
	 * Set the user ID used by [llms-user] to the user receiving the email.
	 *
	 * @since 5.0.0
	 *
	 * @param int $uid WP_User ID of the current user.
	 * @return int
	 */
	public function set_shortcode_user( $uid ) {
		return $this->student->ID;
	}

}
