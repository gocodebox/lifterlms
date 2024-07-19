<?php
/**
 * LifterLMS Admin Course Builder
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.13.0
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Builder class
 *
 * @since 3.13.0
 * @since 3.30.0 Fixed issues related to custom field sanitization.
 * @since 3.37.11 Made method `get_existing_posts_where()` static.
 * @since 3.37.12 Refactored the `process_trash()` method.
 *                Added new filter, `llms_builder_{$post_type}_force_delete` to allow control of how post type deletion is handled
 *                when deleted via the builder.
 * @since 3.38.0 Improve backwards compatibility handling for the `llms_get_quiz_theme_settings` filter.
 * @since 3.38.2 On quiz saving, made sure that a question as a type set, otherwise set it by default to `'choice'`.
 */
class LLMS_Admin_Builder {

	/**
	 * Search term string used by `get_existing_posts_where()` when querying for existing posts to clone/add to a course.
	 *
	 * @var string
	 */
	private static $search_term = '';

	/**
	 * Add menu items to the WP Admin Bar to allow quiz returns to the dashboard from the course builder
	 *
	 * @since 3.16.7
	 * @since 3.24.0 Unknown.
	 *
	 * @param  WP_Admin_Bar $wp_admin_bar Instance of WP_Admin_Bar
	 * @return void
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {

		// Partially lifted from `wp_admin_bar_site_menu()` in wp-includes/admin-bar.php.
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
	 * Retrieve the current user's builder autosave preferences
	 *
	 * Defaults to enabled for users who have never configured a setting value.
	 *
	 * @since 4.14.0
	 *
	 * @return string Either "yes" or "no".
	 */
	protected static function get_autosave_status() {

		$autosave = get_user_option( 'llms_builder_autosave' );
		$autosave = empty( $autosave ) ? 'no' : $autosave;

		/**
		 * Gets the status of autosave for the builder
		 *
		 * This can be configured on a per-user basis in the user's profile screen on the WP Admin Panel.
		 *
		 * @since 4.14.0
		 *
		 * @param string $autosave Status of autosave for the current user. Either "yes" or "no".
		 */
		return apply_filters( 'llms_builder_autosave_enabled', $autosave );
	}

	/**
	 * Retrieve custom field schemas
	 *
	 * @since 3.17.0
	 * @since 3.17.6 Add backwards compatibility for the deprecated `llms_get_quiz_theme_settings` filter.
	 * @since 3.38.0 Only run backwards compatibility for `llms_get_quiz_theme_settings` when the filter is being used.
	 *
	 * @return array
	 */
	private static function get_custom_schemas() {

		$quiz_fields = array();

		/**
		 * Handle old quiz layout compatibility API:
		 * Translate the old filter into the new one for quizzes.
		 */
		if ( get_theme_support( 'lifterlms-quizzes' ) && has_filter( 'llms_get_quiz_theme_settings' ) ) {

			$theme = wp_get_theme();

			$old = llms_get_quiz_theme_setting( 'layout' );

			$field = array(
				'attribute' => $old['id'],
				'id'        => $old['id'],
				'label'     => $old['name'],
				'type'      => ( 'select' === $old['type'] ) ? 'select' : 'radio',
				'options'   => $old['options'],
			);

			if ( isset( $old['id_prefix'] ) ) {
				$field['attribute_prefix'] = $old['id_prefix'];
			}

			$quiz_fields[ sprintf( '%s_backwards_theme_group', $theme->get_stylesheet() ) ] = array(
				// Translators: %s = Theme name.
				'title'      => sprintf( __( '%s Theme Settings', 'lifterlms' ), $theme->get( 'Name' ) ),
				'toggleable' => true,
				'fields'     => array( array( $field ) ),
			);

		}

		/**
		 * Add custom fields to the LifterLMS Builder.
		 *
		 * @since 3.17.0
		 *
		 * @link https://lifterlms.com/docs/course-builder-custom-fields-for-developers
		 *
		 * @param array[] $fields Array of post types containing arrays of custom field data.
		 */
		return apply_filters(
			'llms_builder_register_custom_fields',
			array(
				'lesson' => array(),
				'quiz'   => $quiz_fields,
			)
		);
	}

