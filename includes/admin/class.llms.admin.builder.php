<?php
/**
 * LifterLMS Admin Course Builder
 * @since    3.13.0
 * @version  3.27.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Builder class.
 */
class LLMS_Admin_Builder {

	private static $search_term = '';

	/**
	 * Add menu items to the WP Admin Bar to allow quiz returns to the dashboad from the course builder
	 * @param    obj     $wp_admin_bar  Instance of WP_Admin_Bar
	 * @return   void
	 * @since    3.16.7
	 * @version  3.24.0
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {

		// partially lifted from `wp_admin_bar_site_menu()` in wp-includes/admin-bar.php
		if ( current_user_can( 'read' ) ) {

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'site-name',
					'id'     => 'dashboard',
					'title'  => __( 'Dashboard', 'lifterlms' ),
					'href'   => admin_url(),
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'site-name',
					'id'     => 'llms-courses',
					'title'  => __( 'Courses', 'lifterlms' ),
					'href'   => admin_url( 'edit.php?post_type=course' ),
				)
			);

			wp_admin_bar_appearance_menu( $wp_admin_bar );

		}

	}

	/**
	 * Retrieve custom field schemas
	 * @return   array
	 * @since    3.17.0
	 * @version  3.17.6
	 */
	private static function get_custom_schemas() {

		$quiz_fields = array();

		/**
		 * Handle old quiz layout compatibility API
		 * translate the old filter into the new one for quizzes
		 */
		if ( get_theme_support( 'lifterlms-quizzes' ) ) {

			llms_log( 'Filter `llms_get_quiz_theme_settings` deprecated since 3.17.6, for more information see new methods at https://lifterlms.com/docs/course-builder-custom-fields-for-developers/' );

			$theme = wp_get_theme();

			$old = llms_get_quiz_theme_setting( 'layout' );

			$field = array(
				'attribute' => $old['id'],
				'id' => $old['id'],
				'label' => $old['name'],
				'type' => ( 'select' === $old['type'] ) ? 'select' : 'radio',
				'options' => $old['options'],
			);

			if ( isset( $old['id_prefix'] ) ) {
				$field['attribute_prefix'] = $old['id_prefix'];
			}

			$quiz_fields[ sprintf( '%s_backwards_theme_group', $theme->get_stylesheet() ) ] = array(
				'title' => sprintf( __( '%s Theme Settings', 'lifterlms' ), $theme->get( 'Name' ) ),
				'toggleable' => true,
				'fields' => array( array( $field ) ),
			);

		}
		// end backwards compat

		return apply_filters( 'llms_builder_register_custom_fields', array(
			'lesson' => array(),
			'quiz' => $quiz_fields,
		) );
	}

