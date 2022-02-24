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

$styles = array(
	'title' => array(
		'typography' => array(
			'fontSize'   => '90px',
			'lineHeight' => '1.1',
		),
		'spacing'    => array(
			'margin' => array(
				'top'    => '40px',
				'bottom' => '0px',
			),
		),
	),
	'h2'    => array(
		'typography' => array(
			'fontSize'   => '48px',
			'lineHeight' => '1.3',
		),
		'spacing'    => array(
			'margin' => array(
				'top'    => '0px',
				'bottom' => '0px',
			),
		),
	),
	'h3'    => array(
		'typography' => array(
			'fontSize'   => '32px',
			'lineHeight' => '1.3',
		),
		'spacing'    => array(
			'margin' => array(
				'top'    => '0px',
				'bottom' => '0px',
			),
		),
	),
	'p'     => array(
		'typography' => array(
			'fontSize'   => '18px',
			'lineHeight' => '1.6',
		),
	),
);

/**
 * Shared block template for the `llms_certificate` and `llms_my_certificate` post types.
 *
 * @since [version]
 */
$certificates = array(
	array(
		'llms/certificate-title',
		array(
			'style' => $styles['title'],
		),
	),
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
			'style'     => $styles['h3'],
		),
	),
	array(
		'core/heading',
		array(
			'content'   => '[llms-user display_name]',
			'level'     => 2,
			'textAlign' => 'center',
			'style'     => $styles['h2'],
		),
	),
	array(
		'core/heading',
		array(
			'content'   => __( 'for demonstration of excellence', 'lifterlms' ),
			'level'     => 3,
			'textAlign' => 'center',
			'style'     => $styles['h3'],
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
							'style'   => $styles['p'],
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
							'style'   => $styles['p'],
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
							'style'   => $styles['p'],
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
							'style'   => $styles['p'],
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
