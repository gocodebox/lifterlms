// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';

const Edit = () => {
	const blockProps = useBlockProps();

	return <div { ...blockProps }>
		<InnerBlocks
			allowedBlocks={ [ 'llms/dashboard-section' ] }
		/>
	</div>;
};

const Save = () => {
	const blockProps = useBlockProps.save();

	return <div { ...blockProps }>
		<InnerBlocks.Content />
	</div>;
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
	save: Save,
} );
