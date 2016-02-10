/* global LLMS, $ */
/* jshint strict: false */

/**
 * Front End Quiz Class
 * Applies only to post type quiz
 * @type {Object}
 */
LLMS.Tabs = {

	post_id: '',
	tab_id: '',
	localStorageId: '',
	/**
	 * init
	 * loads class methods
	 */
	init: function() {
		this.openLastTab();
		this.bind();
	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return {[type]} [description]
	 */
	bind: function() {
		var self = this;

		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('ul.tabs li').removeClass('current');
			$('.tab-content').removeClass('current');

			$(this).addClass('current');
			$('#' + tab_id).addClass('current');

			if(self.localStorageId) {
				localStorage.setItem(self.localStorageId, tab_id);
			}
		});

	},

	/**
	 * openLastTab Method
	 * Opens last open tab after reload
	 */
	openLastTab: function() {
		this.post_id = $('#post_ID').val();
		this.localStorageId = "currentTabIndex" + this.post_id;
		this.tab_id = localStorage.getItem(this.localStorageId);
		if (this.tab_id) {
			$('ul.tabs li').removeClass('current');
			$('.tab-content').removeClass('current');
			$('*[data-tab="' + this.tab_id + '"]').addClass('current');
			$('#' + this.tab_id).addClass('current');
		}
	}
};
