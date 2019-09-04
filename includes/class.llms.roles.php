<?php
/**
 * LifterLMS Custom Roles and Capabilities
 *
 * @since 3.13.0
 * @version 3.34.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Roles class.
 *
 * @since 3.13.0
 * @since 3.14.0 Add the `lifterlms_instructor` capability.
 * @since 3.34.0 Added the `list_users` capability to instructors.
 *                  Added capabilities for student management.
 */
class LLMS_Roles {

	/**
	 * Retrieve an array of all capabilities for a role
	 *
	 * @since 3.13.0
	 * @param string $role Name of the role.
	 * @return array
	 */
	private static function get_all_caps( $role ) {

		$caps         = array();
		$caps['core'] = self::get_core_caps( $role );
		$caps['wp']   = self::get_wp_caps( $role );
		$caps         = array_merge( $caps, self::get_post_type_caps( $role ) );

		return apply_filters( 'llms_get_all_' . $role . '_caps', $caps );

	}

	/**
	 * Get an array of registered core lifterlms caps
	 *
	 * @since 3.13.0
	 * @since 3.14.0 Add the `lifterlms_instructor` capability.
	 * @since 3.34.0 Added capabilities for student management.
	 *
	 * @return string[]
	 */
	public static function get_all_core_caps() {
		return apply_filters(
			'llms_get_all_core_caps',
			array(
				'lifterlms_instructor',
				'manage_lifterlms',
				'view_lifterlms_reports',
				'view_others_lifterlms_reports',
				'enroll',
				'unenroll',
				'create_students',
				'view_students',
				'view_others_students',
				'edit_students',
				'edit_others_students',
				'delete_students',
				'delete_others_students',
			)
		);
	}

	/**
	 * Retrieve the LifterLMS core capabilities for a give role
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Added student management capabilities.
	 *
	 * @param string $role Name of the role.
	 * @return array
	 */
	private static function get_core_caps( $role ) {

		$all_caps = array_fill_keys( array_values( self::get_all_core_caps() ), true );

		switch ( $role ) {

			case 'instructor':
			case 'instructors_assistant':
				$caps = $all_caps;
				unset(
					$caps['enroll'],
					$caps['unenroll'],
					$caps['manage_lifterlms'],
					$caps['view_others_lifterlms_reports'],
					$caps['create_students'],
					$caps['view_others_students'],
					$caps['edit_students'],
					$caps['edit_others_students'],
					$caps['delete_students'],
					$caps['delete_others_students']
				);
				break;

			case 'administrator':
			case 'lms_manager':
				$caps = $all_caps;
				break;

			default:
				$caps = array();

		}

		return apply_filters( 'llms_get_' . $role . '_core_caps', $caps, $all_caps );

	}

	/**
	 * Retrieve the post type specific capabilities for a give role
	 *
	 * @since 3.13.0
	 *
	 * @param string $role Name of the role
	 * @return array
	 */
	private static function get_post_type_caps( $role ) {

		$caps = array();

		// students get nothing
		if ( 'student' !== $role ) {

			$post_types = array(
				'course'          => 'course',
				'lesson'          => 'lesson',
				'llms_quiz'       => array( 'quiz', 'quizzes' ),
				'llms_question'   => 'question',
				'llms_membership' => 'membership',
			);
			foreach ( $post_types as $post_type => $names ) {

				$post_caps = LLMS_Post_Types::get_post_type_caps( $names );

				// filter the caps down for these roles
				if ( in_array( $role, array( 'instructor', 'instructors_assistant' ) ) ) {

					$allowed = array(
						'instructor'            => array(
							'delete_posts',
							'delete_published_posts',
							'edit_post',
							'edit_posts',
							'edit_published_posts',
							'publish_posts',
							'create_posts',
						),
						'instructors_assistant' => array(
							'edit_post',
							'edit_posts',
							'edit_published_posts',
						),
					);

					foreach ( $post_caps as $post_cap => $cpt_cap ) {

						if ( ! in_array( $post_cap, $allowed[ $role ] ) ) {
							unset( $post_caps[ $post_cap ] );
						}
					}
				}

				$caps[ $post_type ] = array_fill_keys( array_values( $post_caps ), true );

			}// End foreach().

			$taxes = array(
				'course_cat'        => 'course_cat',
				'course_difficulty' => array( 'course_difficulty', 'course_difficulties' ),
				'course_tag'        => 'course_tag',
				'course_track'      => 'course_track',
				'membership_cat'    => 'membership_cat',
				'membership_tag'    => 'membership_tag',
			);
			foreach ( $taxes as $tax => $names ) {

				$tax_caps = LLMS_Post_Types::get_tax_caps( $names );

				// filter the caps down for these roles
				if ( in_array( $role, array( 'instructor', 'instructors_assistant' ) ) ) {

					$allowed = array(
						'assign_terms',
					);

					foreach ( $tax_caps as $tax_cap => $ct_cap ) {

						if ( ! in_array( $tax_cap, $allowed ) ) {
							unset( $tax_caps[ $tax_cap ] );
						}
					}
				}

				$caps[ $tax ] = array_fill_keys( array_values( $tax_caps ), true );

			}
		}// End if().

		return apply_filters( 'llms_get_' . $role . '_post_type_caps', $caps );

	}

