// External deps.
import classnames from 'classnames';

// Internal deps.
import { RichText, useBlockProps } from '@wordpress/block-editor';

/**
 * Save the block content.
 *
 * @since 6.0.0
 *
 * @param {Object} args            Save arguments.
 * @param {Object} args.attributes Block attributes.
 * @return {WPElement} Block HTML fragment.
 */
export default function save( { attributes } ) {
	const { textAlign, content, level } = attributes,
		TagName = 'h' + level,
		className = classnames( {
			[ `has-text-align-${ textAlign }` ]: textAlign,
		} );

	return (
		<TagName { ...useBlockProps.save( { className } ) }>
			<RichText.Content value={ content } />
		</TagName>
	);
}
