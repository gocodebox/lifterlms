/**
 * Define LifterLMS Course and Membership Visibility Options
 *
 * @since   1.3.0
 * @version 1.3.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const visibilityOptions = applyFilters(
	'llms_blocks_post_visibility_options',
	[
		{
			value: 'catalog_search',
			label: __( 'Visible', 'lifterlms' ),
			info: __(
				'Visible in the catalog and search results.',
				'lifterlms'
			),
		},
		{
			value: 'catalog',
			label: __( 'Catalog only', 'lifterlms' ),
			info: __( 'Only visible in the catalog.', 'lifterlms' ),
		},
		{
			value: 'search',
			label: __( 'Search only', 'lifterlms' ),
			info: __( 'Only visible in search results.', 'lifterlms' ),
		},
		{
			value: 'hidden',
			label: __( 'Hidden', 'lifterlms' ),
			info: __( 'Hidden from catalog and search results.', 'lifterlms' ),
		},
	]
);
