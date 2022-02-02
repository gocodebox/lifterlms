<?php
/**
 * LLMS_Trait_User_Engagement_Type definition
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods to help get a user engagement type label name.
 *
 * Classes that use this trait MUST implement {@see LLMS_Interface_User_Engagement_Type} and {@see LLMS_Interface_Case}
 * because traits can not define constants.
 *
 * @since [version]
 */
trait LLMS_Trait_User_Engagement_Type {

	use LLMS_Trait_Case;

	/**
	 * The type of user engagement, e.g. 'achievement' or 'certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type;

	/**
	 * The post type of an awarded engagement, e.g. 'llms_my_achievement' or 'llms_my_certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_awarded;

	/**
	 * The post type of an engagement template, e.g. 'llms_achievement' or 'llms_certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_template;

	/**
	 * Returns an awarded child LLMS_Abstract_User_Engagement instance for the given post or false if not found.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post A WP_Post object or a WP_Post ID. A falsy value will use
	 *                               the current global `$post` object (if one exists).
	 * @return LLMS_Abstract_User_Engagement|false
	 */
	protected function get_awarded_engagement( $post ) {

		$post = get_post( $post );
		if ( ! $post || "llms_my_$this->engagement_type" !== $post->post_type ) {
			return false;
		}

		$class = 'LLMS_User_' . ucwords( $this->engagement_type, '_' );

		return new $class( $post );
	}

	/**
	 * Returns the label name for the given post type.
	 *
	 * @since [version]
	 *
	 * @param WP_Post_Type|null $post_type_object   WP_Post_Type object if it exists, null otherwise.
	 * @param int               $plural_or_singular Either the PLURAL or SINGULAR constant from {@see LLMS_Interface_User_Engagement_Type}.
	 * @return string
	 */
	private function get_engagement_label_name( $post_type_object, $plural_or_singular ) {

		if ( is_null( $post_type_object ) ) {
			$name = __( 'Unknown Engagement Type', 'lifterlms' );
		} else {
			switch ( $plural_or_singular ) {
				case self::PLURAL:
					$name = $post_type_object->labels->name;
					break;
				default:
				case self::SINGULAR:
					$name = $post_type_object->labels->singular_name;
					break;
			}
		}

		return $name;
	}

	/**
	 * Returns the translated label name of this user engagement type.
	 *
	 * @since [version]
	 *
	 * @param int $plural_or_singular Either the PLURAL or SINGULAR constant from {@see LLMS_Interface_User_Engagement_Type}.
	 * @param int $case               One of the CASE_ constants from {@see LLMS_Interface_Case}.
	 * @param int $type               Either the AWARDED or TEMPLATE constant from {@see LLMS_Interface_User_Engagement_Type}.
	 * @return string
	 */
	protected function get_engagement_type_name( $plural_or_singular, $case, $type ) {

		$post_type_object = $this->get_engagement_type_object( $type );
		$name             = $this->get_engagement_label_name( $post_type_object, $plural_or_singular );
		$name             = $this->change_case( $name, $case );

		return $name;
	}

	/**
	 * Returns the post type object for this awarded engagement or engagement template.
	 *
	 * LifterLMS post types are defined in {@see LLMS_Post_Types::register_post_types()}.
	 *
	 * @since [version]
	 *
	 * @param int $type Either the AWARDED or TEMPLATE constant from {@see LLMS_Interface_User_Engagement_Type}.
	 * @return WP_Post_Type|null
	 */
	private function get_engagement_type_object( $type ) {

		switch ( $type ) {
			case self::AWARDED:
				$post_type_name = $this->post_type_awarded;
				break;
			case self::TEMPLATE:
			default:
				$post_type_name = $this->post_type_template;
				break;
		}
		$post_type_object = get_post_type_object( $post_type_name );

		return $post_type_object;
	}
}
