<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Shortcodes
 *
 * @since    1.0.0
 * @version  3.23.0
 */
class LLMS_Shortcodes {

	/**
	 * init shortcodes array
	 *
	 * @return void
	 * @since    1.0.0
	 * @version  3.11.1
	 */
	public static function init() {

		// new method
		$scs = apply_filters(
			'llms_load_shortcodes',
			array(
				'LLMS_Shortcode_Course_Author',
				'LLMS_Shortcode_Course_Continue',
				'LLMS_Shortcode_Course_Continue_Button',
				'LLMS_Shortcode_Course_Meta_Info',
				'LLMS_Shortcode_Course_Outline',
				'LLMS_Shortcode_Course_Prerequisites',
				'LLMS_Shortcode_Course_Reviews',
				'LLMS_Shortcode_Course_Syllabus',
				'LLMS_Shortcode_Courses',
				'LLMS_Shortcode_Hide_Content',
				'LLMS_Shortcode_Lesson_Mark_Complete',
				'LLMS_Shortcode_Membership_Link',
				'LLMS_Shortcode_My_Achievements',
				'LLMS_Shortcode_Registration',
			)
		);

		// include abstracts
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.shortcode.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.shortcode.course.element.php';

		foreach ( $scs as $class ) {

			$filename = strtolower( str_replace( '_', '.', $class ) );
			$path     = apply_filters( 'llms_load_shortcode_path', LLMS_PLUGIN_DIR . 'includes/shortcodes/class.' . $filename . '.php', $class );

			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		/**
		 * @deprecated  2.0.0
		 * @todo        deprecate
		 */
		add_shortcode( 'courses', array( LLMS_Shortcode_Courses::instance(), 'output' ) );

		// old method
		$shortcodes = array(
			'lifterlms_access_plan_button' => __CLASS__ . '::access_plan_button',
			'lifterlms_my_account'         => __CLASS__ . '::my_account',
			'lifterlms_checkout'           => __CLASS__ . '::checkout',
			'lifterlms_course_info'        => __CLASS__ . '::course_info',
			'lifterlms_course_progress'    => __CLASS__ . '::course_progress',
			'lifterlms_course_title'       => __CLASS__ . '::course_title',
			'lifterlms_user_statistics'    => __CLASS__ . '::user_statistics',
			'lifterlms_related_courses'    => __CLASS__ . '::related_courses',
			'lifterlms_login'              => __CLASS__ . '::login',
			'lifterlms_pricing_table'      => __CLASS__ . '::pricing_table',
			'lifterlms_memberships'        => __CLASS__ . '::memberships',

		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );

		}

	}

	/**
	 * Allows shortcodes to enqueue a script by handle
	 * Ensures the handle is registered and that it hasn't already been enqueued
	 *
	 * @param    [type] $handle  script handle used to register the script
	 *                           the script should be registered in `LLMS_Frontend_Assets`
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
	 *
	 * @return   int
	 * @since    2.7.9
	 * @version  3.16.0
	 */
	private static function get_course_id() {

		if ( is_course() ) {
			return get_the_ID();
		} elseif ( is_lesson() ) {
			$lesson = new LLMS_Lesson( get_the_ID() );
			return $lesson->get_parent_course();
		} elseif ( is_quiz() ) {
			$quiz   = new LLMS_Quiz_Legacy( get_the_ID() );
			$lesson = new LLMS_Lesson( $quiz->assoc_lesson );
			return $lesson->get_parent_course();
		}

		return 0;
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

			$before = empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
			$after  = empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

			echo $before;
			call_user_func( $function, $atts );
			echo $after;

			return ob_get_clean();
	}

