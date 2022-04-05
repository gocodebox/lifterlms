// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal deps.
import { NAMESPACE, API_VERSION } from '../constants';

/**
 * Data store reducer key.
 *
 * @type {string}
 */
export const STORE_NAME = 'llms/access-plans';

/**
 * API path Resource name.
 *
 * @type {string}
 */
export const API_RESOURCE_NAME = 'access-plans';

/**
 * Entity record kind.
 *
 * @type {string}
 */
export const ENTITY_KIND = `${ NAMESPACE }/postType`;

/**
 * Entity record name.
 *
 * @type {string}
 */
export const ENTITY_NAME = 'accessPlan';

/**
 * Entity configuration object.
 *
 * @type {Object}
 */
export const ENTITY_CONFIG = {
	kind: ENTITY_KIND,
	name: ENTITY_NAME,
	baseURL: `${ NAMESPACE }/${ API_VERSION }/${ API_RESOURCE_NAME }`,
	baseURLParams: {
		context: 'edit',
	},
	label: __( 'Access Plan', 'lifterlms' ),
	transientEdits: {
		blocks: true,
		selection: true,
	},
	mergedEdits: {
		meta: true,
	},
	rawAttributes: [ 'title', 'excerpt', 'content' ],
	getTitle: ( record ) => record?.title?.rendered || record?.title,
};

