<?php
/**
 * LifterLMS Admin Notification Class.
 *
 * @package LifterLMS/Admin/Classes
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
	 * @var array
	 */
	public array $conditions;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @param object $object Notification stdClass.
	 */
	public function __construct( $object ) {

		$this->id          = $object->id ?? 0;
		$this->title       = $object->title ?? esc_html__( 'Untitled Notification', 'lifterlms' );
		$this->content     = $object->content ?? '';
		$this->start_date  = $object->start_date ?? date( 'd/m/Y' );
		$this->end_date    = $object->end_date ?? date( 'd/m/Y', strtotime( '+1 week' ) );
		$this->type        = $object->type ?? 'info';
		$this->icon        = $object->icon ?? 'info-outline';
		$this->priority    = $object->priority ?? 10;
		$this->dismissible = $object->dismissible ?? true;
		$this->conditions  = $object->conditions ?? [];

	}


}
