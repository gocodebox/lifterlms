jQuery(document).ready(function($) {

    $( '.llms-lesson-link-locked' ).click(function(e) {
    	var tip  = $(this).attr("title");
    	$('#lockedTooltip').html(tip);

 		var left = $(this).offset().left + ($(this).outerWidth()/2) - ($('#lockedTooltip').outerWidth()/2);
    	var top  = $(this).offset().top - parseInt($(this).css("padding")) - ($('#lockedTooltip').outerHeight()/2) - 8;

 		$('#lockedTooltip').css('top',top);
        $('#lockedTooltip').css('left',left);
    	$('#lockedTooltip').fadeIn();
    })
});