	/**
	 * Retrieve the core WP capabilities for a give role
	 *
	 * @since 3.13.0
	 * @since 3.34.0 Add the `list_users` capability to instructors.
	 *
	 * @param string $role Name of the role.
	 * @return array
	 */
	private static function get_wp_caps( $role ) {

		$caps = array(
			'read' => true,
		);

		switch ( $role ) {

			case 'instructor':
				$add = array(
					'create_users'  => true,
					'edit_users'    => true,
					'promote_users' => true,
					'list_users'    => true,

					'read'          => true,
					'upload_files'  => true,

					// see WP Core issue(s)
					// https://core.trac.wordpress.org/ticket/22895
					// https://core.trac.wordpress.org/ticket/16808
					'edit_posts'    => true,
				);

				break;

			case 'instructors_assistant':
				$add = array(
					'read'         => true,
					'upload_files' => true,

					// see WP Core issue(s)
					// https://core.trac.wordpress.org/ticket/22895
					// https://core.trac.wordpress.org/ticket/16808
					'edit_posts'   => true,
				);

				break;

			case 'lms_manager':
				$add = array(
					'read_private_pages'     => true,
					'read_private_posts'     => true,
					'edit_posts'             => true,
					'edit_pages'             => true,
					'edit_published_posts'   => true,
					'edit_published_pages'   => true,
					'edit_private_pages'     => true,
					'edit_private_posts'     => true,
					'edit_others_posts'      => true,
					'edit_others_pages'      => true,
					'publish_posts'          => true,
					'publish_pages'          => true,
					'delete_posts'           => true,
					'delete_pages'           => true,
					'delete_private_pages'   => true,
					'delete_private_posts'   => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'delete_others_posts'    => true,
					'delete_others_pages'    => true,
					'manage_categories'      => true,
					'manage_links'           => true,
					'moderate_comments'      => true,
					'upload_files'           => true,
					'export'                 => true,
					'import'                 => true,

					'edit_users'             => true,
					'create_users'           => true,
					'list_users'             => true,
					'promote_users'          => true,
					'delete_users'           => true,
				);

				break;

			default:
				$add = array();

		}// End switch().

		return apply_filters( 'llms_get_' . $role . '_wp_caps', array_merge( $add, $caps ) );

	}

	/**
	 * Retrieve LifterLMS roles and role names
	 *
	 * @since 3.13.0
	 *
	 * @return array
	 */
	public static function get_roles() {

		return apply_filters(
			'llms_get_roles',
			array(
				'lms_manager'           => __( 'LMS Manager', 'lifterlms' ),
				'instructor'            => __( 'Instructor', 'lifterlms' ),
				'instructors_assistant' => __( 'Instructor\'s Assistant', 'lifterlms' ),
				'student'               => __( 'Student', 'lifterlms' ),
			)
		);

	}

	/**
	 * Install custom roles and related capabilities
	 *
	 * Called from LLMS_Install during installation and upgrades.
	 *
	 * @since 3.13.0
	 *
	 * @return void
	 */
	public static function install() {

		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		// self::remove_roles(); // @todo remove, this is here for dev reasons only

		$roles                  = self::get_roles();
		$roles['administrator'] = __( 'Administrator', 'lifterlms' );

		$wp_roles = wp_roles();

		foreach ( $roles as $role => $name ) {

			$role_obj = $wp_roles->get_role( $role );

			if ( ! $role_obj ) {
				$role_obj = $wp_roles->add_role( $role, $name );
			}

			self::update_caps( $role_obj, 'add' );

		}

	}

	/**
	 * Uninstall custom roles and remove custom caps from default WP roles
	 *
	 * @since 3.13.0
	 *
	 * @return void
	 */
	public static function remove_roles() {

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		$wp_roles = wp_roles();

		// delete all our custom roles
		foreach ( array_keys( self::get_roles() ) as $role ) {
			$wp_roles->remove_role( $role );
		}

		// remove custom caps from the WP core admin role
		self::update_caps( $wp_roles->get_role( 'administrator' ), 'remove' );

	}

	/**
	 * Update the capabilities for a given role
	 *
	 * @since 3.13.0
	 *
	 * @param WP_Role $role Role object.
	 * @param string  $type Update type [add|remove].
	 * @return void
	 */
	private static function update_caps( $role, $type = 'add' ) {

		foreach ( self::get_all_caps( $role->name ) as $group => $caps ) {

			foreach ( array_keys( $caps ) as $cap ) {

				if ( 'add' === $type ) {
					$role->add_cap( $cap );
				} elseif ( 'remove' === $type ) {
					$role->remove_cap( $cap );
				}
			}
		}

	}

}
