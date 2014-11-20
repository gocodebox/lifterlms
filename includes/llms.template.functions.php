<?php
/**
* Front end template functions
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) exit;



function llms_template_redirect() {
	global $wp_query, $wp;

	// When default permalinks are enabled, redirect shop page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == "" && $_GET['page_id'] == llms_get_page_id( 'shop' ) ) {
		wp_safe_redirect( get_post_type_archive_link('course') );
		exit;
	}
	// When default permalinks are enabled, redirect memberships page to post type archive url
	if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == "" && $_GET['page_id'] == llms_get_page_id( 'memberships' ) ) {
		wp_safe_redirect( get_post_type_archive_link('llms_membership') );
		exit;
	}
}
add_action( 'template_redirect', 'llms_template_redirect' );


if ( ! function_exists( 'lifterlms_template_single_featured_image' ) ) {

	function lifterlms_template_single_featured_image() {

		llms_get_template( 'course/featured-image.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_title' ) ) {

	function lifterlms_template_single_title() {

		llms_get_template( 'course/title.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_short_description' ) ) {

	function lifterlms_template_single_short_description() {

		llms_get_template( 'course/short-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_course_content' ) ) {

	function lifterlms_template_single_course_content() {
		global $post;
		$page_restricted = llms_page_restricted($post->ID);

		if ( $page_restricted['is_restricted'] ) {
			llms_get_template( 'course/short-description.php' );
		}
		else {
			llms_get_template( 'course/full-description.php' );
		}

	}
}

if ( ! function_exists( 'lifterlms_template_single_full_description' ) ) {

	function lifterlms_template_single_full_description() {

		llms_get_template( 'lesson/full-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_membership_full_description' ) ) {

	function lifterlms_template_single_membership_full_description() {

		llms_get_template( 'membership/full-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_price' ) ) {

	function lifterlms_template_single_price() {
		global $post;

		if ($post->post_type == 'course') {
			llms_get_template( 'course/price.php' );
		}
		elseif ($post->post_type == 'llms_membership') {
	
			llms_get_template( 'membership/price.php' );
		}
	}
}

if ( ! function_exists( 'lifterlms_template_single_lesson_length' ) ) {

	function lifterlms_template_single_lesson_length() {

		llms_get_template( 'course/lesson_length.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_purchase_link' ) ) {

	function lifterlms_template_single_purchase_link() {
		global $post;

		if ($post->post_type == 'course') {
			llms_get_template( 'course/purchase-link.php' );
		}
		elseif ($post->post_type == 'llms_membership') {
	
			llms_get_template( 'membership/purchase-link.php' );
		}
	}
}

if ( ! function_exists( 'lifterlms_template_single_video' ) ) {

	function lifterlms_template_single_video() {

		llms_get_template( 'course/video.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_lesson_video' ) ) {

	function lifterlms_template_single_lesson_video() {

		llms_get_template( 'lesson/video.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_audio' ) ) {

	function lifterlms_template_single_audio() {

		llms_get_template( 'course/audio.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_lesson_audio' ) ) {

	function lifterlms_template_single_lesson_audio() {

		llms_get_template( 'lesson/audio.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_difficulty' ) ) {

	function lifterlms_template_single_difficulty() {

		llms_get_template( 'course/difficulty.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_syllabus' ) ) {

	function lifterlms_template_single_syllabus() {

		llms_get_template( 'course/syllabus.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_section_syllabus' ) ) {

	function lifterlms_template_section_syllabus() {

		llms_get_template( 'course/section_syllabus.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_parent_course' ) ) {

	function lifterlms_template_single_parent_course() {

		llms_get_template( 'course/parent_course.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_parent_course' ) ) {

	function lifterlms_template_single_parent_course() {

		llms_get_template( 'course/parent_course.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_complete_lesson_link' ) ) {

	function lifterlms_template_complete_lesson_link() {

		llms_get_template( 'course/complete-lesson-link.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_lesson_navigation' ) ) {

	function lifterlms_template_lesson_navigation() {

		llms_get_template( 'course/lesson-navigation.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_membership_title' ) ) {

	function lifterlms_template_single_membership_title() {

		llms_get_template( 'membership/title.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_quiz_attempts' ) ) {

	function lifterlms_template_quiz_attempts() {

		llms_get_template( 'quiz/attempts.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_passing_percent' ) ) {

	function lifterlms_template_passing_percent() {

		llms_get_template( 'quiz/passing-percent.php' );
	}
}
if ( ! function_exists( 'lifterlms_template_start_button' ) ) {

	function lifterlms_template_start_button() {

		llms_get_template( 'quiz/start-button.php' );
	}
}



/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_course_data( $post ) {
	if  ( ! is_admin() ) {

		if ($post->post_type == 'course') {
			unset( $GLOBALS['course'] );

			if ( is_int( $post ) )
				$post = get_post( $post );

			if ( empty( $post->post_type ) )
				return;

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
	if  ( ! is_admin() ) {

		if ($post->post_type == 'llms_quiz') {
			unset( $GLOBALS['quiz'] );

			if ( is_int( $post ) )
				$post = get_post( $post );

			if ( empty( $post->post_type ) )
				return;

				$GLOBALS['quiz'] = llms_get_quiz( $post );

				return $GLOBALS['quiz'];
		}
	}

}
add_action( 'the_post', 'llms_setup_quiz_data' );

/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_product_data( $post ) {

	if  ( ! is_admin() ) {

		if ($post->post_type == 'course' || $post->post_type == 'llms_membership' ) {
			unset( $GLOBALS['product'] );

			if ( is_int( $post ) )
				$post = get_post( $post );

			if ( empty( $post->post_type ) )
				return;

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
	if  ( ! is_admin() ) {

		if ($post->post_type == 'lesson') {
			unset( $GLOBALS['lesson'] );
			//unset( $GLOBALS['course'] );

			if ( is_int( $post ) )
				$post = get_post( $post );

			if ( empty( $post->post_type ) )
				return;


			$courseid = get_post_meta( $post->ID, '_parent_course');

			if ( isset($courseid) ) {
			$parent_course = get_post( $courseid[0] );
			}

			$GLOBALS['lesson'] = get_lesson( $post );

			llms_setup_course_data( $parent_course );
			//$GLOBALS['course'] = get_course( $parent_course );


			return $GLOBALS['lesson'];
		}
	}

}
add_action( 'the_post', 'llms_setup_lesson_data' );


function llms_price( $price, $args = array() ) {

	return $price;
}

/**
 * Returns post array of data for sections associated with a course
 *
 * @param array
 * @return array
 */
