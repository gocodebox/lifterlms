<?php
/**
 * LLMS_Admin_Notices class file.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Admin Notices.
 *
 * @since 3.0.0
 */
class LLMS_Admin_Notices {

	/**
	 * Array of messages to display.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of messages already displayed in the current request.
	 *
	 * @var array
	 */
	private static $printed_notices = array();

	/**
	 * Static constructor
	 *
	 * @since 3.0.0
	 * @since 4.13.0 Populate the `self::$notices` using `self::load_notices()`.
	 *
	 * @return void
	 */
	public static function init() {

		self::$notices = self::load_notices();

		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'current_screen', array( __CLASS__, 'add_output_actions' ) );
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );
	}

	/**
	 * Add output notice actions depending on the current screen.
	 *
	 * Notices are added later for LifterLMS settings screens to accommodate
	 * settings that are updated later in the load cycle.
	 *
	 * @since 3.0.0
	 * @since 5.9.0 Output notices at `admin_notices` in favor of `admin_print_styles`.
	 *
	 * @return void
	 */
	public static function add_output_actions() {

		$screen = get_current_screen();
		if ( ! empty( $screen->base ) && 'lifterlms_page_llms-settings' === $screen->base ) {
			add_action( 'lifterlms_settings_notices', array( __CLASS__, 'output_notices' ) );
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		}
	}

	/**
	 * Add a notice
	 *
	 * Saves options to the database to be output later
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Added "flash" option.
	 *
	 * @param string $notice_id       Unique id of the notice.
	 * @param string $html_or_options Html content of the notice for short notices that don't need a template
	 *                                or an array of options, html of the notice will be in a template
	 *                                passed as the "template" param of this array.
	 * @param array  $options         Array of options, when passing html directly via $html_or_options.
	 *                                Notice options should be passed in this array.
	 * @return void
	 */
	public static function add_notice( $notice_id, $html_or_options = '', $options = array() ) {

		// Don't add the notice if we've already dismissed or delayed it.
		if ( get_transient( 'llms_admin_notice_' . $notice_id . '_delay' ) ||
			( is_numeric( get_option( 'llms_admin_notice_' . $notice_id . '_delay' ) ) &&
				time() < get_option( 'llms_admin_notice_' . $notice_id . '_delay' )
			) ) {
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
				'flash'            => false, // If true, will delete the notice after displaying it.
				'html'             => '',
				'remind_in_days'   => 7,
				'remindable'       => false,
				'type'             => 'info', // Info, warning, success, error.
				'template'         => false, // Template name, eg "admin/notices/notice.php".
				'template_path'    => '', // Allow override of default llms()->template_path().
				'default_path'     => '', // Allow override of default path llms()->plugin_path() . '/templates/'. An addon may add a notice and pass it's own path in here.
			)
		);

		self::$notices = array_unique( array_merge( self::get_notices(), array( $notice_id ) ) );
		update_option( 'llms_admin_notice_' . $notice_id, $options );
	}

	/**
	 * Delete a notice by id
	 *
	 * @since 3.0.0
	 * @since 3.4.3 Unknown.
	 *
	 * @param string $notice_id Unique id of the notice.
	 * @param string $trigger   Deletion action/trigger, accepts 'delete' (default), 'hide', or 'remind'.
	 * @return void
	 */
	public static function delete_notice( $notice_id, $trigger = 'delete' ) {
		self::$notices = array_diff( self::get_notices(), array( $notice_id ) );
		$notice        = self::get_notice( $notice_id );
		delete_option( 'llms_admin_notice_' . $notice_id );
		if ( $notice ) {
			$delay = 0;
			if ( 'remind' === $trigger && $notice['remindable'] ) {
				$delay = isset( $notice['remind_in_days'] ) ? $notice['remind_in_days'] : 0;
			}
			if ( 'hide' === $trigger && $notice['dismissible'] ) {
				$delay = isset( $notice['dismiss_for_days'] ) ? $notice['dismiss_for_days'] : 7;
			}
			if ( $delay ) {
				update_option( 'llms_admin_notice_' . $notice_id . '_delay', time() + ( DAY_IN_SECONDS * $delay ) );
			}

			/**
			 * Hook run when a notice is dismissed.
			 *
			 * The dynamic portion of this hook `{$trigger}` refers to the deletion trigger, either 'delete',
			 * 'hide', or 'remind'.
			 *
			 * The dynamic portion of this hook, `{$notice_id}` refers to the ID of the notice being dismissed.
			 *
			 * @since 4.10.0
			 */
			do_action( "lifterlms_{$trigger}_{$notice_id}_notice" );
		}
	}

	/**
	 * Flash a notice on screen, isn't saved and is automatically deleted after being displayed
	 *
	 * @since 3.3.0
	 *
	 * @param string $message Message text / html to display onscreen.
	 * @param string $type    Notice type [info|warning|success|error].
	 * @return void
	 */
	public static function flash_notice( $message, $type = 'info' ) {

		$id = 'llms-flash-notice-';
		$i  = 0;

		// Increment the notice id so we can flash multiple notices on screen in one load if necessary.
		while ( self::has_notice( $id . $i ) ) {
			++$i;
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
	 * @since 3.0.0
	 * @since 4.13.0 When the notice cannot be found, return an empty array in favor of an empty string.
	 *
	 * @param string $notice_id Notice id.
	 * @return array
	 */
	public static function get_notice( $notice_id ) {
		return get_option( 'llms_admin_notice_' . $notice_id, array() );
	}

	/**
	 * Get notices
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Determine if a notice is already set
	 *
	 * @since 3.0.0
	 * @since 4.10.0 Use a strict comparison.
	 *
	 * @param string $notice_id Id of the notice.
	 * @return boolean
	 */
	public static function has_notice( $notice_id ) {
		return in_array( $notice_id, self::get_notices(), true );
	}

	/**
	 * Called when "Dismiss X" or "Remind Me" is clicked on a notice
	 *
	 * Validates request and deletes the notice.
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Unslash input data.
	 * @since 5.2.0 Remove notice and notice query string vars and redirect after clearing.
	 *
	 * @return void
	 */
	public static function hide_notices() {
		if ( ( isset( $_GET['llms-hide-notice'] ) || isset( $_GET['llms-remind-notice'] ) ) && isset( $_GET['_llms_notice_nonce'] ) ) {
			if ( ! llms_verify_nonce( '_llms_notice_nonce', 'llms_hide_notices_nonce', 'GET' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}
			if ( isset( $_GET['llms-hide-notice'] ) ) {
				$notice = sanitize_text_field( wp_unslash( $_GET['llms-hide-notice'] ) );
				$action = 'hide';
			} elseif ( isset( $_GET['llms-remind-notice'] ) ) {
				$notice = sanitize_text_field( wp_unslash( $_GET['llms-remind-notice'] ) );
				$action = 'remind';
			}
			self::delete_notice( $notice, $action );
			llms_redirect_and_exit( remove_query_arg( array( 'llms-hide-notice', 'llms-remind-notice', '_llms_notice_nonce' ) ) );
		}
	}

	/**
	 * Loads stored notice IDs from the database
	 *
	 * Handles potentially malformed data by ensuring that only an array of strings
	 * can be loaded.
	 *
	 * @since 4.13.0
	 *
	 * @return string[]
	 */
	protected static function load_notices() {

		$notices = get_option( 'llms_admin_notices', array() );

		if ( ! is_array( $notices ) ) {
			$notices = array( $notices );
		}

		// Remove empty and non-string values.
		return array_filter(
			$notices,
			function ( $notice ) {
				return ( ! empty( $notice ) && is_string( $notice ) );
			}
		);
	}

	/**
	 * Output a single notice by ID
	 *
	 * @since 3.0.0
	 * @since 3.7.4 Unknown.
	 * @since 5.2.0 Ensure `template_path` and `default_path` are properly passed to `llms_get_template()`.
	 * @since 5.3.1 Delete empty notices and do not display them.
	 *
	 * @param string $notice_id Notice id.
	 * @return void
	 */
	public static function output_notice( $notice_id ) {

		if ( current_user_can( 'manage_options' ) ) {

			$notice = self::get_notice( $notice_id );

			// Don't output those rogue empty notices I can't find.
			// @todo find the source.
			if ( empty( $notice ) || ( empty( $notice['template'] ) && empty( $notice['html'] ) ) ) {
				self::delete_notice( $notice_id );

				return;
			}
			?>
			<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> llms-admin-notice" id="llms-notice<?php echo esc_attr( $notice_id ); ?>" style="position:relative;">
				<div class="llms-admin-notice-icon"></div>
				<div class="llms-admin-notice-content">
					<?php if ( $notice['dismissible'] ) : ?>
						<a class="notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-hide-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>">
							<span class="screen-reader-text"><?php esc_html_e( 'Dismiss', 'lifterlms' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $notice['template'] ) ) : ?>

						<?php llms_get_template( $notice['template'], array(), $notice['template_path'], $notice['default_path'] ); ?>

					<?php elseif ( ! empty( $notice['html'] ) ) : ?>

						<?php echo wpautop( wp_kses_post( $notice['html'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already using wp_kses_post() ?>

					<?php endif; ?>

					<?php if ( $notice['remindable'] ) : ?>
						<p style="text-align:right;"><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-remind-notice', $notice_id ), 'llms_hide_notices_nonce', '_llms_notice_nonce' ) ); ?>"><?php esc_html_e( 'Remind me later', 'lifterlms' ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>
			<?php

			if ( isset( $notice['flash'] ) && $notice['flash'] ) {
				self::delete_notice( $notice_id, 'delete' );
			}
		}
	}

	/**
	 * Output all saved notices.
	 *
	 * @since 3.0.0
	 * @since 7.1.0 Made sure to print the notices only once.
	 *
	 * @return void
	 */
	public static function output_notices() {

		$notices_to_print = array_diff( self::get_notices(), self::$printed_notices );

		foreach ( $notices_to_print as $notice_id ) {
			self::output_notice( $notice_id );
			self::$printed_notices[] = $notice_id;
		}
	}

	/**
	 * Save notices in the database
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function save_notices() {
		update_option( 'llms_admin_notices', self::get_notices() );
	}
}

LLMS_Admin_Notices::init();
