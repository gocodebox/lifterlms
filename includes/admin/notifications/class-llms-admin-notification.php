<?php
/**
 * LifterLMS Admin Notification Class.
 *
 * @package LifterLMS/Admin/Notifications/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Notification
 *
 * @since [version]
 */
class LLMS_Admin_Notification {

	/**
	 * Notification ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Notification title.
	 *
	 * @var string
	 */
	public string $title;

	/**
	 * Notification content.
	 *
	 * @var string
	 */
	public string $content;

	/**
	 * Notification start date.
	 *
	 * @var string
	 */
	public string $start_date;

	/**
	 * Notification end date.
	 *
	 * @var string
	 */
	public string $end_date;

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	public string $type;

	/**
	 * Notification icon.
	 *
	 * @var string
	 */
	public string $icon;

	/**
	 * Notification priority.
	 *
	 * @var int
	 */
	public int $priority;

	/**
	 * Notification dismissible.
	 *
	 * @var bool
	 */
	public bool $dismissible;

	/**
	 * Notification conditions.
	 *
	 * @var LLMS_Admin_Notification_Condition[]
	 */
	public array $conditions;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @param object $args Notification object.
	 */
	public function __construct( object $args ) {

		$this->id          = $args->id ?? 0;
		$this->title       = $args->title ?? esc_html__( 'Untitled Notification', 'lifterlms' );
		$this->content     = $args->content ?? '';
		$this->start_date  = $args->start_date ?? date( 'Y-m-d' );
		$this->end_date    = $args->end_date ?? date( 'Y-m-d', strtotime( '+1 week' ) );
		$this->type        = $args->type ?? 'info';
		$this->icon        = $args->icon ?? 'info-outline';
		$this->priority    = $args->priority ?? 10;
		$this->dismissible = $args->dismissible ?? true;
		$this->conditions  = array_map(
			static fn( object $condition ) => new LLMS_Admin_Notification_Condition( $condition ),
			(array) ( $args->conditions ?? [] )
		);

	}

	/**
	 * Check if the notification should be displayed.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function check_conditions() {

		if ( ! $this->check_date_range() ) {
			return false;
		}

		if ( ! $this->conditions ) {
			return true;
		}

		foreach ( $this->conditions as $condition ) {
			if ( ! $condition->check_callback() ) {
				return false;
			}
		}

		return true;

	}

	/**
	 * Checks if a notification is within date range.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function check_date_range(): bool {

		if ( ! $this->start_date && ! $this->end_date ) {
			return false;
		}

		$start = strtotime( $this->start_date );
		$end   = strtotime( $this->end_date );

		if ( ! $start && ! $end ) {
			return false;
		}

		$time = time();

		if ( $time < $start ) {
			return false;
		}

		if ( $time > $end ) {
			return false;
		}

		return true;
	}

}
