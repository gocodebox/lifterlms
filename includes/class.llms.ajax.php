<?php
defined( 'ABSPATH' ) || exit;

/**
 * AJAX Event Handler
 * @since    1.0.0
 * @version  3.24.0
 */
class LLMS_AJAX {

	/**
	 * nonce validation argument
	 * @var string
	 */
	const NONCE = 'llms-ajax';

	/**
	 * Hook into ajax events
	 * @since    1.0.0
	 * @version  3.16.0
	 */
	public function __construct() {

		$ajax_events = array(
			'check_voucher_duplicate'	=> false,
			'query_quiz_questions'      => false,

			// @todo DEPRECATE
			// @deprecated 3.13.0
			'get_achievements'			=> false,
			'get_all_posts'				=> false,
			'get_associated_lessons'	=> false,
			'get_certificates'			=> false,
			'get_courses'				=> false,
			'get_course_tracks'			=> false,
			'get_emails'				=> false,
			'get_enrolled_students'     => false,
			'get_lesson'				=> false,
			'get_lessons'				=> false,
			'get_lessons_alt'			=> false,
			'get_memberships'			=> false,
			'get_question'				=> false,
			'get_sections'				=> false,
			'get_sections_alt'			=> false,
			'get_students'              => false,
			'update_syllabus'			=> false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}

		self::register();

		require_once 'admin/class.llms.admin.builder.php';
		add_filter( 'heartbeat_received', array( 'LLMS_Admin_Builder', 'heartbeat_received' ), 10, 2 );

	}

	/**
	 * Register the AJAX handler class with all the appropriate WordPress hooks.
	 */
	public function register() {

		$handler = 'LLMS_AJAX';

		$methods = get_class_methods( 'LLMS_AJAX_Handler' );

		foreach ( $methods as $method ) {
			add_action( 'wp_ajax_' . $method, array( $handler, 'handle' ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $handler, 'handle' ) );
			add_action( 'wp_loaded', array( $this, 'register_script' ) );
		}

	}

	/**
	 * Handles the AJAX request for my plugin.
	 */
	public static function handle() {

		// Make sure we are getting a valid AJAX request
		check_ajax_referer( self::NONCE );

		// $request = self::scrub_request( $_REQUEST );
		$request = $_REQUEST;

		$response = call_user_func( 'LLMS_AJAX_Handler::' . $request['action'], $request );

		if ( $response instanceof WP_Error ) {
			self::send_error( $response );
		}

		wp_send_json_success( $response );

		die();
	}

	public static function scrub_request( $request ) {

		foreach ( $request as $key => $value ) {

			if ( is_array( $value ) ) {
				$request[ $key ] = self::scrub_request( $value );
			} else {
				$request[ $key ] = llms_clean( $value );
			}
		}

		return $request;

	}

	/**
	 * Register our AJAX JavaScript.
	 * @since    1.0.0
	 * @version  3.17.8
	 */
	public function register_script() {

		// script will only register once
		wp_register_script( 'llms',  LLMS_PLUGIN_URL . '/assets/js/llms' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );
		wp_localize_script( 'llms', 'wp_ajax_data', $this->get_ajax_data() );

		// ensure this doesn't load on the wp-login.php screen
		if ( isset( $_SERVER['SCRIPT_NAME'] ) && false === stripos( $_SERVER['SCRIPT_NAME'], strrchr( wp_login_url(), '/' ) ) ) {
			wp_enqueue_script( 'llms' );
		}

	}

	/**
	 * Get the AJAX data
	 * Currently only retrieves the nonce until we can figuare out how to get the post id too
	 *
	 * @return array
	 */
	public function get_ajax_data() {
		return array(
			'nonce' => wp_create_nonce( LLMS_AJAX::NONCE ),
		);
	}

	/**
	 * Sends a JSON response with the details of the given error.
	 *
	 * @param WP_Error $error
	 */
	private static function send_error( $error ) {
		wp_send_json(array(
			'code' => $error->get_error_code(),
			'message' => $error->get_error_message(),
		));
	}

