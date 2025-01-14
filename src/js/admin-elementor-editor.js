jQuery(document).ready(function($) {
	if (typeof elementor === 'undefined') {
		return;
	}

	elementor.modules.layouts.panel.pages.menu.Menu.addItem({
		name:'Course Builder',
		title:'Launch Course Builder',
		icon: 'wp-menu-image dashicons-before dashicons-welcome-learn-more',
		callback: function callback() {
			window.location.href = llms_elementor.builder_url;
		}}, 'navigate_from_page', 'finder');
});
