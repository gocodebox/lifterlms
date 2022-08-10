/**
 * Renders a Font Awesome icon.
 *
 * @since [version]
 *
 * @param {Object}    props              Component properties.
 * @param {string}    props.icon         The Icon ID.
 * @param {String}    props.iconStyle    The icon style, enum: "solid", "regular", or "brands".
 * @param {String}    props.iconPrefix   The project's icon prefix.
 * @param {String}    props.label        The (optional) accessibility label to display for the icon.
 * @param {...Object} props.wrapperProps Any remaining properties which are passed to the icon wrapper component.
 * @return {WPElement} The component.
 */
export default function( { icon, iconStyle = 'solid', iconPrefix = 'llms-fa-', label = '', ...wrapperProps } ) {
	return (
		<i 
			className={ `${ iconPrefix }${ iconStyle } ${ iconPrefix }${ icon }` }
			{ ...wrapperProps }
		>
			{ label && (
				<span class="screen-reader-text">{ label }</span>
			) }
		</i>
	);
}