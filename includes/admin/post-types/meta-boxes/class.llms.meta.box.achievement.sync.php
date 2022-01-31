<?php
/**
 * LLMS_Meta_Box_Achievement_Sync class
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box to sync between awarded achievements and achievement templates.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Achievement_Sync extends LLMS_Abstract_Meta_Box_User_Engagement_Sync {

	/**
	 * Type of user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'achievement';

	/**
	 * The post type of an awarded engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_awarded = 'llms_my_achievement';

	/**
	 * The post type of an engagement template.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_template = 'llms_achievement';

	/**
	 * Post types that this meta box should be added to.
	 *
	 * @var string[]
	 */
	public $screens = array(
		'llms_achievement', // Template.
		'llms_my_achievement', // Awarded.
	);
}