	/*
		                     /$$ /$$ /$$                           /$$
		                    | $$| $$| $$                          | $$
		  /$$$$$$$  /$$$$$$ | $$| $$| $$$$$$$   /$$$$$$   /$$$$$$$| $$   /$$  /$$$$$$$
		 /$$_____/ |____  $$| $$| $$| $$__  $$ |____  $$ /$$_____/| $$  /$$/ /$$_____/
		| $$        /$$$$$$$| $$| $$| $$  \ $$  /$$$$$$$| $$      | $$$$$$/ |  $$$$$$
		| $$       /$$__  $$| $$| $$| $$  | $$ /$$__  $$| $$      | $$_  $$  \____  $$
		|  $$$$$$$|  $$$$$$$| $$| $$| $$$$$$$/|  $$$$$$$|  $$$$$$$| $$ \  $$ /$$$$$$$/
		 \_______/ \_______/|__/|__/|_______/  \_______/ \_______/|__/  \__/|_______/
	*/

	public function check_voucher_duplicate() {
		global $wpdb;
		$table = $wpdb->prefix . 'lifterlms_vouchers_codes';

		$codes = array_key_exists( 'codes', $_REQUEST ) ? $_REQUEST['codes'] : array();
		$post_id = array_key_exists( 'postId', $_REQUEST ) ? (int) $_REQUEST['postId'] : 0;

		$codes_as_string = join( '","' , $codes );

		$query = 'SELECT code
                  FROM ' . $table . '
                  WHERE code IN ("' . $codes_as_string . '")
                  AND voucher_id != ' . $post_id;
		$codes_result = $wpdb->get_results( $query, ARRAY_A );

		echo json_encode( array(
			'success' => true,
			'duplicates' => $codes_result,
		) );

		wp_die();
	}