function get_section_data ($sections) {
	global $post;
	$html = '';
	$args = array(
	    'post_type' => 'section',
	    'post_status' => 'publish',
	    'nopaging' 		=> true,
	);

	$query = get_posts( $args );

	$array = array();

	foreach($sections as $key => $value) :

		foreach($query as $post) :

			if ($value == $post->ID) {
				$array[$post->ID] = $post;
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
function get_lesson_data ($lessons) {
	global $post;
	$html = '';
	$args = array(
	    'post_type' => 'lesson',
	    'post_status' => 'publish',
	    'nopaging' 		=> true,
	);

	$query = get_posts( $args );

	$array = array();


	foreach($lessons as $key => $value) :

		foreach($query as $post) :

			if ($value == $post->ID) {
				$array[$value] = $post;
			}

		endforeach;

	endforeach;

	return $array;
}


if ( ! function_exists( 'lifterlms_page_title' ) ) {

	function lifterlms_page_title( $echo = true ) {
		$page_title = '';

		if ( is_search() ) {
			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'lifterlms' ), get_search_query() );

			if ( get_query_var( 'paged' ) )
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'lifterlms' ), get_query_var( 'paged' ) );

		} elseif ( is_tax() ) {

			$page_title = single_term_title( "", false );

		} else {

			//$shop_page_id = llms_get_page_id( 'shop' );
			//$page_title   = get_the_title( $shop_page_id );

		}

		$page_title = apply_filters( 'lifterlms_page_title', $page_title );

		if ( $echo )
	    	echo $page_title;
	    else
	    	return $page_title;
	}
}
if ( ! function_exists( 'lifterlms_membership_loop_start' ) ) {

	function lifterlms_membership_loop_start( $echo = true ) {
		ob_start();
		llms_get_template( 'loop/loop-start.php' );
		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}
if ( ! function_exists( 'lifterlms_membership_loop_end' ) ) {

	function lifterlms_membership_loop_end( $echo = true ) {
		ob_start();

		llms_get_template( 'loop/loop-end.php' );

		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}
if ( ! function_exists( 'lifterlms_course_loop_start' ) ) {

	function lifterlms_course_loop_start( $echo = true ) {
		ob_start();
		llms_get_template( 'loop/loop-start.php' );
		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}
if ( ! function_exists( 'lifterlms_course_loop_end' ) ) {

	function lifterlms_course_loop_end( $echo = true ) {
		ob_start();

		llms_get_template( 'loop/loop-end.php' );

		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}

if ( ! function_exists( 'lifterlms_course_progress_bar') ) {
	/**
	 * Outputs the html for a progress bar
	 * @param  int / $progress / percent completion
	 * @param  string / $link / permalink to link the button to, if false will output a span with no href
	 * @param  bool / $button / output a button with the link
	 * @param  bool / $echo / true will echo content, false will return it
	 * @return null / html content
	 */
	function lifterlms_course_progress_bar($progress,$link=false,$button=true,$echo = true) {

		$tag = ($link) ? 'a' : 'span';
		$href = ($link) ? ' href=" ' .$link. ' "' : '';

		$r = '
			<div class="llms-progress">
				<div class="progress__indicator">' . sprintf( __( '%s%%', 'lifterlms' ), $progress ) . '</div>
					<div class="progress-bar">
					<div class="progress-bar-complete" style="width:' . $progress . '%"></div>
				</div>
			</div>';


		if($button) {
			$r .= '<' . $tag . ' class="llms-button llms-purchase-button"'. $href .'>' . sprintf( __( 'Continue (%s%%)', 'lifterlms' ), $progress ) . '</' . $tag . '>';	
		}

		if($echo) {
			echo $r;
		} else {
			return $r;
		}
	}

}

if ( ! function_exists( 'lifterlms_course_subcategories' ) ) {

	function lifterlms_course_subcategories( $args = array() ) {
		global $wp_query;

		$defaults = array(
			'before'  => '',
			'after'  => '',
			'force_display' => false
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Main query only
		if ( ! is_main_query() && ! $force_display ) return;

		// Don't show when filtering, searching or when on page > 1 and ensure we're on a course archive
		if ( is_search() || is_filtered() || is_paged() || ( ! is_course_category() && ! is_shop() ) ) return;

		// Check categories are enabled
		if ( is_shop() && get_option( 'lifterlms_shop_page_display' ) == '' ) return;

		// Find the category + category parent, if applicable
		$term 			= get_queried_object();
		$parent_id 		= empty( $term->term_id ) ? 0 : $term->term_id;

		if ( is_course_category() ) {
			$display_type = get_lifterlms_term_meta( $term->term_id, 'display_type', true );

			switch ( $display_type ) {
				case 'courses' :
					return;
				break;
				case '' :
					if ( get_option( 'lifterlms_category_archive_display' ) == '' )
						return;
				break;
			}
		}

		$args = apply_filters( 'lifterlms_course_subcategories_args', array(
			'child_of'		=> $parent_id,
			'menu_order'	=> 'ASC',
			'hide_empty'	=> 1,
			'hierarchical'	=> 1,
			'taxonomy'		=> 'course_cat',
			'pad_counts'	=> 1
		) );

		$course_categories     = get_categories( $args );
		$course_category_found = false;

		if ( $course_categories ) {

			foreach ( $course_categories as $category ) {

				if ( $category->parent != $parent_id ) {
					continue;
				}
				if ( $args['hide_empty'] && $category->count == 0 ) {
					continue;
				}

				if ( ! $course_category_found ) {
					// We found a category
					$course_category_found = true;
					echo $before;
				}

				llms_get_template( 'content-course_cat.php', array(
					'category' => $category
				) );

			}

		}

		// If we are hiding courses disable the loop and pagination
		if ( $course_category_found ) {
			if ( is_course_category() ) {
				$display_type = get_lifterlms_term_meta( $term->term_id, 'display_type', true );

				switch ( $display_type ) {
					case 'subcategories' :
						$wp_query->post_count = 0;
						$wp_query->max_num_pages = 0;
					break;
					case '' :
						if ( get_option( 'lifterlms_category_archive_display' ) == 'subcategories' ) {
							$wp_query->post_count = 0;
							$wp_query->max_num_pages = 0;
						}
					break;
				}
			}
			if ( is_shop() && get_option( 'lifterlms_shop_page_display' ) == 'subcategories' ) {
				$wp_query->post_count = 0;
				$wp_query->max_num_pages = 0;
			}

			echo $after;
			return true;
		}

	}
}

if ( ! function_exists( 'is_filtered' ) ) {

	function is_filtered() {
		global $_chosen_attributes;

		return apply_filters( 'lifterlms_is_filtered', ( sizeof( $_chosen_attributes ) > 0 || ( isset( $_GET['max_price'] ) && isset( $_GET['min_price'] ) ) ) );
	}
}

if ( ! function_exists( 'is_course_category' ) ) {


	function is_course_category( $term = '' ) {
		return is_tax( 'course_cat', $term );
	}
}

if ( ! function_exists( 'is_shop' ) ) {

	function is_shop() {
		return ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'shop' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_shop' ) ) {

	function is_memberships() {
		return ( is_post_type_archive( 'llms_membership' ) || is_page( llms_get_page_id( 'memberships' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_account_page' ) ) {

	function is_account_page() {
		return is_page( llms_get_page_id( 'myaccount' ) ) || apply_filters( 'lifterlms_is_account_page', false ) ? true : false;
	}
}


if ( ! function_exists( 'is_checkout' ) ) {

	function is_checkout() {
		return is_page( llms_get_page_id( 'checkout' ) ) ? true : false;
	}
}

if( ! function_exists( 'is_lesson') ) {

	/**
	 * Determine if current post is a lifterLMS Lesson
	 * @return boolean
	 */
	function is_lesson() {
		return ( get_post_type() == 'lesson' ) ? true : false;
	}

}

if ( ! function_exists( 'is_llms_endpoint_url' ) ) {

	function is_llms_endpoint_url( $endpoint ) {
		global $wp;

		$llms_endpoints = LLMS()->query->get_query_vars();

		if ( ! isset( $llms_endpoints[ $endpoint ] ) ) {
			return false;
		} else {
			$endpoint_var = $llms_endpoints[ $endpoint ];
		}

		return isset( $wp->query_vars[ $endpoint_var ] ) ? true : false;
	}
}

if ( ! function_exists( 'lifterlms_courses_will_display' ) ) {


	function lifterlms_courses_will_display() {
		if ( is_shop() )
			return get_option( 'lifterlms_shop_page_display' ) != 'subcategories';

		if ( ! is_course_taxonomy() )
			return false;

		if ( is_search() || is_filtered() || is_paged() )
			return true;

		$term = get_queried_object();

		if ( is_course_category() ) {
			switch ( get_lifterlms_term_meta( $term->term_id, 'display_type', true ) ) {
				case 'subcategories' :
					// Nothing - we want to continue to see if there are courses/subcats
				break;
				case 'courses' :
				case 'both' :
					return true;
				break;
				default :
					// Default - no setting
					if ( get_option( 'lifterlms_category_archive_display' ) != 'subcategories' )
						return true;
				break;
			}
		}

		// Begin subcategory logic
		global $wpdb;

		$parent_id             = empty( $term->term_id ) ? 0 : $term->term_id;
		$taxonomy              = empty( $term->taxonomy ) ? '' : $term->taxonomy;
		$courses_will_display = true;

		if ( ! $parent_id && ! $taxonomy ) {
			return true;
		}

		if ( false === ( $courses_will_display = get_transient( 'llms_courses_will_display_' . $parent_id ) ) ) {
			$has_children = $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE parent = %d AND taxonomy = %s", $parent_id, $taxonomy ) );

			if ( $has_children ) {
				// Check terms have courses inside - parents first. If courses are found inside, subcats will be shown instead of courses so we can return false.
				if ( sizeof( get_objects_in_term( $has_children, $taxonomy ) ) > 0 ) {
					$courses_will_display = false;
				} else {
					// If we get here, the parents were empty so we're forced to check children
					foreach ( $has_children as $term ) {
						$children = get_term_children( $term, $taxonomy );

						if ( sizeof( get_objects_in_term( $children, $taxonomy ) ) > 0 ) {
							$courses_will_display = false;
							break;
						}
					}
				}
			} else {
				$courses_will_display = true;
			}
		}

		set_transient( 'llms_courses_will_display_' . $parent_id, $courses_will_display, YEAR_IN_SECONDS );

		return $courses_will_display;
	}
}

if ( ! function_exists( 'lifterlms_template_loop_short_description' ) ) {

	function lifterlms_template_loop_short_description() {
		llms_get_template( 'loop/short-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_loop_price' ) ) {

	function lifterlms_template_loop_price() {
		llms_get_template( 'loop/price.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_loop_length' ) ) {

	function lifterlms_template_loop_length() {
		llms_get_template( 'loop/length.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_loop_difficulty' ) ) {

	function lifterlms_template_loop_difficulty() {
		llms_get_template( 'loop/difficulty.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_loop_course_thumbnail' ) ) {

	function lifterlms_template_loop_course_thumbnail() {
		echo lifterlms_get_course_thumbnail();
	}
}

if ( ! function_exists( 'lifterlms_template_loop_view_link' ) ) {

	function lifterlms_template_loop_view_link() {
		llms_get_template( 'loop/view-link.php' );
	}
}

if ( ! function_exists( 'lifterlms_get_course_thumbnail' ) ) {

	function lifterlms_get_course_thumbnail() {
		global $post;

		// if (!$post) {
		// 	$post = get_post($post_id);
		// }

		LLMS_log($post);
LLMS_log('lifterlms_get_course_thumbnail called');
		if ( has_post_thumbnail() ) {
			LLMS_log('it has a thumbnail');
			return lifterlms_get_featured_image( $post->ID );
		}
		elseif ( llms_placeholder_img_src() ) {
			return llms_placeholder_img( 'full' );
		}
	}
}

if ( ! function_exists( 'lifterlms_get_featured_image' ) ) {

	function lifterlms_get_featured_image( $post_id ) {
		
		if ( has_post_thumbnail($post_id) ) {

			return llms_featured_img( $post_id, 'full' );
		}
		elseif ( llms_placeholder_img_src() ) {

			return llms_placeholder_img();
		}
	}
}

if ( ! function_exists( 'llms_get_post_content' ) ) {
function llms_get_post_content( $content ) {
	$page_restricted = llms_page_restricted(get_the_id());

	global $post;
	if( ! $post instanceof WP_Post ) return $content;
		switch( $post->post_type ) {
		case 'course':
			if ( $page_restricted['is_restricted'] ) {
				add_filter('the_excerpt', array($GLOBALS['wp_embed'], 'autoembed'), 9);

				if ($post->post_excerpt) {
				add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_short_description', 10 );
				$content = '';
				LLMS_log($content);
				}
			}
			$template_before  = llms_get_template_part_contents( 'content', 'single-course-before' );
			$template_after  = llms_get_template_part_contents( 'content', 'single-course-after' );

			ob_start();
			load_template($template_before);
			$output_before = ob_get_clean();
	
			ob_start();
			load_template($template_after);
			$output_after = ob_get_clean();

			return do_shortcode($output_before . $content . $output_after);

		case 'lesson':
			if ( $page_restricted['is_restricted'] ) {
				$content = '';
				$template_before  = llms_get_template_part_contents( 'content', 'no-access-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'no-access-after' );
			}
			else {
				$template_before  = llms_get_template_part_contents( 'content', 'single-lesson-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'single-lesson-after' );
			}

			ob_start();
			load_template($template_before);
			$output_before = ob_get_clean();
	
			ob_start();
			load_template($template_after);
			$output_after = ob_get_clean();

			return do_shortcode($output_before . $content . $output_after);

		case 'llms_membership':
			$template_before  = llms_get_template_part_contents( 'content', 'single-membership-before' );
			$template_after  = llms_get_template_part_contents( 'content', 'single-membership-after' );

			ob_start();
			load_template($template_before);
			$output_before = ob_get_clean();
	
			ob_start();
			load_template($template_after);
			$output_after = ob_get_clean();

			return do_shortcode($output_before . $content . $output_after);

			case 'llms_quiz':
			$template_before  = llms_get_template_part_contents( 'content', 'single-quiz-before' );
			$template_after  = llms_get_template_part_contents( 'content', 'single-quiz-after' );

			ob_start();
			load_template($template_before);
			$output_before = ob_get_clean();
	
			ob_start();
			load_template($template_after);
			$output_after = ob_get_clean();

			return do_shortcode($output_before . $content . $output_after);

		default:
		  return $content;
		}
	}
}
 
add_filter( 'the_content', 'llms_get_post_content' );


if ( ! function_exists( 'lifterlms_get_featured_image_banner' ) ) {

	function lifterlms_get_featured_image_banner( $post_id ) {
		if (get_option('lifterlms_course_display_banner') == 'yes') {
			if ( has_post_thumbnail($post_id) ) {
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

	return apply_filters('lifterlms_placeholder_img', '<img src="' . llms_placeholder_img_src() . '" alt="Placeholder" class="llms-course-image llms-placeholder wp-post-image" />' );
}

/**
 * Get the featured image
 *
 * @access public
 * @return string
 */
function llms_featured_img( $post_id, $size ) {
	$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );

	return apply_filters('lifterlms_featured_img', '<img src="' . $img[0] . '" alt="Placeholder" class="llms-course-image llms-featured-imaged wp-post-image" />' );
}

if ( ! function_exists( 'lifterlms_output_content_wrapper' ) ) {

	function lifterlms_output_content_wrapper() {
		llms_get_template( 'global/wrapper-start.php' );
	}
}
if ( ! function_exists( 'lifterlms_output_content_wrapper_end' ) ) {

	function lifterlms_output_content_wrapper_end() {
		llms_get_template( 'global/wrapper-end.php' );
	}
}

if ( ! function_exists( 'lifterlms_get_sidebar' ) ) {

	function lifterlms_get_sidebar() {
		llms_get_template( 'global/sidebar.php' );
	}
}

if ( ! function_exists( 'is_lifterlms' ) ) {
	function is_lifterlms() {
		return apply_filters( 'is_lifterlms', ( is_shop() || is_course_taxonomy() || is_course() || is_lesson() ) ? true : false );
	}
}

if ( ! function_exists( 'is_course_taxonomy' ) ) {

	function is_course_taxonomy() {
		return is_tax( get_object_taxonomies( 'course' ) );
	}
}

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

// function llms_lostpassword_url() {
//     return llms_get_endpoint_url( 'lost-password', '', get_permalink( llms_get_page_id( 'myaccount' ) ) );
// }
// add_filter( 'lostpassword_url',  'llms_lostpassword_url', 10, 0 );

function get_product_query_var( $vars ){
	$vars[] = "product";
	return $vars;
}
add_filter( 'query_vars', 'get_product_query_var' );



function get_available_payment_options() {

	$_available_options = array();
	$option_prefix = 'lifterlms_gateway_enable_';
	$options = array(
			'paypal'
	);

	foreach( $options as $option ) {
		$single_option = '';

	$single_option = get_option( $option_prefix . $option, 'no' );

		if ( $single_option  === 'yes' ) {

 			array_push($_available_options, $option);
		}

	llms_get_template( 'checkout/' . $option . '.php' );
	
	}
}



/**
 * Get page object
 *
 * @param string $the_course = false, $args = array()
 * @return array 
 */
function llms_get_product( $the_product = false, $args = array() ) {
	return LLMS()->course_factory->get_product( $the_product, $args );
}

/**
 * Get page object
 *
 * @param string $the_course = false, $args = array()
 * @return array 
 */
function llms_get_quiz( $the_quiz = false, $args = array() ) {
	return LLMS()->course_factory->get_quiz( $the_quiz, $args );
}

function tpp_posts_comments_return() {
	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;

	if ($post_id > 0) {
		$post = get_post($post_id);
		?>
		<div class="tpp_post" id="post"><?php echo $post->post_content; ?></div>
		<?php
	}
	die();
}
add_action('wp_ajax_nopriv_tpp_comments', 'tpp_posts_comments_return');



/**
 * Paginate Courses on Course Archive by llms setting
 * @param  object / $query / global $wp_query query args
 * @return object / $query
 */
function llms_courses_per_page( $query ) {
	if(!is_admin() && is_shop() && is_main_query()) {
		$per_page = get_option( 'lifterlms_shop_courses_per_page', 10 );
		$query->query_vars['posts_per_page'] = $per_page;
	}
	return $query;
}
add_filter( 'pre_get_posts', 'llms_courses_per_page' );
