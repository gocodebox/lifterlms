/*
* File: jquery.topModal.js
* Version: 1.0.0
* Description: Simple module for displaying content and forms inside Wordpress admin post edit pages. Modal loads at window top
* Author: Mark Nelson
* Copyright 2015, Mark Nelson
* http://www.therealmarknelson.com
* Free to use and abuse under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
*/

(function ($) {

    $.fn.topModal = function (options) {

	var defaults = $.extend({
            centerPopup: true,
            open: function() {},
            closed: function() {}
	}, options);

	/******************************
	Private Variables
	*******************************/

	var object = $(this);
	var settings = $.extend(defaults, options);

	/******************************
	Public Methods
	*******************************/

	var methods = {

	    init: function() {
		return this.each(function () {
		    methods.appendHTML();
		    methods.setEventHandlers();
		    methods.showPopup();
		});
	    },

	    /******************************
	    Append HTML
	    *******************************/

	    appendHTML: function() {
		// if this has already been added we don't need to add it again
		if ($('.topModalBackground').length === 0) {
			var container = '<div class="topModalContainer"></div>';
		    var background = '<div class="topModalBackground"></div>';
		    $('body').prepend(container);
		    $('.topModalContainer').css("display","block");
		    $('.topModalContainer').prepend(background);


		}
		if( settings.title && object.find('.llms-modal-header').length === 0) {
			var title = '<div class="llms-modal-header">' + settings.title + '</div>';
			object.prepend(title);
		}
		if(object.find('.topModalClose').length === 0) {
		    var close = '<div class="topModalClose">'
				+ '<svg version="1.1" id="llms-icon-modal-close" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"'
				+ 'width="41.347px" height="41.347px" viewBox="0 0 41.347 41.347" enable-background="new 0 0 41.347 41.347" xml:space="preserve">'
				+ '<path d="M39.552,32.456L27.769,20.673L39.552,8.89c2.189-2.189,2.405-5.524,0.481-7.448l-0.129-0.129'
				+ 'c-1.923-1.924-5.259-1.708-7.448,0.482L20.673,13.578L8.89,1.794C6.701-0.395,3.366-0.611,1.442,1.313L1.313,1.442'
				+ 'C-0.611,3.365-0.395,6.701,1.795,8.89l11.783,11.783L1.795,32.456c-2.19,2.19-2.406,5.526-0.482,7.448l0.129,0.129'
				+ 'c1.924,1.924,5.258,1.709,7.448-0.481l11.783-11.783l11.783,11.783c2.19,2.19,5.526,2.406,7.448,0.482l0.129-0.13'
				+ 'C41.957,37.98,41.742,34.646,39.552,32.456z"/>'
				+ '</svg>'
		    	+ '</div>';
		    object.prepend(close);
			}
	    },

	    /******************************
	    Set Event Handlers
	    *******************************/

	    setEventHandlers: function() {

		$(".topModalClose, .topModalBackground").on("click", function (event) {
		    methods.hidePopup();
		});
           // event = new Event('build');
		$(window).on('build', function (e) {methods.hidePopup()});


		$(window).on("resize", function(event){

                    if(settings.centerPopup) {
                        methods.positionPopup();
                    }
		});

	    },

            removeEventListners: function() {
		$(".topModalClose, .topModalBackground").off("click");
            },

	    showPopup: function() {
		$(".topModalBackground").css({
		    "opacity": "0.7"
		});
				$('.topModalContainer').css("display","block");
                $(".topModalBackground").fadeIn("fast");
                object.insertAfter('.topModalBackground');
                $('body').addClass('modal-open');

		object.fadeIn("slow", function(){
                    settings.open();
                });

                if(settings.centerPopup) {
                    methods.positionPopup();
                }
	    },

	    hidePopup: function() {
		$(".topModalContainer").fadeOut("fast");
		object.fadeOut("fast", function(){
                    methods.removeEventListners();
                    settings.closed();
                    $('body').removeClass('modal-open');
                });
	    },

	    positionPopup: function() {
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		var popupWidth = object.width();
		var popupHeight = object.height();
		var scrollTop     = $(window).scrollTop();
		var topPos = (windowHeight / 2) - (popupHeight / 2);
		var leftPos = (windowWidth / 2) - (popupWidth / 2);
		if(topPos < 30) topPos = 30;

		object.css({
		    //"position": "absolute",
		    "top": 0,
		    //"left": leftPos
		});

		$( 'body, html' ).animate( {
			scrollTop:( scrollTop ) }, 'slow' );
	    },

	};

	if (methods[options]) { // $("#element").pluginName('methodName', 'arg1', 'arg2');
	    return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof options === 'object' || !options) { 	// $("#element").pluginName({ option: 1, option:2 });
	    return methods.init.apply(this);
	} else {
	    $.error( 'Method "' +  method + '" does not exist in simple popup plugin!');
	}
    };

})(jQuery);
