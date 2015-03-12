<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Engagments Class
*
* Finds and triggers the appropriate engagement
*/
class LLMS_Engagements {

	/**
	 * protected instance of class
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 * @return object [Instance of engagements class]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Adds actions to events that trigger engagements
	 */
	public function __construct() {
		
	 	$this->init();

		add_action( 'lifterlms_lesson_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'lifterlms_section_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'lifterlms_course_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'user_register_notification', array( $this, 'llms_user_register' ), 10, 1 );
		//add_action( 'init', array( $this, 'llms_user_register' ), 10, 1 );
	}

	/**
	 * Include engagement types (excluding email)
	 * @return void
	 */
	public function init() {

		include( 'class.llms.certificates.php' );
		include( 'class.llms.achievements.php' );

	}

	/**
	 * Lesson completed engagements
	 * Triggers appropriate engagement when lesson is completed
	 * REFACTOR: lesson, section and course triggers this method. RENAME
	 * 
	 * @param  int $person_id [ID of the current user]
	 * @param  int $lesson_id [ID of the lesson, course or section]
	 * @return void
	 */
	public function lesson_completed( $person_id, $lesson_id ) {

		if ( ! $person_id )
			return;

		if ($hooks = get_post_meta( $lesson_id, '_llms_engagement_trigger' )) {

			foreach ( $hooks as $key => $value ) {
				
				$engagement_meta = get_post_meta($value);
				$engagement_id = $engagement_meta['_llms_engagement'][0];

				if ($engagement_meta['_llms_engagement_type'][0] == 'email') {
					do_action( 'lifterlms_lesson_completed_engagement', $person_id, $engagement_id);
				}

				elseif ($engagement_meta['_llms_engagement_type'][0] == 'certificate') {
					LLMS()->certificates();
					do_action( 'lifterlms_lesson_completed_certificate', $person_id, $engagement_id, $lesson_id);
				}
				elseif ($engagement_meta['_llms_engagement_type'][0] == 'achievement') {
					LLMS()->achievements();

					do_action( 'lifterlms_lesson_completed_achievement', $person_id, $engagement_id, $lesson_id);
				}
				else {
					do_action( 'lifterlms_external_engagement', $person_id, $engagement_id, $lesson_id);
				}
			}
		}
	}

	/**
	 * Get the engagement hooks
	 * @param  [type] $lesson_id [lesson, section or course id that triggered the engagment]
	 * @return array [array of all engagement post ids]
	 */
	public function get_engagement_hooks($lesson_id) {
		$engagement_ids = array();

		$args = array(
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'orderby'          => 'title',
			'post_type'        => 'llms_engagement',
			); 

		$all_posts = get_posts($args);

		if ($all_posts) :

			foreach ( $all_posts as $p  ) : 
				array_push($engagement_ids, $p->ID);
			endforeach;
		endif;

		return $engagement_ids;
	}

	/**
	 * new user registered engagement method
	 * Called when new user is registered
	 * Overridable by child classes
	 * 
	 * @param  object $user [Current user data]
	 * @return void
	 */
	public function llms_user_register($user) {

		if ( ! $user )
			return;

		$args = array(
			'posts_per_page'   => 100,
			'post_status'	   => 'publish',
			'orderby'          => 'title',
			'post_type'        => 'llms_engagement',
				'meta_query' => array(
					array(
					'key'       => '_llms_trigger_type',
					'compare'   => '=',
					'value'   => 'user_registration',
					)
				)
			); 

		$all_posts = get_posts($args);

		if ($all_posts) {

			foreach ( $all_posts as $key => $value ) {
				
				$engagement_meta = get_post_meta($value->ID);
				$achievement_id = $engagement_meta['_llms_engagement'][0];

				if ($engagement_meta['_llms_engagement_type'][0] == 'email') {

					do_action( 'lifterlms_custom_engagement', $user, $achievement_id, $value->ID );
				}

				elseif ($engagement_meta['_llms_engagement_type'][0] == 'certificate') {
					LLMS()->certificates();
					do_action( 'lifterlms_custom_certificate', $user, $achievement_id, $value->ID );
				}
				elseif ($engagement_meta['_llms_engagement_type'][0] == 'achievement') {
					LLMS()->achievements();

					do_action( 'lifterlms_custom_achievement', $user, $achievement_id, $value->ID );
				}
				else {
					do_action( 'lifterlms_external_engagement', $person_id, $engagement_id, $lesson_id);
				}
			}
		}
		
	}

}
