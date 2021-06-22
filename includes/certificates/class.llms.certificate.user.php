<?php
/**
 * Certificate
 *
 * @package LifterLMS/Classes/Certificates
 *
 * @since 1.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Certificate class
 *
 * Generates certificate post for user, triggered from engagement.
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Certificate_User extends LLMS_Certificate {

	/**
	 * @var string|false
	 * @since 1.0.0
	 */
	public $account_link;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public $email_content;

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
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * Check if the user has already earned this achievement
	 * used to prevent duplicates
	 *
	 * @return   boolean
	 * @since    3.4.1
	 * @version  3.17.4
	 */
	private function has_user_earned() {

		global $wpdb;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( pm.meta_id )
			FROM {$wpdb->postmeta} AS pm
			JOIN {$wpdb->prefix}lifterlms_user_postmeta AS upm ON pm.post_id = upm.meta_value
			WHERE pm.meta_key = '_llms_certificate_template'
			  AND pm.meta_value = %d
			  AND upm.meta_key = '_certificate_earned'
			  AND upm.user_id = %d
			  AND upm.post_id = %d
			  LIMIT 1
			;",
				array( $this->certificate_template_id, $this->userid, $this->lesson_id )
			)
		);

		/**
		 * @filter llms_certificate_has_user_earned
		 * Allow 3rd parties to override default dupcheck functionality for certificates
		 */
		return apply_filters( 'llms_certificate_has_user_earned', ( $count >= 1 ), $this );

	}

	/**
	 * Sets up data needed to generate certificate.
	 *
	 * @param    int $email_id   ID of Certificate
	 * @param    int $person_id  ID of the user receiving the certificate
	 * @param    int $lesson_id  ID of associated lesson
	 * @return   void
	 * @since    ??
	 * @version  3.24.0
	 */
	public function init( $email_id, $person_id, $lesson_id ) {
		global $wpdb;

		$email_content = get_post( $email_id );
		$email_meta    = get_post_meta( $email_content->ID );

		$this->certificate_template_id = $email_id;
		$this->lesson_id               = $lesson_id;
		$this->title                   = $email_content->post_title;
		$this->certificate_title       = $email_meta['_llms_certificate_title'][0];
		$this->content                 = $email_content->post_content;
		$this->image                   = $email_meta['_llms_certificate_image'][0];
		$this->userid                  = $person_id;
		$this->user                    = get_user_meta( $person_id );
		$this->user_data               = get_userdata( $person_id );
		$this->user_firstname          = ( '' != $this->user['first_name'][0] ? $this->user['first_name'][0] : $this->user['nickname'][0] );
		$this->user_lastname           = ( '' != $this->user['last_name'][0] ? $this->user['last_name'][0] : '' );
		$this->user_email              = $this->user_data->data->user_email;
		$this->template_html           = 'certificates/template.php';
		$this->email_content           = $email_content->post_content;
		$this->account_link            = get_permalink( llms_get_page_id( 'myaccount' ) );

		$this->user_login = $this->user_data->user_login;

	}

	/**
	 * [trigger description]
	 *
	 * @param  int $user_id   [ID of the user receiving the certificate]
	 * @param  int $email_id  [ID of the certificate]
	 * @param  int $lesson_id [ID of the associated lesson]
	 *
	 * @return void
	 */
	public function trigger( $user_id, $email_id, $lesson_id ) {

		$this->init( $email_id, $user_id, $lesson_id );

		// Only award cert if the user hasn't already earned it.
		if ( $this->has_user_earned() ) {
			return;
		}

		if ( $user_id ) {
			$this->object     = new WP_User( $user_id );
			$this->user_email = stripslashes( $this->object->user_email );
			$this->recipient  = $this->user_email;

		}

		if ( ! $this->is_enabled() ) {
			return; }

		$this->create( $this->get_content() );
	}

	/**
	 * get_content_html function.
	 *
	 * @since 1.0.0
	 * @since 3.17.4 Unknown.
	 * @since 5.0.0 Merge the [llms-user] (and others) shortcode.
	 *
	 * @return string
	 */
	public function get_content_html() {

		add_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );

		$codes = apply_filters(
			'llms_certificate_merge_codes',
			array(
				'{site_title}'    => $this->get_blogname(),
				'{user_login}'    => $this->user_login,
				'{site_url}'      => $this->account_link,
				'{first_name}'    => $this->user_firstname,
				'{last_name}'     => $this->user_lastname,
				'{email_address}' => $this->user_email,
				'{student_id}'    => $this->userid,
				'{current_date}'  => date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ),
			),
			$this
		);

		$this->find    = array_keys( $codes );
		$this->replace = array_values( $codes );

		$content = $this->format_string( $this->content );

		// In certain circumstances shortcodes won't be registered yet.
		LLMS_Shortcodes::init();

		ob_start();
		llms_get_template(
			$this->template_html,
			array(
				'email_message' => do_shortcode( $content ),
				'title'         => $this->title,
				'image'         => $this->image,
			)
		);

		remove_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );

		return ob_get_clean();

	}

	/**
	 * Set the user ID used by [llms-user] to the user earning the certificate.
	 *
	 * @since 5.0.0
	 *
	 * @param int $uid WP_User ID of the current user.
	 * @return int
	 */
	public function set_shortcode_user( $uid ) {
		return $this->userid;
	}

}

return new LLMS_Certificate_User();
