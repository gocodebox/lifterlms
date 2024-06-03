import formatNumber from './format-number';

/**
 * Creates a new DOM node for the min or max count display.
 *
 * @since 2.0.0
 *
 * @param {string} classNameSuffix Suffix to add to the element's classname.
 * @param {string} text            Text to display.
 * @param {number} limit           Word count limit.
 * @return {Element} A DOM node element.
 */
function createCounterNode( classNameSuffix, text, limit ) {
	const node = document.createElement( 'i' );

	node.className = `ql-wordcount-${ classNameSuffix }`;

	node.style.opacity = '0.5';
	node.style.marginRight = '10px';

	node.innerHTML = `${ text }: ${ formatNumber( limit ) }`;

	return node;
}

/**
 * Creates a container element to house the wordcount module UI.
 *
 * @since 2.0.0
 *
 * @param {Object} options A `WordCountModuleOptions` options object.
 * @return {Element} The container DOM node element.
 */
export default function( options ) {
	const { l10n, min, max } = options,
		container = document.createElement( 'div' );

	container.className = 'ql-wordcount ql-toolbar ql-snow';
	container.style.marginTop = '-1px';
	container.style.fontSize = '85%';

	if ( min ) {
		container.appendChild( createCounterNode( 'min', l10n.min, min ) );
	}

	if ( max ) {
		container.appendChild( createCounterNode( 'max', l10n.max, max ) );
	}

	return container;
}
