<?php
/**
 * Renders shortcode blocks.
 *
 * Available variables:
 *
 * $attributes (array): The block attributes.
 * $content (string): The block default content.
 * $block (WP_Block): The block instance.
 *
 * @package LifterLMS/Templates/Blocks
 *
 * @since [version]
 * @version [version]
 */

$attributes = $attributes ?? array();
$content    = $content ?? '';
$block      = $block ?? null;

if ( ! property_exists( $block, 'name' ) ) {
	return;
}

$name = str_replace(
	array( 'llms/', '-' ),
	array( '', '_' ),
	$block->name
);

$atts = '';

foreach ( $attributes as $key => $value ) {
	if ( ! empty( $value ) && ! str_contains( $key, 'llms_visibility' ) ) {
		$atts .= " $key=$value";
	}
}

$shortcode = trim( do_shortcode( "[lifterlms_$name $atts]" ) );

// This allows emptyResponsePlaceholder to be used when no content is returned.
if ( ! $shortcode ) {
	return;
}

// Use emptyResponsePlaceholder for Courses block instead of shortcode message.
if ( false !== strpos( $shortcode, __( 'No products were found matching your selection.', 'lifterlms' ) ) ) {
	return;
}

$html = '<div ' . get_block_wrapper_attributes() . '>';
$html .= $shortcode;
$html .= '</div>';

echo $html;
