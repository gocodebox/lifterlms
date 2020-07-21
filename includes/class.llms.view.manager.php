<?php
/**
 * View Manager
 *
 * Allows qualifying user roles to view as various user types to make easier testing and editing of LLMS Content.
 *
 * @package LifterLMS/Classes
 *
 * @since 3.7.0
 * @version 4.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_View_Manager class.
 *
 * @since 3.7.0
 * @since 3.35.0 Sanitize `$_GET` data.
 * @since 4.2.0 Disable the view management when creating a pending order.
 *               Added `modify_display_free_enroll_form` method.
 *               `get_url` method modified so to take into account already present
 *               query args in the request URI.
 *               Update admin bar icon.
 */
class LLMS_View_Manager {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 * @since 4.2.0 Added early return when creating a pending order.
	 */
	public function __construct() {

		// Do nothing if we're creating a pending order.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['action'] ) && 'create_pending_order' === $_POST['action'] ) {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		add_action( 'init', array( $this, 'add_actions' ) );

	}

	/**
	 * Add actions & filters.
	 *
	 * @since 3.7.0
	 * @since 4.2.0 Added filter to handle the displaying of the free enroll.
	 *
	 * @return void
	 */
	public function add_actions() {

		// Output view links on the admin menu.
		add_action( 'admin_bar_menu', array( $this, 'add_menu_items' ), 777 );

		// Filter page restrictions.
		add_filter( 'llms_page_restricted', array( $this, 'modify_restrictions' ), 10, 1 );
		add_filter( 'llms_is_course_open', array( $this, 'modify_course_open' ), 10, 1 );
		add_filter( 'llms_is_course_enrollment_open', array( $this, 'modify_course_open' ), 10, 1 );

		// Filters we'll only run when view as links are called.
		if ( isset( $_GET['llms-view-as'] ) ) {

			add_filter( 'llms_is_course_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_lesson_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_track_complete', array( $this, 'modify_completion' ), 10, 1 );

			add_filter( 'llms_get_enrollment_status', array( $this, 'modify_enrollment_status' ), 10, 1 );

			add_filter( 'llms_display_free_enroll_form', array( $this, 'modify_display_free_enroll_form' ), 10, 1 );

			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		}

	}

	/**
	 * Add view links to the admin menu bar for qualifying users.
	 *
	 * @since 3.7.0
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Updated icon.
	 *
	 * @return void
	 */
	public function add_menu_items() {

		// Don't display on admin panel.
		if ( is_admin() ) {
			return;
		}

		// Check this to prevent leaked globals creating a false positive below.
		if ( is_post_type_archive() ) {
			return;
		}

		// Don't need to do anything for most post types.
		global $post;
		if ( ! $post || ( ! is_llms_checkout() && ! in_array( $post->post_type, array( 'course', 'lesson', 'llms_membership', 'llms_quiz' ), true ) ) ) {
			return;
		}

		global $wp_admin_bar;

		$view = $this->get_view();

		$views = $this->get_views();

		$title = sprintf( __( 'Viewing as %s', 'lifterlms' ), $views[ $view ] );

		$wp_admin_bar->add_node(
			array(
				'id'     => 'llms-view-as-menu',
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon"><img src="' . LLMS()->plugin_url() . '/assets/images/lifterlms-icon.png" style="height:17px;margin-top:3px;opacity:0.65;"></span>' . $title,
			)
		);

		foreach ( $views as $slug => $title ) {

			if ( $slug === $view ) {
				continue;
			}

			$wp_admin_bar->add_node(
				array(
					'href'   => $this->get_url( $slug ),
					'id'     => 'llms-view-as--' . $slug,
					'parent' => 'llms-view-as-menu',
					'title'  => sprintf( __( 'View as %s', 'lifterlms' ), $title ),
				)
			);

		}

	}

	/**
	 * Inline JS.
	 *
	 * Updates links so admins can navigate around quickly when "viewing as".
	 *
	 * @since 3.7.0
	 * @since 3.35.0 Sanitize `$_GET` data.
	 * @return string
	 */
	private function get_inline_script() {
		ob_start();
		?>
		window.llms.ViewManager.set_nonce( '<?php echo llms_filter_input( INPUT_GET, 'view_nonce', FILTER_SANITIZE_STRING ); ?>' ).set_view( '<?php echo $this->get_view(); ?>' ).update_links();
		<?php
		return ob_get_clean();
	}

