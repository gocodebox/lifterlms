<?php
/**
 * LifterLMS Admin Notices
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Notices {

	/**
	 * Array of messages to display
	 * @var  array
	 */
	private static $notices = array();

	/**
	 * Static constructor
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function init() {

		self::$notices = get_option( 'llms_admin_notices', array() );

		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );
		add_action( 'admin_print_styles', array( __CLASS__, 'output_notices' ) );

	}

	/**
	 * Add a notice and save it's HTML to the database
	 * @param    string     $notice_id  unique id of the notice
	 * @param    string     $html       html content of the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function add_notice( $notice_id, $html ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $notice_id ) ) );
		update_option( 'llms_admin_notice_' . $notice_id, wp_kses_post( $html ) );
	}

	/**
	 * Delete a notice by id
	 * @param    string     $notice_id  unique id of the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function delete_notice( $notice_id ) {
		self::$notices = array_diff( self::get_notices(), array( $notice_id ) );
		delete_option( 'llms_admin_notice_' . $notice_id );
	}

	/**
	 * Get the HTML of a single notice
	 * @param    string     $notice_id  unique id of the notice
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notice_html( $notice_id ) {
		return apply_filters( 'llms_admin_notice_' . $notice_id, get_option( 'llms_admin_notice_' . $notice_id, '' ) );
	}

	/**
	 * Determine the type of notice
	 * Pass ${id}__{$notice_type} into the id to set the type
	 * defaults to info (blue)
	 * @param    string $notice_id  notice id
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notice_type( $notice_id ) {
		$exp = explode( '__', $notice_id );
		$class = array_pop( $exp );

		if ( ! in_array( $class, array( 'error', 'info', 'success', 'warning' ) ) ) {
			$class = 'info';
		}

		return 'notice-' . $class;
	}

	/**
	 * Get notices
	 * @return array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Determine if a notice is already set
	 * @param    string     $notice_id   id of the notice
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function has_notice( $notice_id ) {
		return in_array( $notice_id, self::get_notices() );
	}

	public static function hide_notices() {
		if ( isset( $_GET['llms-hide-notice'] ) && isset( $_GET['_llms_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_llms_notice_nonce'], 'llms_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}
			$hide_notice = sanitize_text_field( $_GET['llms-hide-notice'] );
			self::delete_notice( $hide_notice );
			do_action( 'lifterlms_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Output a single notice by ID
	 * @param    string     $notice_id  notice id
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_notice( $notice_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			$html = self::get_notice_html( $notice_id );
			if ( $html ) {
			?>
				<div class="notice <?php echo self::get_notice_type( $notice_id ); ?> llms-admin-notice" id="llms-notice<?php echo $notice_id; ?>">
					<a class="notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-hide-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>">
						<span class="screen-reader-text"><?php _e( 'Dismiss', 'lifterlms' ); ?></span>
					</a>
					<?php echo wp_kses_post( wpautop( $html ) ); ?>
				</div>
			<?php
			}
		}
	}

	/**
	 * Output all saved notices
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function output_notices() {
		foreach ( self::get_notices() as $notice_id ) {
			self::output_notice( $notice_id );
		}
	}

	/**
	 * Save notices in the databse
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function save_notices() {
		update_option( 'llms_admin_notices', self::get_notices() );
	}

}
LLMS_Admin_Notices::init();
