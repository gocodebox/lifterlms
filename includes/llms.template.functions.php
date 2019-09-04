<?php
/**
 * Front end template functions
 *
 * @since    1.0.0
 * @version  3.25.1
 */

defined( 'ABSPATH' ) || exit;

require 'functions/llms-functions-content.php';

require 'functions/llms.functions.templates.achievements.php';
require 'functions/llms.functions.templates.certificates.php';
require 'functions/llms.functions.templates.dashboard.php';
require 'functions/llms.functions.templates.dashboard.widgets.php';
require 'functions/llms.functions.templates.loop.php';
require 'functions/llms.functions.templates.pricing.table.php';
require 'functions/llms.functions.templates.privacy.php';
require 'functions/llms.functions.templates.quizzes.php';

/**
 * Output email body content
 *
 * @return   void
 * @since    3.8.0
 * @version  3.8.0
 */
if ( ! function_exists( 'llms_email_body' ) ) {

	function llms_email_body( $content = '' ) {
		echo apply_filters( 'the_content', $content );
	}
}


/**
 * Output email footer template
 *
 * @return   void
 * @since    3.8.0
 * @version  3.8.0
 */
if ( ! function_exists( 'llms_email_footer' ) ) {

	function llms_email_footer() {
		llms_get_template( 'emails/footer.php' );
	}
}

/**
 * Output email header template with optional heading
 *
 * @param    string  $heading   optional heading text to output above the main content
 * @return   void
 * @since    3.8.0
 * @version  3.8.0
 */
if ( ! function_exists( 'llms_email_header' ) ) {

	function llms_email_header( $heading = '' ) {
		llms_get_template(
			'emails/header.php',
			array(
				'email_heading' => $heading,
			)
		);
	}
}

/**
 * Template Redirect
 *
 * @return void
 */
function llms_template_redirect() {
	global $wp_query, $wp;

	// When default permalinks are enabled, redirect shop page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == '' && llms_get_page_id( 'shop' ) == $_GET['page_id'] ) {
		wp_safe_redirect( get_post_type_archive_link( 'course' ) );
		exit;
	}
	// When default permalinks are enabled, redirect memberships page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == '' && llms_get_page_id( 'memberships' ) == $_GET['page_id'] ) {
		wp_safe_redirect( get_post_type_archive_link( 'llms_membership' ) );
		exit;
	}
}
add_action( 'template_redirect', 'llms_template_redirect' );

/**
 * Title Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_title' ) ) {

	function lifterlms_template_single_title() {

		llms_get_template( 'course/title.php' );
	}
}

/**
 * Short Description Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_short_description' ) ) {

	function lifterlms_template_single_short_description() {

		llms_get_template( 'course/short-description.php' );
	}
}

/**
 * Course Content Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_course_content' ) ) {

	function lifterlms_template_single_course_content() {
		global $post;
		$page_restricted = llms_page_restricted( $post->ID );

		if ( $page_restricted['is_restricted'] ) {
			llms_get_template( 'course/short-description.php' );
		} else {
			llms_get_template( 'course/full-description.php' );
		}

	}
}

/**
 * Course Full Description Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_full_description' ) ) {

	function lifterlms_template_single_full_description() {

		llms_get_template( 'lesson/full-description.php' );
	}
}

/**
 * Membership Featured Image Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_membership_full_description' ) ) {

	function lifterlms_template_single_membership_full_description() {

		llms_get_template( 'membership/full-description.php' );
	}
}

/**
 * Add a course progress bar with a continue button
 *
 * @return   void
 * @since    3.0.1
 * @version  3.0.1
 */
if ( ! function_exists( 'lifterlms_template_single_course_progress' ) ) {
	function lifterlms_template_single_course_progress() {
		llms_get_template( 'course/progress.php' );
	}
}



