import icons from './metadata.json';

export { default as Icon } from './components/icon';
export { default as IconPicker } from './components/icon-picker';

/**
 * An icon metadata object.
 *
 * @typedef {Object} IconMeta
 * @property {string[]} styles An array of icon styles available for the icon. Enum: "solid", "regular", or "brands".
 * @property {string}   label  The human-readable name / title of the icon.
 * @property {string[]} terms  A list of keywords or terms for the icon.
 */

/**
 * Retrieves metadata for a given icon.
 *
 * @since 0.0.1
 *
 * @param {string} iconId The icon ID.
 * @return {IconMeta|boolean} An icon metadata object or `false` if the icon can't be found.
 */
export function getMetadata( iconId ) {
	return icons[ iconId ] ?? false;
}
