<?php
/**
 * Post type block templates.
 *
 * Returns an array of post type block types for use in post type registration.
 *
 * @package LifterLMS/Schemas
 *
 * @since [version]
 * @version [version]
 *
 * @see LLMS_Post_Types::get_template().
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared block template for the `llms_certificate` and `llms_my_certificate` post types.
 *
 * @since [version]
 */
$certificates = array(
	array( 'llms/certificate-title' ),
	array(
		'core/spacer',
		array(
			'height' => 100,
		),
	),
	array(
		'core/heading',
		array(
			'content'   => __( 'Presented to', 'lifterlms' ),
			'level'     => 3,
			'textAlign' => 'center',
		),
	),
	array(
		'core/heading',
		array(
			'content'   => '[llms-user display_name]',
			'level'     => 2,
			'textAlign' => 'center',
		),
	),
	array(
		'core/heading',
		array(
			'content'   => __( 'for demonstration of excellence', 'lifterlms' ),
			'level'     => 3,
			'textAlign' => 'center',
		),
	),
	array(
		'core/spacer',
		array(
			'height' => 100,
		),
	),
	array(
		'core/columns',
		array(
			'isStackedOnMobile' => false,
		),
		array(
			array(
				'core/column',
				array(),
				array(
					array(
						'core/paragraph',
						array(
							'align'   => 'center',
							'content' => '{current_date}',
						),
					),
					array(
						'core/separator',
						array(
							'align' => 'center',
						),
					),
					array(
						'core/paragraph',
						array(
							'align'   => 'center',
							'content' => __( 'DATE', 'lifterlms' ),
						),
					),
				),
			),
			array( 'core/column' ),
			array(
				'core/column',
				array(),
				array(
					array(
						'core/paragraph',
						array(
							'align'   => 'center',
							'content' => '{site_title}',
						),
					),
					array(
						'core/separator',
						array(
							'align' => 'center',
						),
					),
					array(
						'core/paragraph',
						array(
							'align'   => 'center',
							'content' => __( 'SIGNED', 'lifterlms' ),
						),
					),
				),
			),
		),
	),
);

return array(
	'llms_certificate'    => $certificates,
	'llms_my_certificate' => $certificates,
);
