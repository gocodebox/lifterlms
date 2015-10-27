jQuery(document).ready(function($) {


	var chosen_config = {
      '.chosen-select'           : {},
      '.chosen-select-deselect'  : {allow_single_deselect:true},
      '.chosen-select-no-single' : {disable_search_threshold:10},
      '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
      '.chosen-select-width'     : {width:"100%"}
    };

    for ( var selector in chosen_config ) {
      $( selector).chosen(chosen_config[selector] );
    }

    $( '.llms-date-range-select-start' ).datepicker();
    $( '.llms-date-range-select-end' ).datepicker();







    var query_vars = get_query_var();
    if ( ( query_vars.page === 'llms-analytics' && query_vars.tab === 'sales' ) ||
      ( query_vars.page === 'llms-analytics' && ! ( 'tab' in query_vars ) )  ) {
        
        google.setOnLoadCallback(drawChart);
        
        $(window).resize(function(){
            drawChart();
        });

    } else if ( query_vars.page === 'llms-analytics' && query_vars.tab === 'courses' ) {
        
        google.setOnLoadCallback(drawChart2);
        google.setOnLoadCallback(drawChart3);
        


        if ( $( window ).width() <= 768 ) {
            google.setOnLoadCallback(drawTableSmall);
        } else {
             google.setOnLoadCallback(drawTable);
        }

        $(window).resize(function(){
            if ( $( window ).width() <= 768 ) {
               drawTableSmall();
            } else {
                drawTable();
            }

            drawChart2();
            drawChart3();

        });

    } else if ( query_vars.page === 'llms-analytics' && query_vars.tab === 'memberships' ) {

        google.setOnLoadCallback( draw_enrolled_members_chart );
        
        if ( $( window ).width() <= 768 ) {
            google.setOnLoadCallback( draw_member_table_small );
        } else {
             google.setOnLoadCallback( draw_member_table );
        }

        $(window).resize(function(){
            if ( $( window ).width() <= 768 ) {
               draw_member_table_small();
            } else {
                draw_member_table();
            }

            draw_enrolled_members_chart();

        });
       
    } else if ( ( query_vars.page === 'llms-students' && ! ( 'tab' in query_vars ) ) 
        || ( query_vars.page === 'llms-students' && query_vars.tab === 'dashboard' ) ) {

        //manage expired users checkbox for students search screen
        //if all products is selected then hide and uncheck the show expired users filter
        if ( $( '.chosen-select-width').chosen().val() === 'all_products' ) {
             $( '#include_expired_users' ).hide();
             $( '#exp_users_filter' ).attr('checked', false);
        }
        //on checkbox selection if the expired users filter is hidden then display it. 
        $( '.chosen-select-width').chosen().change( function() {
            if ( $( '.chosen-select-width').chosen().val() == 'all_products' ) {
                $( '#include_expired_users' ).hide(); 
                $( '#exp_users_filter' ).attr('checked', false);
            } else {
                $( '#include_expired_users' ).show(); 
            }
        });

        //get search results table
        google.setOnLoadCallback( draw_student_search_results_table );

        $(window).resize(function(){

            draw_student_search_results_table()

        });
    
    } else if ( query_vars.page === 'llms-students' && query_vars.tab === 'profile' ) {

        google.setOnLoadCallback( draw_student_course_table );

        $(window).resize(function(){
            draw_student_course_table()
        });

    }

    

});

google.load("visualization", "1", {packages:["corechart", "table"]});

get_query_var = function() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

     
      
      function drawChart() {
        var data = google.visualization.arrayToDataTable(myJson);

        var options = {
          title: 'Sales Volume',
          hAxis: {title: 'Date Range',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }

      function drawChart2() {
        var data = google.visualization.arrayToDataTable(enrolled_students);

        var options = {
          title: 'Student Enrollment',
          //curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }

      function drawChart3() {
        var chart_height = ( ( lesson_completion_percent.length * 50 ) + 100 );
        var data = google.visualization.arrayToDataTable(lesson_completion_percent);

        var options = {
          title: 'Lesson Completion Percentage',
          legend: { position: 'bottom' },
          height: chart_height,
          hAxis: {title: 'Percentage',  titleTextStyle: {color: '#333'}, minValue: 0, maxValue: 100 },
          vAxis: { title: 'Lesson' }
        };

        var chart = new google.visualization.BarChart(document.getElementById('lesson-completion-chart'));
        chart.draw(data, options);
      }


      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Last');
        data.addColumn('string', 'First');
        data.addColumn('string', 'Enrolled');
        data.addColumn('string', 'Completion');
        data.addColumn('string', 'Last Lesson Completed');
        data.addColumn('string', 'View');
        data.addRows(students_result_large);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }

      function drawTableSmall() {
        console.log(students_result_small);
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Last');
        data.addColumn('string', 'First');
        data.addColumn('string', 'View');
        data.addRows(students_result_small);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }


      
      function draw_enrolled_members_chart() {
        var data = google.visualization.arrayToDataTable(enrolled_members);

        var options = {
          title: 'Membership Enrollment by Day',
          //curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('enrolled_members_chart'));

        chart.draw(data, options);
      }


     
      function draw_member_table() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Last');
        data.addColumn('string', 'First');
        data.addColumn('string', 'Enrolled');
        data.addColumn('string', 'Expires');
        data.addColumn('string', 'View');
        data.addRows(members_result_large);

        var table = new google.visualization.Table(document.getElementById('members_table'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }

      function draw_member_table_small() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Last');
        data.addColumn('string', 'First');
        data.addColumn('string', 'View');
        data.addRows(members_result_small);

        var table = new google.visualization.Table(document.getElementById('members_table'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }


      function draw_student_search_results_table() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Last');
        data.addColumn('string', 'First');
        data.addColumn('string', 'View');
        data.addRows(students_search_result_large);

        var table = new google.visualization.Table(document.getElementById('student_search_results'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }

       function draw_student_course_table() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Course');
        data.addColumn('string', 'Enrolled Date');
        data.addColumn('string', 'Status');
        data.addRows(student_course_list);

        var table = new google.visualization.Table(document.getElementById('student_course_table'));

        table.draw(data, {showRowNumber: true, allowHtml: true});
      }

      
       