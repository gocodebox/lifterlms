<?php
/**
 * Add-ons meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Dashboard
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

$view = new LLMS_Admin_AddOns();

$view->output_for_settings();
?>

<p>
	<a
		class="llms-button-primary"
		href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ); ?>"><?php esc_html_e( 'View Add-ons & more', 'lifterlms' ); ?></a>
</p>
