<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Email Class
*
* Handles generating and sending the email
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Engagement {

	// // is the email enabled
	// var $enabled;

	// var $heading;

	// function __construct() {

	// 		// Default template base if not declared in child constructor
	// 		if ( is_null( $this->template_base ) ) {
	// 			$this->template_base = LLMS()->plugin_path() . '/templates/';
	// 		}

	// 		// Settings TODO Refoactor: theses can come from the email post now
	// 		$this->email_type     	= 'html';
	// 		$this->enabled   		= get_option( 'enabled' );

	// 		$this->find = array( '{blogname}', '{site_title}' );
	// 		$this->replace = array( $this->get_blogname(), $this->get_blogname() );
	// }

	// function is_enabled() {
	// 	$enabled = $this->enabled == "yes" ? true : false;
	// 	//TODO: Refactor this is not always true. This needs to be tied to the email post. 
	// 	return true;//apply_filters( 'lifterlms_email_enabled_' . $this->id, $enabled, $this->object );
	// }

	// function get_blogname() {
	// 	return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	// }

	// function get_content_type() {
	// 	return 'text/html';
	// }

	// function get_from_name() {
	// 	return wp_specialchars_decode( esc_html( get_option( 'lifterlms_email_from_name' ) ), ENT_QUOTES );
	// }

	// function get_from_address() {
	// 	return sanitize_email( get_option( 'lifterlms_email_from_address' ) );
	// }

	// function get_recipient() {
	// 	return apply_filters( 'lifterlms_email_recipient_' . $this->id, $this->recipient, $this->object );
	// }

	// function get_subject() {
	// 	return apply_filters( 'lifterlms_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
	// }

	// function get_headers() {
	// 	return apply_filters( 'lifterlms_email_headers', "Content-Type: " . $this->get_content_type() . "\r\n", $this->id, $this->object );
	// }
	// function format_string( $string ) {
	// 	return str_replace( $this->find, $this->replace, $string );
	// }

	// function get_heading() {
	// 	return apply_filters( 'lifterlms_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
	// }

	// function get_content() {

	// $this->sending = true;

	// $email_content = $this->get_content_html();

	// 	return wordwrap( $email_content, 70 );
	// }

	// function get_content_html() {}

	// function send( $to, $subject, $message, $headers ) {

	// 	add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
	// 	add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
	// 	add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	
	// 	$return = wp_mail( $to, $subject, $message, $headers );

	// 	remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
	// 	remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
	// 	remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		
	// 	return $return;
	// }

}

