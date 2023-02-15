<?php
/**
 * Renders shortcode blocks.
 *
 * Available variables:
 *
 * $attributes (array): The block attributes.
 * $content (string): The block default content.
 * $block (WP_Block): The block instance.
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

if ( ! $shortcode || str_contains( $shortcode, __( 'No products were found matching your selection.', 'lifterlms' ) ) ) {
	return;
}

$html  = '<div ' . get_block_wrapper_attributes() . '>';
$html .= $shortcode;
$html .= '</div>';

echo $html;
