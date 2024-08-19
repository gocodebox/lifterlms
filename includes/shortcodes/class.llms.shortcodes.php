<?php
/**
 * LifterLMS Shortcodes
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcodes
 *
 * @since 1.0.0
 * @since 4.0.0 Remove reliance on deprecated class `LLMS_Quiz_Legacy` & stop registering deprecated shortcode `[courses]` and `[lifterlms_user_statistics]`.
 */
class LLMS_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( 'LLMS_Shortcodes', 'init' ) );
	}

	/**
	 * Initialize shortcodes array.
	 *
	 * @since 1.0.0
	 * @since 3.11.1 Unknown.
	 * @since 4.0.0 Stop registering previously deprecated shortcode `[courses]` and `[lifterlms_user_statistics]`.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 * @since 6.4.0 Allowed `LLMS_Shortcode_User_Info` class to be filtered.
	 * @since 7.5.0 Added `LLMS_Shortcode_Favorites` class in shortcodes array.
	 *
	 * @return void
	 */
	public static function init() {

		// New method.
		$scs = apply_filters(
			/**
			 * Filters the shortcodes to initialize.
			 *
			 * @since Unknown
			 *
			 * @param string[] $shortcodes Array of shortcode class names to initialize.
			 */
			'llms_load_shortcodes',
			array(
				'LLMS_Shortcode_Course_Author',
				'LLMS_Shortcode_Course_Continue',
				'LLMS_Shortcode_Course_Continue_Button',
				'LLMS_Shortcode_Course_Instructors',
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
				'LLMS_Shortcode_User_Info',
				'LLMS_Shortcode_Favorites',
			)
		);

		$hyphenated_file_classes = array(
			'LLMS_Shortcode_User_Info',
		);

		foreach ( $scs as $class ) {

			$separator = in_array( $class, $hyphenated_file_classes, true ) ? '-' : '.';
			$filename  = "class{$separator}" . strtolower( str_replace( '_', $separator, $class ) );
			/**
			 * Filters the path of the shortcode class file.
			 *
			 * @since Unknown
			 *
			 * @param string $file  The shortcode class file name.
			 * @param string $class The shortcode class name.
			 */
			$path = apply_filters( 'llms_load_shortcode_path', LLMS_PLUGIN_DIR . "includes/shortcodes/{$filename}.php", $class );

			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		/**
		 * @deprecated 2.0.0
		 * @todo       deprecate
		 */
		add_shortcode( 'courses', array( LLMS_Shortcode_Courses::instance(), 'output' ) );

		// Old method.
		$shortcodes = array(
			'lifterlms_access_plan_button' => __CLASS__ . '::access_plan_button',
			'lifterlms_my_account'         => __CLASS__ . '::my_account',
			'lifterlms_checkout'           => __CLASS__ . '::checkout',
			'lifterlms_course_info'        => __CLASS__ . '::course_info',
			'lifterlms_course_progress'    => __CLASS__ . '::course_progress',
			'lifterlms_course_title'       => __CLASS__ . '::course_title',
			'lifterlms_related_courses'    => __CLASS__ . '::related_courses',
			'lifterlms_login'              => __CLASS__ . '::login',
			'lifterlms_pricing_table'      => __CLASS__ . '::pricing_table',
			'lifterlms_memberships'        => __CLASS__ . '::memberships',
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode(
				/**
				 * Filters the shortcode tag.
				 *
				 * The dynamic portion of the hook name, `$shortcode` refers to the shortcode tag itself.
				 *
				 * @since Unknown
				 *
				 * @param string $shortcode The shortcode tag.
				 */
				apply_filters( "{$shortcode}_shortcode_tag", $shortcode ),
				$function
			);
		}
	}

	/**
	 * Allows shortcodes to enqueue a script by handle
	 *
	 * Ensures the handle is registered and that it hasn't already been enqueued.
	 *
	 * @since 3.0.2
	 *
	 * @param string $handle Script handle used to register the script.
	 *                       The script should be registered in `LLMS_Frontend_Assets`.
	 * @return void
	 */
	private static function enqueue_script( $handle ) {

		if ( wp_script_is( $handle, 'registered' ) && ! wp_script_is( $handle, 'enqueued' ) ) {

			wp_enqueue_script( $handle );

		}
	}

	/**
	 * Retrieve the course ID from within a course, lesson, or quiz
	 *
	 * @since 2.7.9
	 * @since 3.16.0 Unknown.
	 * @since 4.0.0 Remove reliance on deprecated class `LLMS_Quiz_Legacy`.
	 *
	 * @return int
	 */
	private static function get_course_id() {

		$id = get_the_ID();

		if ( is_course() ) {
			return $id;
		}

		$course = llms_get_post_parent_course( $id );
		if ( $course ) {
			return $course->get( 'id' );
		}

		return 0;
	}

	/**
	 * Creates a wrapper for shortcode.
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'lifterlms',
			'before' => null,
			'after'  => null,
		)
	) {

			ob_start();

			echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : wp_kses_post( $wrapper['before'] );
			call_user_func( $function, $atts );
			echo empty( $wrapper['after'] ) ? '</div>' : wp_kses_post( $wrapper['after'] );

			return ob_get_clean();
	}

	/**
	 * Create a button for an Access Plan
	 *
	 * @since 3.2.5
	 * @since 3.4.1 Unknown.
	 *
	 * @param array  $atts    Associative array of shortcode attributes.
	 * @param string $content Optional. Shortcode content, enables custom text/html in the button. Default empty string.
	 * @return string
	 */
	public static function access_plan_button( $atts, $content = '' ) {

		$atts = shortcode_atts(
			array(
				'classes' => '',
				'id'      => null,
				'size'    => '', // Can be: small, large.
				'type'    => 'primary', // Can be: primary, secondary, action, danger.
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

		/**
		 * Filters the access plan button shortcode output
		 *
		 * @since unknown
		 *
		 * @param string $ret     The shortcode output.
		 * @param array  $atts    Associative array of shortcode attributes.
		 * @param string $content Shortcode content, enables custom text/html in the button. Default empty string.
		 */
		return apply_filters( 'llms_shortcode_access_plan_button', $ret, $atts, $content );
	}

	/**
	 * Add a login form
	 *
	 * @since 3.0.4
	 * @since 3.19.4 Unknown.
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
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
	 * My account shortcode
	 *
	 * Used for displaying account.
	 *
	 * @see self::shortcode_wrapper()
	 *
	 * @return string
	 */
	public static function my_account( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_My_Account', 'output' ), $atts );
	}



	/**
	 * Memberships Shortcode
	 *
	 * Used for shortcode [lifterlms_memberships].
	 *
	 * @since 1.4.4
	 * @since 3.0.2
	 * @since 4.12.0 Handle pagination when the shortcode is used on the static front page.
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
	 */
	public static function memberships( $atts ) {

		// Enqueue match height so the loop isn't all messed up visually.
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
			'paged'          => is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' ),
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
			$pagination = paginate_links(
				array(
					'base'      => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
					'format'    => '?page=%#%',
					'total'     => $query->max_num_pages,
					'current'   => max( 1, $args['paged'] ),
					'prev_next' => true,
					'prev_text' => '«' . __( 'Previous', 'lifterlms' ),
					'next_text' => __( 'Next', 'lifterlms' ) . '»',
					'type'      => 'list',
				)
			);
			if ( ! empty( $pagination ) ) {
				echo wp_kses_post( $pagination );
			}
			echo '</nav>';

		else :

			llms_get_template( 'loop/none-found.php' );

		endif;

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Checkout shortcode
	 *
	 * Used for displaying checkout form.
	 *
	 * @see self::shortcode_wrapper
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
	 */
	public static function checkout( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_Checkout', 'output' ), $atts );
	}

	/**
	 * Output various pieces of metadata about a course
	 *
	 * @since 3.0.0
	 * @since 3.4.1 Unknown.
	 *
	 * @param array $atts Array of user-submitted shortcode attributes.
	 * @return string
	 */
	public static function course_info( $atts ) {
		extract(
			shortcode_atts(
				array(
					'date_format' => 'F j, Y', // If $type is date, a custom date format can be supplied.
					'id'          => get_the_ID(),
					'key'         => '',
					'type'        => '', // Can either be: date, price or empty string.
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

		/**
		 * Filters the course info shortcode output
		 *
		 * @since unknown
		 *
		 * @param string $ret  The shortcode output.
		 * @param array  $atts Associative array of shortcode attributes.
		 */
		return apply_filters( 'llms_shortcode_course_info', $ret, $atts );
	}

	/**
	 * Course Progress Bar Shortcode
	 *
	 * @since unknown
	 * @since 3.38.0 Added logic to display the bar only to enrolled user.
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
	 */
	public static function course_progress( $atts ) {

		$course_id = self::get_course_id();
		if ( ! $course_id ) {
			return '';
		}

		if ( ! empty( $atts['check_enrollment'] ) && ! llms_is_user_enrolled( get_current_user_id(), $course_id ) ) {
			return '';
		}

		$course = new LLMS_Course( $course_id );

		$course_progress = $course->get_percent_complete();

		return lifterlms_course_progress_bar( $course_progress, false, false, false );
	}

	/**
	 * Retrieve the Course Title
	 *
	 * @since unknown
	 * @since 2.7.9 Unknown
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
	 */
	public static function course_title( $atts ) {
		$course_id = self::get_course_id();
		if ( ! $course_id ) {
			return '';
		}
		return get_the_title( $course_id );
	}

	/**
	 * Courses shortcode
	 *
	 * Used for [lifterlms_related_courses].
	 *
	 * @since unknown
	 *
	 * @param array $atts Associative array of shortcode attributes.
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
	 * Output a Pricing Table anywhere a shortcode can be output
	 *
	 * @since 3.2.5
	 * @since 3.23.0 Unknown.
	 * @since 3.38.0 Use `in_array()` with strict comparison.
	 *
	 * @param array $atts Associative array of shortcode attributes.
	 * @return string
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

		// get product id from loop if used from within a course or membership.
		if ( ! $atts['product'] ) {
			$id = get_the_ID();
			if ( in_array( get_post_type( $id ), array( 'course', 'llms_membership' ), true ) ) {
				$atts['product'] = get_the_ID();
			}
		}

		if ( ! empty( $atts['product'] ) && is_numeric( $atts['product'] ) ) {

			// enqueue match height for height alignments.
			self::enqueue_script( 'llms-jquery-matchheight' );

			ob_start();
			lifterlms_template_pricing_table( $atts['product'] );
			$ret = ob_get_clean();
		}

		/**
		 * Filters the pricing table shortcode output
		 *
		 * @since unknown
		 *
		 * @param string $ret  The shortcode output.
		 * @param array  $atts Associative array of shortcode attributes.
		 */
		return apply_filters( 'llms_shortcode_pricing_table', $ret, $atts );
	}
}

return new LLMS_Shortcodes();
