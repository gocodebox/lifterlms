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

		if ( is_search() ) {
			$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'lifterlms' ), get_search_query() );

			if ( get_query_var( 'paged' ) )
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'lifterlms' ), get_query_var( 'paged' ) );

		} elseif ( is_tax() ) {

			$page_title = single_term_title( "", false );

		} else {

			$shop_page_id = llms_get_page_id( 'shop' );
			$page_title   = get_the_title( $shop_page_id );

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
	 * @return null
	 */
	function lifterlms_course_progress_bar($progress,$link=false) {

		$tag = ($link) ? 'a' : 'span';
		$href = ($link) ? ' href=" ' .$link. ' "' : '';

		echo '
			<div class="llms-progress">
				<div class="progress__indicator">' . sprintf( __( '%s%%', 'lifterlms' ), $progress ) . '</div>
					<div class="progress-bar">
					<div class="progress-bar-complete" style="width:' . $progress . '%"></div>
				</div>
			</div>
			<' . $tag . ' class="llms-button llms-purchase-button"'. $href .'>' . sprintf( __( 'Continue (%s%%)', 'lifterlms' ), $progress ) . '</' . $tag . '>
		';
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

	function lifterlms_get_course_thumbnail( $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $post;

		if ( has_post_thumbnail() )
			return get_the_post_thumbnail( $post->ID, $size, array('class' => 'llms-course-image') );
		elseif ( llms_placeholder_img_src() )
			return llms_placeholder_img( $size );
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
function llms_placeholder_img( $size = 'shop_thumbnail' ) {
	$dimensions = llms_get_image_size( $size );

	return apply_filters('lifterlms_placeholder_img', '<img src="' . llms_placeholder_img_src() . '" alt="Placeholder" width="' . esc_attr( $dimensions['width'] ) . '" class="llms-course-image llms-placeholder wp-post-image" height="' . esc_attr( $dimensions['height'] ) . '" />' );
}

/**
 * Get an image size.
 *
 * @param string $image_size
 * @return array
 */
function llms_get_image_size( $image_size ) {
	if ( in_array( $image_size, array( 'shop_thumbnail', 'shop_catalog', 'shop_single' ) ) ) {
		$size           = get_option( $image_size . '_image_size', array() );
		$size['width']  = isset( $size['width'] ) ? $size['width'] : '300';
		$size['height'] = isset( $size['height'] ) ? $size['height'] : '300';
		$size['crop']   = isset( $size['crop'] ) ? $size['crop'] : 1;
	} else {
		$size = array(
			'width'  => '300',
			'height' => '300',
			'crop'   => 1
		);
	}

	return apply_filters( 'lifterlms_get_image_size_' . $image_size, $size );
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
		return apply_filters( 'is_lifterlms', ( is_shop() || is_course_taxonomy() || is_course() ) ? true : false );
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



