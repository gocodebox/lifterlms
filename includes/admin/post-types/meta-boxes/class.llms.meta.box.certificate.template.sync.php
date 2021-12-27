<?php
/**
 * Certificate template sync meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Certificate template sync meta box class.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Certificate_Template_Sync extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function configure() {

		$this->id      = 'certificate_template_sync';
		$this->screens = array(
			'llms_certificate',
		);
		$this->title   = sprintf(
			// Translators: %1$s = Awarded certificate post type plural label.
			__( 'Sync %1$s', 'lifterlms' ),
			get_post_type_object( 'llms_my_certificate' )->labels->name
		);
		$this->context = 'side';

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
				// Translators: %1$s = Certificate template post type singular label, %2$s = Awarded certificate post type plural label.
				__( 'This %1$s has no %2$s to sync.', 'lifterlms' ),
				strtolower( get_post_type_object( 'llms_certificate' )->labels->singular_name ),
				strtolower( get_post_type_object( 'llms_my_certificate' )->labels->name )
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

		$awarded_certificates_number = ( new LLMS_Awards_Query(
			array(
				'fields'    => 'ids',
				'templates' => $this->post->ID,
				'per_page'  => 1,
				'status'    => array(
					'publish',
					'future',
				),
				'type'      => 'certificate',
			)
		) )->get_found_results();

		if ( ! $awarded_certificates_number ) {
			return '';
		}

		$base_url = remove_query_arg( 'action' ); // Current url without 'action' arg.
		$sync_url = add_query_arg(
			'action',
			'sync_awarded_certificates',
			wp_nonce_url( $base_url, 'llms-cert-sync-actions', '_llms_cert_sync_actions_nonce' )
		);

		$awarded_certificate_label = strtolower(
			( $awarded_certificates_number > 1 ) ? get_post_type_object( 'llms_my_certificate' )->labels->name : get_post_type_object( 'llms_my_certificate' )->labels->singular_name
		);

		// Translators: %1$d = Number of awarded certificates, %2$s = Awarded certificate post type label (singular or plural).
		$sync_alert = sprintf(
			__( 'This action will replace the current title, content, background etc. of %1$d %2$s with the ones of this template.\nAre you sure you want to proceed?', 'lifterlms' ),
			$awarded_certificates_number,
			$awarded_certificate_label
		);
		$on_click   = "return confirm('${sync_alert}')";

		return sprintf(
			'<p>%4$s</p><p style="text-align:right;margin:1em 0"><a href="%1$s" class="llms-button-primary sync-action full" onclick="%2$s" style="box-sizing:border-box;">%3$s</a></p>',
			$sync_url,
			$on_click,
			__( 'Sync', 'lifterlms' ),
			sprintf(
				// Translators: %1$d = Number of awarded certificates, %2$s = Awarded certificate post type label (singular or plural), %3$s = Certificate template post type singular label.
				__( 'Sync %1$d %2$s with this %3$s.', 'lifterlms' ),
				$awarded_certificates_number,
				$awarded_certificate_label,
				strtolower( get_post_type_object( 'llms_certificate' )->labels->singular_name )
			)
		);

	}

}
