<?php
/**
 * User Achievement class, inherits methods from LLMS_Achievement
 *
 * Generates achievements for users.
 *
 * @package LifterLMS/Classes/Achievements
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Achievement_User class
 *
 * @since 1.0.0
 * @since 3.17.4 Unknown.
 * @since 3.24.0 Unknown.
 * @since 3.30.3 Explicitly define undefined properties.
 * @deprecated 6.0.0 Class `LLMS_Achievement_User` is deprecated with no direct replacement.
 */
class LLMS_Achievement_User extends LLMS_Achievement {

	/**
	 * @var string|false
	 * @since 1.0.0
	 */
	public $account_link;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $recipient;

	/**
	 * partial path and file name of HTML template
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $template_html;

	/**
	 * user meta fields
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $user = array();

	/**
	 * @var WP_User|false
	 * @since 1.0.0
	 */
	public $user_data;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $user_email;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $user_firstname;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $user_lastname;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $user_login;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $user_pass;

	/**
	 * Alert when deprecated methods are used.
	 *
	 * This class as well as core classes extending it have been deprecated. All public and protected methods
	 * have been changed to private and will be made accessible through this magic method which also emits a
	 * deprecation warning.
	 *
	 * This public method has been intentionally marked as private to denote it's temporary lifespan. It will be
	 * removed alongside this class in the next major release.
	 *
	 * @since 6.0.0
	 *
	 * @access private
	 *
	 * @param string $name Name of the method being called.
	 * @param array  $args Arguments provided to the method.
	 * @return void
	 */
	public function __call( $name, $args ) {
		_deprecated_function( __CLASS__ . '::' . esc_html( $name ), '6.0.0' );
		if ( method_exists( $this, $name ) ) {
			$this->$name( ...$args );
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * Check if the user has already earned this achievement used to prevent duplicates
	 *
	 * @since 3.4.1
	 * @since 3.17.4 Unknown.
	 * @deprecated 6.0.0 `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
	 *
	 * @return boolean
	 */
	private function has_user_earned() {

		global $wpdb;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( pm.meta_id )
			FROM {$wpdb->postmeta} AS pm
			JOIN {$wpdb->prefix}lifterlms_user_postmeta AS upm ON pm.post_id = upm.meta_value
			WHERE pm.meta_key = '_llms_achievement_template'
			  AND pm.meta_value = %d
			  AND upm.meta_key = '_achievement_earned'
			  AND upm.user_id = %d
			  AND upm.post_id = %d
			  LIMIT 1
			;",
				array( $this->achievement_template_id, $this->userid, $this->lesson_id )
			)
		);

		/**
		 * @filter llms_achievement_has_user_earned
		 * Allow 3rd parties to override default dupcheck functionality for achievements
		 */
		return apply_filters( 'llms_achievement_has_user_earned', ( $count >= 1 ), $this );
	}

	/**
	 * Initializes all of the variables needed to create the achievement post.
	 *
	 * @since 1.0.0
	 * @since 3.24.0 Unknown.
	 * @deprecated 6.0.0 `LLMS_Achievement_User::init()` is deprecated with no replacement.
	 *
	 * @param int $id        Id of post.
	 * @param int $person_id Id of user.
	 * @param int $lesson_id Id of associated lesson.
	 * @return void
	 */
	private function init( $id, $person_id, $lesson_id ) {
		global $wpdb;

		$content = get_post( $id );
		$meta    = get_post_meta( $content->ID );

		$this->achievement_template_id = $id;
		$this->lesson_id               = $lesson_id;
		$this->title                   = $content->post_title;
		$this->achievement_title       = $meta['_llms_achievement_title'][0];
		$this->content                 = ( ! empty( $content->post_content ) ) ? $content->post_content : $meta['_llms_achievement_content'][0];
		$this->image                   = $meta['_llms_achievement_image'][0];
		$this->userid                  = $person_id;
		$this->user                    = get_user_meta( $person_id );
		$this->user_data               = get_userdata( $person_id );
		$this->user_firstname          = ( '' != $this->user['first_name'][0] ? $this->user['first_name'][0] : $this->user['nickname'][0] );
		$this->user_lastname           = ( '' != $this->user['last_name'][0] ? $this->user['last_name'][0] : '' );
		$this->user_email              = $this->user_data->data->user_email;
		$this->template_html           = 'achievements/template.php';
		$this->account_link            = get_permalink( llms_get_page_id( 'myaccount' ) );
	}

	/**
	 * Creates new instance of WP_User and calls parent method create
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
	 *
	 * @param int $user_id   ID of user.
	 * @param int $id        ID of post.
	 * @param int $lesson_id ID of associated lesson.
	 * @return void
	 */
	private function trigger( $user_id, $id, $lesson_id ) {

		$this->init( $id, $user_id, $lesson_id );

		// Only award achievement if the user hasn't already earned it.
		if ( $this->has_user_earned() ) {
			return;
		}

		if ( $user_id ) {

			$this->object     = new WP_User( $user_id );
			$this->user_login = stripslashes( $this->object->user_login );
			$this->user_email = stripslashes( $this->object->user_email );
			$this->recipient  = $this->user_email;

		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->create( $this->get_content() );
	}

	/**
	 * Gets post content and replaces merge fields with user meta-data
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
	 *
	 * @return string
	 */
	private function get_content_html() {

		$this->find    = array(
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
		llms_get_template(
			$this->template_html,
			array(
				'content' => $this->content,
				'title'   => $this->format_string( $this->title ),
				'image'   => $this->image,
			)
		);
		return ob_get_clean();
	}
}

return new LLMS_Achievement_User();
