<?php
/**
 * Award engagement submit meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Award engagement submit meta box class.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Award_Engagement_Submit extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'submitdiv';
		$this->title    = __( 'Award', 'lifterlms' );
		$this->screens  = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);
		$this->context  = 'side';
		$this->priority = 'high';

		remove_meta_box( 'submitdiv', $this->screens, 'side' );

		add_filter( 'wp_redirect', array( $this, 'redirect_on_deletion' ) );
	}

	/**
	 * Not used because our metabox doesn't use the standard fields api.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output() {
		global $action;

		$engagement             = $this->post;
		$engagement_id          = (int) $this->post->ID;
		$engagement_type_object = get_post_type_object( $this->post->post_type );
		$can_publish            = current_user_can( $engagement_type_object->cap->publish_posts );

		include LLMS_PLUGIN_DIR . 'includes/admin/views/metaboxes/view-award-engagement-submit.php';

	}

	/**
	 * Redirect on permanet deletion from the editor.
	 *
	 * @since [version]
	 *
	 * @param string $location Redirect location.
	 * @return string
	 */
	public function redirect_on_deletion( $location ) {

		global $pagenow;

		parse_str( wp_parse_url( $location, PHP_URL_QUERY ), $_get );

		if ( 'post.php' === $pagenow && ! empty( $_get['deleted'] ) && ! empty( $_get['post_type'] ) && in_array( $_get['post_type'], $this->screens, true ) ) { //phpcs:ignore -- no need to sanitize here.
			$location = admin_url( 'admin.php?page=llms-reporting' );
		}
		return $location;

	}

}