/**
 * Open the course meta information wrapper
 *
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_meta_wrapper_start' ) ) {
	function lifterlms_template_single_meta_wrapper_start() {
		llms_get_template( 'course/meta-wrapper-start.php' );
	}
}
/**
 * Close the course meta information wrapper
 *
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_meta_wrapper_end' ) ) {
	function lifterlms_template_single_meta_wrapper_end() {
		llms_get_template( 'course/meta-wrapper-end.php' );
	}
}

/**
 * Course Estimated Length Template
 * replaced 'lifterlms_template_single_lesson_length()' which was misnamed as being related to a lesson
 * when it was actually related to a course
 *
 * @return  void
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_length' ) ) {
	function lifterlms_template_single_length() {

		llms_get_template( 'course/length.php' );
	}
}

/**
 * Display a list of course categories
 *
 * @return  void
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_course_categories' ) ) {
	function lifterlms_template_single_course_categories() {
		llms_get_template( 'course/categories.php' );
	}
}

/**
 * Display a list of course tags
 *
 * @return  void
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_course_tags' ) ) {
	function lifterlms_template_single_course_tags() {
		llms_get_template( 'course/tags.php' );
	}
}

/**
 * Display a list of course tracks
 *
 * @return  void
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_course_tracks' ) ) {
	function lifterlms_template_single_course_tracks() {
		llms_get_template( 'course/tracks.php' );
	}
}

/**
 * Display a list of course tags
 *
 * @return  void
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! function_exists( 'lifterlms_template_single_course_tags' ) ) {
	function lifterlms_template_single_course_tags() {
		llms_get_template( 'course/tags.php' );
	}
}

/**
 * Course Video Embed Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_video' ) ) {

	function lifterlms_template_single_video() {

		llms_get_template( 'course/video.php' );
	}
}

/**
 * Lesson Video Embed Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_lesson_video' ) ) {

	function lifterlms_template_single_lesson_video() {

		llms_get_template( 'lesson/video.php' );
	}
}

/**
 * Course Audio Embed Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_audio' ) ) {

	function lifterlms_template_single_audio() {

		llms_get_template( 'course/audio.php' );
	}
}

/**
 * Lesson Audio Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_lesson_audio' ) ) {

	function lifterlms_template_single_lesson_audio() {

		llms_get_template( 'lesson/audio.php' );
	}
}

/**
 * Course Difficulty Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_difficulty' ) ) {

	function lifterlms_template_single_difficulty() {

		llms_get_template( 'course/difficulty.php' );
	}
}

/**
 * Course Difficulty Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_prerequisites' ) ) {

	function lifterlms_template_single_prerequisites() {

		global $post;
		llms_get_template(
			'course/prerequisites.php',
			array(
				'course' => new LLMS_Course( $post ),
			)
		);

	}
}

/**
 * Course Syllabus Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_syllabus' ) ) {

	function lifterlms_template_single_syllabus() {

		llms_get_template( 'course/syllabus.php' );
	}
}

/**
 * Parent Course Link Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_parent_course' ) ) {

	function lifterlms_template_single_parent_course() {

		llms_get_template( 'course/parent-course.php' );
	}
}

/**
 * Complete Lesson Link Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_complete_lesson_link' ) ) {

	function lifterlms_template_complete_lesson_link() {
		llms_get_template( 'course/complete-lesson-link.php' );
	}
}

/**
 * Lesson Navigation Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_lesson_navigation' ) ) {

	function lifterlms_template_lesson_navigation() {

		llms_get_template( 'course/lesson-navigation.php' );
	}
}

/**
 * Membership Title Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_membership_title' ) ) {

	function lifterlms_template_single_membership_title() {

		llms_get_template( 'membership/title.php' );
	}
}



if ( ! function_exists( 'lifterlms_get_content' ) ) {

	function lifterlms_get_content( $args ) {

		llms_get_template( 'content-single-question.php', $args );
	}
}

/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_course_data( $post ) {
	if ( ! is_admin() ) {

		if ( $post && 'course' === $post->post_type ) {

			unset( $GLOBALS['course'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post );
			}

			if ( empty( $post->post_type ) ) {
				return;
			}

			$GLOBALS['course'] = new LLMS_Course( $post );

			return $GLOBALS['course'];

		}
	}

}
add_action( 'the_post', 'llms_setup_course_data' );

// /**
// * When the_post is called, put quiz data into a global.
// *
// * @param mixed $post
// * @return LLMS_Course
// */
// function llms_setup_quiz_data( $post ) {
// if ( ! is_admin() ) {

// if ( $post->post_type == 'llms_quiz' ) {

// unset( $GLOBALS['quiz'] );

// if ( is_int( $post ) ) {
// $post = get_post( $post );
// }

// if ( empty( $post->post_type ) ) {
// return;
// }

// $GLOBALS['quiz'] = llms_get_quiz( $post );
// $student = llms_get_student();
// if ( isset( $_GET['attempt_key'] ) && $student ) {
// $GLOBALS['llms_quiz_attempt'] = $student->quizzes()->get_attempt_by_key( $_GET['attempt_key'] );
// }
// }
// }

