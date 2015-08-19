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
		//var that = this;

		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('ul.tabs li').removeClass('current');
			$('.tab-content').removeClass('current');

			$(this).addClass('current');
				$('#' + tab_id).addClass('current');
		});

	}
};
