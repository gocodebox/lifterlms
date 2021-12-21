// External deps.
import classnames from 'classnames';

// Internal deps.
import { RichText, useBlockProps } from '@wordpress/block-editor';

import { getFont } from './fonts-store';


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