// }
// add_action( 'the_post', 'llms_setup_quiz_data' );

/**
 * When the_post is called, put question data into a global.
 *
 * @param mixed $post
 * @return LLMS_Question
 */
function llms_setup_question_data( $post ) {
	if ( ! is_admin() ) {

		if ( 'llms_question' == $post->post_type ) {
			unset( $GLOBALS['question'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post ); }

			if ( empty( $post->post_type ) ) {
				return; }

				$GLOBALS['question'] = llms_get_question( $post );

				return $GLOBALS['question'];
		}
	}

}
add_action( 'the_post', 'llms_setup_question_data' );

/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_product_data( $post ) {

	if ( ! is_admin() ) {

		if ( 'course' == $post->post_type || 'llms_membership' == $post->post_type ) {
			unset( $GLOBALS['product'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post ); }

			if ( empty( $post->post_type ) ) {
				return; }

				$GLOBALS['product'] = llms_get_product( $post );

				return $GLOBALS['product'];
		}
	}

}
add_action( 'the_post', 'llms_setup_product_data' );

/**
 * When the_post is called, put lesson data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_lesson_data( $post ) {
	if ( ! is_admin() ) {

		if ( 'lesson' == $post->post_type ) {
			unset( $GLOBALS['lesson'] );
			// unset( $GLOBALS['course'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post ); }

			if ( empty( $post->post_type ) ) {
				return; }

			$courseid = get_post_meta( $post->ID, '_llms_parent_course' );

			if ( isset( $courseid ) ) {
				$parent_course = get_post( $courseid );
			}

			$GLOBALS['lesson'] = get_lesson( $post );

			llms_setup_course_data( $parent_course );
			// $GLOBALS['course'] = get_course( $parent_course );

			return $GLOBALS['lesson'];
		}
	}

}
add_action( 'the_post', 'llms_setup_lesson_data' );

/**
 * Returns post array of data for sections associated with a course
 *
 * @param array
 * @return array
 */
function get_section_data( $sections ) {
	global $post;
	$html = '';
	$args = array(
		'post_type'   => 'section',
		'post_status' => 'publish',
		'nopaging'    => true,
	);

	$sections_query = get_posts( $args );

	$array = array();

	foreach ( $sections as $key => $value ) :

		foreach ( $sections_query as $section ) :

			if ( $value == $section->ID ) {
				$array[ $section->ID ] = $section;
			}

		endforeach;

	endforeach;

	return $array;

}

/**
 * Returns post array of data for lessons associated with a course
 *
 * @param array
 * @return array
 */
function get_lesson_data( $lessons ) {
	global $post;
	$html = '';
	$args = array(
		'post_type'   => 'lesson',
		'post_status' => 'publish',
		'nopaging'    => true,
	);

	$lessons_query = get_posts( $args );

	$array = array();

	foreach ( $lessons as $key => $value ) :

		foreach ( $lessons_query as $lesson ) :

			if ( $value == $lesson->ID ) {
				$array[ $value ] = $lesson;
			}

		endforeach;

	endforeach;

	return $array;
}

/**
 * Get Page Title
 *
 * @param  boolean $echo [echo string?]
 * @return string $page_title [page title]
 */
if ( ! function_exists( 'lifterlms_page_title' ) ) {

	function lifterlms_page_title( $echo = true ) {

		$page_title = '';

		if ( is_search() ) {
			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'lifterlms' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'lifterlms' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_tax() ) {

			$page_title = single_term_title( '', false );

		} elseif ( is_post_type_archive( 'course' ) ) {

			$page_title = get_the_title( llms_get_page_id( 'courses' ) );

		} elseif ( is_post_type_archive( 'llms_membership' ) ) {

			$page_title = get_the_title( llms_get_page_id( 'memberships' ) );

		}

		$page_title = apply_filters( 'lifterlms_page_title', $page_title );

		if ( $echo ) {

			echo $page_title;

		} else {

			return $page_title;

		}

	}
}// End if().

/**
 * Outputs the html for a progress bar
 *
 * @param    int     $progress  percent completion
 * @param    string   $link     permalink to link the button to, if false will output a span with no href
 * @param    bool     $button   output a button with the link
 * @param    bool     $echo     true will echo content, false will return it
 * @return   void|string
 * @since    1.0.0
 * @version  3.24.0
 */
