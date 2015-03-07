<?php

/**
* Shortcode base class
*
* Shortcode logic
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Shortcodes {

	/**
	* init shortcodes array
	*
	* @return void
	*/
	public static function init() {

		$shortcodes = array(
			'lifterlms_my_account' => __CLASS__ . '::my_account',
			'lifterlms_my_achievements' => __CLASS__ . '::my_achievements',
			'lifterlms_checkout' => __CLASS__ . '::checkout',
			'lifterlms_courses' => __CLASS__ . '::courses', // added here so that we can deprecate the non-prefixed "courses" (maybe)
			'courses' => __CLASS__ . '::courses',
			'lifterlms_course_progess' => __CLASS__ . '::course_progress',
			'lifterlms_course_title' => __CLASS__ . '::course_title',
			'lifterlms_user_statistics' => __CLASS__ . '::user_statistics'
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );

		}

	}

	/**
	 * Output onscreen messages when shortcodes encounter errors
	 * @param string / $message / warning notice to display
	 * @return html
	 */
	private static function _warn( $message = 'There was an error outputting your shortcode!' ) {

		return '<p style="color: red;">' . $message . '</p>';

	}


	/**
	* Creates a wrapper for shortcode.
	*
	* @return void
	*/
	public static function shortcode_wrapper(

		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'lifterlms',
			'before' => null,
			'after'  => null
	) ) {

		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	* My account shortcode.
	*
	* Used for displaying account.
	*
	* @return self::shortcode_wrapper
	*/
	public static function my_account( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_My_Account', 'output' ), $atts );

	}


	public static function my_achievements( $atts ) {

		extract( shortcode_atts( array(

			'count' => null,
			'user_id' => 0

		), $atts, 'lifterlms_my_achievements' ) );

		ob_start();

		include( llms_get_template_part_contents( 'myaccount/my', 'achievements' ) );

		$html = ob_get_clean();

		return $html;
	}



	/**
	* Checkout shortcode.
	*
	* Used for displaying checkout form
	*
	* @return self::shortcode_wrapper
	*/
	public static function checkout( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_Checkout', 'output' ), $atts );

	}


	/**
	 * Course Progress Bar Shortcode
	 * @param  [type] $atts [description]
	 * @return [type]       [description]
	 */
	public static function course_progress( $atts ) {

		if ( is_course() ) {
			$course_id = get_the_ID();
		} elseif( is_lesson() ) {
			$lesson = new LLMS_Lesson( get_the_ID() );
			$course_id = $lesson->get_parent_course();
		} else {
			return self::_warn( 'shortcode [ lifter_lms_course_progress_bar ] can only be displayed on course or lesson posts!' );
		}

		$course = new LLMS_Course ( $course_id );

		$course_progress = $course->get_percent_complete();

		return lifterlms_course_progress_bar( $course_progress, false, false, false );
	}

	/**
	 * Course Progress Bar Shortcode
	 * @param  [type] $atts [description]
	 * @return [type]       [description]
	 */
	public static function course_title( $atts ) {
		if ( is_lesson() ) {
			$lesson = new LLMS_Lesson( get_the_ID() );
			$course_id = $lesson->get_parent_course();
		}
		else {
			return self::_warn( 'shortcode [ lifterlms_course_title ] can only be displayed on lesson posts!' );
		}
		return get_the_title( $course_id );
	}



	/**
	* courses shortcode
	*
	* Used for courses [courses]
	*
	* @return array
	*/
	public static function courses( $atts ) {

	    ob_start();

	    $query = new WP_Query( array(
	        'post_type' => 'course',
	        'post_status' => 'publish',
	        'posts_per_page' => isset($atts['per_page']) ? $atts['per_page'] : -1,
	        'order' => isset($atts['order']) ? $atts['order'] : 'ASC',
	        'orderby' => isset($atts['orderby']) ? $atts['orderby'] : 'title',
	    ) );

	    if ( $query->have_posts() ) {

	       lifterlms_course_loop_start();

			while ( $query->have_posts() ) : $query->the_post();

				llms_get_template_part( 'content', 'course' );

			endwhile;

			lifterlms_course_loop_end();

	    	$courses = ob_get_clean();
	    	wp_reset_postdata();
	   		return $courses;
	    }

	}


	/**
	 * Output user statstics related to courses enrolled, completed, etc...
	 * @param  [array] $atts / array of user input attributes
	 * @return string / html content
	 */
	public static function user_statistics( $atts ) {
		extract(shortcode_atts(array(
			'type' => 'course', // course, lesson, section
			'stat' => 'completed' // completed, enrolled
		),$atts));

		// setup the meta key to search on
		switch($stat) {
			case 'completed':
				$key = '_is_complete';
				$val = false;
			break;

			case 'enrolled':
				$key = '_status';
				$val = 'Enrolled';
			break;
		}

		// get user id of logged in user
		$uid = wp_get_current_user()->ID;

		// init person class
		$person = new LLMS_Person();
		// get results
		$results = $person->get_user_postmetas_by_key($uid,$key);

		// pp_dump($results,$uid,$key);

		if($results) {
			// unset all items that are not courses
			foreach($results as $key=>$obj) {
				if(get_post_type($obj->post_id) != $type) {
					unset($results[$key]);
				}
			}
		}

		// filter by value if set
		if(is_array($results) && $val)  {
			foreach($results as $key=>$obj) {
				// remove from the results array if $val doesn't match
				if($obj->meta_value != $val) {
					unset($results[$key]);
				}
			}
		}

		$count = (is_array($results)) ? count($results) : 0;

		return $count . ' ' . _n( $type, $type.'s', $count, 'lifterlms' );
	}
}

