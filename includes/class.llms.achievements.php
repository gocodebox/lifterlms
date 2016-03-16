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
	function __construct() {
		$this->init();

		add_action( 'lifterlms_lesson_completed_achievement', array( $this, 'lesson_completed' ), 10, 3 );
		add_action( 'lifterlms_custom_achievement', array( $this, 'custom_achievement_earned' ), 10, 3 );

	}

	/**
	 * Includes achivement class
	 * @return void
	 */
	function init() {
		include_once( 'class.llms.achievement.php' );

		$this->achievements['LLMS_Achievement_User'] = include_once( 'achievements/class.llms.achievement.user.php' );

	}

	/**
	 * Lesson completed trigger for generating achievements
	 * Calls tigger method passing arguments
	 *
	 * @param  int $person_id [ID of the current user]
	 * @param  int $id  [Achivement template post ID]
	 * @param  int $lesson_id [Associated lesson with achievement]
	 *
	 * @return [type]            [description]
	 */
	function lesson_completed( $person_id, $id, $lesson_id ) {
		if ( ! $person_id ) {
			return; }

		$achievement = $this->achievements['LLMS_Achievement_User'];

		$achievement->trigger( $person_id, $id, $lesson_id );
	}

	/**
	 * Earn a custom achievement which is no associated with a specific lesson
	 * Calls tigger method passing arguments
	 *
	 * @param  int $person_id [ID of the current user]
	 * @param  int $achievement  [Achivement template post ID]
	 * @param  int $engagement_id  [Engagment trigger post ID]
	 *
	 * @return [type]            [description]
	 */
	function custom_achievement_earned( $person_id, $achievement_id, $engagement_id ) {
		if ( ! $person_id ) {
			return; }

		$achievement = $this->achievements['LLMS_Achievement_User'];

		$achievement->trigger( $person_id, $achievement_id, $engagement_id );
	}

}



