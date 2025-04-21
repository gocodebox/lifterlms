<?php
/**
 * LLMS_DB_Upgrader class file
 *
 * @package LifterLMS/Classes
 *
 * @since 5.2.0
 * @version 5.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage database updates and migrations
 *
 * @since 5.2.0
 */
class LLMS_DB_Upgrader {

	/**
	 * DB Version that's being upgraded from.
	 *
	 * @var string
	 */
	protected $db_version = '';

	/**
	 * Instance of the bg updater class
	 *
	 * @var LLMS_Background_Updater
	 */
	protected $updater = null;

	/**
	 * Update list
	 *
	 * @var array
	 */
	protected $updates = array();

	/**
	 * Constructor
	 *
	 * @since 5.2.0
	 *
	 * @see includes/schemas/llms-db-updates.php For an example updates schema.
	 *
	 * @param string       $db_version The DB version that is being upgraded from.
	 * @param null|array[] $updates    A list of database updates conforming to the database updates schema
	 *                                 or null to load the LifterLMS core schema.
	 */
	public function __construct( $db_version, $updates = null ) {

		if ( ! LLMS_Install::$background_updater ) {
			LLMS_Install::init_background_updater();
		}
		$this->updater = LLMS_Install::$background_updater;

		// Background updates may trigger a notice during a cron and notices might not be available.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		if ( is_null( $updates ) ) {
			$updates = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-db-updates.php';
		}

