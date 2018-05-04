<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow admins to view as various user types
 * to make easier testing and editing of LLMS Content
 * @since    3.7.0
 * @version  3.17.8
 */
class LLMS_View_Manager {

	/**
	 * Constructor
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_actions' ) );

	}

	/**
	 * Add actions & filters
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	public function add_actions() {

		// if user can't bypass restrictions don't do anything
		if ( ! llms_can_user_bypass_restrictions( get_current_user_id() ) ) {
			return;
		}

		// output view links on the admin menu
		add_action( 'admin_bar_menu', array( $this, 'add_menu_items' ), 777 );

		// filter page restrictions
		add_filter( 'llms_page_restricted', array( $this, 'modify_restrictions' ), 10, 1 );
		add_filter( 'llms_is_course_open', array( $this, 'modify_course_open' ), 10, 1 );
		add_filter( 'llms_is_course_enrollment_open', array( $this, 'modify_course_open' ), 10, 1 );

		// filters we'll only run when view as links are called
		if ( isset( $_GET['llms-view-as'] ) ) {

			add_filter( 'llms_is_course_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_lesson_complete', array( $this, 'modify_completion' ), 10, 1 );
			add_filter( 'llms_is_track_complete', array( $this, 'modify_completion' ), 10, 1 );

			add_filter( 'llms_get_enrollment_status', array( $this, 'modify_enrollment_status' ), 10, 1 );

			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		}

	}

	/**
	 * Add view links to the admin menu bar for qualifying users
	 * @return   void
	 * @since    3.7.0
	 * @version  3.16.0
	 */
	public function add_menu_items() {

		// dont display on admin panel
		if ( is_admin() ) {
			return;
		}

		// check this to prevent leaked globals creating a false positive below
		if ( is_post_type_archive() ) {
			return;
		}

		// don't need to do anything for most post types
		global $post;
		if ( ! $post || ! in_array( $post->post_type, array( 'course', 'lesson', 'llms_membership', 'llms_quiz' ) ) ) {
			return;
		}

		global $wp_admin_bar;

		$view = $this->get_view();

		$views = $this->get_views();

		$title = sprintf( __( 'Viewing as %s', 'lifterlms' ), $views[ $view ] );

		$wp_admin_bar->add_node( array(
			'id' => 'llms-view-as-menu',
			'parent' => 'top-secondary',
			'title' => '<span class="ab-icon"><img src="' . LLMS()->plugin_url() . '/assets/images/lifterlms-rocket-grey.png"></span>' . $title,
		) );

		foreach ( $views as $slug => $title ) {

			if ( $slug === $view ) {
				continue;
			}

			$wp_admin_bar->add_node( array(
				'href' => $this->get_url( $slug ),
				'id' => 'llms-view-as--' . $slug,
				'parent' => 'llms-view-as-menu',
				'title' => sprintf( __( 'View as %s', 'lifterlms' ), $title ),
			) );

		}

	}

	/**
	 * Inline JS
	 * Updates links so admins can navigate around quickly when "viewing as"
	 * @return   string
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	private function get_inline_script() {
		ob_start();
		?>
		window.llms.ViewManager.set_nonce( '<?php echo $_GET['view_nonce']; ?>' ).set_view( '<?php echo $this->get_view(); ?>' ).update_links();
		<?php
		return ob_get_clean();
	}

	/**
	 * Get a view url for the requested view
	 * @param    string     $role  view option [self|visitor|student]
	 * @param    array      $args  additional query args to add to the url
	 * @return   string
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	private function get_url( $role, $args = array() ) {

		global $post;
		$permalink = get_permalink( $post->ID );

		if ( 'self' === $role ) {
			return $permalink;
		}

		$args['llms-view-as'] = $role;

		$href = add_query_arg( $args, $permalink );
		$href = wp_nonce_url( $href, 'llms-view-as', 'view_nonce' );

		return $href;

	}

	/**
	 * Get the current view role/type
	 * @return   string
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	private function get_view() {

		if ( ! isset( $_GET['llms-view-as'] ) || ! isset( $_GET['view_nonce'] ) || ! wp_verify_nonce( $_GET['view_nonce'], 'llms-view-as' ) ) {
			return 'self';
		}

		// ensure it's a valid view
		$views = $this->get_views();
		if ( ! isset( $views[ $_GET['llms-view-as'] ] ) ) {
			return 'self';
		}

		return $_GET['llms-view-as'];

	}

	/**
	 * Get a list of available views
	 * @return   array
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	private function get_views() {
		return 	array(
			'self' => __( 'Myself', 'lifterlms' ),
			'visitor' => __( 'Visitor', 'lifterlms' ),
			'student' => __( 'Student', 'lifterlms' ),
		);
	}

	/**
	 * Modify the completion status of course, lessons, tracks based on current view
	 * Visitors and students will always show content as not completed
	 * @param    boolean     $completed   actual status for the current user
	 * @return   boolean
	 * @since    3.7.0
	 * @version  3.7.0
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
	 * Modify the status of a course access period based on the current view
	 * Students and Visitors will see the actual access period
	 * If viewing as self and self can bypass restrictions will appear as if course is open
	 * @param    boolean    $status  default status
	 * @return   boolean
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	public function modify_course_open( $status ) {

		if ( 'self' === $this->get_view() && llms_can_user_bypass_restrictions( get_current_user_id() ) ) {

			return true;

		}

		return $status;

	}

	/**
	 * Modify the enrollment status of current user based on the view
	 * students will always show as enrolled
	 * visitors will always show as not-enrolled
	 * @param    string     $status   actual status for the current user
	 * @return   string
	 * @since    3.7.0
	 * @version  3.7.0
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
	 * Modify llms_page_restricted for qualifying users to allow them to bypass restrictions
	 * @param    array     $restrictions  restriction data
	 * @return   array
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	public function modify_restrictions( $restrictions ) {

		if ( 'self' === $this->get_view() && llms_can_user_bypass_restrictions( get_current_user_id() ) ) {

			$restrictions['is_restricted'] = false;
			$restrictions['reason'] = 'role-access';

		}

		return $restrictions;
	}

	/**
	 * Enqueue Scripts
	 * @return   void
	 * @since    3.7.0
	 * @version  3.17.8
	 */
	public function scripts() {

		// if it's self we don't need anything fancy going on here
		if ( 'self' === $this->get_view() ) {
			return;
		}

		wp_enqueue_script( 'llms-view-manager', LLMS_PLUGIN_URL . '/assets/js/llms-view-manager' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '', true );
		wp_add_inline_script( 'llms-view-manager', $this->get_inline_script(), 'after' );

	}

}

return new LLMS_View_Manager();
