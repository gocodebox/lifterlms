<?php
/**
 * LLMS_Meta_Box_Certificate_Sync class
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box to sync between awarded certificates and certificate templates.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Certificate_Sync extends LLMS_Abstract_Meta_Box_User_Engagement_Sync {

	/**
	 * Type of user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'certificate';

	/**
	 * The post type of an awarded engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_awarded = 'llms_my_certificate';

	/**
	 * The post type of an engagement template.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_template = 'llms_certificate';

	/**
	 * Post types that this meta box should be added to.
	 *
	 * @var string[]
	 */
	public $screens = array(
		'llms_certificate', // Template.
		'llms_my_certificate', // Awarded.
	);
}
