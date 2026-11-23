/**
 * Block-level visibility checks
 *
 * @since 1.6.0
 * @version 2.3.0
 */

// WP Deps.
import { applyFilters } from '@wordpress/hooks';

/**
 * Returns a list of blocks that we've decided should not support block visibility
 *
 * @since 2.0.0
 * @since 2.3.0 Disallow block visibility for LifterLMS PHP Template block.
 *
 * @return {Array} List of block names.
 */
const getDisallowedBlocks = () => {
	/**
	 * Filters the list of blocks which explicitly do not support block-level visibility
	 *
	 * @since Unkneown
	 *
	 * @param {string[]} blockNames A list of blocknames.
	 */
	return applyFilters( 'llms_block_visibility_disallowed_blocks', [
		/**
		 * Otherwise known as the "Classic" block.
		 *
		 * @see {@link https://github.com/gocodebox/lifterlms-blocks/issues/41}
		 */
		'core/freeform',
		'llms/php-template',
	] );
};

/**
 * Determine if a block should have visibility settings added to it.
 *
 * @since 1.6.0
 * @since 1.8.0 Add a "blacklist" of blocks that don't support visibility.
 * @since 2.0.0 Use `getDisallowedBlocks()` in favor of removed `getBlacklist()`.
 *
 * @param {Object} settings Block settings object.
 * @param {string} name     Block name, eg "core/paragraph".
 * @return {boolean} Returns `true` when visibility is supported, otherwise `false`.
 */
export default function supportsVisibility( settings, name ) {
	let ret = true;

	// Don't add to Dynamic Blocks.
	if ( -1 !== window.llms.dynamic_blocks.indexOf( name ) ) {
		ret = false;

		// Don't add to blocks that explicitly don't support llms_visibility.
	} else if (
		settings.supports &&
		false === settings.supports.llms_visibility
	) {
		ret = false;

		// Exclude blocks identified by our blacklist.
	} else if ( getDisallowedBlocks().includes( name ) ) {
		ret = false;
	}

	/**
	 * Filters whether or not a block supports block-level visibility
	 *
	 * @since 1.7.1
	 *
	 * @param {boolean} ret      Visibility support value. Will be `true` when visibility is supported and `false` when not.
	 * @param {Object}  settings Block settings object.
	 * @param {string}  name     Block name, eg "core/paragraph".
	 */
	return applyFilters(
		'llms_block_supports_visibility',
		ret,
		settings,
		name
	);
}
