jQuery(document).ready(function($) {
	var tip = $('.llms-lesson-tooltip');

    $( '.llms-lesson-link-locked' ).click(function(e) {
    	e.preventDefault();
    	var el = $(this);
    	var thistip = el.find('.llms-lesson-tooltip');

	    if(!thistip.length) {
	      el.append(tip.clone());
	      thistip = el.find('.llms-lesson-tooltip');
    	  thistip.html(el.attr("title"));
	    }

    	thistip.toggleClass('active');
    })
});



