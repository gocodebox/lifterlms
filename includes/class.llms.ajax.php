<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* lifterLMS AJAX Event Handler
*
* Handles server side ajax communication.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		$ajax_events = array(
			'get_courses' 				=> false,
			'get_sections' 				=> false,
			'get_lesson' 				=> false,
			'get_lessons' 				=> false,
			'get_emails' 				=> false,
			'get_achievements' 			=> false,
			'get_certificates' 			=> false,
			'update_syllabus' 			=> false,
			'get_associated_lessons' 	=> false,
			'get_question' 				=> false,
			'get_questions' 			=> false,
			'get_quiz_questions' 		=> false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

	/**
	 * Return array of courses (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_courses(){

		$args = array(
			'post_type' 	=> 'course',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode($postslist);

		die();
	}

	/**
	 * Return array of sections (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_sections(){
		
		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'section',
		'nopaging' 			=> true,
		'post_status'   	=> 'publish',
		'meta_query' 		=> array(
			array(
			    'key' => '_parent_course',
			    'compare' => 'NOT EXISTS'
			    )
			)                   
		);
		$postslist = get_posts( $args );

		if (empty($postslist)) { 
			$args = array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> 'lesson',
				'nopaging' 			=> true
			);
				
			$postslist = get_posts( $args );
		}

		foreach($postslist as $key => $value) {
			$value->edit_url = get_edit_post_link($value->ID);
		}

		echo json_encode($postslist);

		die();
	}

	/**
	 * Return single lesson post
	 *
	 * @param string
	 * @return array
	 */
	public function get_lesson(){

		$lesson_id = $_REQUEST['lesson_id'];
		$post = get_post( $lesson_id  );
		$post->edit_url = get_edit_post_link($post->ID, false);

		echo json_encode($post);
		die();
	}

	/**
	 * Return array of lessons (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_lessons(){

		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'lesson',
		'nopaging' 			=> true,
		'meta_query' 		=> array(
			array(
			    'key' => '_parent_section',
			    'compare' => 'NOT EXISTS'
			    )
			)                   
		);
		$postslist = get_posts( $args );

		if (empty($postslist)) { 
			$args = array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> 'lesson',
				'nopaging' 			=> true
			);
				
			$postslist = get_posts( $args );
		}

		foreach($postslist as $key => $value) {
			$value->edit_url = get_edit_post_link($value->ID, false);
		}

		echo json_encode($postslist);

		die();
	}

	/**
	 * Return single lesson post
	 *
	 * @param string
	 * @return array
	 */
	public function get_question(){

		$question_id = $_REQUEST['question_id'];
		$post = get_post( $question_id  );
		$post->edit_url = get_edit_post_link($post->ID, false);

		echo json_encode($post);
		die();
	}

	/**
	 * Return array of questions (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_questions(){

		$args = array(
			'posts_per_page' 	=> -1,
			'post_type' 		=> 'llms_question',
			'nopaging' 			=> true,
			'post_status'   	=> 'publish',      
		);
		$postslist = get_posts( $args );

		foreach($postslist as $key => $value) {
			$value->edit_url = get_edit_post_link($value->ID, false);
		}
		echo json_encode($postslist);
		die();
	}

	/**
	 * Return array of lessons (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_associated_lessons(){
		$parent_section = $_REQUEST['section_id'];

		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'lesson',
		'nopaging' 			=> true,
		'post_status'   	=> 'publish',
		'meta_query' 		=> array(
			array(
			    'key' => '_parent_section',
			    'value' => $parent_section
			    )
			)                   
		);
		$postslist = get_posts( $args );

		foreach($postslist as $key => $value) {
			$value->edit_url = get_edit_post_link($value->ID);
		}

		echo json_encode($postslist);

		die();
	}


	/**
	 * Return array of courses (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_emails(){

		$args = array(
			'post_type' 	=> 'llms_email',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode($postslist);

		die();
	}

	/**
	 * Return array of courses (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_achievements(){

		$args = array(
			'post_type' 	=> 'llms_achievement',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode($postslist);

		die();
	}

	/**
	 * Return array of courses (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_certificates(){

		$args = array(
			'post_type' 	=> 'llms_certificate',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode($postslist);

		die();
	}

	/**
	 * Updates course syllabus JSON object
	 *
	 * @param object
	 * @return string
	 */
	public function update_syllabus() {
		$post_id  = $_REQUEST['post_id'];

		// Parse section id and create new array for comparison.
		function parse_new_sections ( $new_sections_array ) {
			$array = array();

		    foreach ($new_sections_array as $key => $value ) {
				if (is_array( $value) ) {
					foreach ($value as $keys => $values) {
						if ($keys === 'section_id') {
						array_push($array, $values);
						}
					}
					parse_new_sections( $value );
				}
			}
			return $array;
		}

		// Parse section ids returned from DB and create new array for comparison.
		function parse_current_sections ( $current_sections_array ) {
		    $array = array();

		    foreach($current_sections_array[0] as $key => $value ) {
		    	foreach($value as $keys => $values ) {
		    		if ($keys == 'section_id') {
		    		array_push($array, $values);
		    		}
		    	}
		    }
		    return $array;
		}

		// Compare arrays and determine if there are any duplicates.
		function array_has_dupes($new_array) {
			return count($new_array) !== count(array_unique($new_array));
		}


		function delete_lesson_meta($post_id) {
			$lesson_ids = array();

			$rd_args = array(
				'post_type' => 'lesson',
				'meta_key' => '_parent_course',
				'meta_value' => $post_id
			);

			$rd_query = new WP_Query( $rd_args );

			$lesson_ids = array();

			//foreach( $rd_query as $key => $value )
			while ( $rd_query->have_posts() ) : $rd_query->the_post();
				//delete_post_meta($rd_query->post->ID, '_parent_course', $post_id)
				array_push($lesson_ids,  $rd_query->post->ID  );
			endwhile;
			wp_reset_postdata();
		}
		
		if ( isset($_REQUEST) ) {

			$success = 'no'; //default response to no.
			$new_sections_array = $_REQUEST['sections'];

			$current_sections_array = get_post_meta( $_REQUEST['post_id'], '_sections');

			$new_array = parse_new_sections($new_sections_array);
			$old_array = parse_current_sections($current_sections_array);

			$result_array = array_intersect_assoc($new_array, $old_array);

			$new_array_duplicates = array_count_values($result_array);
			if (array_has_dupes($new_array)) {
				$success = 'no';
			}
			else {
				update_post_meta( $_REQUEST['post_id'], '_sections', ( $_REQUEST['sections'] === '' ) ? '' : $_REQUEST['sections']  );
				$success = 'yes';

				//Manage Section _parent_course
				//find all sections that where assigned to the course and delete the metadata
				$section_args = array(
					'post_type' => 'section',
					'meta_key' => '_parent_course',
					'meta_value' => $post_id
				);

				$section_query = new WP_Query( $section_args );

				while ( $section_query->have_posts() ) : $section_query->the_post();
					//delete all metadata

					//find all lessons that were assigned to sections and delete post_meta data
					$ols_args = array(
						'post_type' => 'lesson',
						'meta_key' => '_parent_section',
						'meta_value' => $section_query->post->ID
					);

					$ols_query = new WP_Query( $ols_args );
//i htink i need to do it right here
//check if section is in request
//if section is not in request don't delete relationship
//should be able to move section blocks between lessons
					while ( $ols_query->have_posts() ) : $ols_query->the_post();
						if ($section_query->post->ID) {
							foreach($new_sections_array as $key => $value)  {
								if ($section_query->post->ID == $value['section_id']) {
									delete_post_meta($ols_query->post->ID, '_parent_section', $section_query->post->ID);
								}
							}
						}
					endwhile;
					//wp_reset_postdata();

					if ($post_id) {
						delete_post_meta($section_query->post->ID, '_parent_course', $post_id);
					}
				endwhile;
				wp_reset_postdata();
					
				//find all sections that are currently assigned to the course
				foreach ($_REQUEST['sections'] as $key => $value) {
					//update _parent_course for section ids
					update_post_meta($value['section_id'], '_parent_course', $post_id);
				}

				//Manage lesson _parent_section and _parent_course
				//find all lessons with _parent_course as $post_id and delete the metadata
				$rd_args = array(
					'post_type' => 'lesson',
					'meta_key' => '_parent_course',
					'meta_value' => $post_id
				);

				$rd_query = new WP_Query( $rd_args );

				while ( $rd_query->have_posts() ) : $rd_query->the_post();
					if ($post_id) {
						delete_post_meta($rd_query->post->ID, '_parent_course', $post_id);
					}
				endwhile;
				wp_reset_postdata();

				//loop through each section
				//find all lessons with _parent_section as section_id and delete the metadata
				foreach($_REQUEST['sections'] as $key => $value) {

					$ls_args = array(
						'post_type' => 'lesson',
						'meta_key' => '_parent_section',
						'meta_value' => $value['section_id']
					);

					$ls_query = new WP_Query( $ls_args );

					while ( $ls_query->have_posts() ) : $ls_query->the_post();
						if ($value['section_id']) {
							delete_post_meta($ls_query->post->ID, '_parent_section', $value['section_id']);
						}
					endwhile;
					wp_reset_postdata();


					foreach($value['lessons'] as $keys => $values) {
						update_post_meta($values['lesson_id'], '_parent_section', $value['section_id']);
						update_post_meta($values['lesson_id'], '_parent_course', $post_id);
					}
				}
			}
		}

	echo json_encode($lesson_ids);
	die();

	}

	/**
	 * Return array of questions (id => name)
	 *
	 * @param string
	 * @return array
	 */
	public function get_quiz_questions(){

		$quiz_id = $_REQUEST['quiz_id'];
		$user_id = $_REQUEST['user_id'];

		$quiz = new LLMS_Quiz($quiz_id );

		//first off. we need to check if the user can actually view this quiz
		//
		//then we need to get the postmeta and then get each question.
		$all_questions = array();
		$questions = $quiz->get_questions();

		if($questions) {
			foreach ($questions as $key => $value) {
				array_push($all_questions, get_post($value['id']));
				//get each question and build array
			}
		}

		$args = array(
			'posts_per_page' 	=> -1,
			'post_type' 		=> 'llms_question',
			'nopaging' 			=> true,
			'post_status'   	=> 'publish',  
		);
		$questions = get_posts( $args );

		foreach($questions as $key => $value) {
			$value->edit_url = get_edit_post_link($value->ID, false);
		}

		echo json_encode($all_questions);
		die();
	}

}

new LLMS_AJAX();
