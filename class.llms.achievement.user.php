<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
/**
* User Achievemnet class, inherits methods from LLMS_Achievment
*
* Generates achievements for users.
*/
class LLMS_Achievement_User extends LLMS_Achievement {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * Initializes all of the variables needed to create the achievement post.
	 *
	 * @param  int $id [id of post]
	 * @param  int $person_id [id of user]
	 * @param  int $lesson_id [id of associated lesson]
	 *
	 * @return void
	 */
	public function init( $id, $person_id, $lesson_id ) {
		global $wpdb;

			$content = get_post( $id );
			$meta = get_post_meta( $content->ID );

			$this->achievement_template_id	= $id;
			$this->lesson_id    			= $lesson_id;
		$this->title 					= $content->post_title;
		$this->achievement_title 		= $meta['_llms_achievement_title'][0];
		$this->content 					= ( ! empty( $content->post_content ) ) ? $content->post_content : $meta['_llms_achievement_content'][0];
		$this->image 					= $meta['_llms_achievement_image'][0];
		$this->userid           		= $person_id;
		$this->user             		= get_user_meta( $person_id );
		$this->user_data				= get_userdata( $person_id );
		$this->user_firstname			= ($this->user['first_name'][0] != '' ?  $this->user['first_name'][0] : $this->user['nickname'][0]);
		$this->user_lastname			= ($this->user['last_name'][0] != '' ?  $this->user['last_name'][0] : '');
		$this->user_email				= $this->user_data->data->user_email;
		$this->template_html 			= 'achievements/template.php';
		$this->account_link 			= get_permalink( llms_get_page_id( 'myaccount' ) );

	}

	/**
	 * Creates new instance of WP_User and calls parent method create
	 *
	 * @param  int $person_id [id of user]
	 * @param  int $id [id of post]
	 * @param  int $lesson_id [id of associated lesson]
	 *
	 * @return void
	 */
	public function trigger( $user_id, $id, $lesson_id ) {
		$this->init( $id, $user_id, $lesson_id );

		if ( $user_id ) {

			$this->object 		      = new WP_User( $user_id );
			$this->user_login         = stripslashes( $this->object->user_login );
			$this->user_email         = stripslashes( $this->object->user_email );
			$this->recipient          = $this->user_email;

		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->create( $this->get_content() );
	}

	/**
	 * Gets post content and replaces merge fields with user meta-data
	 *
	 * @return mixed [returns formatted post content]
	 */
	public function get_content_html() {

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
			date( 'M d, Y', strtotime( current_time( 'mysql' ) ) ),
		);

		$content = $this->format_string( $this->content );

		ob_start();
		llms_get_template( $this->template_html, array(
			'content'        => $this->content,
			'title'			 => $this->format_string( $this->title ),
			'image'			 => $this->image,
		) );
		return ob_get_clean();
	}

}

return new LLMS_Achievement_User();
