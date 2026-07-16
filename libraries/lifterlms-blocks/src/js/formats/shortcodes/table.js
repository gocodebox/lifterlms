/**
 * Components used to display the shortcode list / table
 *
 * @since 2.0.0
 * @version 2.0.0
 */

import './editor.scss';

import { __ } from '@wordpress/i18n';
import { ClipboardButton, Button, Tooltip } from '@wordpress/components';
import { create, replace, insert } from '@wordpress/rich-text';
import { useCopyToClipboard } from '@wordpress/compose';
import { applyFilters } from '@wordpress/hooks';

/**
 * Retrieve the shortcode for a given meta key with optional default value
 *
 * @since 2.0.0
 *
 * @param {string} key          User meta key.
 * @param {string} defaultValue Default value.
 * @return {string} Shortcode content.
 */
function getShortcode( key, defaultValue = '' ) {
	const or = defaultValue ? ` or="${ defaultValue }"` : '';
	return `[llms-user ${ key }${ or }]`;
}

/**
 * Component for a "click to copy" button
 *
 * @since 2.0.0
 *
 * @param {Object}   options
 * @param {string}   options.text      Text to copy to the clipboard.
 * @param {Function} options.onSuccess Success callback.
 * @return {Object} The copy button fragment.
 */
function CopyButton( { text, onSuccess } ) {
	const canUseHook = 'undefined' !== typeof useCopyToClipboard;

	// WP 5.8+.
	const HookButton = () => {
		const ref = useCopyToClipboard( text, onSuccess );
		return (
			<Button isLink ref={ ref }>
				{ text }
			</Button>
		);
	};

	// WP < 5.8.
	const BackwardsButton = () => {
		return (
			<ClipboardButton isLink text={ text } onCopy={ onSuccess }>
				{ text }
			</ClipboardButton>
		);
	};

	return (
		<Tooltip text={ __( 'Click to copy.', 'lifterlms' ) }>
			{ canUseHook && <HookButton /> }
			{ ! canUseHook && <BackwardsButton /> }
		</Tooltip>
	);
}

/**
 * Render an item in the table
 *
 * @since 2.0.0
 *
 * @param {Object}   item
 * @param {string}   item.label          Field label.
 * @param {string}   item.name           Field name.
 * @param {string}   item.data_store_key The usermeta key name.
 * @param {boolean}  isActive            Whether or not the format is active by user selection/cursor location.
 * @param {Function} closeModal          Function to close the containing modal.
 * @param {Function} onChange            Change function for the current rich text editor.
 * @param {Object}   value               Rich text value object.
 * @param {string}   defaultValue        User submitted default value.
 */
function RenderTableData(
	{ label, name, data_store_key },
	isActive,
	closeModal,
	onChange,
	value,
	defaultValue
) {
	const shortcodeText = getShortcode( data_store_key, defaultValue );
	return (
		<tr key={ name }>
			<td>{ label }</td>
			<td>
				<CopyButton text={ shortcodeText } onSuccess={ closeModal } />
			</td>
			<td>
				<Button
					isSecondary
					isSmall
					onClick={ () => {
						const newVal = create( {
							html: `<span class="llms-user-sc-wrap">${ shortcodeText }</span>`,
						} );

						closeModal();
						onChange(
							isActive
								? replace( value, /\[user .+?\]/, newVal )
								: insert( value, newVal )
						);
					} }
				>
					{ __( 'Insert', 'lifterlms' ) }
				</Button>
			</td>
		</tr>
	);
}

/**
 * Determines whether or not a given item matches the current user search filter.
 *
 * @since 2.0.0
 *
 * @param {string} searchQuery            User submitted search query.
 * @param {Object} options
 * @param {string} options.label          Field label.
 * @param {string} options.name           Field name.
 * @param {string} options.id             Field ID.
 * @param {string} options.data_store_key Field usermeta key.
 * @return {boolean} Returns `true` if the field matches the query, otherwise `false`.
 */
function matchesSearchQuery(
	searchQuery,
	{ label, name, id, data_store_key }
) {
	const fieldsToSearch = [ label, name, id, data_store_key ],
		searchQueryLower = searchQuery.toLowerCase();

	return fieldsToSearch.some( ( string ) => {
		return string.toLowerCase().includes( searchQueryLower );
	} );
}

/**
 * Table component
 *
 * @since [version
 *
 * @param {Object} options
 * @param {Function} options.closeModal Function to close the containing modal.
 * @param {boolean} options.isActive    Whether or not the format is active by user selection/cursor location.
 * @param {Function} options.onChange   Change function for the current rich text editor.
 * @param {string} options.searchQuery  User submitted search query.
 * @param {Object} options.value        Rich text value object.
 * @param {string} options.defaultValue User submitted default value.
 * @return {Object} Component fragment.
 */
export default function ( {
	closeModal,
	isActive,
	onChange,
	searchQuery,
	value,
	defaultValue,
} ) {
	let { userInfoFields } = window.llms;

	// If we have a search query filter the list to those matching the query.
	if ( searchQuery ) {
		userInfoFields = userInfoFields.filter( ( item ) =>
			matchesSearchQuery( searchQuery, item )
		);
	}

	const emptySearch = ! userInfoFields.length;

	// Nothing to display.
	if ( emptySearch ) {
		userInfoFields.push( {
			data_store_key: searchQuery,
			label: __( 'Custom User Information', 'lifterlms' ),
			id: 'custom',
			name: searchQuery,
		} );
	}

	/**
	 * Filters a list of user information fields which are not eligible for use by the [llms-user] shortcode
	 *
	 * @since 2.0.0
	 *
	 * @param {string[]} exclude List of field IDs which should be excluded.
	 */
	const exclude = applyFilters( 'llms/userInfoShortcodes/exclude', [
		'password',
	] );
	userInfoFields = userInfoFields.filter(
		( { id } ) => ! exclude.includes( id )
	);

	return (
		<>
			{ emptySearch && (
				<p className="llms-error">
					{ __(
						'No fields found matching your search but you can use the shortcode below if the meta information exists in the database.',
						'lifterlms'
					) }
				</p>
			) }
			<table className="llms-table zebra">
				<thead>
					<tr>
						<th>{ __( 'Name', 'lifterlms' ) }</th>
						<th>{ __( 'Shortcode', 'lifterlms' ) }</th>
						<th>{ __( 'Insert', 'lifterlms' ) }</th>
					</tr>
				</thead>
				<tbody>
					{ userInfoFields.map( ( field ) =>
						RenderTableData(
							field,
							isActive,
							closeModal,
							onChange,
							value,
							defaultValue
						)
					) }
				</tbody>
			</table>
		</>
	);
}
