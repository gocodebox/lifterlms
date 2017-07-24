<?php

/**
 * Add, Customize, and Manage LifterLMS Questions Post Table Columns
 *
 * @since    3.9.6
 * @version  3.9.6
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class LLMS_Admin_Post_Table_Questions {
	public $quiz_p_id = 0;
	public $lesson_p_id = 0;

	/**
	 * Constructor
	 * @return  void
	 * @since    3.9.6
	 * @version  3.9.6
	 */
	public function __construct() {
		add_filter( 'manage_llms_question_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_question_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
		//add course filter
		add_action( 'restrict_manage_posts', array( $this, 'filters' ), 10 );

		//change query
		add_action( 'pre_get_posts', array( $this, 'query_posts_filter' ), 10,1 );

		//disable default date
		add_filter( 'months_dropdown_results', array( $this, 'default_date_filter' ), 10 ,2 );
	}

	/**
	 * Add Custom lesson Columns
	 * @param   array  $columns  array of default columns
	 * @return  array
	 * @since    3.9.6
	 * @version  3.9.6
	 */
	public function add_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Lesson Title', 'lifterlms' ),
			'course' => __( 'Course', 'lifterlms' ),
		'lesson' => __( 'Lesson', 'lifterlms' ),
			'quiz' => __( 'Quiz', 'lifterlms' ),
			'date' => __( 'Date', 'lifterlms' ),
		);
		return $columns;
	}

	/**
	 * Manage content of custom lesson columns
	 * @param  string $column   column key/name
	 * @param  int    $post_id  WP Post ID of the lesson for the row
	 * @return void
	 * @since   3.9.6
	 * @version  3.9.6
	 */
	public function manage_columns( $column, $post_id ) {
		//set data starts
		$this->quiz_p_id = 0;
		$this->lesson_p_id  = 0;
		$all_quizes = $this->get_posts( 'llms_quiz' );
		foreach ( $all_quizes as $q_id ) {
			//get questions of quiz
			$q = array();
			$q = get_post_meta( $q_id, '_llms_questions', true );
			$r = wp_list_pluck( $q, 'id' );

			//check if current question exists this quiz quiz
			if ( in_array( $post_id,$r ) ) {
				$edit_link = get_edit_post_link( $q_id );

				//set quiz id
				$this->quiz_p_id = $q_id;
			}
		}
		$all_less = $this->get_posts( 'lesson' );		foreach ( $all_less as $lesson_id ) {
			$quiz_id = absint( get_post_meta( $lesson_id, '_llms_assigned_quiz', true ) );
			if ( $this->quiz_p_id ) {
				if ( $quiz_id == $this->quiz_p_id ) {
					$edit_link = get_edit_post_link( $lesson_id );

					//set lesson id
					$this->lesson_p_id = $lesson_id;
				}
			}
		}

		//set data ends
		switch ( $column ) {
			case 'course' :
				$parent_id = absint( get_post_meta( $this->lesson_p_id, '_llms_parent_course', true ) );
				$edit_link = get_edit_post_link( $parent_id );
				if ( ! empty( $parent_id ) ) {
					printf( '<a href="%1$s">%2$s</a>' , $edit_link, get_the_title( $parent_id ) );
				}
			break;
			case 'lesson' :
				$edit_link = get_edit_post_link( $this->lesson_p_id );
				if ( ! empty( $this->lesson_p_id ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $this->lesson_p_id ) );
				}
			break;
			case 'quiz' :
				$edit_link = get_edit_post_link( $this->quiz_p_id );
				if ( ! empty( $this->quiz_p_id ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $this->quiz_p_id ) );
				}
			break;
		}// End switch().
	}

	/**
	 * Add filters
	 *
	 * @return string/html
	 * @since 3.9.6
	 */
	public function filters( $post_type ) {

		//only add filter to post type you want
		if ( 'llms_question' !== $post_type ) { return; }
		?>
			<?php $selected_course_id = isset( $_GET['filter_course_id'] )? sanitize_text_field( $_GET['filter_course_id'] ):''; ?>
			<select name="filter_course_id" id="filter_course_id">
				<option value=""><?php _e( 'All Courses ', 'lifterlms' ); ?></option>
				<?php foreach ( $this->get_posts() as $course_id ) {  ?>
					<option value="<?php echo $course_id; ?>" <?php selected( $course_id,$selected_course_id ); ?> ><?php echo get_the_title( $course_id ); ?></option>
				<?php } ?>
			</select>
			<script>

			/* auto submit on course ,lesson filter change */
			jQuery ( document ).ready( function( $ ) {
				$( '#filter_course_id' ).change( function() {
					$( '#filter_lesson_id' ).val( '' );
					$( '#filter_quiz_id' ).val( '' );
					$( '#posts-filter' ).submit();
				} );
				$( '#filter_lesson_id' ).change( function() {
					$( '#filter_quiz_id' ).val( '' );
					$( '#posts-filter' ).submit();
				} );
			} );
			</script>
			<?php

			//get all lessons of course
			//TO DO: use clasess :issue arise after submitting using classes
			$filter_all_lessons = array();
			$selected_lesson_id = isset( $_GET['filter_lesson_id'] )? sanitize_text_field( $_GET['filter_lesson_id'] ):'';
			$all_less = $this->get_posts( 'lesson' );
			foreach ( $all_less as $lesson_id ) {
				$parent_id = absint( get_post_meta( $lesson_id, '_llms_parent_course', true ) );
				if ( $selected_course_id == $parent_id ) {
					$filter_all_lessons[] = $lesson_id;
				}
			}
			?>
			<?php ?>
			<select name="filter_lesson_id" id="filter_lesson_id">
				<option value=""><?php _e( 'All Lessons ', 'lifterlms' ); ?></option>
				<?php foreach ( $filter_all_lessons as $lesson_id ) { ?>
					<option value="<?php echo $lesson_id; ?>" <?php selected( $lesson_id,$selected_lesson_id ); ?> ><?php echo get_the_title( $lesson_id ); ?></option>
				<?php } ?>
			</select>
			<!-- quiz -->
			<?php
			$selected_quiz_id = isset( $_GET['filter_quiz_id'] )? sanitize_text_field( $_GET['filter_quiz_id'] ):'';
			$quiz_ids = array();

			//when lesson is selected
			if ( $selected_lesson_id ) {

				//to check if single lesson is set then no need for all lesson
				$filter_all_lessons = array( $selected_lesson_id );
			}
			foreach ( $filter_all_lessons as $lesson_id ) {
				$parent_id = absint( get_post_meta( $lesson_id, '_llms_parent_course', true ) );
				if ( $selected_course_id == $parent_id ) {
					$quiz_ids[] = absint( get_post_meta( $lesson_id, '_llms_assigned_quiz', true ) );
				}
			}
			$quiz_ids = array_unique( $quiz_ids );

			//remove 0 value array
			if ( ! $selected_lesson_id ) {
				$quiz_ids = array_diff( $quiz_ids, array( 0 ) );
			}
			?>
			<select name="filter_quiz_id" id="filter_quiz_id">
				<option value=""><?php _e( 'All Quizes ', 'lifterlms' ); ?></option>
				<?php foreach ( $quiz_ids as $quiz_id ) { ?>
					<?php if ( $quiz_id ) : ?>
					<option value="<?php echo $quiz_id; ?>" <?php selected( $quiz_id,$selected_quiz_id ); ?> ><?php echo get_the_title( $quiz_id ); ?></option>
					<?php endif; ?>
				<?php } ?>
			</select>
			<?php
			//date filter
			global $wpdb ,$wp_locale;
			$extra_checks = "AND post_status != 'auto-draft'";
			$months = $wpdb->get_results( $wpdb->prepare( "
				SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				FROM $wpdb->posts
				WHERE post_type = %s
				$extra_checks
				ORDER BY post_date DESC
			", $post_type ) );
			$month_count = count( $months );
			if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;
			$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
			?>
					<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date', 'lifterlms' ); ?></label>
					<select name="m" id="filter-by-date">
					<option <?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates', 'lifterlms' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
				continue;
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					sprintf( '%1$s %2$d', $wp_locale->get_month( $month ), $year )
				);
			}
			?>
					</select>
			<?php
	}

	/**
	 * Get posts
	 *
	 * @arg: post type
	 * @return array
	 * @since 3.9.6
	 */
	public function get_posts( $post_type = 'course' ) {
		 global $wpdb;
		/** Grab  posts from  DB */
		$query = $wpdb->prepare('
			SELECT  * FROM %1$s 
			WHERE post_status = "%2$s" 
		AND post_type = "%3$s"
			ORDER BY ID DESC',
			$wpdb->posts,
			'publish',
			'' . $post_type . ''
		);
		return $wpdb->get_col( $query );
	}

	/**
	 * Change query on filter submit
	 *
	 * @return Void
	 * @Since 3.9.6
	 */
	public function query_posts_filter( $query ) {
		global $pagenow;
		$type = 'post';
		if ( isset( $_GET['post_type'] ) ) {
			$type = $_GET['post_type'];
		}
		if ( 'llms_question' == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET['filter_course_id'] ) && $_GET['filter_course_id'] != '' ) {
			$selected_course_id = isset( $_GET['filter_course_id'] )? sanitize_text_field( $_GET['filter_course_id'] ):'';
			$selected_lesson_id = isset( $_GET['filter_lesson_id'] )? sanitize_text_field( $_GET['filter_lesson_id'] ):'';
			$selected_quiz_id = isset( $_GET['filter_quiz_id'] )? sanitize_text_field( $_GET['filter_quiz_id'] ):'';

			//get all lessons of course
			$lesson	= new LLMS_Lesson( $selected_lesson_id );
			$l_id = $lesson->get( 'assigned_quiz' );
		} else {
			$all_less = $this->get_posts( 'lesson' );
			if ( $selected_lesson_id ) {

				//to check if single lesson is set then no need for all lesson
				$all_less = array( $selected_lesson_id );
			}
			foreach ( $all_less as $lesson_id ) {
				$parent_id = absint( get_post_meta( $lesson_id, '_llms_parent_course', true ) );
				if ( $selected_course_id == $parent_id ) {
					$quiz_ids[] = absint( get_post_meta( $lesson_id, '_llms_assigned_quiz', true ) );
				}
			}
			if ( ! empty( $quiz_ids ) ) {
				// array unique
				$quiz_ids = array_unique( $quiz_ids );
				//remove 0 value array
				if ( ! $selected_lesson_id ) {
					$quiz_ids = array_diff( $quiz_ids, array( 0 ) );
				}

				//get questions of quiz
				$q = array();
				$questions_ids = array();

				//set seleted quiz
				if ( $selected_quiz_id ) {
					$quiz_ids = array( $selected_quiz_id );
				}
				foreach ( $quiz_ids as $single_q_id ) {
					$q = get_post_meta( $single_q_id, '_llms_questions', true );
					$questions_ids[] = wp_list_pluck( $q, 'id' );
				}
				$l_id = 'novalue';
				if ( is_array( $quiz_ids ) ) {
				}
				if ( ! empty( $questions_ids ) ) {
					if ( is_array( $questions_ids[0] ) ) {
						$l_id = implode( ',',$questions_ids[0] );
					}
				}
				if ( $l_id ) {

					//set query var these quizes will show
					$query->query_vars['post__in'] = $questions_ids[0];
				}
				if ( $l_id == 0 ) {

					//set query var these quizes will show
					$query->query_vars['post__in'] = array( 0 );
				}
			} else {

				//if no lesson on course
				//set to no quiz found
				$query->query_vars['post__in'] = array( 0 );
			}// End if().
		}// End if().
	}

	/**
	 * Hide default date filter  only on llms_quiz post types
	 *
	 * @return empty array | months array
	 * @Since 3.9.6
	 */
	public function default_date_filter( $months, $post_type ) {
		if ( $post_type == 'llms_question' ) {
			return array();
		}
		return $months;
	}
}
return new LLMS_Admin_Post_Table_Questions();
