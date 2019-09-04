<?php
/**
 * LLMS Pagination Template
 *
 * @package LifterLMS/Templates
 *
 * @since    1.0.0
 * @version  3.16.0
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
if ( $wp_query->max_num_pages < 2 ) {
	return;
}
?>

<nav class="llms-pagination">
<?php
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
