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
		add_action( 'current_screen', array( __CLASS__, 'add_output_actions' ) );
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );

	}

	/**
	 * Add output notice actioins depending on the current sceen
	 * Adds later for LLMS Settings screens to accommodate for settings that are updated later in the load cycle
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function add_output_actions() {

		$screen = get_current_screen();
		if ( ! empty ( $screen->base ) && 'lifterlms_page_llms-settings' === $screen->base ) {
			add_action( 'lifterlms_settings_notices', array( __CLASS__, 'output_notices' ) );
		} else {
			add_action( 'admin_print_styles', array( __CLASS__, 'output_notices' ) );
		}

	}

	/**
	 * Add a notice and save it's HTML to the database
	 * @param    string     $notice_id  unique id of the notice
	 * @param    string     $html       html content of the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function add_notice( $notice_id, $html, $options = array() ) {

		// dont add the notice if we've already dismissed of delayed it
		if ( get_transient( 'llms_admin_notice_' . $notice_id . '_delay' ) ) {
			return;
		}

		$options = wp_parse_args( $options, array(
			'dismissible' => true,
			'dismiss_for_days' => 7,
			'remind_in_days' => 7,
			'remindable' => false,
			'type' => 'info',
		) );

		$options['html'] = wp_kses_post( $html );

		self::$notices = array_unique( array_merge( self::get_notices(), array( $notice_id ) ) );
		update_option( 'llms_admin_notice_' . $notice_id, $options );

	}

	/**
	 * Delete a notice by id
	 * @param    string     $notice_id  unique id of the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function delete_notice( $notice_id, $trigger = 'delete' ) {
		self::$notices = array_diff( self::get_notices(), array( $notice_id ) );
		$notice = self::get_notice( $notice_id );
		delete_option( 'llms_admin_notice_' . $notice_id );
		if ( 'remind' === $trigger && $notice['remindable'] ) {
			$delay = $notice['dismiss_for_days'];
		} elseif ( 'hide' === $trigger ) {
			$delay = $notice['remind_in_days'];
		} else {
			$delay = 0;
		}
		if ( $delay ) {
			set_transient( 'llms_admin_notice_' . $notice_id . '_delay', 'yes', DAY_IN_SECONDS * $notice['remind_in_days'] );
		}
		do_action( 'lifterlms_' . $trigger . '_' . $notice_id  . '_notice' );
	}

	/**
	 * Get notice details array from the DB
	 * @param    string  $notice_id  notice id
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notice( $notice_id ) {
		return get_option( 'llms_admin_notice_' . $notice_id, '' );
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

	/**
	 * Called when "Dismiss X" or "Remind Me" is clicked on a notice
	 * Validates request and deletes the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function hide_notices() {
		if ( ( isset( $_GET['llms-hide-notice'] ) || isset( $_GET['llms-remind-notice'] ) ) && isset( $_GET['_llms_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_llms_notice_nonce'], 'llms_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}
			if ( isset( $_GET['llms-hide-notice'] ) ) {
				$notice = sanitize_text_field( $_GET['llms-hide-notice'] );
				$action = 'hide';
			} elseif ( isset( $_GET['llms-remind-notice'] ) ) {
				$notice = sanitize_text_field( $_GET['llms-remind-notice'] );
				$action = 'remind';
			}
			self::delete_notice( $notice, $action );
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
			$notice = self::get_notice( $notice_id );
			?>
			<div class="notice notice-<?php echo $notice['type']; ?> llms-admin-notice" id="llms-notice<?php echo $notice_id; ?>">
				<?php if ( $notice['dismissible'] ) : ?>
					<a class="notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-hide-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>">
						<span class="screen-reader-text"><?php _e( 'Dismiss', 'lifterlms' ); ?></span>
					</a>
				<?php endif; ?>
				<?php echo wp_kses_post( wpautop( $notice['html'] ) ); ?>
				<?php if ( $notice['remindable'] ) : ?>
					<p style="text-align:right;"><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-remind-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>"><?php _e( 'Remind me later', 'lifterlms' ); ?></a></p>
				<?php endif; ?>
			</div>
			<?php
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