if ( ! function_exists( 'lifterlms_course_progress_bar' ) ) {

	function lifterlms_course_progress_bar( $progress, $link = false, $button = true, $echo = true ) {

		$progress = round( $progress, 2 );

		$tag  = ( $link ) ? 'a' : 'span';
		$href = ( $link ) ? ' href=" ' . $link . ' "' : '';

		$html = llms_get_progress_bar_html( $progress );

		if ( $button ) {
			$html .= '<' . $tag . ' class="llms-button-primary llms-purchase-button"' . $href . '>' . __( 'Continue', 'lifterlms' ) . '(' . $progress . '%)</' . $tag . '>';
		}

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
}

function llms_get_progress_bar_html( $percentage ) {

	$percentage = sprintf( '%s%%', $percentage );

	$html = '<div class="llms-progress">
		<div class="progress__indicator">' . $percentage . '</div>
		<div class="llms-progress-bar">
			<div class="progress-bar-complete" data-progress="' . $percentage . '"  style="width:' . $percentage . '"></div>
		</div></div>';

	return $html;

}


/**
 * Output a course continue button linking to the incomplete lesson for a given student
 * If the course is complete "Course Complete" is displayed
 *
 * @param    int        $post_id   WP Post ID for a course, lesson, or quiz
 * @param    obj        $student   instance of an LLMS_Student, defaults to current student
 * @param    integer    $progress  current progress of the student through the course
 * @return   void
 * @since    3.11.1
 * @version  3.15.0
 */
if ( ! function_exists( 'lifterlms_course_continue_button' ) ) {

	function lifterlms_course_continue_button( $post_id = null, $student = null, $progress = null ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return '';
			}
		}

		$course = llms_get_post( $post_id );
		if ( ! $course || ! is_a( $course, 'LLMS_Post_Model' ) ) {
			return '';
		}
		if ( in_array( $course->get( 'type' ), array( 'lesson', 'quiz' ) ) ) {
			$course = llms_get_post_parent_course( $course->get( 'id' ) );
			if ( ! $course ) {
				return '';
			}
		}

		if ( ! $student ) {
			$student = llms_get_student();
		}
		if ( ! $student || ! $student->exists() || ! llms_is_user_enrolled( $student->get_id(), $course->get( 'id' ) ) ) {
			return '';
		}

		if ( is_null( $progress ) ) {
			$progress = $student->get_progress( $course->get( 'id' ), 'course' );
		}

		if ( 100 == $progress ) {

			echo '<p class="llms-course-complete-text">' . apply_filters( 'llms_course_continue_button_complete_text', __( 'Course Complete', 'lifterlms' ), $course ) . '</p>';

		} else {

			$lesson = apply_filters( 'llms_course_continue_button_next_lesson', $student->get_next_lesson( $course->get( 'id' ) ), $course, $student );
			if ( $lesson ) { ?>

				<a class="llms-button-primary llms-course-continue-button" href="<?php echo get_permalink( $lesson ); ?>">

					<?php if ( 0 == $progress ) : ?>

						<?php _e( 'Get Started', 'lifterlms' ); ?>

					<?php else : ?>

						<?php _e( 'Continue', 'lifterlms' ); ?>

					<?php endif; ?>

				</a>

				<?php
			}
		}

	}
}// End if().


/**
 * Is template filtered
 *
 * @return boolean
 */
if ( ! function_exists( 'is_filtered' ) ) {

	function is_filtered() {
		global $_chosen_attributes;
		return apply_filters( 'lifterlms_is_filtered', ( count( $_chosen_attributes ) > 0 || ( isset( $_GET['max_price'] ) && isset( $_GET['min_price'] ) ) ) );
	}
}

/**
 * Is Course Category Check
 *
 * @param  string  $term [Category name]
 * @return boolean       [Is Tax associated with Course]
 */
if ( ! function_exists( 'is_course_category' ) ) {

	function is_course_category( $term = '' ) {
		return is_tax( 'course_cat', $term );
	}
}


/**
 * Is course archive page
 * This replaces "is_llms_shop()" which replaced "is_shop()"
 *
 * @return boolean
 * @since   1.4.4
 * @version 3.0.0
 */
