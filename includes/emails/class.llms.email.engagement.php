<?php
/**
* Engagement Email Class
* Generates emails and sends to user. Triggered from an engagement.
* @since   1.0.0
* @version 3.1.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Email_Engagement extends LLMS_Email {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 * Inherits parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Initialize all variables
	 * @param    int $user_id          WP User ID of the recieving user
	 * @param    int $email_id         WP Post ID of an llms_email Post Type
	 * @param    int $related_post_id  WP Post ID of the triggering post
	 * @return   void
	 * @since    1.0.0
	 * @version  3.1.0
	 */
	public function init( $email_id, $user_id, $related_post_id ) {

		global $wpdb;

		$email_content = get_post( $email_id );
		$email_meta = get_post_meta( $email_content->ID );

		$this->id 					= 'engagement_email';
		$this->title 				= __( 'Engagement Email', 'lifterlms' );
		$this->template_html 		= 'emails/template.php';
		$this->subject 				= isset( $email_meta['_llms_email_subject'] ) ? $email_meta['_llms_email_subject'][0] : '';
		$this->heading      		= isset( $email_meta['_llms_email_heading'] ) ? $email_meta['_llms_email_heading'][0] : '';
		$this->to            		= isset( $email_meta['_llms_email_to'] ) ? $email_meta['_llms_email_to'][0] : '{student_email}'; // fall back to student email for existing emails with no definition
		$this->cc            		= isset( $email_meta['_llms_email_cc'] ) ? $email_meta['_llms_email_cc'][0] : '';
		$this->bcc            		= isset( $email_meta['_llms_email_bcc'] ) ? $email_meta['_llms_email_bcc'][0] : '';
		$this->email_content		= $email_content->post_content;
		$this->account_link 		= get_permalink( llms_get_page_id( 'myaccount' ) );
		$this->related_post_id      = $related_post_id;

		if ( $user_id ) {

			$this->object 			  = new WP_User( $user_id );
			$this->user_login         = stripslashes( $this->object->user_login );
			$this->user_email         = stripslashes( $this->object->user_email );
			$this->recipient          = $this->user_email;
			$this->user_firstname	  = stripslashes( $this->object->first_name );
			$this->user_lastname	  = stripslashes( $this->object->last_name );

		}

		$date_format = apply_filters( 'llms_email_engagement_date_format', 'M d, Y' );

		$this->find = array(
			'{site_title}',
			'{user_login}',
			'{site_url}',
			'{first_name}',
			'{last_name}',
			'{email_address}',
			'{current_date}',
		);
		$this->replace = array(
			$this->get_blogname(),
			$this->user_login,
			$this->account_link,
			$this->user_firstname,
			$this->user_lastname,
			$this->user_email,
			date_i18n( $date_format, strtotime( current_time( 'mysql' ) ) ),
		);

	}

	/**
	 * Builds an array of CC or BCC emails that can be merged into the header array
	 * Merges email address codes and validates addresses
	 * @param    string     $list    list of unmerged email address
	 * @param    string     $header  type of header [CC|BCC]
	 * @return   array
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	private function build_email_header( $list, $header ) {

		$headers = array();

		$list = $this->merge_emails( $list );
		foreach ( explode( ',', $list ) as $email ) {
			$email = trim( $email );
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$headers[] = $header . ': ' . $email . "\r\n";
			}
		}

		return $headers;

	}

	/**
	 * get_content_html function.
	 * @return string
	 * @since    1.0.0
	 * @version  3.1.0
	 */
	public function get_content_html() {

		$content = $this->format_string( $this->email_content );

		ob_start();
		llms_get_template( $this->template_html, array(
			'email_heading'      => $this->get_heading(),
			'user_login'         => $this->user_login,
			'user_pass'          => $this->user_pass,
			'blogname'           => $this->get_blogname(),
			'email_message' 	 => $content,
			'sent_to_admin' => false,
			'plain_text'    => false,
		) );
		return ob_get_clean();
	}

	/**
	 * Gets an array of email headers ultimately passed to wp_mail
	 * overrides the parent function to add CC & BCC
	 * @return   array
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	public function get_headers() {

		$headers = array(
			'Content-Type: ' . $this->get_content_type() . "\r\n"
		);

		if ( $this->cc ) {
			$headers = array_merge( $headers, $this->build_email_header( $this->cc, 'CC' ) );
		}

		if ( $this->bcc ) {
			$headers = array_merge( $headers, $this->build_email_header( $this->bcc, 'BCC' ) );
		}

		return apply_filters( 'lifterlms_email_headers', $headers, $this->id, $this->object );
	}

	/**
	 * Get recipient email address
	 * Overrides parent function and returns an array of merged & valid email addresses
	 * This array is ultimately sent to wp_mail which knows how to handle an array for the "to" arg
	 * @return   array
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	public function get_recipient() {

		$to = $this->merge_emails( $this->to );
		$to = explode( ',', $to );
		$emails = array();
		foreach ( $to as $email ) {
			$email = trim( $email );
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$emails[] = trim( $email );
			}
		}

		return apply_filters( 'lifterlms_email_recipient_' . $this->id, $emails, $this->object );
	}

	/**
	 * Handles email merge codes that can be used in the to, cc, and bcc fields
	 * @param    string  $list  unmerged, comma-separated list of emails
	 * @return   string
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	private function merge_emails( $list ) {

		$codes = array(
			'{student_email}',
			'{admin_email}',
		);

		$addresses = array(
			$this->recipient,
			get_option( 'admin_email' ),
		);

		return str_replace( $codes, $addresses, $list );

	}

	/**
	 * Sends an engagement email to a user
	 * @param    int $user_id          WP User ID of the recieving user
	 * @param    int $email_id         WP Post ID of an llms_email Post Type
	 * @param    int $related_post_id  WP Post ID of the triggering post
	 * @return   void
	 * @since    1.0.0
	 * @version  3.4.1
	 */
	public function trigger( $user_id, $email_id, $related_post_id ) {

		global $wpdb;

		$res = (int) $wpdb->get_var( $wpdb->prepare( "
			SELECT count( meta_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE post_id = %d
			  AND user_id = %d
			  AND meta_value = %d
			  LIMIT 1
			;",
			array(
				$related_post_id,
				$user_id,
				$email_id,
			)
		) );

		if ( $res >= 1 ) {
			return;
		}

		$this->init( $email_id, $user_id, $related_post_id );

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$send = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers() );

		if ( $send ) {

			$wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
				array(
					'user_id' 			=> $user_id,
					'post_id' 			=> $related_post_id,
					'meta_key'			=> '_email_sent',
					'meta_value'		=> $email_id,
					'updated_date'		=> current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%d', '%s' )
			);

		} // not sent and debug enabled
		elseif ( ! $send && defined( 'LLMS_ENGAGEMENT_DEBUG' ) && LLMS_ENGAGEMENT_DEBUG ) {
			llms_log( sprintf( 'Error: email `#%d` was not sent', $email_id ) );
		}
	}

}

return new LLMS_Email_Engagement();
