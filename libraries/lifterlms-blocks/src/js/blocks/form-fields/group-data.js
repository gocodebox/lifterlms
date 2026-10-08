/**
 * Manage confirm group block data
 *
 * @since 2.0.0
 * @version 2.2.0
 */

// WP deps.
import { dispatch, select } from '@wordpress/data';

// Exterenal deps.
import { find, isEmpty, merge } from 'lodash';

/**
 * Retrieve a list of field group blocks
 *
 * @since 2.0.0
 *
 * @return {?Array.<Object>} Array of supporting block objects
 */
function getSupportingParents() {
	const { getBlockTypes, hasBlockSupport } = select( 'core/blocks' );
	return getBlockTypes().filter( ( block ) =>
		hasBlockSupport( block, 'llms_field_group' )
	);
}

/**
 * Retrieve the field group parent for a given block
 *
 * @since 2.0.0
 * @since 2.1.0 Export method for use by other components.
 *
 * @param {string} clientId The client ID of an existing block.
 * @return {Object} WP Block object of the parent.
 */
export function getParentFieldGroup( clientId ) {
	const { getBlock, getBlockParentsByBlockName } = select(
		'core/block-editor'
	);
	return getBlock(
		getBlockParentsByBlockName(
			clientId,
			getSupportingParents().map( ( { name } ) => name )
		)
	);
}

/**
 * Retrieve the sibling block for a block in a field group
 *
 * @since 2.0.0
 * @since 2.1.0 Export method for use by other components.
 *
 * @param {string} clientId The client ID of an existing block.
 * @return {?Object} WP Block object of the sibling.
 */
export function getSibling( clientId ) {
	const parent = getParentFieldGroup( clientId );
	return parent && parent.innerBlocks.length
		? find( parent.innerBlocks, ( block ) => block.clientId !== clientId )
		: null;
}

/**
 * Update children blocks of a confirm group
 *
 * @since 2.2.0
 *
 * @param {Object}   options
 * @param {Function} options.setAttributes   The setAttributes from the block.
 * @param {Object}   options.currentUpdates  Object describing updates to the current block attributes.
 * @param {string}   options.siblingClientId Sibling block client id.
 * @param {Object}   options.siblingUpdates  Object describing updates to the sibling block attributes.
 * @return {void}
 */
function updateChildren( {
	setAttributes,
	currentUpdates,
	siblingClientId,
	siblingUpdates,
} = {} ) {
	const { updateBlockAttributes } = dispatch( 'core/block-editor' );

	/**
	 * Persist the staged updates.
	 *
	 * The setTimeout is bad but fixes no-op/memory leak.
	 *
	 * @see {@link https://github.com/WordPress/gutenberg/issues/21049#issuecomment-632134201}
	 */
	setTimeout( () => {
		if ( ! isEmpty( currentUpdates ) ) {
			setAttributes( currentUpdates );
		}

		if ( siblingClientId && ! isEmpty( siblingUpdates ) ) {
			updateBlockAttributes( siblingClientId, siblingUpdates );
		}
	} );
}

/**
 * Retrieve the updates for fields within the confirm group
 *
 * @since 2.0.0
 * @since 2.2.0 Stop setting name, match, and id attribute.
 *
 * @param {Object} attributes        Block attributes.
 * @param {Object} siblingAttributes Sibling block attributes.
 * @return {Object} Object containing objects describing the required sibling and block attribute updates.
 */
function getConfirmGroupUpdates( attributes, siblingAttributes ) {
	const currentUpdates = {},
		siblingUpdates = {};

	// Sync required attribute between grouped fields.
	if ( attributes.required !== siblingAttributes.required ) {
		siblingUpdates.required = attributes.required;
	}

	// Sync the field variation type.
	if ( attributes.field !== siblingAttributes.field ) {
		siblingUpdates.field = attributes.field;
	}

	return {
		currentUpdates,
		siblingUpdates,
	};
}

export default function ( { attributes, clientId, setAttributes } = {} ) {
	const sibling = getSibling( clientId );

	let currentUpdates = {},
		siblingUpdates = {};

	// When a preview is generated in the block switcher you don't have anything to work with here.
	if ( ! sibling ) {
		return;
	}

	const siblingClientId = sibling.clientId;

	// Syncing for confirm group fields.
	if (
		attributes.isConfirmationControlField ||
		attributes.isConfirmationField
	) {
		const groupUpdates = getConfirmGroupUpdates(
			attributes,
			sibling.attributes
		);

		currentUpdates = merge( currentUpdates, groupUpdates.currentUpdates );
		siblingUpdates = merge( siblingUpdates, groupUpdates.siblingUpdates );
	}

	updateChildren( {
		setAttributes,
		currentUpdates,
		siblingClientId,
		siblingUpdates,
	} );
}
