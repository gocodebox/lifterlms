<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Initialize class and add actions
	 */
	function __construct() {

		$this->init();

		add_action( 'lifterlms_lesson_completed_certificate', array( $this, 'lesson_completed' ), 10, 3 );
		add_action( 'lifterlms_custom_certificate', array( $this, 'custom_certificate_earned' ), 10, 3 );

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
	 * [lesson_completed description]
	 *
	 * @param  int $person_id [ID of the current user]
	 * @param  int $cert_id  [ID of the Certificate template]
	 * @param  int $lesson_id [description]
	 *
	 * @return void
	 */
	function lesson_completed( $person_id, $cert_id, $lesson_id ) {

		if ( ! $person_id )
			return;

		$certificate = $this->certs['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $cert_id, $lesson_id );
	}

	/**
	 * Earn a custom certificate which is no associated with a specific lesson
	 * Calls tigger method passing arguments
	 *
	 * @param  int $person_id [ID of the current user]
	 * @param  int $certificate  [certificate template post ID]
	 * @param  int $engagement_id  [Engagment trigger post ID]
	 *
	 * @return [type]            [description]
	 */
	function custom_certificate_earned( $person_id, $certificate_id, $engagement_id ) {
		if ( ! $person_id )
			return;

		$certificate = $this->emails['LLMS_Certificate_User'];

		$certificate->trigger( $person_id, $certificate_id, $engagement_id );
	}

}
