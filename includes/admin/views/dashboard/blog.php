<?php
/**
 * Blog meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Dashboard
 *
 * @since 7.1.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

// Get RSS Feed(s).
require_once ABSPATH . WPINC . '/feed.php';

// Get a SimplePie feed object from the specified feed source.
$rss = fetch_feed( 'https://lifterlms.com/feed' );

$maxitems = 0;

if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly.

	// Figure out how many total items there are, but limit it to 3.
	$maxitems = $rss->get_item_quantity( 3 );

	// Build an array of all the items, starting with element 0 (first element).
	$rss_items = $rss->get_items( 0, $maxitems );

endif;

?>

<ul>
	<?php if ( 0 === $maxitems ) : ?>
		<li><?php esc_html_e( 'No news found.', 'lifterlms' ); ?></li>
	<?php else : ?>
		<?php // Loop through each feed item and display each item as a hyperlink. ?>
		<?php foreach ( $rss_items as $item ) : ?>
			<li>
				<a
					href="<?php echo esc_url( $item->get_permalink() ); ?>"
					title="<?php printf( esc_attr__( 'Posted %s', 'lifterlms' ), esc_attr( date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ) ); ?>"
					target="_blank"
					rel="noopener"><?php echo esc_html( $item->get_title() ); ?></a>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<p>
	<a
		class="llms-button-secondary small"
		href="https://lifterlms.com/blog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Blog"
		target="_blank"
		rel="noopener"><i class="fa fa-external-link" aria-hidden="true"></i> <?php esc_html_e( 'View More', 'lifterlms' ); ?></a>
</p>
