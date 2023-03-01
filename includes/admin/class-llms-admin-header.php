<?php
/**
 * LLMS_Admin_Header class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Header UI.
 *
 * @since [version]
 */
class LLMS_Admin_Header {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'in_admin_header', array( $this, 'admin_header' ) );
	}

	/**
	 * Show admin header banner on LifterLMS admin screens.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function admin_header() {

		// Assume we should not show our header.
		$show_header = false;

		// Get the current screen and determine if we should show the header.
		$current_screen = get_current_screen();

		// Show header on our custom post types in admin, but not on the block editor.
		if (
			isset( $current_screen->post_type ) &&
			in_array( $current_screen->post_type, array( 'course', 'lesson', 'llms_review', 'llms_membership', 'llms_engagement', 'llms_order', 'llms_coupon', 'llms_voucher', 'llms_form', 'llms_achievement', 'llms_my_achievement', 'llms_certificate', 'llms_my_certificate', 'llms_email' ), true ) &&
			false === $current_screen->is_block_editor
		) {
			$show_header = true;
		}

		// Show header on our settings pages.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- No nonce verification needed here
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- No sanitization needed here, we're not gonna use this value other than for checks
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- No unslash needed here, we're not gonna use this value other than for checks
		if ( ! empty( $_GET['page'] ) && str_starts_with( $_GET['page'], 'llms-' ) ) {
			$show_header = true;
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Exclude the wizard.
		if ( ! empty( $_GET['page'] ) && 'llms-setup' === $_GET['page'] ) {
			$show_header = false;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Don't show header on the Course Builder.
		if ( 'admin_page_llms-course-builder' === $current_screen->base ) {
			$show_header = false;
		}

		// Conditionally show our header.
		if ( ! empty( $show_header ) ) { ?>
			<header class="llms-header">
				<div class="llms-inside-wrap">
					<img class="lifterlms-logo" src="<?php echo llms()->plugin_url(); ?>/assets/images/lifterlms-logo-black.png" alt="<?php esc_attr_e( 'LifterLMS Logo', 'lifterlms' ); ?>">
					<div class="llms-meta">
						<div class="llms-meta-left">
							<span class="llms-version"><?php echo sprintf( __( 'Version: %s', 'lifterlms' ), llms()->version ); ?></span>
						</div>
						<div class="llms-meta-right">
							<?php
							// Show a license link in header if we aren't on the Add-ons screen.
							$screen = get_current_screen();
							if ( 'lifterlms_page_llms-add-ons' !== $screen->id ) {
								?>
								<span class="llms-license">
									<?php
									// Get active keys for this site.
									$my_keys = llms_helper_options()->get_license_keys();

									if ( empty( $my_keys ) ) {
										$license_class = 'llms-license-none';
										$license_label = __( 'No License', 'lifterlms' );
									} else {
										$license_class = 'llms-license-active';
										$license_label = __( 'My License Keys', 'lifterlms' );
									}
									?>
									<a class="<?php echo esc_attr( $license_class ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ); ?>"><?php echo esc_html( $license_label ); ?></a>
								</span>
								<?php
							}
							?>
							<span class="llms-support">
								<a href="https://lifterlms.com/my-account/my-tickets/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Support" target="_blank"><?php esc_html_e( 'Get Support', 'lifterlms' ); ?></a>
							</span>
						</div>
					</div>
				</div>
			</header>
			<?php
		}
	}
}

return new LLMS_Admin_Header();
