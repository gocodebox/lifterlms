<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LLMS_Email_Person_New extends LLMS_Email {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 */
	function __construct() {
		global $wpdb;

		 $querystr = "
		    SELECT $wpdb->posts.* 
		    FROM $wpdb->posts, $wpdb->postmeta
		    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
		    AND $wpdb->postmeta.meta_value = 'email_new_user' 
		    AND $wpdb->posts.post_status = 'publish' 
		    AND $wpdb->posts.post_type = 'llms_email'
		    ORDER BY $wpdb->posts.post_date DESC LIMIT 1
		 ";

 		$email_content = $wpdb->get_results($querystr, OBJECT);
 		$email_meta = get_post_meta( $email_content[0]->ID );
		$this->id 				= 'person_new_account';
		$this->title 			= __( 'New account', 'lifterlms' );
		$this->description		= __( 'Person new account emails are sent when a person signs up via the checkout or My Account page.', 'lifterlms' );

		$this->template_html 	= 'emails/template.php';

		$this->subject 			= $email_meta['_email_subject'][0];
		$this->heading      	= isset($email_meta['_email_heading'][0]) ? $email_meta['_email_heading'][0] : __( 'Welcome to {site_title}', 'lifterlms');//__( 'Welcome to {site_title}', 'lifterlms');
		$this->email_content	= $email_content[0]->post_content;
		$this->account_link 	= get_permalink( llms_get_page_id( 'myaccount' ) );

		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @return void
	 */
	function trigger( $user_id, $user_pass = '', $password_generated = false ) {

		if ( $user_id ) {

			$this->object 			    = new WP_User( $user_id );
			$this->user             	= get_user_meta( $user_id );
			$this->user_data			= get_userdata( $user_id );
			$this->user_firstname		= ($this->user['first_name'][0] != '' ?  $this->user['first_name'][0] : $this->user['nickname'][0]);
			$this->user_lastname		= ($this->user['last_name'][0] != '' ?  $this->user['last_name'][0] : '');
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
			'{site_url}' ,
			'{first_name}',
			'{last_name}',
			'{email_address}',
			'{current_date}');
		$this->replace = array( 
			$this->get_blogname(), 
			$this->user_login, 
			$this->account_link,
			$this->user_firstname,
			$this->user_lastname,
			$this->user_email,
			date('M d, Y', strtotime(current_time('mysql'))),
		);

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

return new LLMS_Email_Person_New();