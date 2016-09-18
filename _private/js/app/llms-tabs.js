/* global LLMS, $ */
/* jshint strict: false */

/**
 * Front End Quiz Class
 * Applies only to post type quiz
 * @type {Object}
 */
LLMS.Tabs = {

	/**
	 * init
	 * loads class methods
	 */
	init: function() {
		this.bind();
	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return {[type]} [description]
	 */
	bind: function() {

		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('ul.tabs li').removeClass('llms-active');
			$('.tab-content').removeClass('llms-active');

			$(this).addClass('llms-active');
			$('#' + tab_id).addClass('llms-active');

		});

	}
};
