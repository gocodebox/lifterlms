/**
 * Renders a Font Awesome icon.
 *
 * @since 0.0.1
 *
 * @param {Object}    props              Component properties.
 * @param {string}    props.icon         The Icon ID.
 * @param {string}    props.iconStyle    The icon style, enum: "solid", "regular", or "brands".
 * @param {string}    props.iconPrefix   The project's icon prefix.
 * @param {string}    props.label        The (optional) accessibility label to display for the icon.
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
				<span className="screen-reader-text">{ label }</span>
			) }
		</i>
	);
}
