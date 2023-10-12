<?php
/**
 * Setup Wizard step: Welcome
 *
 * @package LifterLMS/Views/Admin/SetupWizard
 *
 * @since 4.4.4
 * @since 4.8.0 Unknown.
 * @since 7.4.0 Escape output.
 * @version 7.4.0
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;
?>

<h1><?php esc_html_e( 'Welcome to LifterLMS!', 'lifterlms' ); ?></h1>
<p><?php esc_html_e( 'Thanks for choosing LifterLMS to power your online courses! This short setup wizard will guide you through the basic settings and configure LifterLMS so you can get started creating courses faster!', 'lifterlms' ); ?></p>
<p><?php esc_html_e( 'It will only take a few minutes and it is completely optional. If you don\'t have the time now, come back later.', 'lifterlms' ); ?></p>
<?php