	/**
	 * Get a view url for the requested view.
	 *
	 * @since 3.7.0
	 * @since 4.2.0 Take into account already present query args. e.g. ?plan=X
	 *
	 * @param string $role View option [self|visitor|student].
	 * @param array  $args Optional. Additional query args to add to the url. Default empty array.
	 * @return string
	 */
	private function get_url( $role, $args = array() ) {

		// Returns the current url without the `llms-view-as` and `view_nonce` query args.
		if ( 'self' === $role ) {
			return remove_query_arg( array( 'llms-view-as', 'view_nonce' ) );
		}

		$args['llms-view-as'] = $role;

		$href = add_query_arg( $args );
		$href = wp_nonce_url( $href, 'llms-view-as', 'view_nonce' );

		return $href;

	}

	/**
	 * Get the current view role/type.
	 *
	 * @since 3.7.0
	 * @since 3.35.0 Sanitize `$_GET` data.
	 *
	 * @return string
	 */
	private function get_view() {

		if ( ! llms_verify_nonce( 'view_nonce', 'llms-view-as', 'GET' ) ) {
			return 'self';
		}

		// Ensure it's a valid view
		$views = $this->get_views();
		if ( ! isset( $views[ $_GET['llms-view-as'] ] ) ) {
			return 'self';
		}

		return llms_filter_input( INPUT_GET, 'llms-view-as', FILTER_SANITIZE_STRING );

	}

	/**
	 * Get a list of available views.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	private function get_views() {
		return array(
			'self'    => __( 'Myself', 'lifterlms' ),
			'visitor' => __( 'Visitor', 'lifterlms' ),
			'student' => __( 'Student', 'lifterlms' ),
		);
	}

	/**
	 * Modify the completion status of course, lessons, tracks based on current view.
	 *
	 * Visitors and students will always show content as not completed.
	 *
	 * @since 3.7.0
	 *
	 * @param boolean $completed The actual status for the current user.
	 * @return boolean
	 */
	public function modify_completion( $completed ) {
		switch ( $this->get_view() ) {
			case 'visitor':
				$status = false;
				break;
			case 'student':
				$status = true;
				break;
		}
		return $completed;
	}

	/**
	 * Modify the status of a course access period based on the current view.
	 *
	 * Students and Visitors will see the actual access period.
	 *
	 * If viewing as self and self can bypass restrictions will appear as if course is open.
	 *
	 * @since 3.7.0
	 *
	 * @param boolean $status The default status.
	 * @return boolean
	 */
	public function modify_course_open( $status ) {

		if ( 'self' === $this->get_view() && llms_can_user_bypass_restrictions( get_current_user_id() ) ) {

			return true;

		}

		return $status;

	}

	/**
	 * Modify the enrollment status of current user based on the view.
	 *
	 * Students will always show as enrolled.
	 *
	 * Visitors will always show as not-enrolled.
	 *
	 * @since 3.7.0
	 *
	 * @param string $status The actual status for the current user.
	 * @return string
	 */
	public function modify_enrollment_status( $status ) {

		switch ( $this->get_view() ) {

			case 'visitor':
				$status = false;
				break;

			case 'student':
				$status = 'enrolled';
				break;

		}

		return $status;

	}

	/**
	 * Modify the displaying of the free enroll form (free access plans).
	 *
	 * Visitors will never be shown the free enroll form.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $display Whether or not the form is being displayed.
	 * @return bool
	 */
	public function modify_display_free_enroll_form( $display ) {

		if ( ! $display || 'visitor' === $this->get_view() ) {
			return false;
		}

		return $display;

	}

	/**
	 * Modify llms_page_restricted for qualifying users to allow them to bypass restrictions.
	 *
	 * @since 3.7.0
	 *
	 * @param array $restrictions Restriction data.
	 * @return array
	 */
	public function modify_restrictions( $restrictions ) {

		if ( 'self' === $this->get_view() && llms_can_user_bypass_restrictions( get_current_user_id() ) ) {

			$restrictions['is_restricted'] = false;
			$restrictions['reason']        = 'role-access';

		}

		return $restrictions;
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 3.7.0
	 * @since 3.17.8 Unknown.
	 * @since 3.35.0 Declare asset version.
	 *
	 * @return void
	 */
	public function scripts() {

		// If it's self we don't need anything fancy going on here
		if ( 'self' === $this->get_view() ) {
			return;
		}

		wp_enqueue_script( 'llms-view-manager', LLMS_PLUGIN_URL . '/assets/js/llms-view-manager' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
		wp_add_inline_script( 'llms-view-manager', $this->get_inline_script(), 'after' );

	}

}

return new LLMS_View_Manager();