	/**
	 * Retrieve Quiz Questions
	 *
	 * Used by Select2 AJAX functions to load paginated quiz questions
	 * Also allows querying by question title
	 *
	 * @return json
	 */
	public function query_quiz_questions() {

		// grab the search term if it exists
		$term = array_key_exists( 'term', $_REQUEST ) ? $_REQUEST['term'] : '';

		$page = array_key_exists( 'page', $_REQUEST ) ? $_REQUEST['page'] : 0;

		global $wpdb;

		$limit = 30;
		$start = $limit * $page;

		if ( $term ) {
			$like = " AND post_title LIKE '%s'";
			$vars = array( '%' . $term . '%', $start, $limit );
		} else {
			$like = '';
			$vars = array( $start, $limit );
		}

		$questions = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, post_title
			 FROM $wpdb->posts
			 WHERE
			 	    post_type = 'llms_question'
			 	AND post_status = 'publish'
			 	$like
			 ORDER BY post_title
			 LIMIT %d, %d
			",
			$vars
		) );

		$r = array();
		foreach ( $questions as $q ) {

			$r[] = array(
				'id' => $q->ID,
				'name' => $q->post_title . ' (' . $q->ID . ')',
			);

		}

		echo json_encode( array(
			'items' => $r,
			'more' => count( $r ) === $limit,
			'success' => true,
		) );

		wp_die();

	}

	/*
		       /$$                                                               /$$                     /$$
		      | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
		                    | $$
		                    | $$
		                    |__/
	*/

	/**
	 * Return array of achivements (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_achievements() {

		llms_deprecated_function( 'LLMS_AJAX::get_achievements()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'llms_achievement',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return array of courses (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_all_posts() {

		llms_deprecated_function( 'LLMS_AJAX::get_all_posts()', '3.13.0' );

		$post_type = llms_clean( $_REQUEST['post_type'] );

		$args = array(
			'post_type' 	=> $post_type,
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return array of lessons (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_associated_lessons() {

		llms_deprecated_function( 'LLMS_AJAX::get_associated_lessons()', '3.13.0' );

		$parent_section = $_REQUEST['section_id'];

		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'lesson',
		'nopaging' 			=> true,
		'post_status'   	=> 'publish',
		'meta_query' 		=> array(
			array(
			    'key' => '_llms_parent_section',
			    'value' => $parent_section,
			    ),
			),
		);
		$postslist = get_posts( $args );

		foreach ( $postslist as $key => $value ) {
			$value->edit_url = get_edit_post_link( $value->ID );
		}

		echo json_encode( $postslist );

		die();
	}


	/**
	 * Return array of certificates (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_certificates() {

		llms_deprecated_function( 'LLMS_AJAX::get_certificates()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'llms_certificate',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return array of courses (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_courses() {

		llms_deprecated_function( 'LLMS_AJAX::get_courses()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'course',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return array of course tracks (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_course_tracks() {

		llms_deprecated_function( 'LLMS_AJAX::get_course_tracks()', '3.13.0' );

		$trackslist = get_terms( 'course_track',array(
			'hide_empty' => '0',
		) );

		$tracks = array();

		foreach ( (array) $trackslist as $num => $track ) {
			$tracks[] = array(
				'ID' 		 => $track->term_id,
				'post_title' => $track->name,
			);
		}

		echo json_encode( $tracks );

		die();
	}

	/**
	 * Return array of courses (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_emails() {

		llms_deprecated_function( 'LLMS_AJAX::get_emails()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'llms_email',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return array of enrolled students
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_enrolled_students() {

		llms_deprecated_function( 'LLMS_AJAX::get_enrolled_students()', '3.13.0' );

		$term = array_key_exists( 'term', $_REQUEST ) ? $_REQUEST['term'] . '%' : '%';
		$post_id = (int) $_REQUEST['postId'];

		global $wpdb;
		$user_table = $wpdb->prefix . 'users';
		$usermeta = $wpdb->prefix . 'lifterlms_user_postmeta';

		$select_user = "SELECT ID, display_name, user_email FROM $user_table
			JOIN $usermeta ON $user_table.ID = $usermeta.user_id
			WHERE $usermeta.post_id = $post_id
			AND $usermeta.meta_key = '_status'
			AND meta_value = 'Enrolled'
			AND ($user_table.user_email LIKE '$term'
			OR $user_table.display_name LIKE '$term')
			LIMIT 30";
		$all_users = $wpdb->get_results( $select_user );

		$users_arr = array();

		foreach ( $all_users as $user ) {
			$temp['id'] = $user->ID;
			$temp['name'] = $user->display_name . ' (' . $user->user_email . ')';
			$users_arr[] = $temp;
		}

		echo json_encode(array(
			'success' => true,
			'items' => $users_arr,
		));

		wp_die();
	}

	/**
	 * [get_enrolled_students_ids description]
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	private function get_enrolled_students_ids() {

		llms_deprecated_function( 'LLMS_AJAX::get_enrolled_students_ids()', '3.13.0' );

		$post_id = (int) $_REQUEST['postId'];

		global $wpdb;
		$user_table = $wpdb->prefix . 'users';
		$usermeta = $wpdb->prefix . 'lifterlms_user_postmeta';

		$select_user = "SELECT ID FROM $user_table
			JOIN $usermeta ON $user_table.ID = $usermeta.user_id
			WHERE $usermeta.post_id = $post_id
			AND $usermeta.meta_key = '_status'
			AND meta_value = 'Enrolled'
			LIMIT 1000";
		$all_users = $wpdb->get_results( $select_user );

		$users_arr = array();

		foreach ( $all_users as $user ) {
			$users_arr[] = $user->ID;
		}

		return $users_arr;
	}

	/**
	 * Return single lesson post
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_lesson() {

		llms_deprecated_function( 'LLMS_AJAX::get_lesson()', '3.13.0' );

		$lesson_id = $_REQUEST['lesson_id'];
		$post = get_post( $lesson_id );
		$post->edit_url = get_edit_post_link( $post->ID, false );

		echo json_encode( $post );
		die();
	}

	/**
	 * Return array of lessons (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_lessons() {

		llms_deprecated_function( 'LLMS_AJAX::get_lessons()', '3.13.0' );

		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'lesson',
		'nopaging' 			=> true,
		'meta_query' 		=> array(
			array(
			    'key' => '_llms_parent_section',
			    'compare' => 'NOT EXISTS',
			    ),
			),
		);
		$postslist = get_posts( $args );

		if ( ! empty( $postslist ) ) {

			foreach ( $postslist as $key => $value ) {
				$value->edit_url = get_edit_post_link( $value->ID, false );
			}

			echo json_encode( $postslist );

		}

		die();
	}

	/**
	 * Return custom array of lessons for use on the engagement page
	 * @return     array Array of lessons
	 * @since      1.3.0
	 * @version    3.13.0
	 * @deprecated 3.13.0
	 */
	public function get_lessons_alt() {

		llms_deprecated_function( 'LLMS_AJAX::get_lessons_alt()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'lesson',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$lessons = get_posts( $args );

		$options = array();

		if ( ! empty( $lessons ) ) {

			foreach ( $lessons as $key => $value ) {

				//get parent course if assigned
				$parent_course = get_post_meta( $value->ID, '_llms_parent_course', true );

				if ( $parent_course ) {
					$title = $value->post_title . ' ( ' . get_the_title( $parent_course ) . ' )';
				} else {
					$title = $value->post_title . ' ( ' . __( 'unassigned', 'lifterlms' ) . ' )';
				}

				$options[] = array(
					'ID' 		 => $value->ID,
					'post_title' => $title,
				);

			}
		}

		echo json_encode( $options );

		wp_die();
	}

	/**
	 * Return array of memberships (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_memberships() {

		llms_deprecated_function( 'LLMS_AJAX::get_memberships()', '3.13.0' );

		$args = array(
				'post_type' 	=> 'llms_membership',
				'nopaging' 		=> true,
				'post_status'   => 'publish',

		);

		$postslist = get_posts( $args );

		echo json_encode( $postslist );

		die();
	}

	/**
	 * Return single question post
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_question() {

		llms_deprecated_function( 'LLMS_AJAX::get_question()', '3.13.0' );

		$question_id = $_REQUEST['question_id'];
		$post = get_post( $question_id );
		$post->edit_url = get_edit_post_link( $post->ID, false );

		echo json_encode( $post );
		die();
	}

	/**
	 * Return array of sections (id => name)
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_sections() {

		llms_deprecated_function( 'LLMS_AJAX::get_sections()', '3.13.0' );

		$args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'section',
		'nopaging' 			=> true,
		'post_status'   	=> 'publish',
		'meta_query' 		=> array(
			array(
			    'key' => '_llms_parent_course',
			    'compare' => 'NOT EXISTS',
			    ),
			),
		);
		$postslist = get_posts( $args );

		if ( ! empty( $postslist ) ) {

			foreach ( $postslist as $key => $value ) {
				$value->edit_url = get_edit_post_link( $value->ID );
			}

			echo json_encode( $postslist );
		}

		die();
	}

	/**
	 * Return custom array of sections for use on the engagement page
	 * @return      array
	 * @since       1.3.0
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_sections_alt() {

		llms_deprecated_function( 'LLMS_AJAX::get_sections_alt()', '3.13.0' );

		$args = array(
			'post_type' 	=> 'section',
			'nopaging' 		=> true,
			'post_status'   => 'publish',

		 );

		$sections = get_posts( $args );

		$options = array();

		if ( ! empty( $sections ) ) {

			foreach ( $sections as $key => $value ) {

				//get parent course if assigned
				$parent_course = get_post_meta( $value->ID, '_llms_parent_course', true );

				if ( $parent_course ) {
					$title = $value->post_title . ' ( ' . get_the_title( $parent_course ) . ' )';
				} else {
					$title = $value->post_title . ' ( ' . __( 'unassigned', 'lifterlms' ) . ' )';
				}

				$options[] = array(
					'ID' 		 => $value->ID,
					'post_title' => $title,
				);

			}
		}

		echo json_encode( $options );

		wp_die();
	}

	/**
	 * Return array of students
	 * @return      array
	 * @since       ??
	 * @version     3.13.0
	 * @deprecated  3.13.0
	 */
	public function get_students() {

		llms_deprecated_function( 'LLMS_AJAX::get_students()', '3.13.0' );

		$term = array_key_exists( 'term', $_REQUEST ) ? $_REQUEST['term'] : '';

		$user_args = array(
				'include'      => array(),
				'orderby'      => 'display_name',
				'order'        => 'ASC',
				'count_total'  => false,
				'fields'       => 'all',
				'search'       => $term . '*',
				'exclude'      => $this->get_enrolled_students_ids(),
				'number'       => 30,
		);
		$all_users = get_users( $user_args );

		$users_arr = array();

		foreach ( $all_users as $user ) {
			$temp['id'] = $user->ID;
			$temp['name'] = $user->display_name . ' (' . $user->user_email . ')';
			$users_arr[] = $temp;
		}

		echo json_encode( array(
			'success' => true,
			'items' => $users_arr,
		) );

		wp_die();
	}

	/**
	 * Updates course syllabus JSON object
	 * @return      array
	 * @since       ??
	 * @version     3.24.0
	 * @deprecated  3.13.0
	 */
	public function update_syllabus() {

		llms_deprecated_function( 'LLMS_AJAX::update_syllabus()', '3.13.0' );

		$post_id  = $_REQUEST['post_id'];

		// Parse section id and create new array for comparison.
		function parse_new_sections( $new_sections_array ) {
			$array = array();

		    foreach ( $new_sections_array as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $keys => $values ) {
						if ( 'section_id' === $keys ) {
							array_push( $array, $values );
						}
					}
					parse_new_sections( $value );
				}
			}
			return $array;
		}

		// Parse section ids returned from DB and create new array for comparison.
		function parse_current_sections( $current_sections_array ) {
		    $array = array();

		    foreach ( $current_sections_array[0] as $key => $value ) {
		    	foreach ( $value as $keys => $values ) {
		    		if ( 'section_id' == $keys ) {
						array_push( $array, $values );
		    		}
		    	}
		    }
		    return $array;
		}

		// Compare arrays and determine if there are any duplicates.
		function array_has_dupes( $new_array ) {
			return count( $new_array ) !== count( array_unique( $new_array ) );
		}

		function delete_lesson_meta( $post_id ) {

			$lesson_ids = array();

			$rd_args = array(
				'post_type' => 'lesson',
				'meta_key' => '_llms_parent_course',
				'meta_value' => $post_id,
			);

			$rd_query = new WP_Query( $rd_args );

			while ( $rd_query->have_posts() ) : $rd_query->the_post();

				array_push( $lesson_ids,  $rd_query->post->ID );

			endwhile;

			wp_reset_postdata();
		}

		if ( isset( $_REQUEST ) ) {

			$success = 'no'; //default response to no.
			$new_sections_array = $_REQUEST['sections'];

			$current_sections_array = get_post_meta( $_REQUEST['post_id'], '_sections' );

			$new_array = parse_new_sections( $new_sections_array );
			$old_array = parse_current_sections( $current_sections_array );

			$result_array = array_intersect_assoc( $new_array, $old_array );

			$new_array_duplicates = array_count_values( $result_array );
			if ( array_has_dupes( $new_array ) ) {
				$success = 'no';
			} else {
				update_post_meta( $_REQUEST['post_id'], '_sections', ( '' === $_REQUEST['sections'] ) ? '' : $_REQUEST['sections'] );
				$success = 'yes';

				//Manage Section _parent_course
				//find all sections that where assigned to the course and delete the metadata
				$section_args = array(
					'post_type' => 'section',
					'meta_key' => '_llms_parent_course',
					'meta_value' => $post_id,
				);

				$section_query = new WP_Query( $section_args );

				while ( $section_query->have_posts() ) : $section_query->the_post();
					//delete all metadata

					//find all lessons that were assigned to sections and delete post_meta data
					$ols_args = array(
						'post_type' => 'lesson',
						'meta_key' => '_llms_parent_section',
						'meta_value' => $section_query->post->ID,
					);

					$ols_query = new WP_Query( $ols_args );

					while ( $ols_query->have_posts() ) : $ols_query->the_post();
						if ( $section_query->post->ID ) {
							foreach ( $new_sections_array as $key => $value ) {
								if ( $section_query->post->ID == $value['section_id'] ) {
									delete_post_meta( $ols_query->post->ID, '_llms_parent_section', $section_query->post->ID );
								}
							}
						}
					endwhile;
					//wp_reset_postdata();

					if ( $post_id ) {
						delete_post_meta( $section_query->post->ID, '_llms_parent_course', $post_id );
					}
				endwhile;
				wp_reset_postdata();

				//find all sections that are currently assigned to the course
				foreach ( $_REQUEST['sections'] as $key => $value ) {
					//update _parent_course for section ids
					update_post_meta( $value['section_id'], '_llms_parent_course', $post_id );
				}

				//Manage lesson _parent_section and _parent_course
				//find all lessons with _parent_course as $post_id and delete the metadata
				$rd_args = array(
					'post_type' => 'lesson',
					'meta_key' => '_llms_parent_course',
					'meta_value' => $post_id,
				);

				$rd_query = new WP_Query( $rd_args );

				while ( $rd_query->have_posts() ) : $rd_query->the_post();
					if ( $post_id ) {
						delete_post_meta( $rd_query->post->ID, '_llms_parent_course', $post_id );
					}
				endwhile;
				wp_reset_postdata();

				foreach ( $_REQUEST['sections'] as $key => $value ) {

					$ls_args = array(
						'post_type' => 'lesson',
						'meta_key' => '_llms_parent_section',
						'meta_value' => $value['section_id'],
					);

					$ls_query = new WP_Query( $ls_args );

					while ( $ls_query->have_posts() ) : $ls_query->the_post();
						if ( $value['section_id'] ) {
							delete_post_meta( $ls_query->post->ID, '_llms_parent_section', $value['section_id'] );
						}
					endwhile;
					wp_reset_postdata();

					foreach ( $value['lessons'] as $keys => $values ) {
						update_post_meta( $values['lesson_id'], '_llms_parent_section', $value['section_id'] );
						update_post_meta( $values['lesson_id'], '_llms_parent_course', $post_id );
					}
				}
			}// End if().
		}// End if().

		//echo json_encode($lesson_ids);
		die();

	}

}

new LLMS_AJAX();
