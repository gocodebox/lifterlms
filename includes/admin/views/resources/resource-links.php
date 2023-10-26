<?php
/**
 * Resource links meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Resources
 *
 * @since 7.4.1
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-resource-links">
	<div class="llms-list">
		<h3><span class="dashicons dashicons-admin-post"></span> <?php esc_html_e( 'Key Documentation', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/docs/shortcodes/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Shortcodes', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/what-payment-gateways-can-i-use-with-lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'LifterLMS Payment Gateways', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/order-management/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Managing Orders', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/getting-started-with-lifterlms-and-woocommerce/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Integrating WooCommerce', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/membership-auto-enrollment/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Selling Bundled Courses', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/use-drip-content-lifterlms-lessons/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Dripping Content', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/use-lifterlms-language-english/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Translating LifterLMS', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/changes-to-the-wordpress-admin-for-lifterlms-5-0/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Editing Registration Forms', 'lifterlms' ); ?></a></li>
		</ul>
		<a class="llms-button-secondary" href="https://lifterlms.com/docs/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Knowledge Base', 'lifterlms' ); ?></a>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'For Developers', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://make.lifterlms.com/category/release-notes/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Changelogs', 'lifterlms' ); ?></a></li>
			<li><a href="https://developer.lifterlms.com/reference/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Code Reference', 'lifterlms' ); ?></a></li>
			<li><a href="https://developer.lifterlms.com/rest-api/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'REST API', 'lifterlms' ); ?></a></li>
			<li><a href="https://developer.lifterlms.com/cli/commands?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'LLMS-CLI', 'lifterlms' ); ?></a></li>
			<li><a href="https://github.com/gocodebox/lifterlms-cs?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Coding Standards', 'lifterlms' ); ?></a></li>
			<li><a href="https://github.com/gocodebox/lifterlms?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Github', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/contributing-to-lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Contribute', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/slack/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Developer Slack Community', 'lifterlms' ); ?></a></li>
		</ul>
		<a class="llms-button-secondary" href="https://developer.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Developer Resources', 'lifterlms' ); ?></a>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-hammer"></span> <?php esc_html_e( 'Common Hang-ups', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/docs/using-the-lifterlms-my-account-page-as-the-wordpress-front-page/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Student Dashboard as Front Page', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/caching-issues-faqs/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Caching Conflicts', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/why-do-i-get-a-notice-saying-my-license-key-is-no-longer-valid-and-was-deactivated/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'License Key Deactivated', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/a-guide-to-understanding-and-fixing-email-issues-in-lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Emails Not Sending', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/docs/rerun-lifterlms-setup-wizard/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Rerun Set Up Wizard', 'lifterlms' ); ?></a></li>
		</ul>
		<a class="llms-button-action" href="https://lifterlms.com/my-account/my-tickets/new/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Contact Support', 'lifterlms' ); ?></a>
	</div>
</div>
<hr />
<div class="llms-resource-links">
	<div class="llms-list">
		<h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Courses &amp; Case Studies', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://academy.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'LifterLMS Quickstart Course', 'lifterlms' ); ?></a></li>
			<li><a href="https://academy.lifterlms.com/course/the-complete-wordpress-for-beginners-masterclass/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'WordPress 101', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/success/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'LifterLMS Case Studies', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/lifterlms-webinars/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'LifterLMS Webinars', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-heart"></span> <?php esc_html_e( 'Community Resources', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/community-events/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Virtual Events', 'lifterlms' ); ?></a></li>
			<li><a href="https://www.facebook.com/lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook Group', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/experts/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'Experts for Hire', 'lifterlms' ); ?></a></li>
			<li><a href="https://wordpress.org/support/plugin/lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'WordPress Forums', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-plugins-checked"></span> <?php esc_html_e( 'Third Party Stuff', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/recommended-resources/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page#third-party-lifterlms-add-ons" target="_blank" rel="noopener"><?php esc_html_e( 'Third Party Add-ons', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/recommended-resources/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page#forms-plugins" target="_blank" rel="noopener"><?php esc_html_e( 'Form Plugins', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/recommended-resources/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page#email-marketing-crm" target="_blank" rel="noopener"><?php esc_html_e( 'Email Marketing', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/recommended-resources/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page#backups-and-security" target="_blank" rel="noopener"><?php esc_html_e( 'Backups and Security', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/recommended-resources?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page" target="_blank" rel="noopener"><?php esc_html_e( 'More Recommendations', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
</div>
