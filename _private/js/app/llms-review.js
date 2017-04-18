/* global LLMS, $, jQuery */
/* jshint strict: false */
/*jshint -W020 */

LLMS.Review = {
	/**
	 * init
	 * loads class methods
	 */
	init: function()
	{
		// console.log('Initializing Review ');
		this.bind();
	},

	/**
	 * This function binds actions to the appropriate hooks
	 */
	bind: function()
	{
		$('#llms_review_submit_button').click(function()
		{
			if ($('#review_title').val() !== '' && $('#review_text').val() !== '')
			{
				jQuery.ajax({
		            type : 'post',
		            dataType : 'json',
		            url : window.llms.ajaxurl,
		            data : {
		            	action : 'LLMSSubmitReview',
		                review_title: $('#review_title').val(),
		                review_text: $('#review_text').val(),
		                pageID : $('#post_ID').val()
		            },
		            success: function()
		            {
		                console.log('Review success');
		                $('#review_box').hide('swing');
		                $('#thank_you_box').show('swing');
		            },
		            error: function(jqXHR, textStatus, errorThrown )
		            {
		                console.log(jqXHR);
		                console.log(textStatus);
		                console.log(errorThrown);
		            },
		        });
			} else {
				if ($('#review_title').val() === '')
				{
					$('#review_title_error').show('swing');
				} else {
					$('#review_title_error').hide('swing');
				}
				if ($('#review_text').val() === '')
				{
					$('#review_text_error').show('swing');
				} else {
					$('#review_text_error').hide('swing');
				}
			}
		});
		if ( $('#_llms_display_reviews').attr('checked') ) {
			$('.llms-num-reviews-top').addClass('top');
			$('.llms-num-reviews-bottom').show();

		} else {
			$('.llms-num-reviews-bottom').hide();
		}
		$('#_llms_display_reviews').change(function() {
			if ( $('#_llms_display_reviews').attr('checked') ) {
				$('.llms-num-reviews-top').addClass('top');
				$('.llms-num-reviews-bottom').show();
			} else {
				$('.llms-num-reviews-top').removeClass('top');
				$('.llms-num-reviews-bottom').hide();
			}
		});

	},
};
