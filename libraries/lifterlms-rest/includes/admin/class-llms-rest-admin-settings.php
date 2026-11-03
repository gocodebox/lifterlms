<?php
/**
 * Manage admin settings pages.
 *
 * @package  LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage admin settings pages.
 *
 * @since 1.0.0-beta.1
 * @since 1.1.3 Added a notice for webhooks that won't be delivered because their topic is not allowed.
 */
class LLMS_REST_Admin_Settings {

	/**
	 * User meta key storing the list of undeliverable webhooks the user dismissed the notice for.
	 *
	 * @var string
	 */
	const UNDELIVERABLE_WEBHOOKS_DISMISSED_META = 'llms_rest_undeliverable_webhooks_notice_dismissed';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.3 Added a notice for webhooks that won't be delivered because their topic is not allowed.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_get_settings_pages', array( $this, 'add_pages' ) );
		add_action( 'admin_init', array( $this, 'maybe_dismiss_undeliverable_webhooks_notice' ) );
		add_action( 'admin_notices', array( $this, 'output_undeliverable_webhooks_notice' ) );

	}

	/**
	 * Retrieves a key identifying the current set of undeliverable webhooks.
	 *
	 * A user's dismissal only applies to the set of webhooks it was made for, so the notice returns when the set changes.
	 *
	 * @since 1.1.3
	 *
	 * @param LLMS_REST_Webhook[] $hooks Undeliverable webhooks.
	 * @return string
	 */
	protected function get_undeliverable_webhooks_key( $hooks ) {

		return implode(
			',',
			array_map(
				function ( $hook ) {
					return $hook->get_id();
				},
				$hooks
			)
		);

	}

	/**
	 * Dismisses the undeliverable webhooks notice for the current user.
	 *
	 * @since 1.1.3
	 *
	 * @return void
	 */
	public function maybe_dismiss_undeliverable_webhooks_notice() {

		if ( ! isset( $_GET['llms-rest-dismiss-webhooks-notice'], $_GET['_llms_rest_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_llms_rest_nonce'] ) ), 'llms-rest-dismiss-webhooks-notice' ) || ! current_user_can( 'manage_lifterlms_webhooks' ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), self::UNDELIVERABLE_WEBHOOKS_DISMISSED_META, $this->get_undeliverable_webhooks_key( LLMS_REST_API()->webhooks()->get_undeliverable() ) );

		llms_redirect_and_exit( remove_query_arg( array( 'llms-rest-dismiss-webhooks-notice', '_llms_rest_nonce' ) ) );

	}

	/**
	 * Outputs a dismissible notice listing webhooks that won't be delivered because their topic is not allowed.
	 *
	 * Shown to each user who can manage webhooks until they dismiss it, and again when the list of webhooks changes.
	 *
	 * @since 1.1.3
	 *
	 * @return void
	 */
	public function output_undeliverable_webhooks_notice() {

		if ( ! current_user_can( 'manage_lifterlms_webhooks' ) ) {
			return;
		}

		$hooks = LLMS_REST_API()->webhooks()->get_undeliverable();
		$key   = $this->get_undeliverable_webhooks_key( $hooks );
		if ( ! $hooks || get_user_meta( get_current_user_id(), self::UNDELIVERABLE_WEBHOOKS_DISMISSED_META, true ) === $key ) {
			return;
		}

		$links = array();
		foreach ( $hooks as $hook ) {
			$links[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $hook->get_edit_link() ), esc_html( $hook->get( 'name' ) ) );
		}

		$dismiss_url = wp_nonce_url( add_query_arg( 'llms-rest-dismiss-webhooks-notice', 1 ), 'llms-rest-dismiss-webhooks-notice', '_llms_rest_nonce' );
		?>
		<div class="notice notice-warning" style="position:relative;padding-right:38px;">
			<p>
				<?php
				printf(
					// Translators: %s = comma-separated list of webhook links.
					esc_html__( 'The following LifterLMS webhooks will not be delivered because their topic is not allowed: %s.', 'lifterlms' ),
					wp_kses_post( implode( ', ', $links ) )
				);
				?>
			</p>
			<p><?php esc_html_e( 'Webhooks using a custom "Action" topic must be saved by an administrator. Edit each webhook to change its topic, or delete it.', 'lifterlms' ); ?></p>
			<a class="notice-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>" style="text-decoration:none;">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss', 'lifterlms' ); ?></span>
			</a>
		</div>
		<?php

	}

	/**
	 * Register the REST API settings page with the LifterLMS Core.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $pages Array of settings page classes.
	 * @return array
	 */
	public function add_pages( $pages ) {

		$pages[] = include 'class-llms-rest-admin-settings-page.php';

		return $pages;

	}

}

return new LLMS_REST_Admin_Settings();
