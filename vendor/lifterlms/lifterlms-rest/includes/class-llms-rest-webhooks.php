<?php
/**
 * CRUD Webhooks
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Webhooks class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.3 Fix formatting error on the default webhook name string.
 * @since 1.0.0-beta.6 "access plan" not "access_plan" for human reading.
 */
class LLMS_REST_Webhooks extends LLMS_REST_Database_Resource {

	use LLMS_REST_Trait_Singleton;

	/**
	 * Resource Name/ID key.
	 *
	 * @var string
	 */
	protected $id = 'webhook';

	/**
	 * Resource Model classname.
	 *
	 * @var string
	 */
	protected $model = 'LLMS_REST_Webhook';

	/**
	 * Default column values (for creating).
	 *
	 * @var array
	 */
	protected $default_column_values = array();

	/**
	 * Array of read only column names.
	 *
	 * @var array
	 */
	protected $read_only_columns = array(
		'id',
	);

	/**
	 * Array of required columns (for creating).
	 *
	 * @var array
	 */
	protected $required_columns = array(
		'topic',
		'delivery_url',
	);

	/**
	 * Create a new API Key
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to a key's properties.
	 * @return WP_Error|LLMS_REST_Webhook
	 */
	public function create( $data ) {

		$data = $this->create_prepare( $data );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Can't set these properties during creation.
		unset( $data['pending_delivery'], $data['failure_count'] );

		return $this->save( new $this->model(), $data );

	}

	/**
	 * Retrieve the base admin url for managing API keys.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_admin_url() {
		return add_query_arg(
			array(
				'page'    => 'llms-settings',
				'tab'     => 'rest-api',
				'section' => 'webhooks',
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Get default column values.
	 *
	 * Overrides parent to dynamically set the class variable since several defaults are generated through functions.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Fix formatting error.
	 *
	 * @return array
	 */
	public function get_default_column_values() {

		$this->default_column_values = array(
			'secret'           => wp_generate_password( 50, true, true ),
			'status'           => 'disabled',
			'failure_count'    => 0,
			'pending_delivery' => 0,
			'user_id'          => get_current_user_id(),
			'name'             => sprintf(
				// Translators: %s = created date.
				__( 'Webhook created on %s', 'lifterlms' ),
				// Translators: Date format.
				strftime( _x( '%b %d, %Y @ %I:%M %p', 'Webhook created on date parsed by strftime', 'lifterlms' ) ) // phpcs:disable WordPress.WP.I18n.UnorderedPlaceholdersText
			),
		);

		return parent::get_default_column_values();

	}