if ( ! function_exists( 'is_courses' ) ) {
	function is_courses() {
		return ( ( is_post_type_archive( 'course' ) ) || ( is_singular() && is_page( llms_get_page_id( 'courses' ) ) ) ) ? true : false;
	}
}


/**
 * Is Membership Archive Page
 *
 * @return boolean [Is Membership Archive?]
 */
if ( ! function_exists( 'is_memberships' ) ) {
	function is_memberships() {
		return ( is_post_type_archive( 'llms_membership' ) || ( is_singular() && is_page( llms_get_page_id( 'memberships' ) ) ) ) ? true : false;
	}
}

/**
 * Is Account Page
 *
 * @since  1.4.6   This function replaces the deprecated is_account_page() function because of WooCommerce conflicts
 * @return boolean [Is My Courses Page?]
 */
if ( ! function_exists( 'is_llms_account_page' ) ) {

	function is_llms_account_page() {
		return is_page( llms_get_page_id( 'myaccount' ) ) || apply_filters( 'lifterlms_is_account_page', false ) ? true : false;
	}
}

/**
 * Is Checkout Page
 *
 * @since  1.4.6   This function replaces the deprecated is_checkout() function because of WooCommerce conflicts
 * @return boolean [Is Checkout Page?]
 */
if ( ! function_exists( 'is_llms_checkout' ) ) {
	function is_llms_checkout() {

		return is_page( llms_get_page_id( 'checkout' ) ) ? true : false;

	}
}

/**
* Determine if current post is a lifterLMS Lesson
 *
* @return boolean
*/
if ( ! function_exists( 'is_lesson' ) ) {

	function is_lesson() {
		return ( get_post_type() == 'lesson' ) ? true : false;
	}
}

/**
 * Determine if current post is a lifterLMS Quiz
 *
 * @return boolean
 */
if ( ! function_exists( 'is_quiz' ) ) {

	function is_quiz() {
		return ( get_post_type() == 'llms_quiz' ) ? true : false;
	}
}

/**
 * Get single post author template
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_course_author' ) ) {

	function lifterlms_template_course_author() {
		llms_get_template( 'course/author.php' );
	}
}

/**
 * Course Thumbnail Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_get_course_thumbnail' ) ) {

	function lifterlms_get_course_thumbnail() {
		global $post;

		if ( has_post_thumbnail() ) {

			return lifterlms_get_featured_image( $post->ID );
		} elseif ( llms_placeholder_img_src() ) {
			return llms_placeholder_img( 'full' );
		}
	}
}

/**
 * Featured Image Template Include
 *
 * @return void
 */
if ( ! function_exists( 'lifterlms_get_featured_image' ) ) {

	function lifterlms_get_featured_image( $post_id ) {

		if ( has_post_thumbnail( $post_id ) ) {

			return llms_featured_img( $post_id, 'full' );
		} elseif ( llms_placeholder_img_src() ) {

			return llms_placeholder_img();
		}
	}
}

/**
 * Get the placeholder image URL for courses
 *
 * @access public
 * @return string
 */
function llms_placeholder_img_src() {
	return apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/placeholder.png' );
}

/**
 * Get the placeholder image
 *
 * @access public
 * @return string
 */
function llms_placeholder_img( $size = 'full' ) {
	return apply_filters( 'lifterlms_placeholder_img', '<img src="' . llms_placeholder_img_src() . '" alt="placeholder" class="llms-placeholder llms-featured-image wp-post-image" />' );
}

/**
 * Get the featured image
 *
 * @access public
 * @return string
 */
function llms_featured_img( $post_id, $size ) {
	$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
	return apply_filters( 'lifterlms_featured_img', '<img src="' . $img[0] . '" alt="' . get_the_title( $post_id ) . '" class="llms-featured-image wp-post-image">' );
}




/**
 * Retrieve author name, avatar, and bio
 *
 * @param    array $args  arguments
 * @return   string
 * @since    3.0.0
 * @version  3.13.0
 */
