<?php
/**
 * View Manager
 *
 * Allows qualifying user roles to view as various user types to make easier testing and editing of LLMS Content.
 *
 * @package LifterLMS/Classes
 *
 * @since 3.7.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_View_Manager class.
 *
 * @since 3.7.0
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
		if ( ! empty( $_POST['action'] ) && 'create_pending_order' === $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		add_action( 'init', array( $this, 'add_actions' ) );
	}

	/**
	 * Add actions & filters.
	 *
	 * @since 3.7.0
	 * @since 4.2.0 Added filter to handle the displaying of the free enroll.
	 * @since 4.16.0 Added filters to handle modification of the student dashboard.
	 * @since 5.9.0 Pass second parameter to `modify_course_open()` methods.
	 *
	 * @return void
	 */
	public function add_actions() {

		// Output view links on the admin menu.
		add_action( 'admin_bar_menu', array( $this, 'add_menu_items' ), 777 );

		// Filter page restrictions.
		add_filter( 'llms_page_restricted', array( $this, 'modify_restrictions' ), 10, 1 );
		add_filter( 'llms_is_course_open', array( $this, 'modify_course_open' ), 10, 2 );
		add_filter( 'llms_is_course_enrollment_open', array( $this, 'modify_course_open' ), 10, 2 );

		// Filters we'll only run when view as links are called.
		if ( isset( $_GET['llms-view-as'] ) ) { // phpcs:disable WordPress.Security.NonceVerification.Recommended

			add_filter( 'llms_is_course_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_lesson_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_track_complete', array( $this, 'modify_completion' ), 10, 1 );

			add_filter( 'llms_get_enrollment_status', array( $this, 'modify_enrollment_status' ), 10, 1 );

			add_filter( 'llms_display_free_enroll_form', array( $this, 'modify_display_free_enroll_form' ), 10, 1 );

			add_filter( 'llms_display_student_dashboard', array( $this, 'modify_dashboard' ), 10, 1 );
			add_filter( 'llms_hide_registration_form', array( $this, 'modify_dashboard' ), 10, 1 );
			add_filter( 'llms_enable_open_registration', array( $this, 'enable_open_reg' ), 10, 1 );
			add_filter( 'llms_hide_login_form', array( $this, 'modify_dashboard' ), 10, 1 );

			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		}
	}

	/**
	 * Add view links to the admin menu bar for qualifying users.
	 *
	 * @since 3.7.0
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Updated icon.
	 * @since 4.5.1 Use `should_display()` method to determine if the view manager should be added to the admin bar.
	 * @since 4.16.0 Retrieve nodes to add from `get_menu_items_to_add()`.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar class instance.
	 * @return void
	 */
	public function add_menu_items( $wp_admin_bar ) {

		if ( ! $this->should_display() ) {
			return;
		}

		foreach ( $this->get_menu_items_to_add() as $node ) {
			$wp_admin_bar->add_node( $node );
		}
	}

	/**
	 * Forces open registration on when previewing the registration form
	 *
	 * If open registration is disabled, adds an action to output an info notice at the start
	 * of the form alerting users that they're viewing a preview.
	 *
	 * @since 5.0.0
	 *
	 * @param string $status Current open registration status.
	 * @return string
	 */
	public function enable_open_reg( $status ) {

		if ( ! llms_parse_bool( $status ) ) {
			add_action( 'lifterlms_register_form_start', array( $this, 'open_reg_notice' ) );
		}

		return 'yes';
	}

	/**
	 * Inline JS.
	 *
	 * Updates links so admins can navigate around quickly when "viewing as".
	 *
	 * @since 3.7.0
	 * @since 3.35.0 Sanitize `$_GET` data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	private function get_inline_script() {
		ob_start();
		?>
		window.llms.ViewManager.set_nonce( '<?php echo esc_js( llms_filter_input_sanitize_string( INPUT_GET, 'view_nonce' ) ); ?>' ).set_view( '<?php echo esc_js( $this->get_view() ); ?>' ).update_links();
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieve an array of nodes to be added to the admin bar
	 *
	 * @since 4.16.0
	 *
	 * @return array[] An array of arrays formatted to be passed to `WP_Admin_Bar::add_node()`.
	 */
	private function get_menu_items_to_add() {

		$nodes  = array();
		$view   = $this->get_view();
		$views  = $this->get_views();
		$top_id = 'llms-view-as-menu';

		// Translators: %s = View manager role name.
		$title = sprintf( __( 'Viewing as %s', 'lifterlms' ), $views[ $view ] );

		// Add the top-level node.
		$nodes[] = array(
			'id'     => $top_id,
			'parent' => 'top-secondary',
			'title'  => '<span class="ab-icon"><img src="' . llms()->plugin_url() . '/assets/images/lifterlms-icon.png" style="height:17px;margin-top:3px;opacity:0.65;"></span>' . $title,
		);

		// Add view as links.
		foreach ( $views as $role => $name ) {

			// Exclude the current view.
			if ( $role === $view ) {
				continue;
			}

			$nodes[] = array(
				'href'   => self::get_url( $role ),
				'id'     => 'llms-view-as--' . $role,
				'parent' => $top_id,
				// Translators: %s = View manager role name.
				'title'  => sprintf( __( 'View as %s', 'lifterlms' ), $name ),
			);

		}

		return $nodes;
	}

	/**
	 * Get a view url for the requested view.
	 *
	 * @since 3.7.0
	 * @since 4.2.0 Take into account already present query args. e.g. ?plan=X.
	 * @since 4.16.0 Changed method signature to add the `$href` parameter and changed access from private to public static.
	 *
	 * @param string       $role Role to view the screen as. Accepts "self", "visitor", or "student".
	 * @param string|false $href Optional. The URL to create a URL for. If `false`, uses `$_SERVER['REQUEST_URI']`.
	 * @param array        $args Optional. Additional query args to add to the url. Default empty array.
	 * @return string
	 */
	public static function get_url( $role, $href = false, $args = array() ) {

		// If we want to view as "self" we should remove the query vars (if they're set).
		if ( 'self' === $role ) {
			return remove_query_arg( array( 'llms-view-as', 'view_nonce' ), $href );
		}

		// Create a new URL.
		$args['llms-view-as'] = $role;
		$href                 = add_query_arg( $args, $href );
		return html_entity_decode( esc_url( wp_nonce_url( $href, 'llms-view-as', 'view_nonce' ) ) );
	}

	/**
	 * Get the current view role/type.
	 *
	 * @since 3.7.0
	 * @since 3.35.0 Sanitize `$_GET` data.
	 * @since 4.16.0 Don't access `$_GET` directly, use `llms_filter_input()`.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	private function get_view() {

		if ( ! llms_verify_nonce( 'view_nonce', 'llms-view-as', 'GET' ) ) {
			return 'self';
		}

		// Ensure it's a valid view.
		$views = $this->get_views();
		$view  = llms_filter_input( INPUT_GET, 'llms-view-as' );
		if ( ! $view || ! isset( $views[ $view ] ) ) {
			return 'self';
		}

		return $view;
	}

	/**
	 * Test get_views() method
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_views() {
		$this->assertEquals( array( 'self', 'visitor', 'student' ), array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_views' ) ) );
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
	 * @since 5.9.0 Pass the course ID to `llms_can_user_bypass_restrictions()`.
	 *
	 * @param boolean $status The default status.
	 * @return boolean
	 */
	public function modify_course_open( $status, $course ) {

		if (
			'self' === $this->get_view() &&
			llms_can_user_bypass_restrictions( get_current_user_id(), $course->get( 'id' ) )
		) {
			return true;
		}

		return $status;
	}

	/**
	 * Modify the student dashboard
	 *
	 * @since 4.16.0
	 *
	 * @param boolean $value Default value from the filter.
	 * @return boolean
	 */
	public function modify_dashboard( $value ) {

		switch ( $this->get_view() ) {

			case 'visitor':
				$value = false;
				break;

			case 'student':
				$value = true;
				break;
		}

		return $value;
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
	 * @since 5.9.0 Pass the course ID to `llms_can_user_bypass_restrictions()`.
	 *
	 * @param array $restrictions Restriction data.
	 * @return array
	 */
	public function modify_restrictions( $restrictions ) {

		if (
			'self' === $this->get_view() &&
			llms_can_user_bypass_restrictions( get_current_user_id(), $restrictions['restriction_id'] )
		) {

			$restrictions['is_restricted'] = false;
			$restrictions['reason']        = 'role-access';

		}

		return $restrictions;
	}

	/**
	 * Output a notice alerting users that open registration is currently disabled
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Added missing textdomain.
	 *
	 * @return void
	 */
	public function open_reg_notice() {
		llms_print_notice( __( 'This is a preview of the Open Registration form but Open Registration is currently disabled. Enable Open Registration to allow users to create accounts on this page.', 'lifterlms' ), 'debug' );
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

		// If it's self we don't need anything fancy going on here.
		if ( 'self' === $this->get_view() ) {
			return;
		}

		wp_enqueue_script( 'llms-view-manager', LLMS_PLUGIN_URL . '/assets/js/llms-view-manager' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), llms()->version, true );
		wp_add_inline_script( 'llms-view-manager', $this->get_inline_script(), 'after' );
	}


	/**
	 * Determine whether or not the view manager should be added to the WP Admin Bar
	 *
	 * The view manager is only displayed when the following criteria is met:
	 * + The current user must have a role that is allowed to bypass LifterLMS restrictions
	 * + Must be viewing one of the following:
	 *   + a single course, lesson, membership, or quiz
	 *   + LifterLMS checkout page
	 *   + LifterLMS student dashboard page
	 *
	 * @since 4.5.1
	 * @since 4.16.0 Display on the student dashboard.
	 * @since 5.9.0 When possible, pass the post ID to `llms_can_user_bypass_restrictions()`.
	 *
	 * @return boolean
	 */
	protected function should_display() {

		$display = false;

		global $post;
		$is_restricted_post = $post && ( is_llms_checkout() || is_llms_account_page() || in_array( $post->post_type, array( 'course', 'lesson', 'llms_membership', 'llms_quiz' ), true ) );
		$post_id            = $is_restricted_post ? $post->ID : null;
		if ( llms_can_user_bypass_restrictions( get_current_user_id(), $post_id ) ) {
			$display = is_admin() || is_post_type_archive() || ! $post || ! $is_restricted_post ? false : true;
		}

		/**
		 * Filters whether or not the "View As..." menu item should be displayed in the WP Admin Bar
		 *
		 * @since 4.5.1
		 *
		 * @param boolean $display Whether or not the menu item should be displayed.
		 */
		return apply_filters( 'llms_view_manager_should_display', $display );
	}
}

return new LLMS_View_Manager();
