<?php
defined( 'ABSPATH' ) || exit;

/**
 * Certificates
 * @see      LLMS()->certificates()
 * @since    1.0.0
 * @version  [version]
 */
class LLMS_Certificates {

	/**
	 * Instance
	 * @var  null
	 */
	protected static $_instance = null;

	/**
	 * Instance singleton
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize Class
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function init() {
		include_once( 'class.llms.certificate.php' );
		$this->certs['LLMS_Certificate_User'] = include_once( 'certificates/class.llms.certificate.user.php' );
	}

	/**
	 * Award a certificate to a user
	 * Calls trigger method passing arguments
	 * @param    int   $person_id        [ID of the current user]
	 * @param    int   $achievement      [Achivement template post ID]
	 * @param    int   $related_post_id  Post ID of the related engagment (eg lesson id)
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function trigger_engagement( $person_id, $certificate_id, $related_post_id ) {
		$certificate = $this->certs['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $certificate_id, $related_post_id );
	}

}