	/**
	 * Retrieve a list of lessons the current user is allowed to clone/attach
	 *
	 * Used for ajax searching to add existing lessons.
	 *
	 * @since 3.14.8
	 * @since 3.16.12 Unknown.
	 * @since 5.8.0 Allow LMS managers to get all lessons. {@link https://github.com/gocodebox/lifterlms/issues/1849}.
	 *              Removed unused `$course_id` parameter.
	 *
	 * @param string $post_type   Optional. Search specific post type(s). By default searches for all post types.
	 * @param string $search_term Optional. Search term (searches post_title). Default is empty string.
	 * @param int    $page        Optional. Used when paginating search results. Default is `1`.
	 * @return array
	 */
	private static function get_existing_posts( $post_type = '', $search_term = '', $page = 1 ) {

		$args = array(
			'order'          => 'ASC',
			'orderby'        => 'post_title',
			'paged'          => $page,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => 10,
		);

		if ( $post_type ) {
			$args['post_type'] = $post_type;
		}

		if ( ! current_user_can( 'manage_lifterlms' ) ) {

			$instructor = llms_get_instructor();
			$parents    = $instructor->get( 'parent_instructors' );
			if ( ! $parents ) {
				$parents = array();
			}

			$args['author__in'] = array_unique(
				array_merge(
					array( get_current_user_id() ),
					$instructor->get_assistants(),
					$parents
				)
			);

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
						$course    = $post->get_course();
						if ( $course ) {
							$course_id = $course->get( 'id' );
						}
					}

					if ( $lesson_id ) {
						// Translators: %1$s = Lesson title; %2$d = Lesson id.
						$parents['lesson'] = sprintf( __( 'Lesson: %1$s (#%2$d)', 'lifterlms' ), '<em>' . get_the_title( $lesson_id ) . '</em>', $lesson_id );
					}
					if ( $course_id ) {
						// Translators: %1$s = Course title; %2$d - Course id.
						$parents['course'] = sprintf( __( 'Course: %1$s (#%2$d)', 'lifterlms' ), '<em>' . get_the_title( $course_id ) . '</em>', $course_id );
					}
				}