function llms_get_author( $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'avatar'      => true,
			'avatar_size' => 96,
			'bio'         => false,
			'label'       => '',
			'user_id'     => get_the_author_meta( 'ID' ),
		)
	);

	$name = get_the_author_meta( 'display_name', $args['user_id'] );

	if ( $args['avatar'] ) {
		$img = get_avatar( $args['user_id'], $args['avatar_size'], apply_filters( 'lifterlms_author_avatar_placeholder', '' ), $name );
	} else {
		$img = '';
	}

	$img = apply_filters( 'llms_get_author_image', $img );

	$desc = '';
	if ( $args['bio'] ) {
		$desc = get_the_author_meta( 'description', $args['user_id'] );
	}

	ob_start();
	?>
	<div class="llms-author">
		<?php echo $img; ?>
		<span class="llms-author-info name"><?php echo $name; ?></span>
		<?php if ( $args['label'] ) : ?>
			<span class="llms-author-info label"><?php echo $args['label']; ?></span>
		<?php endif; ?>
		<?php if ( $desc ) : ?>
			<p class="llms-author-info bio"><?php echo $desc; ?></p>
		<?php endif; ?>
	</div>
	<?php
	$html = ob_get_clean();

	return apply_filters( 'llms_get_author', $html );

}

/**
 * Global Content Wrapper Start Template
 *
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_output_content_wrapper' ) ) {

	function lifterlms_output_content_wrapper() {
		llms_get_template( 'global/wrapper-start.php' );
	}
}

/**
 * Global Content Wrapper End Template
 *
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_output_content_wrapper_end' ) ) {

	function lifterlms_output_content_wrapper_end() {
		llms_get_template( 'global/wrapper-end.php' );
	}
}

/**
 * Sidebar Template
 *
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_get_sidebar' ) ) {

	function lifterlms_get_sidebar() {
		llms_get_template( 'global/sidebar.php' );
	}
}


/**
 * Is LifterLMS check
 * Checks if archive post type is associated with lifterLMS
 *
 * @return [type] [description]
 */
if ( ! function_exists( 'is_lifterlms' ) ) {
	function is_lifterlms() {
		return apply_filters( 'is_lifterlms', ( is_courses() || is_course_taxonomy() || is_course() || is_lesson() || is_membership() || is_memberships() || is_quiz() ) );
	}
}

/**
 * Is Course Tax
 *
 * @return bool [Is Tax of Course?]
 */
if ( ! function_exists( 'is_course_taxonomy' ) ) {

	function is_course_taxonomy() {
		return is_tax( get_object_taxonomies( 'course' ) );
	}
}

/**
 * Is Membership Tax
 *
 * @return   bool
 * @since    3.22.0
 * @version  3.22.0
 */
if ( ! function_exists( 'is_membership_taxonomy' ) ) {
	function is_membership_taxonomy() {
		return is_tax( get_object_taxonomies( 'llms_membership' ) );
	}
}

/**
 * Is Course Check
 *
 * @return bool [Is Post Type Course?]
 */
if ( ! function_exists( 'is_course' ) ) {

	function is_course() {
		return is_singular( array( 'course' ) );
	}
}

/**
 * Is Membership Check
 *
 * @return bool
 * @since 3.0.0
 */
if ( ! function_exists( 'is_membership' ) ) {

	function is_membership() {
		return is_singular( array( 'llms_membership' ) );
	}
}

/**
 * Get the link to the edit account details page
 *
 * @return string
 */
function llms_person_edit_account_url() {
	$edit_account_url = llms_get_endpoint_url( 'edit-account', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );

	return apply_filters( 'lifterlms_person_edit_account_url', $edit_account_url );
}

/**
 * Get the link to the redeem voucher page
 *
 * @return string
 */
function llms_person_redeem_voucher_url() {

	$url = llms_get_endpoint_url( 'redeem-voucher', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );

	return apply_filters( 'lifterlms_person_redeem_voucher_url', $url );

}

/**
 * Get the link to the My Courses endpoint
 *
 * @return string
 *
 * @since  3.0.0
 */
function llms_person_my_courses_url() {

	$url = llms_get_endpoint_url( 'my-courses', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );

	return apply_filters( 'lifterlms_person_my_courses_url', $url );

}


/**
 * Get Product Query Var
 * REFACTOR: Move to query class
 *
 * @param  array $vars [array of query variables]
 * @return array $vars [array of query variables]
 */
function get_product_query_var( $vars ) {
	$vars[] = 'product';
	return $vars;
}
add_filter( 'query_vars', 'get_product_query_var' );

/**
 * Get available payment gateway options
 * Get's available payment gateways options IE: single, recurring
 *
 * @return void
 */
