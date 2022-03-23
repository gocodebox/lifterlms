// External deps.
import { Global } from '@emotion/react';

// WP Deps.
import { applyFilters } from '@wordpress/hooks';

/**
 * Colors used by default themes.
 *
 * @type {Object}
 */
const colors = {
	white: '#fff',
	lightGrey: '#dedede',
};

/**
 * Default themes.
 *
 * {@see {@link https://emotion.sh/docs/globals}}
 * {@see {@link https://emotion.sh/docs/object-styles}}
 *
 * @type {Object}
 */
const DEFAULT_THEMES = {
	default: {
		'.llms-sortable-list.theme--default .llms-sortable-list--item': {
			border: `1px solid ${ colors.lightGrey }`,
			marginBottom: ' -1px',
			padding: '10px',
			position: 'relative',
			zIndex: 100,
			'&.llms-is-dragging': {
				boxShadow: `0 4px 8px 2px ${ colors.lightGrey }`,
				border: `1px solid ${ colors.lightGrey }`,
				background: colors.white,
				zIndex: 999,
			},
			'& .llms-sortable-list--item-header': {
				display: 'flex',
				alignItems: 'center',
				'& section': {
					flex: 2,
					'& small': {
						marginLeft: '3px',
					},
				},
				'& aside': {
					flex: 1,
					textAlign: 'right',
				},
				'& .components-button.is-small.has-icon:not(.has-text)': {
					minWidth: '24px',
					padding: 0,
				},
			},
		},
	},
};

/**
 * Retrieves a <Global> styles component for the requested SortableList theme.
 *
 * @since [version]
 *
 * @param {Object} props         Component properties.
 * @param {string} props.themeId The ID of the theme to use.
 * @return {?Global} Returns the Global styles component or `null` if the requested theme isn't defined.
 */
export default function ( { themeId } ) {
	/**
	 * Filters the list of themes available for the <SortableList> component.
	 *
	 * This hook can be used to add custom themes or modify the default themes.
	 *
	 * @since [version]
	 *
	 * @param {Object} themes An object of themes.
	 */
	const themes = applyFilters( 'llms.SortableList.themes', DEFAULT_THEMES ),
		themeStyles = themes[ themeId ];
	if ( ! themeStyles ) {
		return null;
	}
	return <Global styles={ themeStyles } />;
}