	/**
	 * Create a button for an Access Plan
	 *
	 * @param    array  $atts      array of shortcode attributes
	 * @param    string $content  optional shortcode content, enables custom text / html in the button
	 * @return   string
	 * @since    3.2.5
	 * @version  3.4.1
	 */
	public static function access_plan_button( $atts, $content = '' ) {

		$atts = shortcode_atts(
			array(
				'classes' => '',
				'id'      => null,
				'size'    => '', // small, large
				'type'    => 'primary', // primary, secondary, action, danger
			),
			$atts,
			'lifterlms_access_plan_button'
		);

		$ret = '';

		if ( ! empty( $atts['id'] ) && is_numeric( $atts['id'] ) ) {
			$plan = new LLMS_Access_Plan( $atts['id'] );

			$classes  = 'llms-button-' . $atts['type'];
			$classes .= ! empty( $atts['size'] ) ? ' ' . $atts['size'] : '';
			$classes .= ! empty( $atts['classes'] ) ? ' ' . $atts['classes'] : '';

			$text = empty( $content ) ? $plan->get_enroll_text() : $content;

			$ret = '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $plan->get_checkout_url() ) . '" title="' . esc_attr( $plan->get( 'title' ) ) . '">' . $text . '</a>';
		}

		return apply_filters( 'llms_shortcode_access_plan_button', $ret, $atts, $content );

	}

