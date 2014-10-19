<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LLMS_Email_Engagement' ) ) :

class LLMS_Email_Engagement extends LLMS_Email {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 */
	function __construct() {
		

		parent::__construct();
	}

	public function init($email_id) {
		global $wpdb;
LLMS_log('email id=' . $email_id);
		//$querystr = get_post($email_id);

		 // $querystr = "
		 //    SELECT $wpdb->posts.* 
		 //    FROM $wpdb->posts, $wpdb->postmeta
		 //    WHERE $wpdb->posts.ID = '$email_id' 
		 //    AND $wpdb->posts.post_status = 'publish' 
		 //    AND $wpdb->posts.post_type = 'llms_email'
		 //    AND $wpdb->posts.post_date < NOW()
		 //    ORDER BY $wpdb->posts.post_date DESC LIMIT 1
		 // ";

 		$email_content = get_post($email_id);//$wpdb->get_results($querystr, OBJECT);
 		LLMS_log($email_content);
 		$email_meta = get_post_meta( $email_content->ID );

		$this->id 				= 'person_new_account';
		$this->title 			= __( 'New account', 'lifterlms' );
		$this->description		= __( 'Person new account emails are sent when a person signs up via the checkout or My Account page.', 'lifterlms' );

		$this->template_html 	= 'emails/template.php';

		$this->subject 			= $email_meta['_email_subject'][0];
		$this->heading      	= $email_meta['_email_heading'][0];//__( 'Welcome to {site_title}', 'lifterlms');
		$this->email_content	= $email_content->post_content;
		$this->account_link 	= get_permalink( llms_get_page_id( 'myaccount' ) );
	}

	/**
	 * trigger function.
	 *
	 * @return void
	 */
	function trigger( $user_id, $email_id ) {
		$this->init($email_id);

		if ( $user_id ) {
			$this->object 		= new WP_User( $user_id );

			$this->user_pass          = $user_pass;
			$this->user_login         = stripslashes( $this->object->user_login );
			$this->user_email         = stripslashes( $this->object->user_email );
			$this->recipient          = $this->user_email;
			$this->password_generated = $password_generated;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() )
			return;

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers() );
	}

	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
	function get_content_html() {

		$this->find = array( 
			'{site_title}', 
			'{user_login}', 
			'{site_url}' );
		$this->replace = array( 
			$this->get_blogname(), 
			$this->user_login, 
			$this->account_link );

		$content = $this->format_string($this->email_content);

		ob_start();
		llms_get_template( $this->template_html, array(
			'email_heading'      => $this->get_heading(),
			'user_login'         => $this->user_login,
			'user_pass'          => $this->user_pass,
			'blogname'           => $this->get_blogname(),
			'password_generated' => $this->password_generated,
			'email_message' 	 => $content,
			'sent_to_admin' => false,
			'plain_text'    => false
		) );
		return ob_get_clean();
	}

}

endif;

return new LLMS_Email_Engagement();