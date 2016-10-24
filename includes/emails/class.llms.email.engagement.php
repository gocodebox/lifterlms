<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Engagement Email Class
* Child Class. Extends from LLMS_Email.
*
* Generates emails and sends to user. Triggered from engagement.
*/
class LLMS_Email_Engagement extends LLMS_Email {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 * Inherits parent constructor
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 * Initialize all variables
	 * @param    int $user_id   WP User ID of the recieving user
	 * @param    int $email_id  WP Post ID of an llms_email Post Type
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.5
	 */
	public function init( $email_id, $user_id ) {

		global $wpdb;

		$email_content = get_post( $email_id );
		$email_meta = get_post_meta( $email_content->ID );

		$this->id 					= 'engagement email';
		$this->title 				= __( 'Engagement Email', 'lifterlms' );
		$this->template_html 		= 'emails/template.php';
		$this->subject 				= isset( $email_meta['_llms_email_subject'] ) ? $email_meta['_llms_email_subject'][0] : '';
		$this->heading      		= isset( $email_meta['_llms_email_heading'] ) ? $email_meta['_llms_email_heading'][0] : '';
		$this->email_content		= $email_content->post_content;
		$this->account_link 		= get_permalink( llms_get_page_id( 'myaccount' ) );

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
	 * Sends an engagement email to a user
	 * @param    int $user_id   WP User ID of the recieving user
	 * @param    int $email_id  WP Post ID of an llms_email Post Type
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.5
	 */
	function trigger( $user_id, $email_id ) {

		$this->init( $email_id, $user_id );

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers() );
	}

	/**
	 * get_content_html function.
	 * @return string
	 * @since    1.0.0
	 * @version  3.0.5
	 */
	function get_content_html() {

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

}

return new LLMS_Email_Engagement();
