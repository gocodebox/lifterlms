<?php
/**
 * Award certificate sync meta box.
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
class LLMS_Meta_Box_Award_Certificate_Sync extends LLMS_Admin_Metabox {

	/**
	 * ID of the student who earned (is about to earn) the engagement.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	private $student_id;

	/**
	 * Allowed post types.
	 *
	 * @since [version]
	 *
	 * @var string[]
	 */
	private $post_types = array(
		'llms_my_achievement' => array(
			'model'          => 'LLMS_User_Achievement',
			'reporting_stab' => 'achievements',
		),
		'llms_my_certificate' => array(
			'model'          => 'LLMS_User_Certificate',
			'reporting_stab' => 'certificates',
		),
	);

	/**
	 * Configure the metabox settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function configure() {

		$this->id      = 'award_certificate_sync'; // Overrides the WordPress core one.
		$this->screens = array(
			'llms_my_certificate',
		);
		$this->title   = sprintf(
			// Translators: %1$s = Awarded certificate post type singular label.
			__( 'Sync %1$s', 'lifterlms' ),
			get_post_type_object( 'llms_my_certificate' )->labels->singular_name
		);
		$this->context  = 'side';
		$this->priority = 'high';

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

		$sync_action = $this->sync_action();

		if ( ! $sync_action ) {
			$sync_action = sprintf(
				// Translators: %1$s = Awarded certificate post type singular label, %2$s = Certificate template post type singular label.
				__( 'This %1$s has no %2$s to sync with.', 'lifterlms' ),
				strtolower( get_post_type_object( 'llms_my_certificate' )->labels->singular_name ),
				strtolower( get_post_type_object( 'llms_certificate' )->labels->singular_name )
			);
		}

		// output the html.
		echo '<div class="llms-mb-container">';
		do_action( 'llms_metabox_before_content', $this->id );
		echo $sync_action;
		do_action( 'llms_metabox_after_content', $this->id );
		echo '</div>';

	}

	/**
	 * Sync action links html.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function sync_action() {

		$certificate_model = llms_get_certificate( $this->post->ID );
		$template_id       = $certificate_model ? $certificate_model->get( 'parent' ) : false;

		if ( empty( $template_id ) || ! llms_get_certificate( $template_id, true ) ) {
			return '';
		}

		$base_url   = remove_query_arg( 'action' ); // Current url without 'action' arg.
		$sync_url   = add_query_arg(
			'action',
			'sync_awarded_certificate',
			wp_nonce_url( $base_url, 'llms-cert-sync-actions', '_llms_cert_sync_actions_nonce' )
		);
		$sync_alert = __(
			'This action will replace the current title, content, and the background image with the template ones.\nAre you sure you want to proceed?',
			'lifterlms'
		);
		$on_click   = "return confirm('${sync_alert}')";

		return sprintf(
			'<p style="text-align:right"><a href="%1$s" class="llms-button-primary sync-action small" onclick="%2$s">%3$s</a></p><p>%4$s</p>',
			$sync_url,
			$on_click,
			__( 'Sync', 'lifterlms' ),
			sprintf(
				// Translators: %1$s = Awarded certificate post type singular label, %2$s = Edit link to certificate template, %3$s = Certificate template post type singular label, %4$s = Closing anchor tag.
				__( 'Sync the %1$s with its %2$s%3$s%4$s', 'lifterlms' ),
				strtolower( get_post_type_object( 'llms_my_certificate' )->labels->singular_name ),
				'<a href="' . get_edit_post_link( $template_id ) . '" target="_blank">',
				strtolower( get_post_type_object( 'llms_certificate' )->labels->singular_name ),
				'</a>'
			)
		);

	}

}