	/**
	 * Retrieve the translated resource name.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function get_i18n_name() {
		return __( 'Webhook', 'lifterlms' );
	}

	/**
	 * Retrieves a list of webhook statuses.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_statuses() {

		/**
		 * Filter the available webhook statuses.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $statuses Array of statuses.
		 */
		return apply_filters(
			'llms_rest_webhook_statuses',
			array(
				'active'   => __( 'Active', 'lifterlms' ),
				'paused'   => __( 'Paused', 'lifterlms' ),
				'disabled' => __( 'Disabled', 'lifterlms' ),
			)
		);

	}

	/**
	 * Retrieves a list of webhook topics.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_topics() {

		/**
		 * Filter the available webhook topics.
		 *
		 * @since 1.0.0-beta.1
		 * @since 1.0.0-beta.6 Fix translated access plans typo.
		 *
		 * @param array $topics Array of topics.
		 */
		return apply_filters(
			'llms_rest_webhook_topics',
			array(
				'course.created'       => __( 'Course created', 'lifterlms' ),
				'course.updated'       => __( 'Course updated', 'lifterlms' ),
				'course.deleted'       => __( 'Course deleted', 'lifterlms' ),
				'course.restored'      => __( 'Course restored', 'lifterlms' ),
				'section.created'      => __( 'Section created', 'lifterlms' ),
				'section.updated'      => __( 'Section updated', 'lifterlms' ),
				'section.deleted'      => __( 'Section deleted', 'lifterlms' ),
				'lesson.created'       => __( 'Lesson created', 'lifterlms' ),
				'lesson.updated'       => __( 'Lesson updated', 'lifterlms' ),
				'lesson.deleted'       => __( 'Lesson deleted', 'lifterlms' ),
				'lesson.restored'      => __( 'Lesson restored', 'lifterlms' ),
				'membership.created'   => __( 'Membership created', 'lifterlms' ),
				'membership.updated'   => __( 'Membership updated', 'lifterlms' ),
				'membership.deleted'   => __( 'Membership deleted', 'lifterlms' ),
				'membership.restored'  => __( 'Membership restored', 'lifterlms' ),
				'access_plan.created'  => __( 'Access Plan created', 'lifterlms' ),
				'access_plan.updated'  => __( 'Access Plan updated', 'lifterlms' ),
				'access_plan.deleted'  => __( 'Access Plan deleted', 'lifterlms' ),
				'access_plan.restored' => __( 'Access Plan restored', 'lifterlms' ),
				'order.created'        => __( 'Order created', 'lifterlms' ),
				'order.updated'        => __( 'Order updated', 'lifterlms' ),
				'order.deleted'        => __( 'Order deleted', 'lifterlms' ),
				'order.restored'       => __( 'Order restored', 'lifterlms' ),
				'transaction.created'  => __( 'Transaction created', 'lifterlms' ),
				'transaction.updated'  => __( 'Transaction updated', 'lifterlms' ),
				'transaction.deleted'  => __( 'Transaction deleted', 'lifterlms' ),
				'student.created'      => __( 'Student created', 'lifterlms' ),
				'student.updated'      => __( 'Student updated', 'lifterlms' ),
				'student.deleted'      => __( 'Student deleted', 'lifterlms' ),
				'enrollment.created'   => __( 'Enrollment created', 'lifterlms' ),
				'enrollment.updated'   => __( 'Enrollment updated', 'lifterlms' ),
				'enrollment.deleted'   => __( 'Enrollment deleted', 'lifterlms' ),
				'progress.updated'     => __( 'Progress updated', 'lifterlms' ),
				'progress.deleted'     => __( 'Progress deleted', 'lifterlms' ),
				'instructor.created'   => __( 'Instructor created', 'lifterlms' ),
				'instructor.updated'   => __( 'Instructor updated', 'lifterlms' ),
				'instructor.deleted'   => __( 'Instructor deleted', 'lifterlms' ),
				'action'               => __( 'Action', 'lifterlms' ),
			)
		);

	}

	/**
	 * Retrieve a list of hooks for each topic.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_hooks() {

		$hooks = array(

			// Courses.
			'course.created'      => array(
				'save_post_course' => 3,
			),
			'course.updated'      => array(
				'edit_post_course' => 2,
			),
			'course.deleted'      => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),
			'course.restored'     => array(
				'untrashed_post' => 1,
			),

			// Sections.
			'section.created'     => array(
				'save_post_section' => 3,
			),
			'section.updated'     => array(
				'edit_post_section' => 2,
			),
			'section.deleted'     => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),

			// Lessons.
			'lesson.created'      => array(
				'save_post_lesson' => 3,
			),
			'lesson.updated'      => array(
				'edit_post_lesson' => 2,
			),
			'lesson.deleted'      => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),
			'lesson.restored'     => array(
				'untrashed_post' => 1,
			),

			// Memberships.
			'membership.created'  => array(
				'save_post_llms_membership' => 3,
			),
			'membership.updated'  => array(
				'edit_post_llms_membership' => 2,
			),
			'membership.deleted'  => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),
			'membership.restored' => array(
				'untrashed_post' => 1,
			),

			// Access Plans.
			'access_plan.created' => array(
				'save_post_llms_access_plan' => 3,
			),
			'access_plan.updated' => array(
				'edit_post_llms_access_plan' => 2,
			),
			'access_plan.deleted' => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),

			// Orders.
			'order.created'       => array(
				'save_post_llms_order' => 3,
			),
			'order.updated'       => array(
				'edit_post_llms_order' => 2,
			),
			'order.deleted'       => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),

			// Transactions.
			'transaction.created' => array(
				'save_post_llms_transaction' => 3,
			),
			'transaction.updated' => array(
				'edit_post_llms_transaction' => 2,
			),
			'transaction.deleted' => array(
				'wp_trash_post' => 1,
				'delete_post'   => 1,
			),

			// Students.
			'student.created'     => array(
				'user_register'             => 1,
				'lifterlms_user_registered' => 1,
			),
			'student.updated'     => array(
				'profile_update'         => 1,
				'lifterlms_user_updated' => 1,
			),
			'student.deleted'     => array(
				'delete_user' => 1,
			),

			// Instructors.
			'instructor.created'  => array(
				'user_register' => 1,
			),
			'instructor.updated'  => array(
				'profile_update' => 1,
			),
			'instructor.deleted'  => array(
				'delete_user' => 1,
			),

			'enrollment.created'  => array(
				'llms_user_course_enrollment_created'     => 2,
				'llms_user_membership_enrollment_created' => 2,
			),
			'enrollment.updated'  => array(
				'llms_user_course_enrollment_updated'     => 2,
				'llms_user_membership_enrollment_updated' => 2,
				'llms_user_removed_from_course'           => 2,
				'llms_user_removed_from_membership_level' => 2,
			),
			'enrollment.deleted'  => array(
				'llms_user_enrollment_deleted' => 2,
			),

			'progress.updated'    => array(
				'llms_mark_complete'   => 2,
				'llms_mark_incomplete' => 2,
			),
			// 'progress.deleted' => array(),

		);

		return apply_filters( 'llms_rest_webhooks_get_hooks', $hooks );

	}

	/**
	 * Retrieve an array of supported post types used as resources for webhooks.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string[]
	 */
	public function get_post_type_resources() {

		/**
		 * Filter the list of supported post types used as resources for webhooks.
		 *
		 * @param string[] $post_types Array of post type names.
		 */
		return apply_filters(
			'llms_rest_get_post_type_resources',
			array(
				'course',
				'section',
				'lesson',
				'llms_membership',
				'llms_access_plan',
				'llms_order',
				'llms_transaction',
			)
		);

	}

	/**
	 * Validate data supplied for creating/updating a key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to a key's properties.
	 * @return WP_Error|true When data is invalid will return a WP_Error with information about the invalid properties,
	 *                            otherwise `true` denoting data is valid.
	 */
	protected function is_data_valid( $data ) {

		// Validate Status.
		if ( isset( $data['status'] ) && ! in_array( $data['status'], array_keys( $this->get_statuses() ), true ) ) {
			// Translators: %s = Invalid permission string.
			return new WP_Error( 'llms_rest_webhook_invalid_status', sprintf( __( '"%s" is not a valid status.', 'lifterlms' ), $data['status'] ) );
		}

		// Validate Topics.
		if ( isset( $data['topic'] ) && ! $this->is_topic_valid( $data['topic'] ) ) {
			// Translators: %s = Invalid permission string.
			return new WP_Error( 'llms_rest_webhook_invalid_topic', sprintf( __( '"%s" is not a valid topic.', 'lifterlms' ), $data['topic'] ) );
		}

		// Prevent empty / blank values being passed.
		foreach ( array( 'name', 'delivery_url' ) as $key ) {
			if ( isset( $data[ $key ] ) && empty( $data[ $key ] ) ) {
				// Translators: %s = field name.
				return new WP_Error( 'llms_rest_webhook_invalid_' . $key, sprintf( __( '"%s" is required.', 'lifterlms' ), $key ) );
			}
		}

		return true;

	}

	/**
	 * Determine if a given topic is valid.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $topic Topic.
	 * @return bool
	 */
	public function is_topic_valid( $topic ) {

		$split = explode( '.', $topic );

		if ( 'action' === $split[0] && ! empty( $split[1] ) ) {
			return true;
		} elseif ( in_array( $topic, array_keys( $this->get_topics() ), true ) && 'action' !== $topic ) {
			return true;
		}

		return false;

	}

	/**
	 * Load webhooks.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return int Number of hooks loaded.
	 */
	public function load() {

		/**
		 * Limit the number of webhooks that are loaded. By default all webhooks are loaded.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param int $limit Number of webhooks to load. Default `null` loads all webhooks.
		 */
		$limit = apply_filters( 'llms_load_webhooks_limit', null );

		$hooks = new LLMS_REST_Webhooks_Query(
			array(
				'status'   => 'active',
				'per_page' => $limit ? $limit : 999,
			)
		);

		$loaded = 0;
		foreach ( $hooks->get_webhooks() as $hook ) {
			$hook->enqueue();
			$loaded++;
		}

		return $loaded;

	}

	/**
	 * Persist data.
	 *
	 * This method assumes the supplied data has already been validated and sanitized.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_REST_Webhook $obj Webhook object.
	 * @param array             $data Associative array of data to persist.
	 * @return obj
	 */
	protected function save( $obj, $data ) {

		if ( isset( $data['delivery_url'] ) && ( ! $obj->exists() || $obj->exists() && $data['delivery_url'] !== $obj->get( 'delivery_url' ) ) ) {
			$obj->set( 'delivery_url', $data['delivery_url'] );
			$ping = $obj->ping();
			if ( is_wp_error( $ping ) ) {
				return $ping;
			}
		}

		$obj->setup( $data )->save();
		return $obj;

	}

	/**
	 * Prepare data for an update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to a resources properties.
	 * @return LLMS_REST_Webhook|WP_Error
	 */
	protected function update_prepare( $data ) {

		$url = isset( $data['delivery_url'] );

		// Merge in (some) default values.
		$defaults = $this->get_default_column_values();
		unset( $defaults['pending_delivery'], $defaults['failure_count'] );
		$data = wp_parse_args( array_filter( $data ), $defaults );

		// URL was supplied but empty so add it back in to get caught by validation.
		if ( $url && ! isset( $data['delivery_url'] ) ) {
			$data['delivery_url'] = '';
		}

		// Validate via default parent methods.
		$data = parent::update_prepare( $data );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Add updated date.
		$data['updated'] = llms_current_time( 'mysql' );

		return $data;

	}

}
