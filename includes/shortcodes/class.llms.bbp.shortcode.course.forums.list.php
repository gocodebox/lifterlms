<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_bbp_course_forums]
 *
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_BBP_Shortcode_Course_Forums_List extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_bbp_course_forums';

	/**
	 * Retrive the forum ids associated with the course
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_forums() {

		$course = llms_get_post( $this->get_attribute( 'course_id' ) );
		if ( $course ) {
			return $course->get( 'bbp_forum_ids' );
		}

		return array();

	}

	/**
	 * Call the template function for the course element
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
				<?php endforeach;
				echo '</ul>';
			echo '</div>';
		}

	}

}

return LLMS_BBP_Shortcode_Course_Forums_List::instance();
