jQuery(document).ready(function($) {

	$('#_llms_trigger_type').change(function() {
		console.log('LLMS Engagement Trigger Changed');

		var triggerOptionSelected = $("option:selected", this);
    	var triggerValueSelected = this.value;

		if (triggerValueSelected == 'lesson_completed') {
			console.log('LLMS Engagement: Begin Lesson Query');
			var lessons = get_all_lessons();
			console.log('LLMS Engagement: End Lesson Query');
		}
		else if (triggerValueSelected == 'section_completed') {
			console.log('LLMS Engagement: Begin Section Query');
			var sections = get_all_sections();
			console.log('LLMS Engagement: End Section Query');
		}
		else if (triggerValueSelected == 'course_completed') {
			console.log('LLMS Engagement: Begin Course Query');
			var courses = get_all_courses();
			console.log('LLMS Engagement: End Course Query');
		}
		else if (triggerValueSelected == 'course_purchased') {
			console.log('LLMS Engagement: Begin Course Query');
			var courses = get_all_courses();
			console.log('LLMS Engagement: End Course Query');
		}
		else {
			clear_trigger_select();
		}
	});


		$('#_llms_engagement_type').change(function() {
			var engOptionSelected = $("option:selected", this);
    		var engValueSelected = this.value;

			if (engValueSelected == 'email') {
				var emails = get_all_emails();
			}
			if (engValueSelected == 'achievement') {
				var achievements = get_all_achievements();
			}
			if (engValueSelected == 'certificate') {
				var certificates = get_all_certificates();
			}
			else {
			clear_engagement_select();
			}

		});
	
});

return_data = function (response) {
	clear_trigger_select();

        var th = document.createElement('th');

		var label = document.createElement("Label");
		label.setAttribute("for",'trigger-select');
		label.innerHTML = "Engagement Trigger";
		th.appendChild(label);

		var td = document.createElement("td");

        var select = document.createElement('select');
		select.setAttribute('id', 'trigger-select');
		select.setAttribute('class', 'chosen-select chosen select section-select');
		select.setAttribute('name', '_llms_engagement_trigger');
		
		td.appendChild(select);
		jQuery(select).chosen({width:"300px"});

		if (!jQuery('#trigger-select').length) {
	
			// populate select with sections.
			jQuery(select).append('<option value="" selected disabled>Please select a post...</option>');
			for (var key in response) {
			    if (response.hasOwnProperty(key)) {
			    	var option = jQuery('<option />').val(response[key]['ID']).text(response[key]['post_title']);
			    	jQuery(select).append(option);
			    }
			}
		
			jQuery('.engagement-option').append(th);
				
			jQuery('.engagement-option').append(td);
		}

	jQuery(select).trigger("chosen:updated");
}

return_engagement_data = function (response) {
	console.log(response);
	clear_engagement_select();

		var th = document.createElement('th');

		var label = document.createElement("Label");
		label.setAttribute("for",'engagement-select');
		label.innerHTML = "Event";
		th.appendChild(label);

		var td = document.createElement("td");

		var select = document.createElement('select');
		select.setAttribute('id', 'engagement-select');
		select.setAttribute('class', 'chosen-select chosen select section-select');
		select.setAttribute('name', '_llms_engagement');
		
		td.appendChild(select);
		jQuery(select).chosen({width:"300px"});

		if (!jQuery('#engagement-select').length) {
		
		
			// populate select with sections.
			jQuery(select).append('<option value="" selected disabled>Please select an engagement...</option>');
			for (var key in response) {
			    if (response.hasOwnProperty(key)) {
			    	var option = jQuery('<option />').val(response[key]['ID']).text(response[key]['post_title']);
			    	jQuery(select).append(option);
			    }
			}
		
			
				
			jQuery('.engagement-posts').append(th);

			jQuery('.engagement-posts').append(td);

		}
		jQuery(select).trigger("chosen:updated");
}

clear_trigger_select = function () {
	jQuery('.engagement-option').empty();
}

clear_engagement_select = function () {
	jQuery('.engagement-posts').empty();
}