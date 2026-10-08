/**
 * Add visibility attributes to all blocks
 *
 * @since 1.0.0
 * @since 1.5.1 Exits early for non LifterLMS dynamic blocks.
 * @since 1.6.0 Setup visibility support checking as a module.
 * @since 1.8.0 Merge default values into block settings.
 */

// Internal deps.
import check from './check';

/**
 * Setup attribute settings for a given attribute.
 *
 * This merges our default attribute settings with existing attribute settings
 * if they exist, allowing blocks to register their own custom visibility defaults.
 *
 * Note: the attribute *type* will always be overwritten even if it's defined in the block.
 * This is done to ensure that our internal logic for handling enrollment data isn't broken.
 *
 * @since 1.8.0
 *
 * @param {Object} attributes    Block settings.attributes object.
 * @param {string} attributeName Visibility attribute name.
 * @param {Object} defaults      Visibility attribute default values.
 * @return {Object} Updated block settings attributes.
 */
const setAttribute = ( attributes, attributeName, defaults ) => {
	// Default is already set, ensure type is set.
	if ( attributes[ attributeName ] && attributes[ attributeName ].default ) {
		attributes[ attributeName ].type = defaults.type;
	} else {
		attributes[ attributeName ] = defaults;
	}

	return attributes;
};

/**
 * Add visibility settings to qualifying blocks.
 *
 * Block registration filter callback.
 *
 * @since 1.0.0
 * @since 1.8.0 Merge default values into block settings.
 *
 * @param {Object} settings Block settings object.
 * @param {string} name Block name, eg "core/paragraph".
 * @return {Object} Block settings object.
 */
export default function visibilityAttributes( settings, name ) {
	if ( ! check( settings, name ) ) {
		return settings;
	}

	if ( ! settings.attributes ) {
		settings.attributes = {};
	}

	const attrs = {
		llms_visibility: {
			default: 'all',
			type: 'string',
		},
		llms_visibility_in: {
			default: '',
			type: 'string',
		},
		llms_visibility_posts: {
			default: '[]',
			type: 'string',
		},
	};

	Object.keys( attrs ).forEach( ( key ) => {
		settings.attributes = setAttribute(
			settings.attributes,
			key,
			attrs[ key ]
		);
	} );

	return settings;
}
