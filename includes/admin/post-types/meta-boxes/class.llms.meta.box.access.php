<?php
/**
 * Metabox: Membership Access Restrictions
 *
 * @since 1.0.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Access class.
 *
 * @since 1.0.0
 * @since 3.0.0 Updated for 3.0.0 compatibility.
 */
class LLMS_Meta_Box_Access extends LLMS_Admin_Metabox {


	/**
	 * Configure the metabox
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id      = 'lifterlms-membership-access';
		$this->title   = __( 'Membership Access', 'lifterlms' );
		$this->screens = $this->get_screens();
		$this->context = 'side';

	}

	/**
	 * Define metabox fields
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {

		$post_type = get_post_type_object( $this->post->post_type );

		$restrictions = get_post_meta( $this->post->ID, $this->prefix . 'restricted_levels', true );

		if ( ! $restrictions ) {
			$restrictions = array();
		}

		return array(

			array(
				'title'  => __( 'Membership Access', 'lifterlms' ),
				'fields' => array(
					array(
						'controls'   => '#' . $this->prefix . 'restricted_levels',
						'desc_class' => 'd-1of2 t-1of2 m-1of2',
						'id'         => $this->prefix . 'is_restricted',
						'label'      => sprintf( _x( 'Restrict this %s', 'apply membership restriction to post type', 'lifterlms' ), $post_type->labels->singular_name ),
						'type'       => 'checkbox',
						'value'      => 'yes',
					),
					array(
						'class'           => 'input-full llms-select2-post',
						'data_attributes' => array(
							'post-type' => 'llms_membership',
						),
						'desc'            => sprintf( __( 'Visitors must belong to one of these memberships to access this %s', 'lifterlms' ), strtolower( $post_type->labels->singular_name ) ),
						'id'              => $this->prefix . 'restricted_levels',
						'label'           => __( 'Memberships', 'lifterlms' ),
						'multi'           => true,
						'type'            => 'select',
						'value'           => llms_make_select2_post_array( $restrictions ),
					),
				),
			),
		);

	}

	/**
	 * Determine the screens where the metabox should be rendered.
	 *
	 * This is determined by finding all public post types and checking if they support
	 * the 'llms-membership-restrictions' feature.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_screens() {

		$screens = array();

		// Check against all public post types.
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'names',
			'and'
		);

		foreach ( $post_types as $post_type ) {

			// check if the post type supports membership restrictions.
			if ( post_type_supports( $post_type, 'llms-membership-restrictions' ) ) {

				$screens[] = $post_type;

			}
		}

		return $screens;

	}

}
