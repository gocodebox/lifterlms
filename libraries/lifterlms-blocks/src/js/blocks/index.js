/**
 * LifterLMS Block Library.
 *
 * @since 1.7.0
 * @since 2.5.0 Remove course syllabus block.
 * @since 2.8.0 Remove pricing table block, now provided by LifterLMS core.
 * @version 2.8.0
 */

/* eslint camelcase: [ "error", { allow: [ "_llms_form_location" ] } ] */

// WP Deps.
import {
	getBlockTypes,
	registerBlockType,
	unregisterBlockType,
} from '@wordpress/blocks';
import { store as editorStore } from '@wordpress/editor';
import { doAction, applyFilters } from '@wordpress/hooks';
import { select } from '@wordpress/data';

// Internal Deps.
import { getCurrentPostType } from '../util/';

// Standard Blocks.
import * as courseContinueButton from './course-continue-button/';
import * as courseInfo from './course-information/';
import * as courseProgress from './course-progress/';
import * as instructors from './instructors/';
import * as lessonNavigation from './lesson-navigation/';
import * as lessonProgression from './lesson-progression/';
import * as phpTemplate from './php-template/';

// Form Field Blocks.
import * as formFields from './form-fields/';

/**
 * Deregister all blocks no on a safelist on the forms post type.
 *
 * @since 1.6.0
 * @since 1.7.0 Block 'llms/form-field-redeem-voucher' only available on registration forms.
 *
 * @return {void}
 */
export const deregisterBlocksForForms = () => {
	/**
	 * Filters the list of blocks allowed to be used within LifterLMS Forms
	 *
	 * All blocks except those explicitly defined in this safelist are excluded from use
	 * in the editor of a form post type.
	 *
	 * @since 2.0.0
	 *
	 * @param {string[]} safelist Array of allowed block names.
	 */
	const safelist = applyFilters( 'llms.formBlocksSafelist', [
		'core/block', // Reusable block.
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/html',
		'core/column',
		'core/columns',
		'core/group',
		'core/separator',
		'core/spacer',
	] );

	const { getCurrentPost } = select( editorStore ),
		{ meta = {} } = getCurrentPost(),
		{ _llms_form_location } = meta;

	/**
	 * Determine if a block should be deregistered from form posts.
	 *
	 * @since 1.7.0
	 * @since 1.12.0 Use `safelist` in favor of `whitelist`.
	 * @since 2.0.0 Add core/block to the safelist & prevent user login on edit account page.
	 *
	 * @param {string} name Block name.
	 * @return {boolean} Returns `true` if a block should be unregistered.
	 */
	const shouldUnregisterBlock = ( name ) => {
		// Allow safelisted blocks.
		if ( -1 !== safelist.indexOf( name ) ) {
			return false;

			// Vouchers can only be used on registration forms.
		} else if ( 0 === name.indexOf( 'llms/form-field-redeem-voucher' ) ) {
			return 'registration' === _llms_form_location ? false : true;

			// User login cannot be used on the account page.
		} else if ( 0 === name.indexOf( 'llms/form-field-user-login' ) ) {
			return 'account' === _llms_form_location;

			// Allow all other form field blocks.
		} else if ( -1 !== name.indexOf( 'llms/form-field' ) ) {
			return false;
		}

		// Unregister everything else.
		return true;
	};

	getBlockTypes().forEach( ( { name } ) => {
		if ( shouldUnregisterBlock( name ) ) {
			unregisterBlockType( name );
		}
	} );
};

/**
 * Register LifterLMS Core Blocks
 *
 * @since 1.0.0
 * @since 1.5.0 Only register blocks for supported post types.
 * @since 1.6.0 Add form field blocks.
 * @since 1.7.3 Move form ready event from domReady to here to ensure blocks are exposed before blocks are parsed.
 * @since 2.0.0 Trigger `llms_form_fields_ready` on `wp_block` posts.
 * @since 2.3.0 Register phpTemplate block.
 * @since 2.5.0 Remove course syllabus block.
 * @since 2.8.0 Remove pricing table block, now provided by LifterLMS core.
 */
export default () => {
	const postType = getCurrentPostType();

	// Blocks to register.
	const blocks = [
		courseContinueButton,
		courseInfo,
		courseProgress,
		instructors,
		lessonNavigation,
		lessonProgression,
		phpTemplate,
	];

	// Add "composed" form fields to the block registration list.
	Object.keys( formFields ).forEach( ( key ) => {
		if ( formFields[ key ].composed ) {
			blocks.push( formFields[ key ] );
		}
	} );

	if ( [ 'llms_form', 'wp_block' ].includes( postType ) ) {
		/**
		 * Expose all form field blocks, regardless of their registration status, for 3rd parties to utilize.
		 *
		 * @since 1.6.0
		 * @since 1.7.3 Moved from domReady to JS initialization.
		 *
		 * @param {Array} formFields Array of form field block data objects.
		 */
		doAction( 'llms_form_fields_ready', formFields );
	}

	blocks.forEach( ( block ) => {
		const { name, postTypes, settings } = block;
		if ( ! postTypes || ! postType || -1 !== postTypes.indexOf( postType ) ) {
			registerBlockType( name, settings );
		}
	} );
};
