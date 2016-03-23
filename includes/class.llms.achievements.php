<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Achievements Base Class
*
* base class for managing achievements
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Achievements {

	public $achievements;

	public $content;

	private $_from_address;

	private $_from_name;

	private $_content_type;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(); }
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->init();

	}

	/**
	 * Includes achivement class
	 * @return void
	 */
	public function init() {
		include_once( 'class.llms.achievement.php' );

		$this->achievements['LLMS_Achievement_User']  = include_once( 'achievements/class.llms.achievement.user.php' );

	}


	/**
	 * Award an achievement to a user
	 * Calls trigger method passing arguments
	 *
	 * @param  int $person_id        [ID of the current user]
	 * @param  int $achievement      [Achivement template post ID]
	 * @param  int $related_post_id  Post ID of the related engagment (eg lesson id)
	 *
	 * @return void
	 */
	public function trigger_engagement( $person_id, $achievement_id, $related_post_id ) {
		$achievement = $this->achievements['LLMS_Achievement_User'];
		$achievement->trigger( $person_id, $achievement_id, $related_post_id );
	}

}



