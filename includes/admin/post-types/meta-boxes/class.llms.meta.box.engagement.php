<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Engagement extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;

		/**
		 * Array of the possible types of engagements
		 * @var array
		 */
		$engagement_types = array(
			array(
				'key' 	=> 'email',
				'title' => 'Send Email',
			),
			array(
				'key' 	=> 'achievement',
				'title' => 'Give Achievement',
			),
			array(
				'key' 	=> 'certificate',
				'title' => 'Give Certificate',
			),
		);

		/**
		 * Array of the possible event triggers
		 * @var array
		 */
		$event_triggers  = array(
			array(
				'key' 	=> 'lesson_completed',
				'title' => 'Lesson Completed',
			),
			array(
				'key' 	=> 'section_completed',
				'title' => 'Section Completed',
			),
			array(
				'key' 	=> 'course_completed',
				'title' => 'Course Completed',
			),
			array(
				'key' 	=> 'user_registration',
				'title' => 'New User Registration',
			),
			array(
				'key' 	=> 'course_purchased',
				'title' => 'Course Purchased',
			),
			array(
				'key' 	=> 'membership_purchased',
				'title' => 'Membership Purchased',
			),
			array(
				'key' 	=> 'days_since_login',
				'title' => 'Days since user last logged in',
			),
			array(
				'key' 	=> 'course_track_completed',
				'title' => 'Course Track Completed',
			),
		);

		$meta_fields_engagement = array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'label' 	=> 'Engagement Type',
						'desc' 		=> 'Select the type of engagement you want to create.',
						'id' 		=> self::$prefix . 'llms_engagement_type',
						'type'  	=> 'select',
						'section' 	=> 'engagement_meta_box',
						'class' 	=> 'llms-chosen-select',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> $engagement_types,
					),
					array(
						'label'  	=> 'Engagement Delay (in days)',
						'desc'  	=> 'If no value or 0 is entered the engagement will trigger immediately.',
						'id'    	=> self::$prefix . 'llms_engagement_delay',
						'type'  	=> 'text',
						'section' 	=> 'engagement_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'label'  	=> 'Event Trigger',
						'desc'  	=> 'Select the event to trigger the engagement on.',
						'id'    	=> self::$prefix . 'llms_trigger_type',
						'type'  	=> 'select',
						'section' 	=> 'engagement_meta_box',
						'class' 	=> 'llms-chosen-select',
						'desc_class' => 'd-all',
						'group' 	=> 'event-trigger-top',
						'value' 	=> $event_triggers,
					),
					array(
						'label'  	=> 'Event Trigger',
						'desc'  	=> 'Select the event to trigger the engagement on.',
						'id'    	=> self::$prefix . 'llms_trigger_type',
						'type'  	=> 'select',
						'section' 	=> 'engagement_meta_box',
						'class' 	=> 'llms-chosen-select',
						'desc_class' => 'd-all',
						'group' 	=> 'bottom event-trigger-bottom',
						'value' 	=> $event_triggers,
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_engagement' )) {
			//Add Fields to the achievement Meta Box
			$meta_fields_engagement = apply_filters( 'llms_meta_fields_engagement', $meta_fields_engagement );
		}

		return $meta_fields_engagement;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

	}

}
