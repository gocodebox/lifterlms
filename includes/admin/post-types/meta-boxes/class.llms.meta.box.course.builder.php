<?php
/**
 * Course Builder Metabox
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Metabox_Course_Builder extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function configure() {

		$this->id = 'course_builder';
		$this->title = __( 'Course Syllabus', 'lifterlms' );
		$this->screens = array( 'course' );
		$this->context = 'side';

	}

	/**
	 * This metabox has no options
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_fields() {
		return array();
	}

	public function output() {
		?>
		<br class="clear">
		<a class="llms-button-primary large" href="#llms-launch-builder"><?php _e( 'Launch Course Builder', 'lifterlms' ); ?></a>
		<br class="clear">
		<br class="clear">
		<?php
	}

}

return new LLMS_Metabox_Course_Builder();
