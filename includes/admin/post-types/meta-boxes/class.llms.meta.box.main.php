<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Main extends LLMS_Admin_Metabox{

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

		//setup course select options
		$course_options = array();
		$course_posts = LLMS_Post_Handler::get_posts( 'course' );
		foreach ( $course_posts as $c_post ) {
			if ( $c_post->ID != $post->ID ) {
				$course_options[] = array(
					'key' 	=> $c_post->ID,
					'title' => $c_post->post_title,
				);
			}
		}

		$course_tracks_options = get_terms( 'course_track', 'hide_empty=0' );
		$course_tracks = array();
		foreach ( (array) $course_tracks_options as $term ) {
			$course_tracks[] = array(
				'key' 	=> $term->term_id,
				'title' => $term->name,
			);
		}

		//setup course difficulty select options
		$difficulty_terms = get_terms( 'course_difficulty', 'hide_empty=0' );
		$difficulty_options = array();
		foreach ( $difficulty_terms as $term ) {
			$difficulty_options[] = array(
				'key' 	=> $term->slug,
				'title' => $term->name,
			);
		}

		//billing period options
		////needs to move to paypal class
		$billing_periods = array(
			array(
				'key' 	=> 'day',
				'title' => 'Day',
			),
			array(
				'key' 	=> 'week',
				'title' => 'Week',
			),
			array(
				'key' 	=> 'month',
				'title' => 'Month',
			),
			array(
				'key' 	=> 'year',
				'title' => 'Year',
			),
		);

		$meta_fields_course_main = array(
			array(
				'title' 	=> 'Description',
				'fields' 	=> array(
					array(
						'type'		=> 'post-content',
						'label'		=> 'Enrolled user and non-enrolled visitor description',
						'desc' 		=> 'This content will be displayed to enrolled users. If the non-enrolled users description
							field is left blank the content will be displayed to both enrolled users and non-logged / restricted
							visitors.',
						'id' 		=> '',
						'class' 	=> '',
						'value' 	=> '',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'type'		=> 'post-excerpt',
						'label'		=> 'Restricted Access Description',
						'desc' 		=> 'Enter content in this field if you would like visitors that
							are not enrolled or are restricted to view different content from
							enrolled users. Visitors who are not enrolled in the course
							or are restricted from the course will see this description if it contains content.',
						'id' 		=> '',
						'class' 	=> '',
						'value' 	=> '',
						'desc_class' => '',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'Course Length',
						'desc' 		=> 'Enter a description of the estimated length. IE: 3 days',
						'id' 		=> self::$prefix . 'lesson_length',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> 'top',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Course Difficulty Category',
						'desc' 		=> 'Choose a course difficulty level from the difficulty categories.',
						'id' 		=> self::$prefix . 'post_course_difficulty',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $difficulty_options,
						'desc_class' => 'd-all',
						'group' 	=> 'bottom',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Video Embed Url',
						'desc' 		=> 'Paste the url for a Wistia, Vimeo or Youtube video.',
						'id' 		=> self::$prefix . 'video_embed',
						'class' 	=> 'code input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Audio Embed Url',
						'desc' 		=> 'Paste the url for an externally hosted audio file.',
						'id' 		=> self::$prefix . 'audio_embed',
						'class' 	=> 'code input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Restrictions',
				'fields' 	=> array(
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Enable Prerequisite',
						'desc' 		=> 'Enable to choose a prerequisite course or course track',
						'id' 		=> self::$prefix . 'has_prerequisite',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> 'llms-prereq-top',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Choose Prerequisite Course',
						'desc' 		=> 'Select a prerequisite course',
						'id' 		=> self::$prefix . 'prerequisite',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $course_options,
						'desc_class' => 'd-all',
						'group' 	=> 'bottom llms-prereq-bottom no-border',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Choose Prerequisite Course Track',
						'desc' 		=> 'Select the prerequisite course track',
						'id' 		=> self::$prefix . 'prerequisite_track',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $course_tracks,
						'desc_class' => 'd-all',
						'group' 	=> 'bottom llms-prereq-bottom',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Course Capacity',
						'desc' 		=> 'Limit the number of users that can enroll in this course. Leave empty to allow unlimited students.',
						'id' 		=> self::$prefix . 'lesson_max_user',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Course Start Date',
						'desc' 		=> 'Enter a date the course becomes available.',
						'id' 		=> self::$prefix . 'course_dates_from',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Course End Date',
						'desc' 		=> 'Enter a date the course ends.',
						'id' 		=> self::$prefix . 'course_dates_to',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Price Single',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'SKU',
						'desc' 		=> 'Enter a SKU for your course.',
						'id' 		=> self::$prefix . 'sku',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Display Custom Text for Price',
						'desc' 		=> 'Enable this to display custom text as the single price on the course page.',
						'id' 		=> self::$prefix . 'is_custom_single_price',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> 'bottom llms-custom-single-price-top',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Custom Single Price Text',
						'desc' 		=> 'Enter custom text to display on the course page. IE: Free!.',
						'id' 		=> self::$prefix . 'custom_single_price_html',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> 'bottom llms-custom-single-price-bottom',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Single Payment Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a price to offer your course for a one time purchase.',
						'id' 		=> self::$prefix . 'regular_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Course is on sale',
						'desc' 		=> 'Enable single payment sale for this course.',
						'id' 		=> self::$prefix . 'on_sale',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Sale Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a sale price for the course.',
						'id' 		=> self::$prefix . 'sale_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price Start Date',
						'desc' 		=> 'Enter the date your sale will begin.',
						'id' 		=> self::$prefix . 'sale_price_dates_from',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price End Date',
						'desc' 		=> 'Enter the date your sale will end.',
						'id' 		=> self::$prefix . 'sale_price_dates_to',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Price Recurring',
				'fields' 	=> array(
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Enable Recurring Payment',
						'desc' 		=> 'Enable recurring payment options.',
						'id' 		=> self::$prefix . 'llms_recurring_enabled',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'Recurring Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the amount you will bill at set intervals.',
						'id' 		=> self::$prefix . 'llms_subscription_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'number',
						'label'		=> 'First Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the payment amount you will charge on product purchase. This can be 0 to give users a free trial period.',
						'id' 		=> self::$prefix . 'llms_subscription_first_payment',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Billing Period',
						'desc' 		=> 'Combine billing period and billing frequency set billing interval. IE: Billing period =  week and frequency 2 will bill every 2 weeks.',
						'id' 		=> self::$prefix . 'llms_billing_period',
						'class' 	=> 'input-full',
						'value' 	=> $billing_periods,
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Frequency',
						'desc' 		=> 'Use with billing period to set billing interval',
						'id' 		=> self::$prefix . 'llms_billing_freq',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Cycles',
						'desc' 		=> 'Enter 0 to charge indefinitely. IE: 12 would bill for 12 months.',
						'id' 		=> self::$prefix . 'llms_billing_cycle',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
				),
			),
			array(
				'title' 	=> 'Students',
				'fields' 	=> array(
					array(
						'type'		=> 'select',
						'label'		=> 'Add Student',
						'desc'		=> 'Add a user to the course.',
						'id'		=> self::$prefix . 'add_new_user',
						'class'		=> 'add-student-select',
						'value' 	=> array(),
						'desc_class' => 'd-all',
						'group' 	=> '',
						'multi'		=> true,
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'add_student_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Add Student',
						'desc_class' => '',
						'group' 	=> '',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Remove Student',
						'desc'		=> 'Remove a user from the course.',
						'id'		=> self::$prefix . 'remove_student',
						'class' 	=> 'remove-student-select',
						'value' 	=> array(),
						'desc_class' => 'd-all',
						'group' 	=> '',
						'multi'		=> true,
					),
					array(
						'type'		=> 'button',
						'label'		=> '',
						'desc' 		=> '',
						'id' 		=> self::$prefix . 'remove_student_submit',
						'class' 	=> 'llms-button-primary',
						'value' 	=> 'Remove Student',
						'desc_class' => '',
						'group' 	=> '',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_course_main' )) {
			//Add Fields to the course main Meta Box
			$meta_fields_course_main = apply_filters( 'llms_meta_fields_course_main', $meta_fields_course_main );
		}

		return $meta_fields_course_main;
	}

}
