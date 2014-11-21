<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Frontend scripts class
*
* Initializes front end scripts
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Engagements {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	 public function __construct() {
		
	 	$this->init();

		add_action( 'lifterlms_lesson_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'lifterlms_section_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'lifterlms_course_completed_notification', array( $this, 'lesson_completed' ), 10, 2 );
		add_action( 'user_register_notification', array( $this, 'llms_user_register' ), 10, 1 );
	}

	public function init() {

		include( 'class.llms.certificates.php' );
		include( 'class.llms.achievements.php' );

	}

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
			}
		}
	}

	public function course_completed( $person_id, $lesson_id ) {

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

			}

		}

	}

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

	public function llms_user_register($user) {
		LLMS_log('user registered engagement triggered');
		do_action('lifterlms_user_registered', $user);

	}

}



