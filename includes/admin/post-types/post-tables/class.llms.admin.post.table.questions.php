<?php

/**
 * Add, Customize, and Manage LifterLMS Questions Post Table Columns
 *
 * @since    3.9.6
 * @version  3.9.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
		add_filter( 'manage_llms_question_posts_columns', array( $this, 'add_questions_columns' ), 10, 1 );
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
	public function add_questions_columns( $q_columns ) {
		//checkbox
		$lessont_obj = new LLMS_Admin_Post_Table_Lessons();
		$q_columns = $lessont_obj->add_columns();
		unset( $q_columns['section'] );
		unset( $q_columns['prereq'] );
		unset( $q_columns['date'] );
		//lesson
		$q_columns['lesson'] = __( 'Lesson', 'lifterlms' );
		//quiz
		$q_columns['quiz'] = __( 'Quiz', 'lifterlms' );
		$q_columns['date'] = __( 'Date', 'lifterlms' );
		return $q_columns;
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
		//assign lesson id
		$this->assign_lesson_id();

		//set data ends
		switch ( $column ) {
			case 'course':
				$parent_id = absint( get_post_meta( $this->lesson_p_id, '_llms_parent_course', true ) );
				$edit_link = get_edit_post_link( $parent_id );
				if ( ! empty( $parent_id ) ) {
					printf( '<a href="%1$s">%2$s</a>' , $edit_link, get_the_title( $parent_id ) );
				}
				break;
			case 'lesson':
				$edit_link = get_edit_post_link( $this->lesson_p_id );
				if ( ! empty( $this->lesson_p_id ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $this->lesson_p_id ) );
				}
				break;
			case 'quiz':
				$edit_link = get_edit_post_link( $this->quiz_p_id );
				if ( ! empty( $this->quiz_p_id ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $this->quiz_p_id ) );
				}
				break;
		}
	}
	//assign lesson id
	public function assign_lesson_id() {
		$all_less = $this->get_posts( 'lesson' );
		foreach ( $all_less as $lesson_id ) {
			$quiz_id = absint( get_post_meta( $lesson_id, '_llms_assigned_quiz', true ) );
			if ( $this->quiz_p_id ) {
				if ( $quiz_id == $this->quiz_p_id ) {
					//set lesson id
					$this->lesson_p_id = $lesson_id;
				}
			}
		}
	}
	//to resolve complexity
	public function get_course_id() {
		$selected_course_id = isset( $_GET['filter_course_id'] ) ? sanitize_text_field( $_GET['filter_course_id'] ) : '';
		return $selected_course_id;
	}
	//to resolve complexity
	public function get_lesson_id() {
		$selected_lesson_id = isset( $_GET['filter_lesson_id'] ) ? sanitize_text_field( $_GET['filter_lesson_id'] ) : '';
		return $selected_lesson_id;
	}
	/**
	 * Add filters
	 *
	 * @return string/html
	 * @since 3.9.6
	 */
	public function filters( $post_type ) {
		//only add filter to post type you want
		if ( 'llms_question' !== $post_type ) {
			return;
		}
		$selected_course_id = $this->get_course_id();
		//get course filter
		$this->get_course_filter();
		//get all lessons of course
		$filter_all_lessons = array();
		$selected_lesson_id = $this->get_lesson_id();
		// lesson filter
		$this->get_lesson_filter();
		// quiz
		$selected_quiz_id = isset( $_GET['filter_quiz_id'] ) ? sanitize_text_field( $_GET['filter_quiz_id'] ) : '';
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
			$quizze_obj = new LLMS_Admin_Post_Table_Quizzes();
			$quizze_obj->date_filter( $post_type );
	}
	/**
	 * get lesson filter
	 */
	public function get_lesson_filter() {
		$selected_lesson_id = isset( $_GET['filter_lesson_id'] ) ? sanitize_text_field( $_GET['filter_lesson_id'] ) : '';
		$selected_course_id = isset( $_GET['filter_course_id'] ) ? sanitize_text_field( $_GET['filter_course_id'] ) : '';
		$all_less = $this->get_posts( 'lesson' );
		foreach ( $all_less as $lesson_id ) {
			$parent_id = absint( get_post_meta( $lesson_id, '_llms_parent_course', true ) );
			if ( $selected_course_id == $parent_id ) {
				$filter_all_lessons[] = $lesson_id;
			}
		}
			?>
			<select name="filter_lesson_id" id="filter_lesson_id">
				<option value=""><?php _e( 'All Lessons ', 'lifterlms' ); ?></option>
				<?php foreach ( $filter_all_lessons as $lesson_id ) { ?>
					<option value="<?php echo $lesson_id; ?>" <?php selected( $lesson_id,$selected_lesson_id ); ?> ><?php echo get_the_title( $lesson_id ); ?></option>
				<?php } ?>
			</select>
			<?php
	}
	/**
	 * get course filter
	 */
	public function get_course_filter() {
			$selected_course_id = isset( $_GET['filter_course_id'] ) ? sanitize_text_field( $_GET['filter_course_id'] ) : '';
			?>
			<select name="filter_course_id" id="filter_course_id">
				<option value=""><?php _e( 'All Courses ', 'lifterlms' ); ?></option>
				<?php foreach ( $this->get_posts() as $course_id ) { ?>
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
		$type = $_GET['post_type'];
		if ( 'llms_question' == $type && isset( $_GET['filter_course_id'] ) && is_admin() && $pagenow == 'edit.php' && $_GET['filter_course_id'] != '' ) {
			$selected_course_id = $this->get_course_id();
			$selected_lesson_id = $this->get_lesson_id();
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
			$helper_class = new Llms_Question_Table_Helper();
			$parse_data = $helper_class->parse_filter( $quiz_ids );
			$query->query_vars['post__in'] = $parse_data;
		}
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
//
class Llms_Question_Table_Helper {
	/*
	* Get quliz ids values | Reduce Cyclomatic  complexity of filter
	*/
	public function parse_filter( $quiz_ids ) {
		$selected_lesson_id = isset( $_GET['filter_lesson_id'] ) ? sanitize_text_field( $_GET['filter_lesson_id'] ) : '';
		$selected_quiz_id = isset( $_GET['filter_quiz_id'] ) ? sanitize_text_field( $_GET['filter_quiz_id'] ) : '';
		if ( ! empty( $quiz_ids ) ) {
			// array unique
			$quiz_ids = array_unique( $quiz_ids );
			//remove 0 value array
			if ( ! $selected_lesson_id ) {
				$quiz_ids = array_diff( $quiz_ids, array( 0 ) );
			}
			//set seleted quiz
			if ( $selected_quiz_id ) {
				$quiz_ids = array( $selected_quiz_id );
			}
			$inside_parse_data = $this->inside_parse_filter( $quiz_ids );
			if ( ! empty( $quiz_ids ) ) {
				return $inside_parse_data;
			} else {
				return array( 0 );
			}
		} else {
			//if no lesson on course
			//set to no quiz found
			return array( 0 );
		}
		return array( 0 );
	}
	//run some logic
	public function inside_parse_filter( $quiz_ids ) {
		//get questions of quiz
		$q_questionsn = array();
		$questions_ids = array();
		foreach ( $quiz_ids as $single_q_id ) {
			$q_questionsn = get_post_meta( $single_q_id, '_llms_questions', true );
			$questions_ids[] = wp_list_pluck( $q_questionsn, 'id' );
		}
		$l_id = 'novalue';
		if ( ! empty( $questions_ids ) ) {
			if ( is_array( $questions_ids[0] ) ) {
				$l_id = implode( ',',$questions_ids[0] );
			}
		}
		if ( $l_id ) {
			//set query var these quizes will show
			return $questions_ids[0];
		}
		if ( $l_id == 0 ) {
			//set query var these quizes will show
			return array( 0 );
		}
	}
}