	/**
	 * Retrieve a list of lessons the current user is allowed to clone/attach
	 * Used for ajax searching to add existing lessons
	 * @param    int        $course_id    WP Post ID of the course
	 * @param    string     $post_type    optionally search specific post type(s)
	 * @param    string     $search_term  optional search term (searches post_title)
	 * @param    integer    $page         page, used when paginating search results
	 * @return   array
	 * @since    3.14.8
	 * @version  3.16.12
	 */
	private static function get_existing_posts( $course_id, $post_type = '', $search_term = '', $page = 1 ) {

		$args = array(
			'order' => 'ASC',
			'orderby' => 'post_title',
			'paged' => $page,
			'post_status' => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => 10,
		);

		if ( $post_type ) {
			$args['post_type'] = $post_type;
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			$instructor = llms_get_instructor();
			$parents = $instructor->get( 'parent_instructors' );
			if ( ! $parents ) {
				$parents = array();
			}

			$args['author__in'] = array_unique( array_merge(
				array( get_current_user_id() ),
				$instructor->get_assistants(),
				$parents
			) );

		}

		self::$search_term = $search_term;
		add_filter( 'posts_where', array( __CLASS__, 'get_existing_posts_where' ), 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_where', array( __CLASS__, 'get_existing_posts_where' ), 10, 2 );

		$posts = array();

		if ( $query->have_posts() ) {

			foreach ( $query->posts as $post ) {

				$post = llms_get_post( $post );

				$parents = array();

				if ( method_exists( $post, 'is_orphan' ) && $post->is_orphan() ) {

					$action = 'attach';

				} else {

					$action = 'clone';

					$course_id = false;
					$lesson_id = false;

					if ( 'lesson' === $post->get( 'type' ) ) {
						$course_id = $post->get( 'parent_course' );
					} elseif ( 'llms_quiz' === $post->get( 'type' ) ) {
						$lesson_id = $post->get( 'lesson_id' );
						$course = $post->get_course();
						if ( $course ) {
							$course_id = $course->get( 'id' );
						}
					}

					if ( $lesson_id ) {
						$parents['lesson'] = sprintf( __( 'Lesson: %1$s (#%2$d)', 'lifterlms' ), '<em>' . get_the_title( $lesson_id ) . '</em>', $lesson_id );
					}
					if ( $course_id ) {
						$parents['course'] = sprintf( __( 'Course: %1$s (#%2$d)', 'lifterlms' ), '<em>' . get_the_title( $course_id ) . '</em>', $course_id );
					}
				}

				$posts[] = array(
					'action' => $action,
					'data' => $post,
					'id' => $post->get( 'id' ),
					'parents' => $parents,
					'text' => sprintf( '%1$s (#%2$d)', $post->get( 'title' ), $post->get( 'id' ) ),
				);

			}// End foreach().
		}// End if().

		$ret = array(
			'results' => $posts,
			'pagination' => array(
				'more' => ( $page < $query->max_num_pages ),
			),
		);

		return $ret;

	}

	/**
	 * Search lessons by search term during existing lesson lookups
	 * @param    string     $where      existing sql where clause
	 * @param    obj        $wp_query   WP_Query
	 * @return   string
	 * @since    3.14.8
	 * @version  3.16.12
	 */
	public function get_existing_posts_where( $where, $wp_query ) {

		if ( self::$search_term ) {
			global $wpdb;
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE "%' . esc_sql( $wpdb->esc_like( self::$search_term ) ) . '%"';
		}

		return $where;

	}

	/**
	 * Retrieve the HTML of a JS template
	 * @param    [type]     $template  [description]
	 * @return   [type]
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private static function get_template( $template, $vars = array() ) {

		ob_start();
		extract( $vars );
		include 'views/builder/' . $template . '.php';
		return ob_get_clean();

	}

	/**
	 * A terrible Rest API for the course builder
	 * @shame    gimme a break pls
	 * @param    array     $request  $_REQUEST
	 * @return   array
	 * @since    3.13.0
	 * @version  3.19.2
	 */
	public static function handle_ajax( $request ) {

		// @todo do some real error handling here
		if ( ! $request['course_id'] || ! current_user_can( 'edit_course', $request['course_id'] ) ) {
			return array();
		}

		switch ( $request['action_type'] ) {

			case 'ajax_save':

				if ( isset( $request['llms_builder'] ) ) {

					$request['llms_builder'] = stripslashes( $request['llms_builder'] );
					wp_send_json( self::heartbeat_received( array(), $request ) );

				}

			break;

			case 'get_permalink':

				$id = isset( $request['id'] ) ? absint( $request['id'] ) : false;
				if ( ! $id ) {
					return array();
				}
				$title = isset( $request['title'] ) ? sanitize_title( $request['title'] ) : null;
				$slug = isset( $request['slug'] ) ? sanitize_title( $request['slug'] ) : null;
				$link = get_sample_permalink( $id, $title, $slug );
				wp_send_json( array(
					'slug' => $link[1],
					'permalink' => str_replace( '%pagename%', $link[1], $link[0] ),
				) );

			break;

			case 'lazy_load':

				$ret = array();
				if ( isset( $request['load_id'] ) ) {
					$post = llms_get_post( absint( $request['load_id'] ) );
					$ret = $post->toArray();
				}
				wp_send_json( $ret );

			break;

			case 'search':
				$page = isset( $request['page'] ) ? $request['page'] : 1;
				$term = isset( $request['term'] ) ? sanitize_text_field( $request['term'] ) : '';
				$post_type = '';
				if ( isset( $request['post_type'] ) ) {
					if ( is_array( $request['post_type'] ) ) {
						$post_type = array_map( 'sanitize_text_field', $request['post_type'] );
					} else {
						$post_type = sanitize_text_field( $request['post_type'] );
					}
				}
				wp_send_json( self::get_existing_posts( absint( $request['course_id'] ), $post_type, $term, $page ) );
			break;

		}// End switch().

		return array();

	}

	/**
	 * Do post locking stuff on the builder
	 * Locking the course edit main screen will lock the builder and vice versa... probably need to find a way
	 * to fix that but for now this'll work just fine and if you're unhappy about it, well, sorry...
	 *
	 * @param    int     $course_id  WP Post ID
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private static function handle_post_locking( $course_id ) {

		if ( ! wp_check_post_lock( $course_id ) ) {
			$active_post_lock = wp_set_post_lock( $course_id );
		}

		?><input type="hidden" id="post_ID" value="<?php echo absint( $course_id ); ?>"><?php

if ( ! empty( $active_post_lock ) ) {
	?>
	<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" />
	<?php
}

		add_filter( 'get_edit_post_link', array( __CLASS__, 'modify_take_over_link' ), 10, 3 );
		add_action( 'admin_footer', '_admin_notice_post_locked' );

	}

	/**
	 * Handle AJAX Heartbeat received calls
	 * All builder data is sent through the heartbeat
	 * @param    array     $res   response data
	 * @param    array     $data  data from the heartbeat api
	 *                            builder data will be in the "llms_builder" array
	 * @return   array
	 * @since    3.16.0
	 * @version  3.24.2
	 */
	public static function heartbeat_received( $res, $data ) {

		// exit if there's no builder data in the heartbeat data
		if ( empty( $data['llms_builder'] ) ) {
			return $res;
		}

		// Isolate builder data & ensure slashes aren't removed.
		$data = $data['llms_builder'];

		// Escape slashes.
		// $data = json_decode( str_replace( '\\', '\\\\', $data ), true );
		$data = json_decode( $data, true );

		// setup our return
		$ret = array(
			'status' => 'success',
			'message' => esc_html__( 'Success', 'lifterlms' ),
		);

		// need a numeric ID for a course post type!
		if ( empty( $data['id'] ) || ! is_numeric( $data['id'] ) || 'course' !== get_post_type( $data['id'] ) ) {

			$ret['status'] = 'error';
			$ret['message'] = esc_html__( 'Error: Invalid or missing course ID.', 'lifterlms' );

		} elseif ( ! current_user_can( 'edit_course', $data['id'] ) ) {

			$ret['status'] = 'error';
			$ret['message'] = esc_html__( 'Error: You do not have permission to edit this course.', 'lifterlms' );

		} else {

			if ( ! empty( $data['detach'] ) && is_array( $data['detach'] ) ) {

				$ret['detach'] = self::process_detachments( $data );

			}

			if ( current_user_can( 'delete_course', $data['id'] ) ) {

				if ( ! empty( $data['trash'] ) && is_array( $data['trash'] ) ) {

					$ret['trash'] = self::process_trash( $data );

				}
			}

			if ( ! empty( $data['updates'] ) && is_array( $data['updates'] ) ) {

				$ret['updates']['sections'] = self::process_updates( $data );

			}
		}

		// Unescape slashes after saved.
		// This ensures that updates are recognized as successful during Sync comparisons.
		// $ret = json_decode( str_replace( '\\\\', '\\', json_encode( $ret ) ), true );

		// Return our data.
		$res['llms_builder'] = $ret;

		return $res;

	}

	/**
	 * Determine if an ID submitted via heartbeat data is a temporary id
	 * if so the object must be created rather than updated
	 * @param    string     $id  an ID string
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.17.0
	 */
	public static function is_temp_id( $id ) {

		return ( ! is_numeric( $id ) && 0 === strpos( $id, 'temp_' ) );

	}

	/**
	 * Modify the "Take Over" link on the post locked modal to send users to the builder when taking over a course
	 * @param    string     $link     default post edit link
	 * @param    int        $post_id  WP Post ID of the course
	 * @param    string     $context  display context
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public static function modify_take_over_link( $link, $post_id, $context ) {

		return add_query_arg( array(
			'page' => 'llms-course-builder',
			'course_id' => $post_id,
		), admin_url( 'admin.php' ) );

	}

	/**
	 * Output the page content
	 * @return   void
	 * @since    3.13.0
	 * @version  3.19.2
	 */
	public static function output() {

		global $post;

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : null;
		if ( ! $course_id || ( $course_id && 'course' !== get_post_type( $course_id ) ) ) {
			_e( 'Invalid course ID', 'lifterlms' );
			return;
		}

		$post = get_post( $course_id );

		$course = llms_get_post( $post );

		if ( ! current_user_can( 'edit_course', $course_id ) ) {
			_e( 'You cannot edit this course!', 'lifterlms' );
			return;
		}

		remove_all_actions( 'the_title' );
		remove_all_actions( 'the_content' );

		global $llms_builder_lazy_load;
		$llms_builder_lazy_load = true;
		?>

		<div class="wrap lifterlms llms-builder">

			<?php do_action( 'llms_before_builder', $course_id ); ?>

			<div class="llms-builder-main" id="llms-builder-main"></div>

			<aside class="llms-builder-sidebar" id="llms-builder-sidebar"></aside>

			<?php
				$templates = array(
					'assignment',
					'course',
					'editor',
					'elements',
					'lesson',
					'lesson-settings',
					'quiz',
					'question',
					'question-choice',
					'question-type',
					'section',
					'settings-fields',
					'sidebar',
					'utilities',
				);

			foreach ( $templates as $template ) {
				echo self::get_template( $template, array(
					'course_id' => $course_id,
				) );
			}

			?>

			<script>window.llms_builder = <?php echo json_encode( array(
				'admin_url' => admin_url(),
				'course' => $course->toArray(),
				'debug' => array(
					'enabled' => ( defined( 'LLMS_BUILDER_DEBUG' ) && LLMS_BUILDER_DEBUG ),
				),
				'questions' => array_values( llms_get_question_types() ),
				'schemas' => self::get_custom_schemas(),
				'sync' => apply_filters( 'llms_builder_sync_settings', array(
					'check_interval_ms' => 10000,
				) ),
			) ); ?></script>

			<?php do_action( 'llms_after_builder', $course_id ); ?>

		</div>

		<?php
		$llms_builder_lazy_load = false;
		self::handle_post_locking( $course_id );

	}

	/**
	 * Process lesson detachments from the heartbeat data
	 * @param    array     $data  array of lesson ids
	 * @return   array
	 * @since    3.16.0
	 * @version  3.27.0
	 */
	private static function process_detachments( $data ) {

		$ret = array();

		foreach ( $data['detach'] as $id ) {

			$res = array(
				'error' => sprintf( esc_html__( 'Unable to detach "%s". Invalid ID.', 'lifterlms' ), $id ),
				'id' => $id,
			);

			$type = get_post_type( $id );

			$post_types = apply_filters( 'llms_builder_detachable_post_types', array( 'lesson', 'llms_question', 'llms_quiz' ) );
			if ( ! is_numeric( $id ) || ! in_array( $type, $post_types ) ) {
				array_push( $ret, $res );
				continue;
			}

			$post = llms_get_post( $id );
			if ( ! is_a( $post, 'LLMS_Post_Model' ) ) {
				array_push( $ret, $res );
				continue;
			}

			if ( 'lesson' === $type ) {
				$post->set( 'parent_course', '' );
				$post->set( 'parent_section', '' );
			} elseif ( 'llms_question' === $type ) {
				$post->set( 'parent_id', '' );
			} elseif ( 'llms_quiz' === $type ) {
				$parent = $post->get_lesson();
				if ( $parent ) {
					$parent->set( 'quiz_enabled', 'no' );
					$parent->set( 'quiz', '' );
					$post->set( 'lesson_id', 0 );
				}
			}

			do_action( 'llms_builder_detach_' . $type, $post );

			unset( $res['error'] );
			array_push( $ret, $res );

		}// End foreach().

		return $ret;

	}

	/**
	 * Delete/trash elements from heartbeat data
	 * @param    array     $data  array of ids to trash/delete
	 * @return   array
	 * @since    3.16.0
	 * @version  3.17.1
	 */
	private static function process_trash( $data ) {

		$ret = array();

		foreach ( $data['trash'] as $id ) {

			$res = array(
				'error' => sprintf( esc_html__( 'Unable to delete "%s". Invalid ID.', 'lifterlms' ), $id ),
				'id' => $id,
			);

			$custom = apply_filters( 'llms_builder_trash_custom_item', null, $res, $id );
			if ( $custom ) {
				array_push( $ret, $custom );
				continue;
			}

			if ( is_numeric( $id ) ) {

				$type = get_post_type( $id );

			} else {

				$type = 'question_choice';

			}

			$post_types = apply_filters( 'llms_builder_trashable_post_types', array( 'lesson', 'llms_quiz', 'llms_question', 'question_choice', 'section' ) );
			if ( ! in_array( $type, $post_types ) ) {
				array_push( $ret, $res );
				continue;
			}

			// lessons, sections, & questions passed as numeric WP Post IDs
			if ( is_numeric( $id ) ) {

				// delete sections
				if ( in_array( $type, array( 'section', 'llms_question', 'llms_quiz' ) ) ) {
					$stat = wp_delete_post( $id, true );
				} // End if().
				else {
					$stat = wp_trash_post( $id );
				}
			} else {

				$split = explode( ':', $id );
				$question = llms_get_post( $split[0] );
				if ( $question && is_a( $question, 'LLMS_Question' ) ) {
					$stat = $question->delete_choice( $split[1] );
				} else {
					$stat = false;
				}
			}

			// both functions return false on failure
			if ( ! $stat ) {
				$res['error'] = sprintf( esc_html__( 'Error deleting %1$s "%s".', 'lifterlms' ), $type, $id );
			} else {
				unset( $res['error'] );
			}

			array_push( $ret, $res );

		}// End foreach().

		return $ret;

	}

	/**
	 * Process all the update data from the heartbeat
	 * @param    array     $data  array of course updates (all the way down the tree)
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private static function process_updates( $data ) {

		$ret = array();

		if ( ! empty( $data['updates']['sections'] ) && is_array( $data['updates']['sections'] ) ) {

			foreach ( $data['updates']['sections'] as $section_data ) {

				if ( ! isset( $section_data['id'] ) ) {
					continue;
				}

				$ret[] = self::update_section( $section_data, $data['id'] );

			}
		}

		return $ret;

	}

	/**
	 * Handle updating custom schema data
	 * @param    string     $type       model type (lesson, quiz, etc...)
	 * @param    obj        $post       LLMS_Post_Model object for the model being updated
	 * @param    array      $post_data  assoc array of raw data to update the model with
	 * @return   void
	 * @since    3.17.0
	 * @version  3.17.1
	 */
	public static function update_custom_schemas( $type, $post, $post_data ) {

		$schemas = self::get_custom_schemas();
		if ( empty( $schemas[ $type ] ) ) {
			return;
		}

		$groups = $schemas[ $type ];

		foreach ( $groups as $name => $group ) {

			// allow 3rd parties to manage their own custom save methods
			if ( apply_filters( 'llms_builder_update_custom_fields_group_' . $name, false, $post, $post_data, $groups ) ) {
				continue;
			}

			foreach ( $group['fields'] as $fields ) {

				foreach ( $fields as $field ) {

					$keys = array( $field['attribute'] );
					if ( isset( $field['switch_attribute'] ) ) {
						$keys[] = $field['switch_attribute'];
					}

					foreach ( $keys as $attr ) {

						if ( isset( $post_data[ $attr ] ) ) {

							if ( isset( $field['sanitize_callback'] ) ) {
								$val = call_user_func( $field['sanitize_callback'], $val );
							} else {
								$val = sanitize_text_field( $post_data[ $attr ] );
							}

							$attr = isset( $field['attribute_prefix'] ) ? $field['attribute_prefix'] . $attr : $attr;
							update_post_meta( $post_data['id'], $attr, $val );

						}
					}
				}
			}
		}

	}

	/**
	 * Update lesson from heartbeat data
	 * @param    array     $lessons  lesson data from heartbeat
	 * @param    obj       $section  instance of the parent LLMS_Section
	 * @return   array
	 * @since    3.16.0
	 * @version  3.17.0
	 */
	private static function update_lessons( $lessons, $section ) {

		$ret = array();

		foreach ( $lessons as $lesson_data ) {

			if ( ! isset( $lesson_data['id'] ) ) {
				continue;
			}

			$res = array_merge( $lesson_data, array(
				'orig_id' => $lesson_data['id'],
			) );

			// create a new lesson
			if ( self::is_temp_id( $lesson_data['id'] ) ) {

				$lesson = new LLMS_Lesson( 'new', array(
					'post_title' => isset( $lesson_data['title'] ) ? $lesson_data['title'] : __( 'New Lesson', 'lifterlms' ),
				) );

				// if the parent section was just created the lesson will have a temp id
				// replace it with the newly created section's real ID
				if ( ! isset( $lesson_data['parent_section'] ) || self::is_temp_id( $lesson_data['parent_section'] ) ) {
					$lesson_data['parent_section'] = $section->get( 'id' );
				}

				$created = true;

			} else {

				$lesson = llms_get_post( $lesson_data['id'] );
				$created = false;

			}

			if ( empty( $lesson ) || ! is_a( $lesson, 'LLMS_Lesson' ) ) {

				$res['error'] = sprintf( esc_html__( 'Unable to update lesson "%s". Invalid lesson ID.', 'lifterlms' ), $lesson_data['id'] );

			} else {

				// return the real ID (important when creating a new lesson)
				$res['id'] = $lesson->get( 'id' );

				$properties = array_merge( array_keys( $lesson->get_properties() ), array(
					'content',
					'title',
				) );

				$skip_props = apply_filters( 'llms_builder_update_lesson_skip_props', array( 'quiz' ) );

				// update all updateable properties
				foreach ( $properties as $prop ) {
					if ( isset( $lesson_data[ $prop ] ) && ! in_array( $prop, $skip_props ) ) {
						$lesson->set( $prop, $lesson_data[ $prop ] );
					}
				}

				// update all custom fields
				self::update_custom_schemas( 'lesson', $lesson, $lesson_data );

				// during clone's we want to ensure custom field data comes with the lesson
				if ( $created && isset( $lesson_data['custom'] ) ) {
					foreach ( $lesson_data['custom'] as $custom_key => $custom_vals ) {
						foreach ( $custom_vals as $val ) {
							add_post_meta( $lesson->get( 'id' ), $custom_key, maybe_unserialize( $val ) );
						}
					}
				}

				// ensure slug gets updated when changing title from default "New Lesson"
				if ( isset( $lesson_data['title'] ) && ! $lesson->has_modified_slug() ) {
					$lesson->set( 'name', sanitize_title( $lesson_data['title'] ) );
				}

				if ( ! empty( $lesson_data['quiz'] ) && is_array( $lesson_data['quiz'] ) ) {
					$res['quiz'] = self::update_quiz( $lesson_data['quiz'], $lesson );
				}
			}// End if().

			// allow 3rd parties to update custom data
			$res = apply_filters( 'llms_builder_update_lesson', $res, $lesson_data, $lesson, $created );

			array_push( $ret, $res );

		}// End foreach().

		return $ret;

	}

	/**
	 * Update quiz questions from heartbeat data
	 * @param    array     $questions  question data array
	 * @param    obj       $parent    instance of an LLMS_Quiz or LLMS_Question (group)
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.11
	 */
	private static function update_questions( $questions, $parent ) {

		$res = array();

		foreach ( $questions as $q_data ) {

			$ret = array_merge( $q_data, array(
				'orig_id' => $q_data['id'],
			) );

			// remove temp id if we have one so we'll create a new question
			if ( self::is_temp_id( $q_data['id'] ) ) {
				unset( $q_data['id'] );
			}

			// remove choices because we'll add them individually after creation
			$choices = ( isset( $q_data['choices'] ) && is_array( $q_data['choices'] ) ) ? $q_data['choices'] : false;
			unset( $q_data['choices'] );

			// remove child questions if it's a question group
			$questions = ( isset( $q_data['questions'] ) && is_array( $q_data['questions'] ) ) ? $q_data['questions'] : false;
			unset( $q_data['questions'] );

			$question_id = $parent->questions()->update_question( $q_data );

			if ( ! $question_id ) {

				$ret['error'] = sprintf( esc_html__( 'Unable to update question "%s". Invalid question ID.', 'lifterlms' ), $q_data['id'] );

			} else {

				$ret['id'] = $question_id;

				$question = $parent->questions()->get_question( $question_id );

				if ( $choices ) {

					$ret['choices'] = array();

					foreach ( $choices as $c_data ) {

						$choice_res = array_merge( $c_data, array(
							'orig_id' => $c_data['id'],
						) );

						unset( $c_data['question_id'] );

						// remove the temp ID so that we create it if it's new
						if ( self::is_temp_id( $c_data['id'] ) ) {
							unset( $c_data['id'] );
						}

						$choice_id = $question->update_choice( $c_data );
						if ( ! $choice_id ) {
							$choice_res['error'] = sprintf( esc_html__( 'Unable to update choice "%s". Invalid choice ID.', 'lifterlms' ), $c_data['id'] );
						} else {
							$choice_res['id'] = $choice_id;
						}

						array_push( $ret['choices'], $choice_res );

					}
				} elseif ( $questions ) {

					$ret['questions'] = self::update_questions( $questions, $question );

				}
			}// End if().

			array_push( $res, $ret );

		}// End foreach().

		return $res;

	}

	/**
	 * Update quizzes during heartbeats
	 * @param    array     $quiz_data  array of quiz updates
	 * @param    obj       $lesson     instance of the parent LLMS_Lesson
	 * @return   array
	 * @since    3.16.0
	 * @version  3.17.6
	 */
	private static function update_quiz( $quiz_data, $lesson ) {

		$res = array_merge( $quiz_data, array(
			'orig_id' => $quiz_data['id'],
		) );

		// create a quiz
		if ( self::is_temp_id( $quiz_data['id'] ) ) {

			$quiz = new LLMS_Quiz( 'new' );

			// update existing quiz
		} else {

			$quiz = llms_get_post( $quiz_data['id'] );

		}

		$lesson->set( 'quiz', $quiz->get( 'id' ) );
		$lesson->set( 'quiz_enabled', 'yes' );

		// we don't have a proper quiz to work with...
		if ( empty( $quiz ) || ! is_a( $quiz, 'LLMS_Quiz' ) ) {

			$res['error'] = sprintf( esc_html__( 'Unable to update quiz "%s". Invalid quiz ID.', 'lifterlms' ), $quiz_data['id'] );

		} else {

			// return the real ID (important when creating a new quiz)
			$res['id'] = $quiz->get( 'id' );

			// if the parent lesson was just created the lesson will have a temp id
			// replace it with the newly created lessons's real ID
			if ( ! isset( $quiz_data['lesson_id'] ) || self::is_temp_id( $quiz_data['lesson_id'] ) ) {
				$quiz_data['lesson_id'] = $lesson->get( 'id' );
			}

			$properties = array_merge( array_keys( $quiz->get_properties() ), array(
				// 'content',
				'status',
				'title',
			) );

			// update all updateable properties
			foreach ( $properties as $prop ) {
				if ( isset( $quiz_data[ $prop ] ) ) {
					$quiz->set( $prop, $quiz_data[ $prop ] );
				}
			}

			if ( isset( $quiz_data['questions'] ) && is_array( $quiz_data['questions'] ) ) {
				$res['questions'] = self::update_questions( $quiz_data['questions'], $quiz );
			}

			// update all custom fields
			self::update_custom_schemas( 'quiz', $quiz, $quiz_data );

		}// End if().

		return $res;

	}

	/**
	 * Update a section with data from the heartbeat
	 * @param    array     $section_data  array of section data
	 * @param    obj       $course_id     instance of the parent LLMS_Course
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.11
	 */
	private static function update_section( $section_data, $course_id ) {

		$res = array_merge( $section_data, array(
			'orig_id' => $section_data['id'],
		) );

		// create a new section
		if ( self::is_temp_id( $section_data['id'] ) ) {

			$section = new LLMS_Section( 'new' );
			$section->set( 'parent_course', $course_id );

			// update existing section
		} else {

			$section = llms_get_post( $section_data['id'] );

		}

		// we don't have a proper section to work with...
		if ( empty( $section ) || ! is_a( $section, 'LLMS_Section' ) ) {
			$res['error'] = sprintf( esc_html__( 'Unable to update section "%s". Invalid section ID.', 'lifterlms' ), $section_data['id'] );
		} else {

			// return the real ID (important when creating a new section)
			$res['id'] = $section->get( 'id' );

			// run through all possible updated fields
			foreach ( array( 'order', 'title' ) as $key ) {

				// update those that were sent through
				if ( isset( $section_data[ $key ] ) ) {

					$section->set( $key, $section_data[ $key ] );

				}
			}

			if ( isset( $section_data['lessons'] ) && is_array( $section_data['lessons'] ) ) {

				$res['lessons'] = self::update_lessons( $section_data['lessons'], $section );

			}
		}

		return $res;

	}

}
