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

$attributes = $attributes ?? [];
$content    = $content ?? '';
$block      = $block ?? null;

unset( $attributes['llms_visibility'] );
unset( $attributes['llms_visibility_in'] );
unset( $attributes['llms_visibility_posts'] );

$block = $block ?? null;
$name  = str_replace(
	[ 'llms/', '-' ],
	[ '', '_' ],
	$block->name ?? ''
);

$atts = '';

foreach ( $attributes as $key => $value ) {
	if ( ! empty( $value ) ) {
		$atts .= " $key=$value";
	}
}

$html = '<div ' . get_block_wrapper_attributes() . '>';
$html .= "[lifterlms_$name $atts]";
$html .= '</div>';

echo do_shortcode( $html );
