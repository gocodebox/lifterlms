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
			'lifterlms_course_info' => __CLASS__ . '::course_info',
			'lifterlms_courses' => __CLASS__ . '::courses', // added here so that we can deprecate the non-prefixed "courses" (maybe)
				'courses' => __CLASS__ . '::courses', // should be deprecated at some point
			'lifterlms_course_progress' => __CLASS__ . '::course_progress',
			'lifterlms_course_title' => __CLASS__ . '::course_title',
			'lifterlms_user_statistics' => __CLASS__ . '::user_statistics',
			'lifterlms_registration' => __CLASS__ . '::registration',
			'lifterlms_regiration' => __CLASS__ . '::registration',
			'lifterlms_course_outline' => __CLASS__ . '::course_outline',
			'lifterlms_hide_content' => __CLASS__ . '::hide_content',
			'lifterlms_related_courses' => __CLASS__ . '::related_courses',
			'lifterlms_login' => __CLASS__ . '::login',
			'lifterlms_memberships' => __CLASS__ . '::memberships',
			'lifterlms_membership_link' => __CLASS__ . '::membership_link',

		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );

		}

	}

	/**
	 * Allows shortcodes to enqueue a script by handle
	 * Ensures the handle is registered and that it hasn't already been enqueued
	 * @param    [type]     $handle  script handle used to register the script
	 *                               the script should be registered in `LLMS_Frontend_Assets`
	 * @return   void
	 * @since    3.0.2
	 * @version  3.0.2
	 */
	private static function enqueue_script( $handle ) {

		if ( wp_script_is( $handle, 'registered' ) && ! wp_script_is( $handle, 'enqueued' ) ) {

			wp_enqueue_script( $handle );

		}

	}

	/**
	 * Retrieve the course ID from within a course, lesson, or quiz
	 * @return   int
	 * @since    2.7.9
	 * @version  2.7.9
	 */
	private static function get_course_id() {
		if ( is_course() ) {
			return get_the_ID();
		} elseif ( is_lesson() ) {
			$lesson = new LLMS_Lesson( get_the_ID() );
			return $lesson->get_parent_course();
		} elseif ( is_quiz() ) {
			$quiz = new LLMS_Quiz( get_the_ID() );
			$lesson = new LLMS_Lesson( $quiz->assoc_lesson );
			return $lesson->get_parent_course();
		} else {
			return 0;
		}
	}

	/**
	* Creates a wrapper for shortcode.
	*
	* @return void
	*/
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'lifterlms',
			'before' => null,
			'after'  => null,
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
	 * Add a login form
	 * @param    array     $atts  shortcode attributes
	 * @return   string
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	public static function login( $atts ) {

		extract( shortcode_atts( array(
			'layout' => 'columns',
			'redirect' => get_permalink(),
		), $atts, 'lifterlms_login' ) );

		ob_start();
		llms_print_notices();
		llms_get_template( 'global/form-login.php', array(
			'layout' => $layout,
			'redirect' => $redirect,
		) );
		return ob_get_clean();

	}

	/**
	 * My account shortcode.
	 *
	 * Used for displaying account.
	 *
	 * @return self::shortcode_wrappers
	 */
	public static function my_account( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_My_Account', 'output' ), $atts );

	}

	/**
	 * Registration page shortcode
	 * Used to seperate registration from login
	 * @param  [atts] $atts [no atts are allowed]
	 * @return [html]       [registration template html]
	 */
	public static function registration( $atts ) {

		ob_start();

		include( llms_get_template_part_contents( 'global/form', 'registration' ) );

		$html = ob_get_clean();

		return $html;

	}

	/**
	 * Output an achor link for a membership
	 * @param    array     $atts  shortcode atts
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function membership_link( $atts ) {

		extract( shortcode_atts( array(
			'id' => get_the_ID(),
		), $atts, 'lifterlms_membership_link' ) );

		$text = apply_filters( 'lifterlms_membership_link_text', get_the_title( $id ), $id );

		return apply_filters( 'lifterlms_membership_link_shortcode', '<a href="' . get_permalink( $id ) . '">' . $text . '</a>', $atts );

	}

	/**
	* Memberships Shortcode
	* Used for shortcode [lifterlms_memberships]
	* @param array $atts   associative array of shortcode attributes
	* @return string
	*
	* @since    1.4.4
	* @version  3.0.2
	*/
	public static function memberships( $atts ) {

		// enqueue match height so the loop isn't all messed up visually
		self::enqueue_script( 'llms-jquery-matchheight' );

		if ( isset( $atts['category'] ) ) {
			$tax = array(
				array(
					'taxonomy' => 'membership_cat',
					'field' => 'slug',
					'terms' => $atts['category'],
				),
			);
		}

		$args = array(
			'paged' => get_query_var( 'paged' ),
			'post_type' => 'llms_membership',
			'post_status' => 'publish',
			'posts_per_page' => isset( $atts['posts_per_page'] ) ? $atts['posts_per_page'] : -1,
			'order' => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
			'orderby' => isset( $atts['orderby'] ) ? $atts['orderby'] : 'title',
			'tax_query' => isset( $tax ) ? $tax : '',
		);

		if ( isset( $atts['id'] ) ) {

			$args['p'] = $atts['id'];

		}

		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) :

			/**
			 * lifterlms_before_loop hook
			 * @hooked lifterlms_loop_start - 10
			 */
			do_action( 'lifterlms_before_loop' );

			while ( $query->have_posts() ) : $query->the_post();

				llms_get_template_part( 'loop/content', get_post_type() );

			endwhile;

			/**
			 * lifterlms_before_loop hook
			 * @hooked lifterlms_loop_end - 10
			 */
			do_action( 'lifterlms_after_loop' );

			echo '<nav class="llms-pagination">';
			echo paginate_links( array(
				'base'         => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
				'format'       => '?page=%#%',
				'total'        => $query->max_num_pages,
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'prev_next'    => true,
				'prev_text'    => '«' . __( 'Previous', 'lifterlms' ),
				'next_text'    => __( 'Next', 'lifterlms' ) . '»',
				'type'         => 'list',
			) );
			echo '</nav>';

		else :

			llms_get_template( 'loop/none-found.php' );

		endif;

		wp_reset_postdata();

		return ob_get_clean();

	}




	public static function my_achievements( $atts ) {

		extract( shortcode_atts( array(
			'count' => null,
			'user_id' => 0,
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
	 * Display content only to user enrolled in a specific course, lesson, or membership
	 * and display an option message to everyone else
	 * @param    array       $atts     array of shortcode atts
	 * @param    string     $content  HTML / Text content to be displayed only if the user is enrolled
	 * @return   content
	 * @since    1.0.0
	 * @version  3.1.1
	 */
	public static function hide_content( $atts, $content = '' ) {

		extract( shortcode_atts( array(
			'membership' => '', // backwards compat, use ID moving forwad

			'message' => '',
			'id' => get_the_ID(), // updated 3.1.1
		), $atts ) );

		if ( $membership ) {
			$id = $membership;
		}

		if ( llms_is_user_enrolled( get_current_user_id(), $id ) ) {
			return $content;
		} else {
			return $message;
		}

	}

	/**
	 * Output various pieces of metadata about a course
	 * @param    array     $atts  array of user-submitted shortcode attributes
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function course_info( $atts ) {
		extract( shortcode_atts( array(
			'date_format' => 'F j, Y', // if $type is date, a custom date format can be supplied
			'id' => get_the_ID(),
			'key' => '',
			'type' => '', // date, price
		), $atts, 'lifterlms_course_info' ) );

		$r = '';

		if ( $key ) {

			$course = new LLMS_Course( $id );

			switch ( $type ) {

				case 'date':
					$r = $course->get_date( $key, $date_format );
				break;

				case 'price':
					$r = $course->get_price( $key );
				break;

				default:
					$r = $course->get( $key );

			}

		}

		return apply_filters( 'llms_shortcode_course_info', $r, $atts );
	}

	/**
	 * Course Progress Bar Shortcode
	 * @param  [type] $atts [description]
	 * @return [type]       [description]
	 */
	public static function course_progress( $atts ) {

		$course_id = self::get_course_id();
		if ( ! $course_id ) {
			return '';
		}

		$course = new LLMS_Course( $course_id );

		$course_progress = $course->get_percent_complete();

		return lifterlms_course_progress_bar( $course_progress, false, false, false );
	}

	/**
	 * Retrieve the Course Title
	 * @param  array  $atts  accepts no arguments
	 * @return string
	 * @version  2.7.9
	 */
	public static function course_title( $atts ) {
		$course_id = self::get_course_id();
		if ( ! $course_id ) {
			return '';
		}
		return get_the_title( $course_id );
	}

	/**
	* courses shortcode
	*
	* @return   array
	* @since    1.0.0
	* @version  3.0.2
	*/
	public static function courses( $atts ) {

		// enqueue match height so the loop isn't all messed up visually
		self::enqueue_script( 'llms-jquery-matchheight' );

		if (isset( $atts['category'] )) {
			$tax = array(
				array(
					'taxonomy' => 'course_cat',
					'field' => 'slug',
					'terms' => $atts['category'],
				),
			);
		}

		$args = array(
			'paged' => get_query_var( 'paged' ),
			'post_type' => 'course',
			'post_status' => 'publish',
			'posts_per_page' => isset( $atts['posts_per_page'] ) ? $atts['posts_per_page'] : -1,
			'order' => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
			'orderby' => isset( $atts['orderby'] ) ? $atts['orderby'] : 'title',
			'tax_query' => isset( $tax ) ? $tax : '',
		);

		if ( isset( $atts['id'] ) ) {

			$args['p'] = $atts['id'];

		}

		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) :

			/**
			 * lifterlms_before_loop hook
			 * @hooked lifterlms_loop_start - 10
			 */
			do_action( 'lifterlms_before_loop' );

			while ( $query->have_posts() ) : $query->the_post();

				llms_get_template_part( 'loop/content', get_post_type() );

			endwhile;

			/**
			 * lifterlms_before_loop hook
			 * @hooked lifterlms_loop_end - 10
			 */
			do_action( 'lifterlms_after_loop' );

			echo '<nav class="llms-pagination">';
			echo paginate_links( array(
				'base'         => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
				'format'       => '?page=%#%',
				'total'        => $query->max_num_pages,
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'prev_next'    => true,
				'prev_text'    => '«' . __( 'Previous', 'lifterlms' ),
				'next_text'    => __( 'Next', 'lifterlms' ) . '»',
				'type'         => 'list',
			) );
			echo '</nav>';

		else :

			llms_get_template( 'loop/none-found.php' );

		endif;

		wp_reset_postdata();

		return ob_get_clean();

	}

	/**
	* courses shortcode
	*
	* Used for [lifterlms_related_courses]
	*
	* @return array
	*/
	public static function related_courses( $atts ) {

		ob_start();

		if (isset( $atts['category'] )) {
			$tax = array(
						array(
							'taxonomy' => 'course_cat',
							'field' => 'slug',
							'terms' => $atts['category'],
						),
					);
		}

		$query = new WP_Query( array(
			'post_type' => 'course',
			'post_status' => 'publish',
			'posts_per_page' => isset( $atts['per_page'] ) ? $atts['per_page'] : -1,
			'order' => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
			'orderby' => isset( $atts['orderby'] ) ? $atts['orderby'] : 'title',
			'tax_query' => isset( $tax ) ? $tax : '',
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
			'stat' => 'completed',// completed, enrolled
		),$atts));

		// setup the meta key to search on
		switch ($stat) {
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
		$results = $person->get_user_postmetas_by_key( $uid,$key );

		if ($results) {
			// unset all items that are not courses
			foreach ($results as $key => $obj) {
				if (get_post_type( $obj->post_id ) != $type) {
					unset( $results[ $key ] );
				}
			}
		}

		// filter by value if set
		if (is_array( $results ) && $val) {
			foreach ($results as $key => $obj) {
				// remove from the results array if $val doesn't match
				if ($obj->meta_value != $val) {
					unset( $results[ $key ] );
				}
			}
		}

		$count = (is_array( $results )) ? count( $results ) : 0;

		return $count . ' ' . _n( $type, $type.'s', $count, 'lifterlms' );
	}

	/**
	 * Course Outline Shortcode
	 *
	 * @return template course/outline-list-small.php
	 */
	public static function course_outline( $atts ) {
		global $post;

		extract(shortcode_atts(array(
			'collapse' => 0, // output collapsible syllabus
			'course_id' => false,
			'outline_type' => 'full', // course, lesson, section
			'toggles' => 0,
			'view_type' => 'list',// completed, enrolled
		),$atts));

		// If no course id is passed try to get course id
		if ( ! $course_id) {

			if ( is_course() ) {
				$course_id = get_the_ID();
			} elseif ( is_lesson() ) {
				$lesson = new LLMS_Lesson( get_the_ID() );
				$course_id = $lesson->get_parent_course();
			} elseif ( is_quiz() ) {
				$quiz = LLMS()->session->get( 'llms_quiz' );
				$lesson = new LLMS_Lesson( $quiz->assoc_lesson );
				$course_id = $lesson->get_parent_course();
			} else {
				return '';
			}

		}

		$course = new LLMS_Course( $course_id );

		$syllabus = $course->get_student_progress();

		if ($outline_type === 'current_section') {

			$next_lesson = new LLMS_Lesson( $course->get_next_uncompleted_lesson() );

			foreach ( $syllabus->sections as $section ) {

				if ((int) $next_lesson->get_parent_section() === (int) $section['id']) {

					$args = array(
								'course' => $course,
								'sections' => array( $section ),
								'syllabus' => $syllabus,
							);

					break;

				}

			}

		} else {

			$post_type = get_post_type();

			if ( 'lesson' === $post_type ) {

				$lesson = new LLMS_Lesson( get_the_ID() );
				$current_section = $lesson->get_parent_section();

			} elseif ( 'course' === $post_type ) {

				$lesson = new LLMS_Lesson( $course->get_next_uncompleted_lesson() );
				$current_section = $lesson->get_parent_section();

			} else {

				$current_section = false;

			}

			$args = array(
				'collapse' => $collapse,
				'course' => $course,
				'current_section' => $current_section,
				'sections' => $syllabus->sections,
				'syllabus' => $syllabus,
				'toggles' => $toggles,
			);

		}

		ob_start();
		llms_get_template( 'course/outline-list-small.php', $args );
		return ob_get_clean();
	}

}
