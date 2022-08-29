import wordsCount from 'words-count';

import createContainer from './create-container';
import getCounterTextColor from './get-counter-text-color';
import formatNumber from './format-number';

/**
 * The modules options object.
 *
 * @typedef {Object} WordCountModuleOptions
 *
 * @property {?number}  min           The minimum required words. If `null` no minimum will be enforced.
 * @property {?number}  max           The maximum required words. If `null` no maximum will be enforced.
 * @property {string}   colorWarning  A CSS color code used when approaching the maximum word count.
 * @property {string}   colorError    A CSS color code used when below the minimum or above the maximum word count.
 * @property {Function} onChange      Callback function invoked when the quill text changes. This function is passed
 *                                    3 parameters: the `quill` object, the module options object, and the current number of words.
 * @property {Object}   l10n          An object of language strings used in the module's UI.
 * @property {string}   l10n.singular The singular unit, default "word".
 * @property {string}   l10n.plural   The plurarl unit, default "words".
 * @property {string}   l10n.min      Text to display for minimum count, default "Minimum".
 * @property {string}   l10n.max      Text to display for maximum count, default "Maximum".
 */

/**
 * Merges default options into the supplied options ensuring all necessary options in exist in the resulting object.
 *
 * @since [version]
 *
 * @param {WordCountModuleOptions} options A full or partial options object.
 * @return {WordCountModuleOptions} A full options object.
 */
export function setOptions( options = {} ) {
	options = {
		...{
			min: null,
			max: null,
			colorWarning: '#ff922b', // Orange.
			colorError: '#e5554e', // Red.
			onChange: () => {},
			l10n: {},
		},
		...options,
	};

	options.l10n = {
		...{
			singular: 'word',
			plural: 'words',
			min: 'Minimum',
			max: 'Maximum',
		},
		...options.l10n,
	};

	return options;
}

/**
 * The Quill Word Count Module.
 *
 * @since [version]
 *
 * @param {Object}                 quill   A `Quill` editor instance.
 * @param {WordCountModuleOptions} options A full or partial options object.
 * @return {void}
 */
export default function( quill, options = {} ) {
	options = setOptions( options );

	const container = createContainer( options ),
		counter = document.createElement( 'span' );

	counter.className = 'ql-wordcount-counter';
	counter.style.float = 'right';

	container.appendChild( counter );

	const updateCounter = () => {
		const wordCount = wordsCount( quill.getText() );

		counter.style.color = getCounterTextColor( wordCount, options );

		const unit = 1 === wordCount ? options.l10n.singular : options.l10n.plural;
		counter.innerHTML = formatNumber( wordCount ) + ' ' + unit;

		options.onChange( quill, options, wordCount );
	};

	updateCounter();

	quill.container.parentNode.insertBefore( container, quill.container.nextSibling );

	quill.on( 'text-change', updateCounter );
}
