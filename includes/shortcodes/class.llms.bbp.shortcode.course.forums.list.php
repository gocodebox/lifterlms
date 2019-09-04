<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_bbp_course_forums]
 *
 * @since    3.12.0
 * @version  3.12.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_BBP_Shortcode_Course_Forums_List extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_bbp_course_forums';

	/**
	 * Retrieve the forum ids associated with the course
	 *
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.1
	 */
	private function get_forums() {

		global $post;

		$course = llms_get_post( $post );
		if ( $course ) {
			return $course->get( 'bbp_forum_ids' );
		}

		return array();

	}

	/**
	 * Call the template function for the course element
	 *
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	protected function template_function() {

		$forums = $this->get_forums();

		if ( $forums ) {
			echo '<div class="llms-bbp-course-forums-wrap">';
				echo '<ul class="llms-bbp-course-forums-list">';
			foreach ( $forums as $forum_id ) : ?>
					<li><a class="llms-bbp-forum-title" href="<?php bbp_forum_permalink( $forum_id ); ?>">
						<?php bbp_forum_title( $forum_id ); ?>
					</a></li>
				<?php
				endforeach;
				echo '</ul>';
			echo '</div>';
		}

	}

}

return LLMS_BBP_Shortcode_Course_Forums_List::instance();