				$posts[] = array(
					'action'  => $action,
					'data'    => $post,
					'id'      => $post->get( 'id' ),
					'parents' => $parents,
					'text'    => sprintf( '%1$s (#%2$d)', $post->get( 'title' ), $post->get( 'id' ) ),
				);

			}
		}

		$ret = array(
			'results'    => $posts,
			'pagination' => array(
				'more' => ( $page < $query->max_num_pages ),
			),
		);

		return $ret;
	}

	/**
	 * Search lessons by search term during existing lesson lookups
	 *
	 * @since 3.14.8
	 * @since 3.16.12 Unknown.
	 * @since 3.37.11 Made method static.
	 *
	 * @param string   $where    Existing sql where clause.
	 * @param WP_QUery $wp_query Query object.
	 * @return string
	 */
	public static function get_existing_posts_where( $where, $wp_query ) {

		if ( self::$search_term ) {
			global $wpdb;
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE "%' . esc_sql( $wpdb->esc_like( self::$search_term ) ) . '%"';
		}

		return $where;
	}

	/**
	 * Retrieve the HTML of a JS template
	 *
	 * @since 3.16.0
	 *
	 * @param string $template Template file slug.
	 * @return string
	 */
	private static function get_template( $template, $vars = array() ) {

		ob_start();
		extract( $vars );
		include 'views/builder/' . $template . '.php';
		return ob_get_clean();
	}

	/**
	 * A terrible Rest API for the course builder
	 *
	 * @since 3.13.0
	 * @since 3.19.2 Unknown.
	 * @since 4.16.0 Remove all filters/actions applied to the title/content when handling the ajax_save by deafault.
	 *               This is specially to prevent plugin conflicts, see https://github.com/gocodebox/lifterlms/issues/1530.
	 * @since 4.17.0 Remove `remove_all_*` hooks added in version 4.16.0.
	 *
	 * @param array $request $_REQUEST
	 * @return array
	 */
	public static function handle_ajax( $request ) {

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
				$slug  = isset( $request['slug'] ) ? sanitize_title( $request['slug'] ) : null;
				$link  = get_sample_permalink( $id, $title, $slug );
				wp_send_json(
					array(
						'slug'      => $link[1],
						'permalink' => str_replace( '%pagename%', $link[1], $link[0] ),
					)
				);

				break;

			case 'lazy_load':
				$ret = array();
				if ( isset( $request['load_id'] ) ) {
					$post = llms_get_post( absint( $request['load_id'] ) );
					$ret  = $post->toArray();
				}
				wp_send_json( $ret );

				break;

			case 'search':
				$page      = isset( $request['page'] ) ? $request['page'] : 1;
				$term      = isset( $request['term'] ) ? sanitize_text_field( $request['term'] ) : '';
				$post_type = '';
				if ( isset( $request['post_type'] ) ) {
					if ( is_array( $request['post_type'] ) ) {
						$post_type = array_map( 'sanitize_text_field', $request['post_type'] );
					} else {
						$post_type = sanitize_text_field( $request['post_type'] );
					}
				}
				wp_send_json( self::get_existing_posts( $post_type, $term, $page ) );
				break;

		}

		return array();
	}

	/**
	 * Do post locking stuff on the builder
	 *
	 * Locking the course edit main screen will lock the builder and vice versa... probably need to find a way
	 * to fix that but for now this'll work just fine and if you're unhappy about it, well, sorry...
	 *
	 * @since 3.13.0
	 *
	 * @param int $course_id WP Post ID.
	 * @return void
	 */
	private static function handle_post_locking( $course_id ) {

		if ( ! wp_check_post_lock( $course_id ) ) {
			$active_post_lock = wp_set_post_lock( $course_id );
		}

		?><input type="hidden" id="post_ID" value="<?php echo absint( $course_id ); ?>">
		<?php

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
	 *
	 * All builder data is sent through the heartbeat.

	 * @since 3.16.0
	 * @since 3.24.2 Unknown.
	 *
	 * @param array $res  Response data.
	 * @param array $data Data from the heartbeat api.
	 *                    Builder data will be in the "llms_builder" array.
	 * @return array
	 */
	public static function heartbeat_received( $res, $data ) {

		// Exit if there's no builder data in the heartbeat data.
		if ( empty( $data['llms_builder'] ) ) {
			return $res;
		}

		// Isolate builder data & ensure slashes aren't removed.
		$data = $data['llms_builder'];

		// Escape slashes.
		$data = json_decode( $data, true );

		// Setup our return.
		$ret = array(
			'status'  => 'success',
			'message' => esc_html__( 'Success', 'lifterlms' ),
		);

		// Need a numeric ID for a course post type!
		if ( empty( $data['id'] ) || ! is_numeric( $data['id'] ) || 'course' !== get_post_type( $data['id'] ) ) {

			$ret['status']  = 'error';
			$ret['message'] = esc_html__( 'Error: Invalid or missing course ID.', 'lifterlms' );

		} elseif ( ! current_user_can( 'edit_course', $data['id'] ) ) {

			$ret['status']  = 'error';
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
		// phpcs:ignore -- commented out code
		// $ret = json_decode( str_replace( '\\\\', '\\', json_encode( $ret ) ), true );

		// Return our data.
		$res['llms_builder'] = $ret;

		return $res;
	}

	/**
	 * Determine if an ID submitted via heartbeat data is a temporary id.
	 *
	 * If so the object must be created rather than updated
	 *
	 * @since 3.16.0
	 * @since 3.17.0
	 *
	 * @param string $id An ID string.
	 * @return bool
	 */
	public static function is_temp_id( $id ) {

		return ( ! is_numeric( $id ) && 0 === strpos( $id, 'temp_' ) );
	}

	/**
	 * Modify the "Take Over" link on the post locked modal to send users to the builder when taking over a course
	 *
	 * @since 3.13.0
	 *
	 * @param string $link    Default post edit link.
	 * @param int    $post_id WP Post ID of the course.
	 * @param string $context Display context.
	 * @return string
	 */
	public static function modify_take_over_link( $link, $post_id, $context ) {

		return add_query_arg(
			array(
				'page'      => 'llms-course-builder',
				'course_id' => $post_id,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Output the page content
	 *
	 * @since 3.13.0
	 * @since 3.19.2 Unknown.
	 * @since 4.14.0 Added builder autosave preference defaults.
	 * @since 7.2.0 Added video explainer template.
	 * @since 7.6.0 Removed video explainer template.
	 *
	 * @return void
	 */
	public static function output() {

		global $post;

		$course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : null;
		if ( ! $course_id || ( $course_id && 'course' !== get_post_type( $course_id ) ) ) {
			esc_html_e( 'Invalid course ID', 'lifterlms' );
			return;
		}

		$post = get_post( $course_id );

		if ( ! current_user_can( 'edit_course', $course_id ) ) {
			esc_html_e( 'You cannot edit this course!', 'lifterlms' );
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			wp_update_post(
				array(
					'ID'          => $course_id,
					'post_status' => 'draft',
					'post_title'  => __( 'New Course', 'lifterlms' ),
				)
			);

			$post = get_post( $course_id );
		}

		$course = llms_get_post( $post );

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
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
					echo self::get_template(
						$template,
						array(
							'course_id' => $course_id,
						)
					);
					// phpcs:enable
				}

				?>

			<script>window.llms_builder =
			<?php
			echo json_encode(
				/**
				 * Filters the settings passed to the builder.
				 *
				 * @since 7.2.0
				 *
				 * @param array $settings Associative array of settings passed to the LifterLMS course builder.
				 */
				apply_filters(
					'llms_builder_settings',
					array(
						'autosave'               => self::get_autosave_status(),
						'admin_url'              => admin_url(),
						'course'                 => $course->toArray(),
						'debug'                  => array(
							'enabled' => ( defined( 'LLMS_BUILDER_DEBUG' ) && LLMS_BUILDER_DEBUG ),
						),
						'questions'              => array_values( llms_get_question_types() ),
						'schemas'                => self::get_custom_schemas(),
						'sync'                   => apply_filters(
							/**
							 * Filters the sync builder settings.
							 *
							 * @since 3.16.0
							 *
							 * @param array $settings Associative array of settings passed to the LifterLMS course builder used for the sync.
							 */
							'llms_builder_sync_settings',
							array(
								'check_interval_ms' => ( 'yes' === self::get_autosave_status() ? 10000 : 1000 ),
							)
						),
						'enable_video_explainer' => true,
						'home_url'               => home_url(),
					)
				)
			);
			?>
			</script>

			<?php do_action( 'llms_after_builder', $course_id ); ?>

		</div>

		<?php
		$llms_builder_lazy_load = false;
		self::handle_post_locking( $course_id );
	}

	/**
	 * Process lesson detachments from the heartbeat data
	 *
	 * @since 3.16.0
	 * @since 3.27.0 Unknown.
	 *
	 * @param array $data Array of lesson ids.
	 * @return array
	 */
	private static function process_detachments( $data ) {

		$ret = array();

		foreach ( $data['detach'] as $id ) {

			$res = array(
				// Translators: %s = Item id.
				'error' => sprintf( esc_html__( 'Unable to detach "%s". Invalid ID.', 'lifterlms' ), $id ),
				'id'    => $id,
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

		}

		return $ret;
	}

	/**
	 * Delete/trash elements from heartbeat data
	 *
	 * @since 3.16.0
	 * @since 3.17.1 Unknown.
	 * @since 3.37.12 Refactored method to reduce method complexity.
	 *
	 * @param array $data Array of ids to trash/delete.
	 * @return array[] Array of arrays containing information about the deleted items.
	 */
	private static function process_trash( $data ) {

		$ret = array();

		foreach ( $data['trash'] as $id ) {
			$ret[] = self::process_trash_item( $id );
		}

		return $ret;
	}

	/**
	 * Trash (or delete) a single item
	 *
	 * @since 3.37.12
	 *
	 * @param mixed $id Item id. Usually a WP_Post ID but can also be custom ID strings.
	 * @return array Associative array containing information about the trashed item.
	 *               On success returns an array with an `id` key corresponding to the item's id.
	 *               On failure returns the `id` as well as an `error` key which is a string describing the error.
	 */
	private static function process_trash_item( $id ) {

		// Default response.
		$res = array(
			// Translators: %s = Item id.
			'error' => sprintf( esc_html__( 'Unable to delete "%s". Invalid ID.', 'lifterlms' ), $id ),
			'id'    => $id,
		);

		/**
		 * Custom or 3rd party items can perform custom deletion actions using this filter.
		 *
		 * Return an associative array containing at least the `$id` to cease execution and have
		 * the custom item returned via the `process_trash()` method.
		 *
		 * A successful deletion return should be: `array( 'id' => $id )`.
		 *
		 * A failure should contain an error message in a second array member:
		 * `array( 'id' => $id, 'error' => esc_html__( 'My error message', 'my-domain' ) )`.
		 *
		 * @since Unknown.
		 *
		 * @param null|array $trash_response Denotes the trash response. See description above for details.
		 * @param array      $res            The initial default error response which can be modified for your needs and then returned.
		 * @param mixed      $id             The ID of the course element. Usually a WP_Post id.
		 */
		$custom = apply_filters( 'llms_builder_trash_custom_item', null, $res, $id );
		if ( $custom ) {
			return $custom;
		}

		// Determine the element's post type.
		$type = is_numeric( $id ) ? get_post_type( $id ) : false;

		if ( $type ) {
			$status = self::process_trash_item_post_type( $id, $type );
		} else {
			$status = self::process_trash_item_non_post_type( $id );
		}

		// Error deleting.
		if ( is_wp_error( $status ) ) {
			$res['error'] = $status->get_error_message();

		} elseif ( true === $status ) {
			// Success.
			unset( $res['error'] );

		}

		return $res;
	}

	/**
	 * Delete non-post type elements
	 *
	 * Currently handles deletion of question choices. In the future additional non-post type elements
	 * may be handled by this method.
	 *
	 * @since 3.37.12
	 *
	 * @param string $id Custom item ID. This should be a question choice id in the format of "{$question_id}:{$choice_id}".
	 * @return null|true|WP_Error `null` when the $id cannot be parsed into a question choice id.
	 *                            `true` on success.
	 *                            `WP_Error` when an error is encountered.
	 */
	private static function process_trash_item_non_post_type( $id ) {

		// Can't process.
		if ( false === strpos( $id, ':' ) ) {
			return null;
		}

		$split    = explode( ':', $id );
		$question = llms_get_post( $split[0] );

		// Not a question choice.
		if ( ! $question || ! is_a( $question, 'LLMS_Question' ) ) {
			return null;
		}

		// Error.
		if ( ! $question->delete_choice( $split[1] ) ) {
			// Translators: %s = Question choice ID.
			return new WP_Error( 'llms_builder_trash_custom_item', sprintf( esc_html__( 'Error deleting the question choice "%s"', 'lifterlms' ), $id ) );
		}

		// Success.
		return true;
	}

	/**
	 * Delete / Trash a post type
	 *
	 * @since 3.37.12
	 *
	 * @param int    $id        WP_Post ID.
	 * @param string $post_type Post type name.
	 * @return boolean|WP_Error `true` when successfully deleted or trashed.
	 *                          `WP_Error` for unsupported post types or when a deletion error is encountered.
	 */
	private static function process_trash_item_post_type( $id, $post_type ) {

		// Used for errors.
		$obj = get_post_type_object( $post_type );

		/**
		 * Filter course elements that can be deleted or trashed via the course builder.
		 *
		 * Note that the use of "trash" in the filter name is not semantically correct as this filter does not guarantee
		 * that the element will be sent to the trash. Use the filter `llms_builder_trash_{$post_type}_force_delete` to
		 * determine if the element is sent to the trash or deleted immediately.
		 *
		 * @since Unknown
		 * @since 3.37.12 The "question_choice" item was removed from the default list and is being handled as a "custom item".
		 *
		 * @param string[] $post_types Array of post type names.
		 */
		$post_types = apply_filters( 'llms_builder_trashable_post_types', array( 'lesson', 'llms_quiz', 'llms_question', 'section' ) );
		if ( ! in_array( $post_type, $post_types, true ) ) {
			// Translators: %s = Post type name.
			return new WP_Error( 'llms_builder_trash_unsupported_post_type', sprintf( esc_html__( '%s cannot be deleted via the Course Builder.', 'lifterlms' ), $obj->labels->name ) );
		}

		// Default force value: these post types are force deleted and others are moved to the trash.
		$force = in_array( $post_type, array( 'section', 'llms_question', 'llms_quiz' ), true );

		/**
		 * Determine whether or not a post type should be moved to the trash or deleted when trashed via the Course Builder.
		 *
		 * The dynamic portion of this hook, `$post_type`, refers to the post type name of the post that's being trashed.
		 *
		 * By default all post types are moved to trash except for `section`, `llms_question`, and `llms_quiz` post types.
		 *
		 * @since 3.37.12
		 *
		 * @param boolean $force If `true` the post is deleted, if `false` it will be moved to the trash.
		 * @param int     $id    WP_Post ID of the post being trashed.
		 */
		$force = apply_filters( "llms_builder_{$post_type}_force_delete", $force, $id );

		// Delete or trash the post.
		$res = $force ? wp_delete_post( $id, true ) : wp_trash_post( $id );
		if ( ! $res ) {
			// Translators: %1$s = Post type singular name; %2$d = Post id.
			return new WP_Error( 'llms_builder_trash_post_type', sprintf( esc_html__( 'Error deleting the %1$s "%2$d".', 'lifterlms' ), $obj->labels->singular_name, $id ) );
		}

		return true;
	}

	/**
	 * Process all the update data from the heartbeat
	 *
	 * @since 3.16.0
	 *
	 * @param array $data Array of course updates (all the way down the tree).
	 * @return array
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
	 *
	 * @since 3.17.0
	 * @since 3.30.0 Fixed typo preventing fields specifying a custom callback from working.
	 * @since 3.30.0 Array fields will run field values through `sanitize_text_field()` instead of requiring a custom sanitization callback.
	 *
	 * @param string          $type Model type (lesson, quiz, etc...).
	 * @param LLMS_Post_Model $post LLMS_Post_Model object for the model being updated.
	 * @param array           $post_data Assoc array of raw data to update the model with.
	 * @return void
	 */
	public static function update_custom_schemas( $type, $post, $post_data ) {

		$schemas = self::get_custom_schemas();
		if ( empty( $schemas[ $type ] ) ) {
			return;
		}

		$groups = $schemas[ $type ];

		foreach ( $groups as $name => $group ) {

			// Allow 3rd parties to manage their own custom save methods.
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
								$val = call_user_func( $field['sanitize_callback'], $post_data[ $attr ] );
							} elseif ( is_array( $post_data[ $attr ] ) ) {
									$val = array_map( 'sanitize_text_field', $post_data[ $attr ] );
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
	 * Update lesson from heartbeat data.
	 *
	 * @since 3.16.0
	 * @since 5.1.3 Made sure a lesson moved in a just created section is correctly assigned to it.
	 * @since 7.3.0 Skip revision creation when creating a brand new lesson.
	 *
	 * @param array        $lessons Lesson data from heartbeat.
	 * @param LLMS_Section $section instance of the parent LLMS_Section.
	 * @return array
	 */
	private static function update_lessons( $lessons, $section ) {

		$ret = array();

		foreach ( $lessons as $lesson_data ) {

			if ( ! isset( $lesson_data['id'] ) ) {
				continue;
			}

			$res = array_merge(
				$lesson_data,
				array(
					'orig_id' => $lesson_data['id'],
				)
			);

			// Create a new lesson.
			if ( self::is_temp_id( $lesson_data['id'] ) ) {

				$lesson = new LLMS_Lesson(
					'new',
					array(
						'post_title' => isset( $lesson_data['title'] ) ? $lesson_data['title'] : __( 'New Lesson', 'lifterlms' ),
					)
				);

				$created = true;

			} else {

				$lesson  = llms_get_post( $lesson_data['id'] );
				$created = false;

			}

			if ( empty( $lesson ) || ! is_a( $lesson, 'LLMS_Lesson' ) ) {

				// Translators: %s = Lesson post id.
				$res['error'] = sprintf( esc_html__( 'Unable to update lesson "%s". Invalid lesson ID.', 'lifterlms' ), $lesson_data['id'] );

			} else {

				// Don't create useless revision on "creating".
				add_filter( 'wp_revisions_to_keep', '__return_zero', 999 );

				/**
				 * If the parent section was just created the lesson will have a temp id
				 * replace it with the newly created section's real ID.
				 */
				if ( ! isset( $lesson_data['parent_section'] ) || self::is_temp_id( $lesson_data['parent_section'] ) ) {
					$lesson_data['parent_section'] = $section->get( 'id' );
				}

				// Return the real ID (important when creating a new lesson).
				$res['id'] = $lesson->get( 'id' );

				$properties = array_merge(
					array_keys( $lesson->get_properties() ),
					array(
						'content',
						'title',
					)
				);

				$skip_props = apply_filters( 'llms_builder_update_lesson_skip_props', array( 'quiz' ) );

				// Update all updatable properties.
				foreach ( $properties as $prop ) {
					if ( isset( $lesson_data[ $prop ] ) && ! in_array( $prop, $skip_props, true ) ) {
						$lesson->set( $prop, $lesson_data[ $prop ] );
					}
				}

				// Update all custom fields.
				self::update_custom_schemas( 'lesson', $lesson, $lesson_data );

				// During clone's we want to ensure custom field data comes with the lesson.
				if ( $created && isset( $lesson_data['custom'] ) ) {
					foreach ( $lesson_data['custom'] as $custom_key => $custom_vals ) {
						foreach ( $custom_vals as $val ) {
							add_post_meta( $lesson->get( 'id' ), $custom_key, maybe_unserialize( $val ) );
						}
					}
				}

				// Ensure slug gets updated when changing title from default "New Lesson".
				if ( isset( $lesson_data['title'] ) && ! $lesson->has_modified_slug() ) {
					$lesson->set( 'name', sanitize_title( $lesson_data['title'] ) );
				}

				// Remove revision prevention.
				remove_filter( 'wp_revisions_to_keep', '__return_zero', 999 );

				if ( ! empty( $lesson_data['quiz'] ) && is_array( $lesson_data['quiz'] ) ) {
					$res['quiz'] = self::update_quiz( $lesson_data['quiz'], $lesson );
				}
			}

			// Allow 3rd parties to update custom data.
			$res = apply_filters( 'llms_builder_update_lesson', $res, $lesson_data, $lesson, $created );

			array_push( $ret, $res );

		}

		return $ret;
	}

	/**
	 * Update quiz questions from heartbeat data
	 *
	 * @since 3.16.0
	 * @since 3.16.11 Unknown.
	 * @since 3.38.2 Make sure that a question as a type set, otherwise set it by default to `'choice'`.
	 *
	 * @param array                   $questions Question data array.
	 * @param LLMS_Quiz|LLMS_Question $parent    Instance of an LLMS_Quiz or LLMS_Question (group).
	 * @return array
	 */
	private static function update_questions( $questions, $parent ) {

		$res = array();

		foreach ( $questions as $q_data ) {

			$ret = array_merge(
				$q_data,
				array(
					'orig_id' => $q_data['id'],
				)
			);

			// Remove temp id if we have one so we'll create a new question.
			if ( self::is_temp_id( $q_data['id'] ) ) {
				unset( $q_data['id'] );
			}

			// Remove choices because we'll add them individually after creation.
			$choices = ( isset( $q_data['choices'] ) && is_array( $q_data['choices'] ) ) ? $q_data['choices'] : false;
			unset( $q_data['choices'] );

			// Remove child questions if it's a question group.
			$questions = ( isset( $q_data['questions'] ) && is_array( $q_data['questions'] ) ) ? $q_data['questions'] : false;
			unset( $q_data['questions'] );

			$question_id = $parent->questions()->update_question( $q_data );

			if ( ! $question_id ) {

				// Translators: %s = Question post id.
				$ret['error'] = sprintf( esc_html__( 'Unable to update question "%s". Invalid question ID.', 'lifterlms' ), $q_data['id'] );

			} else {

				$ret['id'] = $question_id;

				$question = $parent->questions()->get_question( $question_id );

				/**
				 * When saving a question, make sure that it has a question type set
				 * otherwise set it by default to `'choice'`.
				 */
				if ( ! $question->get( 'question_type', true ) ) {
					$question->set( 'question_type', 'choice' );
				}

				if ( $choices ) {

					$ret['choices'] = array();

					foreach ( $choices as $c_data ) {

						$choice_res = array_merge(
							$c_data,
							array(
								'orig_id' => $c_data['id'],
							)
						);

						unset( $c_data['question_id'] );

						// Remove the temp ID so that we create it if it's new.
						if ( self::is_temp_id( $c_data['id'] ) ) {
							unset( $c_data['id'] );
						}

						$choice_id = $question->update_choice( $c_data );
						if ( ! $choice_id ) {
							// Translators: %s = Question choice ID.
							$choice_res['error'] = sprintf( esc_html__( 'Unable to update choice "%s". Invalid choice ID.', 'lifterlms' ), $c_data['id'] );
						} else {
							$choice_res['id'] = $choice_id;

							if ( isset( $c_data['choice']['id'] ) ) {
								// The quiz IDs are needed for later verification of access by the protected media filters.
								$quiz_ids = get_post_meta( $c_data['choice']['id'], '_llms_quiz_id', true );
								if ( ! is_array( $quiz_ids ) ) {
									$quiz_ids = array();
								}
								$quiz_id = $parent->get( 'parent_id' ) ? $parent->get( 'parent_id' ) : $parent->get( 'id' );
								if ( ! in_array( $quiz_id, $quiz_ids ) ) {
									$quiz_ids[] = $quiz_id;
								}
								update_post_meta( $c_data['choice']['id'], '_llms_quiz_id', $quiz_ids );
							}
						}

						array_push( $ret['choices'], $choice_res );

					}
				} elseif ( $questions ) {

					$ret['questions'] = self::update_questions( $questions, $question );

				}
			}

			array_push( $res, $ret );

		}

		return $res;
	}

	/**
	 * Update quizzes during heartbeats
	 *
	 * @since 3.16.0
	 * @since 3.17.6 Unknown.
	 *
	 * @param array       $quiz_data Array of quiz updates.
	 * @param LLMS_Lesson $lesson    Instance of the parent LLMS_Lesson.
	 * @return array
	 */
	private static function update_quiz( $quiz_data, $lesson ) {

		$res = array_merge(
			$quiz_data,
			array(
				'orig_id' => $quiz_data['id'],
			)
		);

		// Create a quiz.
		if ( self::is_temp_id( $quiz_data['id'] ) ) {

			$quiz = new LLMS_Quiz( 'new' );

			// Update existing quiz.
		} else {

			$quiz = llms_get_post( $quiz_data['id'] );

		}

		$lesson->set( 'quiz', $quiz->get( 'id' ) );
		$lesson->set( 'quiz_enabled', 'yes' );

		// We don't have a proper quiz to work with...
		if ( empty( $quiz ) || ! is_a( $quiz, 'LLMS_Quiz' ) ) {

			// Translators: %s = Quiz post id.
			$res['error'] = sprintf( esc_html__( 'Unable to update quiz "%s". Invalid quiz ID.', 'lifterlms' ), $quiz_data['id'] );

		} else {

			// Return the real ID (important when creating a new quiz).
			$res['id'] = $quiz->get( 'id' );

			/**
			 * If the parent lesson was just created the lesson will have a temp id
			 * replace it with the newly created lessons's real ID.
			 */
			if ( ! isset( $quiz_data['lesson_id'] ) || self::is_temp_id( $quiz_data['lesson_id'] ) ) {
				$quiz_data['lesson_id'] = $lesson->get( 'id' );
			}

			$properties = array_merge(
				array_keys( $quiz->get_properties() ),
				array(
					// phpcs:ignore -- commented out code
					// 'content',
					'status',
					'title',
				)
			);

			// Update all updatable properties.
			foreach ( $properties as $prop ) {
				if ( isset( $quiz_data[ $prop ] ) ) {
					$quiz->set( $prop, $quiz_data[ $prop ] );
				}
			}

			if ( isset( $quiz_data['questions'] ) && is_array( $quiz_data['questions'] ) ) {
				$res['questions'] = self::update_questions( $quiz_data['questions'], $quiz );
			}

			// Update all custom fields.
			self::update_custom_schemas( 'quiz', $quiz, $quiz_data );

		}

		return $res;
	}

	/**
	 * Update a section with data from the heartbeat
	 *
	 * @since 3.16.0
	 * @since 3.16.11 Unknown.
	 *
	 * @param array       $section_data Array of section data.
	 * @param LLMS_Course $course_id    Instance of the parent LLMS_Course.
	 * @return array
	 */
	private static function update_section( $section_data, $course_id ) {

		$res = array_merge(
			$section_data,
			array(
				'orig_id' => $section_data['id'],
			)
		);

		// Create a new section.
		if ( self::is_temp_id( $section_data['id'] ) ) {

			$section = new LLMS_Section( 'new' );
			$section->set( 'parent_course', $course_id );

			// Update existing section.
		} else {

			$section = llms_get_post( $section_data['id'] );

		}

		// We don't have a proper section to work with...
		if ( empty( $section ) || ! is_a( $section, 'LLMS_Section' ) ) {
			// Translators: %s = Section post id.
			$res['error'] = sprintf( esc_html__( 'Unable to update section "%s". Invalid section ID.', 'lifterlms' ), $section_data['id'] );
		} else {

			// Return the real ID (important when creating a new section).
			$res['id'] = $section->get( 'id' );

			// Run through all possible updated fields.
			foreach ( array( 'order', 'title' ) as $key ) {

				// Update those that were sent through.
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