		$this->db_version = $db_version;
		$this->updates    = $updates;
	}

	/**
	 * Determine if an auto-update is possible from the specified DB version
	 *
	 * Auto updating is possible as long as none of the required updates are marked as "manual".
	 *
	 * @since 5.2.0
	 *
	 * @return boolean Returns `true` when an auto-update is possible and `false` if manual updating
	 *                 is required.
	 */
	public function can_auto_update() {

		$autoupdate = true;

		foreach ( $this->get_required_updates( $this->db_version ) as $update ) {

			// If we find a manual update we cannot auto-update.
			if ( 'manual' === $update['type'] ) {
				$autoupdate = false;
				break;
			}
		}

		/**
		 * Filters the list of database updates.
		 *
		 * @since 5.2.0
		 *
		 * @param boolean          $autoupdate Whether or not an automatic update can be run.
		 * @param string           $db_version The specified DB that's being upgraded from.
		 * @param LLMS_DB_Upgrader $upgrader   Instance of the database upgrader.
		 */
		return apply_filters( 'llms_can_auto_update_db', $autoupdate, $this->db_version, $this );
	}

	/**
	 * Retrieve the callback's prefix string based on the schema's namespace declaration.
	 *
	 * If `$info['namespace']` is empty, no prefix will be added.
	 * If `$info['namespace']` is `true`, the namespace is assumed to be `LLMS\Updates`.
	 * If `$info['namespace']` is a string, that string will be used.
	 *
	 * If a namespace is found, `\Version_X_X_X` will automatically be appended to the namespace. The
	 * string `X_X_X` is the database version for the upgrade substituting underscores for dots.
	 *
	 * @since 5.6.0
	 *
	 * @param array  $info    Upgrade schema array.
	 * @param string $version Version string for the upgrade.
	 * @return string
	 */
	protected function get_callback_prefix( $info, $version ) {

		if ( ! empty( $info['namespace'] ) ) {

			$ver = explode( '-', $version ); // Drop prerelease data.
			$ver = str_replace( '.', '_', $ver[0] );
			$ns  = true === $info['namespace'] ? 'LLMS\Updates' : $info['namespace'];
			return sprintf( '%1$s\\Version_%2$s\\', $ns, $ver );

		}

		return '';
	}

	/**
	 * Enqueue and dispatch required updates
	 *
	 * Adds callbacks for all required updates to the LLMS_Background_Updater and dispatches
	 * the updater in the background.
	 *
	 * If the update group cannot be auto-updated the following admin notices will be included:
	 * + The "update started" notice will be immediately displayed/added.
	 * + The "update complete" notice will be added to the end of the queue (and then displayed when the update is complete).
	 *
	 * @since 5.2.0
	 * @since 5.6.0 Add namespace prefix to qualifying callback functions.
	 *
	 * @return void
	 */
	public function enqueue_updates() {

		$queued = false;
		foreach ( $this->get_required_updates() as $version => $info ) {

			$prefix = $this->get_callback_prefix( $info, $version );
			foreach ( $info['updates'] as $callback ) {

				$callback = $prefix . $callback;

				$this->updater->log( sprintf( 'Queuing %s - %s', $version, $callback ) );
				$this->updater->push_to_queue( $callback );
				$queued = true;

			}
		}

		// No updates to add, return early.
		if ( ! $queued ) {
			return;
		}

		// Show a start and complete notice for manual updates.
		if ( ! $this->can_auto_update() ) {
			$this->show_notice_started();
			$this->updater->push_to_queue( array( $this, 'show_notice_complete' ) );
		}

		$this->updater->save();

		add_action( 'shutdown', array( 'LLMS_Install', 'dispatch_db_updates' ) );
	}

	/**
	 * Retrieves the updates list
	 *
	 * @since 5.2.0
	 *
	 * @return array
	 */
	public function get_updates() {

		/**
		 * Filters the list of database updates.
		 *
		 * @since 5.2.0
		 *
		 * @param array            $updates  List of updates to be run.
		 * @param LLMS_DB_Upgrader $upgrader Instance of the database upgrader.
		 */
		return apply_filters( 'llms_db_updates_list', $this->updates, $this );
	}

	/**
	 * Retrieve a filtered list of updates as required by the specified DB version
	 *
	 * All updates greater than the specified version will be returned.
	 *
	 * @since 5.2.0
	 *
	 * @return array[]
	 */
	public function get_required_updates() {

		$db_version = $this->db_version;

		return array_filter(
			$this->get_updates(),
			function ( $update_version ) use ( $db_version ) {
				return version_compare( $db_version, $update_version, '<' );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Determine whether or not there are required updates for a specified DB version.
	 *
	 * @since 5.2.0
	 *
	 * @return boolean Returns `true` if there are updates to run, otherwise returns `false`.
	 */
	public function has_required_updates() {

		$required = $this->get_required_updates( $this->db_version );
		return ! empty( $required );
	}

	/**
	 * Show the db upgrade admin notice.
	 *
	 * Users can click this notice to start the database upgrade(s).
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	protected function show_notice_pending() {

		$notice_id = 'bg-db-update';

		if ( LLMS_Admin_Notices::has_notice( $notice_id ) ) {
			LLMS_Admin_Notices::delete_notice( $notice_id );
		}

		LLMS_Admin_Notices::add_notice(
			$notice_id,
			array(
				'dismissible'  => false,
				'template'     => 'db-update.php',
				'default_path' => LLMS_PLUGIN_DIR . 'includes/admin/views/notices/',
			)
		);
	}

	/**
	 * Show a notice when a manual update is started.
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	protected function show_notice_started() {

		LLMS_Admin_Notices::add_notice(
			'bg-db-update-started',
			__( 'Your database is being upgraded in the background. Feel free to leave this page. A notice like this will appear when the update is complete.', 'lifterlms' ),
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 0,
			)
		);
	}

	/**
	 * Show a notice when the update is complete
	 *
	 * This will also delete the started notice. When short updates run quickly the started and completed notice
	 * may show up on the same page load which is confusing to look at it. If we just started and it's already done
	 * when the next page loads we only need to see that update is complete.
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function show_notice_complete() {

		// If the update started notice exists, delete it to avoid confusing UX when the update finishes before the page loads.
		if ( LLMS_Admin_Notices::has_notice( 'bg-db-update-started' ) ) {
			LLMS_Admin_Notices::delete_notice( 'bg-db-update-started' );
		}

		LLMS_Admin_Notices::add_notice(
			'bg-db-update-complete',
			__( 'The LifterLMS database update is complete.', 'lifterlms' ),
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 0,
			)
		);
	}

	/**
	 * Start the update
	 *
	 * If autoupdating is possible, will enqueue and dispatch the bg updater. Otherwise
	 * it will show the update pending notice which will prompt an admin to manually
	 * start the update.
	 *
	 * @since 5.2.0
	 *
	 * @return boolean Returns `false` if there are no updates to run and `true` otherwise.
	 */
	public function update() {

		if ( ! $this->has_required_updates() ) {
			return false;
		}

		// Auto update if it can.
		if ( $this->can_auto_update() ) {
			$this->enqueue_updates();
		} else {
			$this->show_notice_pending();
		}

		return true;
	}
}