	/**
	 * Add a login form
	 *
	 * @param    array $atts  shortcode attributes
	 * @return   string
	 * @since    3.0.4
	 * @version  3.19.4
	 */
	public static function login( $atts ) {

		extract(
			shortcode_atts(
				array(
					'layout'   => 'columns',
					'redirect' => get_permalink(),
				),
				$atts,
				'lifterlms_login'
			)
		);

		ob_start();
		llms_print_notices();
		llms_get_login_form( null, $redirect, $layout );
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
	 * Memberships Shortcode
	 * Used for shortcode [lifterlms_memberships]
	 *
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
					'field'    => 'slug',
					'terms'    => $atts['category'],
				),
			);
		}

		$args = array(
			'paged'          => get_query_var( 'paged' ),
			'post_type'      => 'llms_membership',
			'post_status'    => 'publish',
			'posts_per_page' => isset( $atts['posts_per_page'] ) ? $atts['posts_per_page'] : -1,
			'order'          => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
			'orderby'        => isset( $atts['orderby'] ) ? $atts['orderby'] : 'title',
			'tax_query'      => isset( $tax ) ? $tax : '',
		);

		if ( isset( $atts['id'] ) ) {

			$args['p'] = $atts['id'];

		}

		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) :

			/**
			 * lifterlms_before_loop hook
			 *
			 * @hooked lifterlms_loop_start - 10
			 */
			do_action( 'lifterlms_before_loop' );

			while ( $query->have_posts() ) :
				$query->the_post();

				llms_get_template_part( 'loop/content', get_post_type() );

			endwhile;

			/**
			 * lifterlms_before_loop hook
			 *
			 * @hooked lifterlms_loop_end - 10
			 */
			do_action( 'lifterlms_after_loop' );

			echo '<nav class="llms-pagination">';
			echo paginate_links(
				array(
					'base'      => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
					'format'    => '?page=%#%',
					'total'     => $query->max_num_pages,
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'prev_next' => true,
					'prev_text' => '«' . __( 'Previous', 'lifterlms' ),
					'next_text' => __( 'Next', 'lifterlms' ) . '»',
					'type'      => 'list',
				)
			);
			echo '</nav>';

		else :

			llms_get_template( 'loop/none-found.php' );

		endif;

		wp_reset_postdata();

		return ob_get_clean();

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
	 * Output various pieces of metadata about a course
	 *
	 * @param    array $atts  array of user-submitted shortcode attributes
	 * @return   string
	 * @since    3.0.0
	 * @version  3.4.1
	 */
	public static function course_info( $atts ) {
		extract(
			shortcode_atts(
				array(
					'date_format' => 'F j, Y', // if $type is date, a custom date format can be supplied
					'id'          => get_the_ID(),
					'key'         => '',
					'type'        => '', // date, price
				),
				$atts,
				'lifterlms_course_info'
			)
		);

		$ret = '';

		if ( $key ) {

			$course = new LLMS_Course( $id );

			switch ( $type ) {

				case 'date':
					$ret = $course->get_date( $key, $date_format );
					break;

				case 'price':
					$ret = $course->get_price( $key );
					break;

				default:
					$ret = $course->get( $key );

			}
		}

		return apply_filters( 'llms_shortcode_course_info', $ret, $atts );
	}

	/**
	 * Course Progress Bar Shortcode
	 *
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
	 *
	 * @param  array $atts  accepts no arguments
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
	 * Used for [lifterlms_related_courses]
	 *
	 * @return array
	 */
	public static function related_courses( $atts ) {

		ob_start();

		if ( isset( $atts['category'] ) ) {
			$tax = array(
				array(
					'taxonomy' => 'course_cat',
					'field'    => 'slug',
					'terms'    => $atts['category'],
				),
			);
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'course',
				'post_status'    => 'publish',
				'posts_per_page' => isset( $atts['per_page'] ) ? $atts['per_page'] : -1,
				'order'          => isset( $atts['order'] ) ? $atts['order'] : 'ASC',
				'orderby'        => isset( $atts['orderby'] ) ? $atts['orderby'] : 'title',
				'tax_query'      => isset( $tax ) ? $tax : '',
			)
		);

		if ( $query->have_posts() ) {

			lifterlms_course_loop_start();

			while ( $query->have_posts() ) :
				$query->the_post();

				llms_get_template_part( 'content', 'course' );

			endwhile;

			lifterlms_course_loop_end();

			$courses = ob_get_clean();
			wp_reset_postdata();
			return $courses;
		}

	}

	/**
	 * Output user statistics related to courses enrolled, completed, etc...
	 *
	 * @param  [array] $atts / array of user input attributes
	 * @return string / html content
	 */
	public static function user_statistics( $atts ) {
		extract(
			shortcode_atts(
				array(
					'type' => 'course', // course, lesson, section
					'stat' => 'completed', // completed, enrolled
				),
				$atts
			)
		);

		// setup the meta key to search on
		switch ( $stat ) {
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
		$results = $person->get_user_postmetas_by_key( $uid, $key );

		if ( $results ) {
			// unset all items that are not courses
			foreach ( $results as $key => $obj ) {
				if ( get_post_type( $obj->post_id ) != $type ) {
					unset( $results[ $key ] );
				}
			}
		}

		// filter by value if set
		if ( is_array( $results ) && $val ) {
			foreach ( $results as $key => $obj ) {
				// remove from the results array if $val doesn't match
				if ( $obj->meta_value != $val ) {
					unset( $results[ $key ] );
				}
			}
		}

		$count = ( is_array( $results ) ) ? count( $results ) : 0;

		if ( 1 == $count ) {
			return $count . ' ' . $type;
		}

		return $count . ' ' . $type . 's';

	}

	/**
	 * Output a Pricing Table anywhere a shortcode can be output
	 *
	 * @param    array $atts  array of shortcode attributes
	 * @return   string
	 * @since    3.2.5
	 * @version  3.23.0
	 */
	public static function pricing_table( $atts ) {

		$atts = shortcode_atts(
			array(
				'product' => null,
			),
			$atts,
			'lifterlms_pricing_table'
		);

		$ret = '';

		// get product id from loop if used from within a course or membership
		if ( ! $atts['product'] ) {
			$id = get_the_ID();
			if ( in_array( get_post_type( $id ), array( 'course', 'llms_membership' ) ) ) {
				$atts['product'] = get_the_ID();
			}
		}

		if ( ! empty( $atts['product'] ) && is_numeric( $atts['product'] ) ) {

			// enqueue match height for height alignments
			self::enqueue_script( 'llms-jquery-matchheight' );

			ob_start();
			lifterlms_template_pricing_table( $atts['product'] );
			$ret = ob_get_clean();
		}

		return apply_filters( 'llms_shortcode_pricing_table', $ret, $atts );

	}

}
