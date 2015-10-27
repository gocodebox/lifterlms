<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Course Class
*
* Class used for instantiating course object
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Course {

	/**
	* ID
	* @access public
	* @var int
	*/
	public $id;

	/**
	* Post Object
	* @access public
	* @var array
	*/
	public $post;

	/**
	* Constructor
	*
	* initializes the course object based on post data
	*/
	public function __construct( $course ) {

		if ( is_numeric( $course ) ) {

			$this->id   = absint( $course );
			$this->post = get_post( $this->id );

		}

		elseif ( $course instanceof LLMS_Course ) {

			$this->id   = absint( $course->id );
			$this->post = $course;

		}

		elseif ( isset( $course->ID ) ) {

			$this->id   = absint( $course->ID );
			$this->post = $course;

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $item ) {

		return metadata_exists( 'post', $this->id, '_' . $item );

	}

	/**
	* __get function
	*
	* initializes the course object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_post_meta( $this->id, '_' . $item, true );

		return $value;
	}

	/**
	 * Get SKU
	 *
	 * @return string
	 */
	public function get_sku() {

		return $this->sku;

	}

	public function get_children_sections() {

		$args = array(
			'post_type' 		=> 'section',
			'posts_per_page'	=> 500,
			'meta_key'			=> '_llms_order',
			'order'				=> 'ASC',
			'orderby'			=> 'meta_value_num',
			'meta_query' 		=> array(
				array(
					'key' 		=> '_parent_course',
	      			'value' 	=> $this->id,
	      			'compare' 	=> '='
      			)
		  	),
		);
		 
		$sections = get_posts( $args );
		 
		return $sections;

	}

	public function get_children_lessons() {

		$lessons = array();

		$args = array(
			'post_type' 		=> 'section',
			'posts_per_page'	=> 500,
			'meta_key'			=> '_llms_order',
			'order'				=> 'ASC',
			'orderby'			=> 'meta_value_num',
			'meta_query' 		=> array(
				array(
					'key' 		=> '_parent_course',
	      			'value' 	=> $this->id,
	      			'compare' 	=> '='
      			)
		  	),
		);
		 
		$sections = get_posts( $args );

		foreach ($sections as $s) {

			$section = new LLMS_Section($s->ID);


			$lessons = array_merge($lessons, $section->get_children_lessons());

		}

		return $lessons;

	}

	/**
	 * Get Lesson Length
	 *
	 * @return string
	 */
	public function get_lesson_length() {

		$enabled = get_option('lifterlms_course_display_length');
		if ( 'yes' != $enabled ) {
			return false;
		} 

		elseif ($this->lesson_length == '') {
			return false;
		}

		return $this->lesson_length;

	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmeta_data( $post_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE post_id = %d', $user_id, $post_id) );

		for ($i=0; $i < count($results); $i++) {
			$results[$results[$i]->meta_key] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmetas_by_key( $post_id, $meta_key ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE post_id = %s and meta_key = "%s" ORDER BY updated_date DESC', $post_id, $meta_key ) );

		for ($i=0; $i < count($results); $i++) {
			$results[$results[$i]->post_id] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	/**
	 * Get checkout url
	 *
	 * @return string
	 */
	public function get_checkout_url() {

		$checkout_page_id = llms_get_page_id( 'checkout' );
		$checkout_url =  apply_filters( 'lifterlms_get_checkout_url', $checkout_page_id ? get_permalink( $checkout_page_id ) : '' );
		
		return add_query_arg( 'product-id', $this->id, $checkout_url );

	}

	/**
	 * Get Video (oembed)
	 *
	 * @return mixed (default: '')
	 */
	public function get_video() {

		if ( ! isset( $this->video_embed ) ) {

			return '';

		}

		else {

			return wp_oembed_get($this->video_embed);

		}

	}

	public function get_start_date() {

		if ( $this->course_dates_from ) {
			return $this->course_dates_from;
		} else {
			return $this->post->post_date;
		}

	}

	public function get_end_date() {
		return $this->course_dates_to;
	}

	/**
	 * Get Audio (wp shortcode)
	 *
	 * @return mixed (default: '')
	 */
	public function get_audio() {

		if ( ! isset( $this->audio_embed ) ) {

			return '';

		}

		else {

			return do_shortcode('[audio src="'. $this->audio_embed . '"]');

		}

	}

	/**
	 * Get Difficulty
	 *
	 * @return string
	 */
	public function get_difficulty() {

		$enabled = get_option('lifterlms_course_display_difficulty');

		if ( 'yes' != $enabled ) {
			return false;
		} 

		$terms = get_the_terms($this->id, 'course_difficulty');

		if ( $terms === false ) {

			return '';

		}

		else {

			foreach ( $terms as $term ) {

        		return $term->name;
        	}

		}

	}

	/**
	 * Get course prerequisite
	 * @return mixed [Returns prerequisite course id or false if none exists]
	 */
	public function get_prerequisite() {

		if ( !empty($this->has_prerequisite) ) {

			return $this->prerequisite;
		}
		else {
			return false;
		}
	}

	/**
	 * Get course prerequisite
	 * @return mixed [Returns prerequisite course id or false if none exists]
	 */
	public function get_prerequisite_track() {

		if ( !empty($this->has_prerequisite) ) {

			return $this->prerequisite_track;
		}
		else {
			return false;
		}
	}

	public function get_enrolled_students() {
		$enrolled_students = array();
    	$users_not_enrolled = array();
    	$enrolled_student_ids = array();

    	$user_args = array(
    		'blog_id'      => $this->id,
			'include'      => array(),
			'exclude'      => $enrolled_students,
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'count_total'  => false,
			'fields'       => 'all',
    	);
    	$all_users = get_users( $user_args );

    	foreach ( $all_users as $key => $value  ) :
    		if ( llms_is_user_enrolled( $value->ID, $this->id ) ) {
    			$enrolled_students[] = $value;
    		}

    	endforeach;

    	return $enrolled_students;
	}

	/**
	 * Find the next uncompleted lesson in the course syllabus
	 * @return int [Next uncompleted lesson]
	 */
	public function get_next_uncompleted_lesson() {
		$lessons_not_completed = array();
		$lesson_ids = array();

		$lessons = $this->get_children_lessons();

		$user = new LLMS_Person;

		foreach( $lessons as $lesson ) {

			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $lesson->ID );

				if ( ! isset($user_postmetas['_is_complete']) ) {

					array_push($lessons_not_completed, $lesson->ID);
			}
		}
		if ($lessons_not_completed) {
			return $lessons_not_completed[0];
		}
	}

	/**
	 * Get all lesson ids associated with a course
	 * @return array $array [array of all lesson ids in a course]
	 */
	public function get_lesson_ids() {
		$lessons = array();

		$args = array(
			'post_type' 		=> 'section',
			'posts_per_page'	=> 500,
			'meta_key'			=> '_llms_order',
			'order'				=> 'ASC',
			'orderby'			=> 'meta_value_num',
			'meta_query' 		=> array(
				array(
					'key' 		=> '_parent_course',
	      			'value' 	=> $this->id,
	      			'compare' 	=> '='
      			)
		  	),
		);
		 
		$sections = get_posts( $args );

		foreach ($sections as $s) 
		{
			$section = new LLMS_Section($s->ID);
			$lessonset = $section->get_children_lessons();
			foreach ($lessonset as $lessonojb) 
			{
				$lessons[] = $lessonojb->ID;
			}
		}

		return $lessons;
	}

	/**
	 * Get the current percent complete by user
	 * @return int [numerical representation of % completion in course]
	 */
	public function get_percent_complete($user_id = '') {

		if ($user_id == '')
		{
			$user_id = get_current_user_id();
		}

		$lesson_ids = $this->get_children_lessons();

		$array = array();
		$i = 0;

		$user = new LLMS_Person;

		foreach( $lesson_ids as $lesson ) {
			array_push($array, $lesson->ID);
		}

		foreach( $array as $key => $value ) {
			$user_postmetas = $user->get_user_postmeta_data( $user_id, $value );
			if ( isset($user_postmetas['_is_complete']) ) {
				if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
					$i++;
				}
			}
		}

		$percent_complete = ($i != 0) ? round(100 / ( ( count($lesson_ids) / $i ) ), 0 ) : 0;

		return $percent_complete;

	}

	/**
	 * Get all sections
	 * @return array $sections [Returns array of all sections associated with a course.]
	 */
	public function get_sections() {
		$syllabus = $this->get_syllabus(); 
		$sections = array();
		if ($syllabus) {
			foreach($syllabus as $key => $value) {

				array_push($sections,$value['section_id']);
			}
		}
		return $sections;
	}

	/**
	 * Get the course short description
	 *
	 * @return string (html)
	 */
	public function get_short_description() {

		$short_description = wpautop($this->post->post_excerpt);

		return $short_description;

	}


	/**
	 * Get the Course Section and Lesson information
	 *
	 * @return array
	 */
	public function get_syllabus() {

		$syllabus = $this->sections;

		return $syllabus;

	}

	public static function check_enrollment( $course_id, $user_id = '' ) {
		global $wpdb;

		//set enrollment to false
		$enrolled = false;

		// if no course id then nothing we can do
		if ( ! empty( $course_id ) ) {

			// if user id is empty get current user id
			if ( empty( $user_id ) ) {

				$user_id = get_current_user_id();
			}

			//query user_postmeta table
			$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
			$results = $wpdb->get_results( 
				$wpdb->prepare(
					'SELECT * FROM '.$table_name.
						' WHERE post_id = %s 
							AND user_id = %s', 
					$course_id, $user_id 
				) 
			);

			if ( $results ) {
				foreach ( $results as $result ) {
					if ( $result->meta_key === '_status' && ( $result->meta_value === 'Enrolled' || $result->meta_value === 'Expired' ) ) {
						$enrolled = $results;
					}
				}
				
			}
		}

		return $enrolled;
	}

	public function is_user_enrolled($user_id = '') {

		$enrolled = false;

		$user_post_data = self::get_user_post_data( $this->id, $user_id );

		if ( $user_post_data ) {

			foreach( $user_post_data as $upd ) {
				if ( $upd->meta_key === '_status' && $upd->meta_value === 'Enrolled' ) {
					$enrolled = true;
				}
			}

		}

		return $enrolled;

	}

	public function get_user_enroll_date($user_id = '') {
		
		$enrolled_date = '';

		//if no user get current user
		if ( empty($user_id) ) {
			$user_id = get_current_user_id();
		}

		if ( $this->is_user_enrolled($user_id = '') ) {

			$user_post_data = self::get_user_post_data( $this->id, $user_id );

			foreach( $user_post_data as $upd ) {
				if ( $upd->meta_value === 'Enrolled' ) {
					$enrolled_date = $upd->updated_date;
				}
			}

		}

		return $enrolled_date;

	}

	public static function get_user_post_data( $post_id, $user_id = '' ) {
		global $wpdb;

		$results = false;

		if ( ! empty( $post_id ) ) {

			// if user id is empty get current user id
			if ( empty( $user_id ) ) {

				$user_id = get_current_user_id();
			}

			// query user postmeta table
			$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
			$results = $wpdb->get_results( 
				$wpdb->prepare(
					'SELECT * FROM '.$table_name.
						' WHERE post_id = %s 
							AND user_id = %s', 
					$post_id, $user_id 
				) 
			);
		}

		return $results;

	}

	public function get_student_progress( $user_id = '' ) {
		
		// if user_id is empty get current user id
		if ( empty( $user_id ) ) {

			$user_id = get_current_user_id();
		}

		//check if user is enrolled
		$enrollment = self::check_enrollment( $this->id, $user_id );


		// set up course details and enrollment information
		$obj = new stdClass();
		$obj->id = $this->id;

		if ( $enrollment ) {

			$obj->is_enrolled = true;
			$obj->is_complete = false;

			//loop through returned rows and save data to object
			foreach ( $enrollment as $row ) {

				if ( $row->meta_key === '_start_date' ) {

					$obj->start_date = $row->updated_date;

				} elseif ( $row->meta_key === '_is_complete' ) {

					$obj->is_complete = true;
					$obj->completed_date = $row->updated_date;

				} elseif ( $row->meta_key === 'status' ) {

					$obj->status = $row->meta_value;

				}
			}

		} else {

			$obj->is_enrolled = false;

		}

		//add sections array to object
		$obj->sections = array();
		//add lessons array to object
		$obj->lessons = array();

		
		//get course syllabus
		$course_syllabus = $this->get_syllabus();

		$sections = $this->get_children_sections();

		foreach( $sections as $child_section ) {
			$section_obj = new LLMS_Section($child_section->ID);

			$section = array();
			$section['id'] = $section_obj->id;
			$section['title'] = $section_obj->post->post_title;

			// get any user post meta data
			$section['is_complete'] = false;
			$section_user_data = self::get_user_post_data( $section_obj->id, $user_id );

			$obj->is_complete = false;
			if ( $section_user_data ) {

				//loop through returned rows and save data to object
				foreach ( $section_user_data as $row ) {

					if ( $row->meta_key === '_is_complete' ) {

						$section['is_complete'] = true;
						$section['completed_date'] = $row->updated_date;

					}

				}

			}
			
			$obj->sections[] = $section;

			//get lesson data
			$lessons = $section_obj->get_children_lessons();

			if ( $lessons ) { 

				foreach ( $lessons as $child_lesson ) {
					$lesson_obj = new LLMS_Lesson($child_lesson->ID);

					$lesson = array();
					$lesson['id'] = $lesson_obj->id;
					$lesson['title'] = $lesson_obj->post->post_title;
					$lesson['parent_id'] = $lesson_obj->get_parent_section();

					$lesson['is_complete'] = false;
					$lesson_user_data = self::get_user_post_data( $lesson_obj->id, $user_id );

					//loop through returned rows and save data to object
					if ( $lesson_user_data ) {

						foreach ( $lesson_user_data as $row ) {

							if ( $row->meta_key === '_is_complete' ) {
								$lesson['is_complete'] = true;
								$lesson['completed_date'] = $row->updated_date;
							}

						}

					}

					$obj->lessons[] = $lesson;

				}

			}

		}		

		return $obj;
		
	}

	/**
	 * Get function for price value.
	 *
	 * @return void
	 */
	public function get_price() {

		return apply_filters( 'lifterlms_get_price', $this->price, $this );

	}

}