<?php
/**
 * LifterLMS Admin Notices
 *
 * @since    3.0.0
 * @version  3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Notices
 *
 * @since  3.0.0
 * @since 3.35.0 Unslash input data.
 */
class LLMS_Admin_Notices {

	/**
	 * Array of messages to display
	 *
	 * @var  array
	 */
	private static $notices = array();

	/**
	 * Static constructor
	 *
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
	 * Add output notice actions depending on the current screen
	 * Adds later for LLMS Settings screens to accommodate for settings that are updated later in the load cycle
	 *
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function add_output_actions() {

		$screen = get_current_screen();
		if ( ! empty( $screen->base ) && 'lifterlms_page_llms-settings' === $screen->base ) {
			add_action( 'lifterlms_settings_notices', array( __CLASS__, 'output_notices' ) );
		} else {
			add_action( 'admin_print_styles', array( __CLASS__, 'output_notices' ) );
		}

	}

	/**
	 * Add a notice
	 * Saves options to the database to be output later
	 *
	 * @param    string $notice_id        unique id of the notice
	 * @param    string $html_or_options  html content of the notice for short notices that don't need a template
	 *                                      or array of options, html of the notice will be in a template
	 *                                      passed as the "template" param of this array
	 * @param    array  $options          array of options, when passing html directly via $html_or_options
	 *                                      notice options should be passed in this array
	 * @return   void
	 * @since    3.0.0
	 * @version  3.3.0 - added "flash" option
	 */
	public static function add_notice( $notice_id, $html_or_options = '', $options = array() ) {

		// dont add the notice if we've already dismissed of delayed it
		if ( get_transient( 'llms_admin_notice_' . $notice_id . '_delay' ) ) {
			return;
		}

		if ( is_array( $html_or_options ) ) {

			$options = $html_or_options;

		} else {

			$options['html'] = $html_or_options;
		}

		$options = wp_parse_args(
			$options,
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 7,
				'flash'            => false, // if true, will delete the notice after displaying it
				'html'             => '',
				'remind_in_days'   => 7,
				'remindable'       => false,
				'type'             => 'info', // info, warning, success, error
				'template'         => false, // template name, eg "admin/notices/notice.php"
				'template_path'    => '', // allow override of default LLMS()->template_path()
				'default_path'     => '', // allow override of default path LLMS()->plugin_path() . '/templates/'
								  // an addon may add a notice and pass it's own path in here
			)
		);

		self::$notices = array_unique( array_merge( self::get_notices(), array( $notice_id ) ) );
		update_option( 'llms_admin_notice_' . $notice_id, $options );

	}

	/**
	 * Delete a notice by id
	 *
	 * @param    string $notice_id  unique id of the notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.4.3
	 */
	public static function delete_notice( $notice_id, $trigger = 'delete' ) {
		self::$notices = array_diff( self::get_notices(), array( $notice_id ) );
		$notice        = self::get_notice( $notice_id );
		delete_option( 'llms_admin_notice_' . $notice_id );
		if ( $notice ) {
			if ( 'remind' === $trigger && $notice['remindable'] ) {
				$delay = isset( $notice['remind_in_days'] ) ? $notice['remind_in_days'] : 0;
			} elseif ( 'hide' === $trigger && $notice['dismissible'] ) {
				$delay = isset( $notice['dismiss_for_days'] ) ? $notice['dismiss_for_days'] : 7;
			} else {
				$delay = 0;
			}
			if ( $delay ) {
				set_transient( 'llms_admin_notice_' . $notice_id . '_delay', 'yes', DAY_IN_SECONDS * $delay );
			}
			do_action( 'lifterlms_' . $trigger . '_' . $notice_id . '_notice' );
		}
	}

	/**
	 * Flash a notice on screen, isn't saved and is automatically deleted after being displayed
	 *
	 * @param    string $message  Message text / html to display onscreen
	 * @param    string $type     notice type [info|warning|success|error]
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public static function flash_notice( $message, $type = 'info' ) {

		$id = 'llms-flash-notice-';
		$i  = 0;

		// increment the notice id so we can flash multiple notices on screen in one load if necessary
		while ( self::has_notice( $id . $i ) ) {
			$i++;
		}

		$id = $id . $i;

		self::add_notice(
			$id,
			$message,
			array(
				'dismissible' => false,
				'flash'       => true,
				'type'        => $type,
			)
		);

	}

	/**
	 * Get notice details array from the DB
	 *
	 * @param    string $notice_id  notice id
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notice( $notice_id ) {
		return get_option( 'llms_admin_notice_' . $notice_id, '' );
	}

	/**
	 * Get notices
	 *
	 * @return array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Determine if a notice is already set
	 *
	 * @param    string $notice_id   id of the notice
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
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Unslash input data.
	 *
	 * @return void
	 */
	public static function hide_notices() {
		if ( ( isset( $_GET['llms-hide-notice'] ) || isset( $_GET['llms-remind-notice'] ) ) && isset( $_GET['_llms_notice_nonce'] ) ) {
			if ( ! llms_verify_nonce( '_llms_notice_nonce', 'llms_hide_notices_nonce', 'GET' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}
			if ( isset( $_GET['llms-hide-notice'] ) ) {
				$notice = sanitize_text_field( wp_unslash( $_GET['llms-hide-notice'] ) );
				$action = 'hide';
			} elseif ( isset( $_GET['llms-remind-notice'] ) ) {
				$notice = sanitize_text_field( wp_unslash( $_GET['llms-remind-notice'] ) );
				$action = 'remind';
			}
			self::delete_notice( $notice, $action );
		}
	}

	/**
	 * Output a single notice by ID
	 *
	 * @param    string $notice_id  notice id
	 * @return   void
	 * @since    3.0.0
	 * @version  3.7.4
	 */
	public static function output_notice( $notice_id ) {

		if ( current_user_can( 'manage_options' ) ) {

			$notice = self::get_notice( $notice_id );

			if ( empty( $notice ) ) {
				return;
			}

			// don't output those rogue empty notices I can't find
			// @todo find the source
			if ( empty( $notice['template'] ) && empty( $notice['html'] ) ) {
				self::delete_notice( $notice_id );
			}
			?>
			<div class="notice notice-<?php echo $notice['type']; ?> llms-admin-notice" id="llms-notice<?php echo $notice_id; ?>" style="position:relative;">
				<?php if ( $notice['dismissible'] ) : ?>
					<a class="notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-hide-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>">
						<span class="screen-reader-text"><?php _e( 'Dismiss', 'lifterlms' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $notice['template'] ) ) : ?>

					<?php llms_get_template( $notice['template'], $notice['template_path'], $notice['default_path'] ); ?>

				<?php elseif ( ! empty( $notice['html'] ) ) : ?>

					<?php echo wpautop( wp_kses_post( $notice['html'] ) ); ?>

				<?php endif; ?>

				<?php if ( $notice['remindable'] ) : ?>
					<p style="text-align:right;"><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-remind-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>"><?php _e( 'Remind me later', 'lifterlms' ); ?></a></p>
				<?php endif; ?>
			</div>
			<?php

			if ( isset( $notice['flash'] ) && $notice['flash'] ) {
				self::delete_notice( $notice_id, 'delete' );
			}
		}// End if().

	}

	/**
	 * Output all saved notices
	 *
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
	 * Save notices in the database
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function save_notices() {
		update_option( 'llms_admin_notices', self::get_notices() );
	}

}
LLMS_Admin_Notices::init();
