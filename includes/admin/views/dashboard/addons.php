<?php
/**
 * Add-ons meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Dashboard
 *
 * @since 7.1.0
 * @version 7.1.0
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
