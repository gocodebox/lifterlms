<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Front end template functions
*/
add_action( 'init', 'update_portfolio_restrictions' );
function update_portfolio_restrictions() {

	$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'portfolio',
		'nopaging' 			=> true,
		'fields' 			=> 'ids',
	);
	$postslist = get_posts( $args );

	 $memberships = get_postmeta( 2408, '_llms_restricted_levels', true );

	foreach ($postslist as $key => $value) {
	 	update_postmeta( $value, '_llms_restricted_levels', $memberships );
	}
}
/**
 * Post Template Include
 * Appends LLMS content above and below post content
 * @param  string $content [WP post content]
 * @return string $content [WP post content with lifterLMS content appended above and below]
 */
if ( ! function_exists( 'llms_get_post_content' ) ) {

	function llms_get_post_content( $content ) {
		$page_restricted = llms_page_restricted( get_the_id() );

		global $post;
		if ( ! $post instanceof WP_Post ) { return $content; }
		switch ( $post->post_type ) {
			case 'course':
				if ( $page_restricted['is_restricted'] ) {

					add_filter( 'the_excerpt', array( $GLOBALS['wp_embed'], 'autoembed' ), 9 );

					if ($post->post_excerpt) {
						$content = llms_get_excerpt( $post->ID );
					}
				}
				$template_before  = llms_get_template_part_contents( 'content', 'single-course-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'single-course-after' );

				ob_start();
				load_template( $template_before, false );
				$output_before = ob_get_clean();

				ob_start();
				load_template( $template_after, false );
				$output_after = ob_get_clean();

			return do_shortcode( $output_before . $content . $output_after );

			case 'lesson':
				if ( $page_restricted['is_restricted'] ) {
					$content = '';
					$template_before  = llms_get_template_part_contents( 'content', 'no-access-before' );
					$template_after  = llms_get_template_part_contents( 'content', 'no-access-after' );
				} else {
					$template_before  = llms_get_template_part_contents( 'content', 'single-lesson-before' );
					$template_after  = llms_get_template_part_contents( 'content', 'single-lesson-after' );
				}

				ob_start();
				load_template( $template_before, false );
				$output_before = ob_get_clean();

				ob_start();
				load_template( $template_after, false );
				$output_after = ob_get_clean();

			return do_shortcode( $output_before . $content . $output_after );

			case 'llms_membership':
				if ( $page_restricted['is_restricted'] ) {
					add_filter( 'the_excerpt', array( $GLOBALS['wp_embed'], 'autoembed' ), 9 );

					if ($post->post_excerpt) {
						$content = llms_get_excerpt( $post->ID );
					}
				}
				$template_before  = llms_get_template_part_contents( 'content', 'single-membership-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'single-membership-after' );

				ob_start();
				load_template( $template_before, false );
				$output_before = ob_get_clean();

				ob_start();
				load_template( $template_after, false );
				$output_after = ob_get_clean();

			return do_shortcode( $output_before . $content . $output_after );

			case 'llms_quiz':
				$template_before  = llms_get_template_part_contents( 'content', 'single-quiz-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'single-quiz-after' );

				ob_start();
				load_template( $template_before, false );
				$output_before = ob_get_clean();

				ob_start();
				load_template( $template_after, false );
				$output_after = ob_get_clean();

			return do_shortcode( $output_before . $content . $output_after );

			default:
			return apply_filters( 'llms_get_post_content', $content );
		}
	}
}
add_filter( 'the_content', 'llms_get_post_content' );

/**
 * Template Redirect
 * @return void
 */
function llms_template_redirect() {
	global $wp_query, $wp;

	// When default permalinks are enabled, redirect shop page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == '' && $_GET['page_id'] == llms_get_page_id( 'shop' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'course' ) );
		exit;
	}
	// When default permalinks are enabled, redirect memberships page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == '' && $_GET['page_id'] == llms_get_page_id( 'memberships' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'llms_membership' ) );
		exit;
	}
}
add_action( 'template_redirect', 'llms_template_redirect' );

/**
 * Featured Image Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_featured_image' ) ) {

	function lifterlms_template_single_featured_image() {

		llms_get_template( 'course/featured-image.php' );
	}
}

/**
 * Title Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_title' ) ) {

	function lifterlms_template_single_title() {

		llms_get_template( 'course/title.php' );
	}
}

/**
 * Short Description Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_short_description' ) ) {

	function lifterlms_template_single_short_description() {

		llms_get_template( 'course/short-description.php' );
	}
}

/**
 * Course Content Template Include
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
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_full_description' ) ) {

	function lifterlms_template_single_full_description() {

		llms_get_template( 'lesson/full-description.php' );
	}
}

/**
 * Membership Featured Image Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_membership_full_description' ) ) {

	function lifterlms_template_single_membership_full_description() {

		llms_get_template( 'membership/full-description.php' );
	}
}

/**
 * Single Price Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_price' ) ) {

	function lifterlms_template_single_price() {
		global $post;

		if ($post->post_type == 'course') {
			llms_get_template( 'course/price.php' );
		} elseif ($post->post_type == 'llms_membership') {

			llms_get_template( 'membership/price.php' );
		}
	}
}

/**
 * Lesson Length Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_lesson_length' ) ) {

	function lifterlms_template_single_lesson_length() {

		llms_get_template( 'course/lesson_length.php' );
	}
}

/**
 * Purchase Link Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_purchase_link' ) ) {

	function lifterlms_template_single_purchase_link() {
		global $post;

		if ($post->post_type == 'course') {
			llms_get_template( 'course/purchase-link.php' );
		} elseif ($post->post_type == 'llms_membership') {

			llms_get_template( 'membership/purchase-link.php' );
		}
	}
}

/**
 * Course Video Embed Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_video' ) ) {

	function lifterlms_template_single_video() {

		llms_get_template( 'course/video.php' );
	}
}

/**
 * Lesson Video Embed Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_lesson_video' ) ) {

	function lifterlms_template_single_lesson_video() {

		llms_get_template( 'lesson/video.php' );
	}
}

/**
 * Course Audio Embed Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_audio' ) ) {

	function lifterlms_template_single_audio() {

		llms_get_template( 'course/audio.php' );
	}
}

/**
 * Lesson Audio Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_lesson_audio' ) ) {

	function lifterlms_template_single_lesson_audio() {

		llms_get_template( 'lesson/audio.php' );
	}
}

/**
 * Course Difficulty Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_difficulty' ) ) {

	function lifterlms_template_single_difficulty() {

		llms_get_template( 'course/difficulty.php' );
	}
}

/**
 * Course Syllabus Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_syllabus' ) ) {

	function lifterlms_template_single_syllabus() {

		llms_get_template( 'course/syllabus.php' );
	}
}

/**
 * Parent Course Link Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_parent_course' ) ) {

	function lifterlms_template_single_parent_course() {

		llms_get_template( 'course/parent_course.php' );
	}
}

/**
 * Complete Lesson Link Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_complete_lesson_link' ) ) {

	function lifterlms_template_complete_lesson_link() {

		llms_get_template( 'course/complete-lesson-link.php' );
	}
}

/**
 * Lesson Navigation Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_lesson_navigation' ) ) {

	function lifterlms_template_lesson_navigation() {

		llms_get_template( 'course/lesson-navigation.php' );
	}
}

/**
 * Membership Title Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_membership_title' ) ) {

	function lifterlms_template_single_membership_title() {

		llms_get_template( 'membership/title.php' );
	}
}

/**
 * Quiz attempts Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_attempts' ) ) {

	function lifterlms_template_quiz_attempts() {

		llms_get_template( 'quiz/attempts.php' );
	}
}

/**
 * Quiz timer
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_time_limit' ) ) {

	function lifterlms_template_quiz_time_limit() {

		llms_get_template( 'quiz/time-limit.php' );
	}
}

/**
 * Quiz timer
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_timer' ) ) {

	function lifterlms_template_quiz_timer() {

		llms_get_template( 'quiz/timer.php' );
	}
}

/**
 * Quiz: wrapper start ( quiz container )
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_wrapper_start' ) ) {

	function lifterlms_template_quiz_wrapper_start() {

		llms_get_template( 'quiz/quiz-wrapper-start.php' );
	}
}

/**
 * Quiz: wrapper end ( quiz container )
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_wrapper_end' ) ) {

	function lifterlms_template_quiz_wrapper_end() {

		llms_get_template( 'quiz/quiz-wrapper-end.php' );
	}
}

/**
 * Question: Wrapper for ajax loaded quiz question
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_question' ) ) {

	function lifterlms_template_quiz_question() {

		llms_get_template( 'quiz/quiz-question.php' );
	}
}

/**
 * Lesson Return link Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_return_link' ) ) {

	function lifterlms_template_quiz_return_link() {

		llms_get_template( 'quiz/return-to-lesson.php' );
	}
}

/**
 * Passing Percent Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_passing_percent' ) ) {

	function lifterlms_template_passing_percent() {

		llms_get_template( 'quiz/passing-percent.php' );
	}
}

/**
 * Start Button Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_start_button' ) ) {

	function lifterlms_template_start_button() {

		llms_get_template( 'quiz/start-button.php' );
	}
}

/**
 * Next Question Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_next_question' ) ) {

	function lifterlms_template_single_next_question( $args ) {

		llms_get_template( 'quiz/next-question.php', $args );
	}
}

/**
 * Prev Question Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_prev_question' ) ) {

	function lifterlms_template_single_prev_question( $args ) {

		llms_get_template( 'quiz/previous-question.php', $args );
	}
}

/**
 * Question Count Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_question_count' ) ) {

	function lifterlms_template_single_question_count( $args ) {

		llms_get_template( 'quiz/question-count.php', $args );
	}
}

if ( ! function_exists( 'lifterlms_get_content' ) ) {

	function lifterlms_get_content( $args ) {

		llms_get_template( 'content-single-question.php', $args );
	}
}

/**
 * Single Choice Question Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_single_choice' ) ) {

	function lifterlms_template_single_single_choice() {

		llms_get_template( 'quiz/single-choice.php' );
	}
}

/**
 * Single Choice Question Template Include AJAX
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_single_single_choice_ajax' ) ) {

	function lifterlms_template_single_single_choice_ajax( $args ) {

		llms_get_template( 'quiz/single-choice_ajax.php', $args );
	}
}

/**
 * Question Wrapper Start Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlmslifterlms_template_question_wrapper_start' ) ) {

	function lifterlmslifterlms_template_question_wrapper_start( $args ) {

		llms_get_template( 'quiz/wrapper-start.php', $args );
	}
}

/**
 * Question Wrapper End Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlmslifterlms_template_question_wrapper_end' ) ) {

	function lifterlmslifterlms_template_question_wrapper_end( $args ) {

		llms_get_template( 'quiz/wrapper-end.php', $args );
	}
}

/**
 * Quiz Results Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_results' ) ) {

	function lifterlms_template_quiz_results() {

		llms_get_template( 'quiz/results.php' );
	}
}

/**
 * Quiz Results Summary Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_quiz_summary' ) ) {

	function lifterlms_template_quiz_summary() {
		global $post;
		if (get_post_meta( $post->ID, '_llms_show_results', true )) {
			llms_get_template( 'quiz/summary.php' );
		}
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

		if ($post && $post->post_type == 'course') {
			unset( $GLOBALS['course'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post );
			}

			if ( empty( $post->post_type ) ) {
				return;
			}

			$GLOBALS['course'] = get_course( $post );

			return $GLOBALS['course'];
		}

	}

}
add_action( 'the_post', 'llms_setup_course_data' );

/**
 * When the_post is called, put quiz data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_quiz_data( $post ) {
	if ( ! is_admin() ) {

		if ($post->post_type == 'llms_quiz') {
			unset( $GLOBALS['quiz'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post ); }

			if ( empty( $post->post_type ) ) {
				return; }

				$GLOBALS['quiz'] = llms_get_quiz( $post );

				return $GLOBALS['quiz'];
		}
	}

}
add_action( 'the_post', 'llms_setup_quiz_data' );

/**
 * When the_post is called, put question data into a global.
 *
 * @param mixed $post
 * @return LLMS_Question
 */
function llms_setup_question_data( $post ) {
	if ( ! is_admin() ) {

		if ($post->post_type == 'llms_question') {
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

		if ($post->post_type == 'course' || $post->post_type == 'llms_membership' ) {
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

		if ($post->post_type == 'lesson') {
			unset( $GLOBALS['lesson'] );
			//unset( $GLOBALS['course'] );

			if ( is_int( $post ) ) {
				$post = get_post( $post ); }

			if ( empty( $post->post_type ) ) {
				return; }

			$courseid = get_post_meta( $post->ID, '_parent_course' );

			if ( isset( $courseid ) ) {
				$parent_course = get_post( $courseid );
			}

			$GLOBALS['lesson'] = get_lesson( $post );

			llms_setup_course_data( $parent_course );
			//$GLOBALS['course'] = get_course( $parent_course );

			return $GLOBALS['lesson'];
		}
	}

}
add_action( 'the_post', 'llms_setup_lesson_data' );


/**
 * Get Price
 *
 * @param  int $price [product price]
 * @param  array  $args  [array of price arguments]
 *
 * @return int $price [formatted price]
 */
function llms_price( $price, $args = array() ) {

	return $price;
}

/**
 * Returns post array of data for sections associated with a course
 *
 * @param array
 * @return array
 */
function get_section_data ( $sections ) {
	global $post;
	$html = '';
	$args = array(
	    'post_type' => 'section',
	    'post_status' => 'publish',
	    'nopaging' 		=> true,
	);

	$sections_query = get_posts( $args );

	$array = array();

	foreach ($sections as $key => $value) :

		foreach ($sections_query as $section) :

			if ($value == $section->ID) {
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
function get_lesson_data ( $lessons ) {
	global $post;
	$html = '';
	$args = array(
	    'post_type' => 'lesson',
	    'post_status' => 'publish',
	    'nopaging' 		=> true,
	);

	$lessons_query = get_posts( $args );

	$array = array();

	foreach ($lessons as $key => $value) :

		foreach ($lessons_query as $lesson) :

			if ($value == $lesson->ID) {
				$array[ $value ] = $lesson;
			}

		endforeach;

	endforeach;

	return $array;
}

/**
 * Get Page Title
 * @param  boolean $echo [echo string?]
 * @return string $page_title [page title]
 */
if ( ! function_exists( 'lifterlms_page_title' ) ) {

	function lifterlms_page_title( $echo = true ) {
		$page_title = '';

		if ( is_search() ) {
			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'lifterlms' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'lifterlms' ), get_query_var( 'paged' ) ); }

		} elseif ( is_tax() ) {

			$page_title = single_term_title( '', false );

		}

		$page_title = apply_filters( 'lifterlms_page_title', $page_title );

		if ( $echo ) {
	    	echo $page_title; } else { 	    	return $page_title; }
	}
}

/**
 * Get Membership Archive loop start
 * @param  boolean $echo [echo string?]
 * @return string [loop start html]
 */
if ( ! function_exists( 'lifterlms_membership_loop_start' ) ) {

	function lifterlms_membership_loop_start( $echo = true ) {
		ob_start();
		llms_get_template( 'loop/loop-start.php' );
		if ( $echo ) {
			echo ob_get_clean(); } else { 			return ob_get_clean(); }
	}
}

/**
 * Get Membership archive loop end
 * @param  boolean $echo [echo string?]
 * @return string [loop end html]
 */
if ( ! function_exists( 'lifterlms_membership_loop_end' ) ) {

	function lifterlms_membership_loop_end( $echo = true ) {
		ob_start();

		llms_get_template( 'loop/loop-end.php' );

		if ( $echo ) {
			echo ob_get_clean(); } else { 			return ob_get_clean(); }
	}
}

/**
 * Get Course Archive loop start
 * @param  boolean $echo [echo string?]
 * @return string [loop start html]
 */
if ( ! function_exists( 'lifterlms_course_loop_start' ) ) {

	function lifterlms_course_loop_start( $echo = true ) {
		ob_start();
		llms_get_template( 'loop/loop-start.php' );
		if ( $echo ) {
			echo ob_get_clean(); } else { 			return ob_get_clean(); }
	}
}

/**
 * Get Course archive loop end
 * @param  boolean $echo [echo string?]
 * @return string [loop end html]
 */
if ( ! function_exists( 'lifterlms_course_loop_end' ) ) {

	function lifterlms_course_loop_end( $echo = true ) {
		ob_start();

		llms_get_template( 'loop/loop-end.php' );

		if ( $echo ) {
			echo ob_get_clean(); } else { 			return ob_get_clean(); }
	}
}

/**
 * Outputs the html for a progress bar
 * @param  int / $progress / percent completion
 * @param  string / $link / permalink to link the button to, if false will output a span with no href
 * @param  bool / $button / output a button with the link
 * @param  bool / $echo / true will echo content, false will return it
 * @return null / html content
 */
if ( ! function_exists( 'lifterlms_course_progress_bar' ) ) {

	function lifterlms_course_progress_bar( $progress, $link = false, $button = true, $echo = true ) {

		$tag = ($link) ? 'a' : 'span';
		$href = ($link) ? ' href=" ' .$link. ' "' : '';

		$r = '
			<div class="llms-progress">
				<div class="progress__indicator">' . sprintf( __( '%s%%', 'lifterlms' ), $progress ) . '</div>
					<div class="llms-progress-bar">
					<div class="progress-bar-complete" style="width:' . $progress . '%"></div>
				</div>
			</div>';

		if ($button) {
			$r .= '<' . $tag . ' class="llms-button llms-purchase-button"'. $href .'>' . sprintf( __( 'Continue (%s%%)', 'lifterlms' ), $progress ) . '</' . $tag . '>';
		}

		if ($echo) {
			echo $r;
		} else {
			return $r;
		}
	}

}

/**
 * Is template filtered
 * @return boolean [template filtered?]
 */
if ( ! function_exists( 'is_filtered' ) ) {

	function is_filtered() {
		global $_chosen_attributes;

		return apply_filters( 'lifterlms_is_filtered', ( sizeof( $_chosen_attributes ) > 0 || ( isset( $_GET['max_price'] ) && isset( $_GET['min_price'] ) ) ) );
	}
}

/**
 * Is Course Category Check
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
 * This function should be used in place of deprecated "is_shop" which will conflict with WooCommerce and cause issues when WooCommerce is installed with LifterLMS
 * @since  1.4.4
 * @return boolean
 */
if ( ! function_exists( 'is_llms_shop' ) ) {

	function is_llms_shop() {

		return ( ( is_post_type_archive( 'course' ) ) || ( is_single() && is_page( llms_get_page_id( 'shop' ) ) ) ) ? true : false;

	}

}


/**
 * Is Memberhsip Archive Page
 * @return boolean [Is Membership Archive?]
 */
if ( ! function_exists( 'is_memberships' ) ) {

	function is_memberships() {
		return ( is_post_type_archive( 'llms_membership' ) || is_page( llms_get_page_id( 'memberships' ) ) ) ? true : false;
	}
}

/**
 * Is Account Page
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
* @return boolean
*/
if ( ! function_exists( 'is_lesson' ) ) {

	function is_lesson() {
		return ( get_post_type() == 'lesson' ) ? true : false;
	}

}

/**
 * Determine if current post is a lifterLMS Quiz
 * @return boolean
 */
if ( ! function_exists( 'is_quiz' ) ) {

	function is_quiz() {
		return ( get_post_type() == 'llms_quiz' ) ? true : false;
	}

}

/**
 * Product Short Description Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_short_description' ) ) {

	function lifterlms_template_loop_short_description() {
		llms_get_template( 'loop/short-description.php' );
	}
}

/**
 * product Price Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_price' ) ) {

	function lifterlms_template_loop_price() {
		llms_get_template( 'loop/price.php' );
	}
}

/**
 * Lesson Length Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_length' ) ) {

	function lifterlms_template_loop_length() {
		llms_get_template( 'loop/length.php' );
	}
}

/**
 * Course Difficulty Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_difficulty' ) ) {

	function lifterlms_template_loop_difficulty() {
		llms_get_template( 'loop/difficulty.php' );
	}
}

/**
 * Product Thumbnail Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_course_thumbnail' ) ) {

	function lifterlms_template_loop_course_thumbnail() {
		llms_get_template( 'loop/featured-image.php' );
	}
}

/**
 * product View Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_loop_view_link' ) ) {

	function lifterlms_template_loop_view_link() {
		llms_get_template( 'loop/view-link.php' );
	}
}

/**
 * Course Thumbnail Template Include
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
 * Get Featured Image Banner
 * @param  int $post_id [ID of the post]
 * @return string [url of featured image full size]
 */
if ( ! function_exists( 'lifterlms_get_featured_image_banner' ) ) {

	function lifterlms_get_featured_image_banner( $post_id ) {
		if (get_option( 'lifterlms_course_display_banner' ) == 'yes') {
			if ( has_post_thumbnail( $post_id ) ) {
				return llms_featured_img( $post_id, 'full' );
			}
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

	return apply_filters( 'lifterlms_placeholder_img', '<img src="' . llms_placeholder_img_src() . '" alt="Placeholder" class="llms-course-image llms-placeholder wp-post-image" />' );
}

/**
 * Get the featured image
 *
 * @access public
 * @return string
 */
function llms_featured_img( $post_id, $size ) {
	$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );

	return apply_filters( 'lifterlms_featured_img', '<img src="' . $img[0] . '" alt="Placeholder" class="llms-course-image llms-featured-imaged wp-post-image" />' );
}

/**
 * Global Content Wrapper Start Template
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_output_content_wrapper' ) ) {

	function lifterlms_output_content_wrapper() {
		llms_get_template( 'global/wrapper-start.php' );
	}
}

/**
 * Global Content Wrapper End Template
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_output_content_wrapper_end' ) ) {

	function lifterlms_output_content_wrapper_end() {
		llms_get_template( 'global/wrapper-end.php' );
	}
}

/**
 * Sidebar Template
 * @return [type] [description]
 */
if ( ! function_exists( 'lifterlms_get_sidebar' ) ) {

	function lifterlms_get_sidebar() {
		llms_get_template( 'global/sidebar.php' );
	}
}


/**
 * Product Short Description Template Include
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_my_account_navigation' ) ) {

	function lifterlms_template_my_account_navigation() {
		llms_get_template( 'myaccount/my-navigation.php' );
	}
}




/**
 * Is LifterLMS check
 * Checks if archive post type is associated with lifterLMS
 * @return [type] [description]
 */
if ( ! function_exists( 'is_lifterlms' ) ) {
	function is_lifterlms() {
		return apply_filters( 'is_lifterlms', ( is_llms_shop() || is_course_taxonomy() || is_course() || is_lesson() ) ? true : false );
	}
}

/**
 * Is Course Tax
 * @return bool [Is Tax of Course?]
 */
if ( ! function_exists( 'is_course_taxonomy' ) ) {

	function is_course_taxonomy() {
		return is_tax( get_object_taxonomies( 'course' ) );
	}

}

/**
 * Is Course Check
 * @return bool [Is Post Type Course?]
 */
if ( ! function_exists( 'is_course' ) ) {

	function is_course() {
		return is_singular( array( 'course' ) );
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
 * @return string
 */
function llms_person_redeem_voucher_url() {

	$url = llms_get_endpoint_url( 'redeem-voucher', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );

	return apply_filters( 'lifterlms_person_redeem_voucher_url', $url );

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
	$option_prefix = 'lifterlms_gateway_enable_';
	$options = array(
			'paypal'
	);

	foreach ( $options as $option ) {
		$single_option = '';

		$single_option = get_option( $option_prefix . $option, 'no' );

		if ( $single_option === 'yes' ) {

				array_push( $_available_options, $option );
		}

		llms_get_template( 'checkout/' . $option . '.php' );

	}
}

/**
 * Get Product
 * @param  boolean $the_product [Is product class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_product( $the_product = false, $args = array() ) {
	return LLMS()->course_factory->get_product( $the_product, $args );
}

/**
 * Get Quiz
 * @param  boolean $the_quiz [Is Quiz class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_quiz( $the_quiz = false, $args = array() ) {
	return LLMS()->course_factory->get_quiz( $the_quiz, $args );
}

/**
 * Get Question
 * @param  boolean $the_question [Is question class init?]
 * @param  array   $args        [class init args]
 * @return new instance of class
 */
function llms_get_question( $the_question = false, $args = array() ) {
	return LLMS()->course_factory->get_question( $the_question, $args );
}

/**
 * Paginate Courses on Course Archive by llms setting
 * @param  object / $query / global $wp_query query args
 * @return object / $query
 */
function llms_courses_per_page( $query ) {
	if ( ! is_admin() && is_llms_shop() && $query->is_main_query() ) {
		$per_page = get_option( 'lifterlms_shop_courses_per_page', 10 );
		$query->query_vars['posts_per_page'] = $per_page;
	}
	return $query;
}
add_filter( 'pre_get_posts', 'llms_courses_per_page' );


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
 * Set Course and Membership to order by order instead of title
 * @param  [obj] $vars [query object]
 * @return [object]       [query object]
 */
function llms_custom_archive_order( $vars ) {
	if ( ! is_admin() && isset( $vars['post_type'] ) ) {

		if ( $vars['post_type'] === 'course' || $vars['post_type'] === 'membership' ) {
			$vars['orderby'] = 'menu_order';
			$vars['order'] = 'ASC';
		}

	}

	return $vars;
}
add_filter( 'request', 'llms_custom_archive_order' );

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

	foreach ($keys as $key) {
		$new[ $key ] = $array[ $key ];
	}

	$array = $new;

	return true;
}

/**
 * Get Image size for custom image sizes
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
