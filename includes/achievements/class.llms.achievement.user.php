<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LLMS_Achievement_User' ) ) :

class LLMS_Achievement_User extends LLMS_Achievement {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 */
	function __construct() {
		

		parent::__construct();
	}

	public function init($email_id, $person_id, $lesson_id) {
		global $wpdb;
LLMS_log('email id=' . $email_id);
LLMS_log('person id=' . $person_id);
LLMS_log('lesson id=' . $lesson_id);


 		$email_content = get_post($email_id);//$wpdb->get_results($querystr, OBJECT);
 		//LLMS_log($email_content);
 		$email_meta = get_post_meta( $email_content->ID );
 		//LLMS_log($email_meta);

 		$this->achievement_template_id	= $email_id;
 		LLMS_log('achievement_template_id=' . $this->achievement_template_id);
 		$this->lesson_id    			= $lesson_id;
		$this->title 					= $email_content->post_title; 
		$this->achievement_title 		= $email_meta['_llms_achievement_title'][0];
		$this->content 					= $email_content->post_content;
		$this->image 					= $email_meta['_llms_achievement_image'][0];
		$this->userid           		= $person_id;
		$this->user             		= get_user_meta( $person_id );
		$this->user_data				= get_userdata( $person_id );

		$this->user_firstname			= ($this->user['first_name'][0] != '' ?  $this->user['first_name'][0] : $this->user['nickname'][0]);
		$this->user_lastname			= ($this->user['last_name'][0] != '' ?  $this->user['last_name'][0] : '');
		$this->user_email				= $this->user_data->data->user_email;

		LLMS_log('get_user_meta');
		LLMS_log($this->user);
		LLMS_log('get_user_data');
		LLMS_log($this->user_data);







		//$this->id 				= '';//$email_meta['_email_subject'][0];
		$this->description		= __( 'Person new account emails are sent when a person signs up via the checkout or My Account page.', 'lifterlms' );

		$this->template_html 	= 'achievements/template.php';

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
	function trigger( $user_id, $email_id, $lesson_id ) {
		$this->init($email_id, $user_id, $lesson_id);

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

		$this->create( $this->get_content() );
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

		$content = $this->format_string($this->content);

		ob_start();
		llms_get_template( $this->template_html, array(
			// 'email_heading'      => $this->get_heading(),
			// 'user_login'         => $this->user_login,
			// 'user_pass'          => $this->user_pass,
			// 'blogname'           => $this->get_blogname(),
			// 'password_generated' => $this->password_generated,
			'email_message' 	 => $content,
			'title'				 => $this->title,
			'image'				 => $this->image,
		) );
		return ob_get_clean();
	}

}

endif;

return new LLMS_Achievement_User();