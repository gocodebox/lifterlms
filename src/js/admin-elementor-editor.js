jQuery(document).ready(function($) {
	if (typeof elementor === 'undefined') {
		return;
	}

	elementor.modules.layouts.panel.pages.menu.Menu.addItem({
		name:'Course Builder',
		title:'Launch Course Builder',
		icon: 'eicon-commenting-o',
		callback: function callback() {
			window.location.href = 'https://lifter2.test/wp-admin/admin.php?page=llms-course-builder&course_id=123';
		}}, 'navigate_from_page', 'finder');
});