function get_available_payment_options() {

	$_available_options = array();
	$option_prefix      = 'lifterlms_gateway_enable_';
	$options            = array(
		'paypal',
	);

	foreach ( $options as $option ) {
		$single_option = '';

		$single_option = get_option( $option_prefix . $option, 'no' );

		if ( 'yes' === $single_option ) {

				array_push( $_available_options, $option );
		}

		llms_get_template( 'checkout/' . $option . '.php' );

	}
}

/**
 * Get Product
 *
 * @param  boolean $the_product [Is product class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_product( $the_product = false, $args = array() ) {
	return LLMS()->course_factory->get_product( $the_product, $args );
}

/**
 * Get Quiz
 *
 * @param  boolean $the_quiz [Is Quiz class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_quiz( $the_quiz = false, $args = array() ) {
	return LLMS()->course_factory->get_quiz( $the_quiz, $args );
}

/**
 * Get Question
 *
 * @param  boolean $the_question [Is question class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_question( $the_question = false, $args = array() ) {
	return LLMS()->course_factory->get_question( $the_question, $args );
}


/**
 * Retrieve an excerpt
 *
 * @todo  deprecate this, I have no idea why this is being done this way...
 *
 * @param  int $post_id WordPress post id
 * @return string
 * @version  2.7.5
 */
function llms_get_excerpt( $post_id ) {
	global $post;

	$temp = $post;
	$post = get_post( $post_id );
	setup_postdata( $post );

	$excerpt = apply_filters( 'the_excerpt', $post->post_excerpt );

	wp_reset_postdata();
	$post = $temp;

	return $excerpt;
}

/**
 * Shuffles an array while keeping the array indices
 *
 * @param array $array
 *
 * @return bool
 */
function llms_shuffle_assoc( &$array ) {
	$keys = array_keys( $array );

	shuffle( $keys );

	foreach ( $keys as $key ) {
		$new[ $key ] = $array[ $key ];
	}

	$array = $new;

	return true;
}

/**
 * Get Image size for custom image sizes
 *
 * @param  string $name
 * @param  arrray $default
 * @return array
 */
if ( ! function_exists( 'llms_get_image_size' ) ) {
	function llms_get_image_size( $name, $default = array() ) {

		global $_wp_additional_image_sizes;

		if ( isset( $_wp_additional_image_sizes[ $name ] ) ) {
			return $_wp_additional_image_sizes[ $name ];
		}

		return $default;
	}
}




/**
 * Displays Login Form
 *
 * @param    string  $message Messages to display before login form
 * @param    string  $redirect URL to redirect to after login
 * @param    type    $layout Form layout [columns|stacked]
 * @since    1.0.0
 * @version  3.19.4
 */
if ( ! function_exists( 'llms_get_login_form' ) ) {
	function llms_get_login_form( $message = null, $redirect = null, $layout = null ) {
		llms_get_template(
			'global/form-login.php',
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'layout'   => $layout,
			)
		);
	}
}



/**
 * Add various css classes to LifterLMS post types when "post_class()" is called
 *
 * succeeds now deprecated llms_lesson_complete_classes()
 *
 * @param    array $classes  array of classes to be applied to the post element
 * @param    array $class    array of additional classes
 * @param    int   $post_id  WP Post ID
 * @return   array
 * @since    2.7.11
 * @version  3.0.0
 *
 * @todo  add additional classes based on course/lesson availability and whatnot
 */
function llms_post_classes( $classes, $class = array(), $post_id = '' ) {

	if ( ! $post_id ) {
		return $classes;
	}

	$post_type = get_post_type( $post_id );

	// add enrolled classes
	if ( 'lesson' === $post_type || 'course' === $post_type || 'llms_membership' === $post_type ) {

		$classes[] = llms_is_user_enrolled( get_current_user_id(), $post_id ) ? 'is-enrolled' : 'not-enrolled';

	}

	// add completion classes
	if ( 'lesson' === $post_type || 'course' === $post_type ) {

		if ( get_current_user_id() ) {

			$student   = new LLMS_Student();
			$classes[] = $student->is_complete( $post_id, $post_type ) ? 'is-complete' : 'is-incomplete';

		} else {

			$classes[] = 'is-complete';

		}
	}

	return $classes;

}

/**
 * Output course reviews
 *
 * @return   void
 * @since    3.1.3
 * @version  3.1.3
 */
if ( ! function_exists( 'lifterlms_template_single_reviews' ) ) {
	function lifterlms_template_single_reviews() {
		LLMS_Reviews::output();
	}
}
