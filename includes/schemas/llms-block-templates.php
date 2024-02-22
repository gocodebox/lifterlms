<?php
/**
 * Post type block templates.
 *
 * Returns an array of post type block types for use in post type registration.
 *
 * @package LifterLMS/Schemas
 *
 * @since 6.0.0
 * @version 7.3.0
 *
 * @see LLMS_Post_Types::get_template().
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
 */

defined( 'ABSPATH' ) || exit;

global $wp_version;

$blocks_styles = array(
	'certificate' => array(
		'title'  => array(
			'style'     => array(
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
			'textColor' => 'black',
		),
		'h2'     => array(
			'style'     => array(
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
			'textColor' => 'black',
		),
		'h3'     => array(
			'style'     => array(
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
			'textColor' => 'black',
		),
		'p'      => array(
			'style'     => array(
				'typography' => array(
					'fontSize'   => '18px',
					'lineHeight' => '1.6',
				),
			),
			'textColor' => 'black',
		),
		'spacer' => array(
			'height' => version_compare( $wp_version, '6.3-beta2', '>=' ) ? '100px' : 100,
		),
	),
);


/**
 * Filters the template blocks styling.
 *
 * @since 6.0.0
 *
 * @param array $blocks_styles Array of blocks styles.
 */
$blocks_styles = apply_filters( 'llms_block_templates_styling', $blocks_styles );

/**
 * Shared block template for the `llms_certificate` and `llms_my_certificate` post types.
 *
 * @since 6.0.0
 * @since 6.1.0 Changed the certificate template's use of the `{current_date}` merge code to `{earned_date}`.
 */
$certificates = array(
	array(
		'llms/certificate-title',
		array(
			'style'     => $blocks_styles['certificate']['title']['style'],
			'textColor' => $blocks_styles['certificate']['title']['textColor'],
		),
	),
	array(
		'core/spacer',
		array(
			'height' => $blocks_styles['certificate']['spacer']['height'],
		),
	),
	array(
		'core/heading',
		array(
			'content'   => __( 'Presented to', 'lifterlms' ),
			'level'     => 3,
			'textAlign' => 'center',
			'style'     => $blocks_styles['certificate']['h3']['style'],
			'textColor' => $blocks_styles['certificate']['h3']['textColor'],
		),
	),
	array(
		'core/heading',
		array(
			'content'   => '[llms-user display_name]',
			'level'     => 2,
			'textAlign' => 'center',
			'style'     => $blocks_styles['certificate']['h2']['style'],
			'textColor' => $blocks_styles['certificate']['h2']['textColor'],
		),
	),
	array(
		'core/heading',
		array(
			'content'   => __( 'for demonstration of excellence', 'lifterlms' ),
			'level'     => 3,
			'textAlign' => 'center',
			'style'     => $blocks_styles['certificate']['h3']['style'],
			'textColor' => $blocks_styles['certificate']['h3']['textColor'],
		),
	),
	array(
		'core/spacer',
		array(
			'height' => $blocks_styles['certificate']['spacer']['height'],
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
							'align'     => 'center',
							'content'   => '{earned_date}',
							'style'     => $blocks_styles['certificate']['p']['style'],
							'textColor' => $blocks_styles['certificate']['p']['textColor'],
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
							'align'     => 'center',
							'content'   => __( 'DATE', 'lifterlms' ),
							'style'     => $blocks_styles['certificate']['p']['style'],
							'textColor' => $blocks_styles['certificate']['p']['textColor'],
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
							'align'     => 'center',
							'content'   => '{site_title}',
							'style'     => $blocks_styles['certificate']['p']['style'],
							'textColor' => $blocks_styles['certificate']['p']['textColor'],
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
							'align'     => 'center',
							'content'   => __( 'SIGNED', 'lifterlms' ),
							'style'     => $blocks_styles['certificate']['p']['style'],
							'textColor' => $blocks_styles['certificate']['p']['textColor'],
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
