<?php
/**
 * LLMS Pagination Template
 *
 * @package LifterLMS/Templates/Loop
 *
 * @since 1.0.0
 * @version 4.10.0
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
if ( $wp_query->max_num_pages < 2 ) {
	return;
}

/**
 * Filter the list of CSS classes on the pagination wrapper element.
 *
 * @since 4.10.0
 *
 * @param string[] $classes Array of CSS classes.
 */
$classes = apply_filters( 'llms_get_pagination_wrapper_classes', array( 'llms-pagination' ) );
?>

<nav class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
<?php
// Generated HTML is escaped inside the function.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo paginate_links(
	array(
		'base'      => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
		'format'    => '?page=%#%',
		'total'     => $wp_query->max_num_pages,
		'current'   => max( 1, get_query_var( 'paged' ) ),
		'prev_next' => true,
		'prev_text' => '« ' . __( 'Previous', 'lifterlms' ),
		'next_text' => __( 'Next', 'lifterlms' ) . ' »',
		'type'      => 'list',
	)
);
?>
</nav>
