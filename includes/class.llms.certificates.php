<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Certificates Class
*
* Queries appropriate certificates
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Certificates {

	public $certs;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(); }
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Initialize class and add actions
	 */
	function __construct() {

		$this->init();

	}

	/**
	 * Initialize Class
	 * @return void
	 */
	function init() {

		include_once( 'class.llms.certificate.php' );

		$this->certs['LLMS_Certificate_User'] = include_once( 'certificates/class.llms.certificate.user.php' );
	}

	/**
	 * Award a certificate to a user
	 * Calls trigger method passing arguments
	 *
	 *
	 * @param  int $person_id        [ID of the current user]
	 * @param  int $achievement      [Achivement template post ID]
	 * @param  int $related_post_id  Post ID of the related engagment (eg lesson id)
	 *
	 * @return void
	 */
	function trigger_engagement( $person_id, $certificate_id, $related_post_id ) {
		$certificate = $this->certs['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $certificate_id, $related_post_id );
	}

}
