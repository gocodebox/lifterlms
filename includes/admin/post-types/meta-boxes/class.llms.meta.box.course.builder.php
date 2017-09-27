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
		$this->title = __( 'Course Builder', 'lifterlms' );
		$this->screens = array( 'course' );
		$this->context = 'side';
		$this->capability = 'edit_course';

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

	/**
	 * Override the output method to output a button
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function output() {
		$url = add_query_arg( array(
			'page' => 'llms-course-builder',
			'course_id' => $this->post->ID,
		), admin_url( 'admin.php' ) );
		?>
		<br class="clear">
		<a class="llms-button-primary large" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Launch Course Builder', 'lifterlms' ); ?></a>
		<br class="clear">
		<br class="clear">
		<?php
	}

}

return new LLMS_Metabox_Course_Builder();
