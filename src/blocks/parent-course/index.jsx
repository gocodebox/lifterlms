// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import { Disabled, Spinner } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';

const Edit = ( { attributes } ) => {
	const blockProps = useBlockProps();
	const currentPost = wp.data.select( 'core/editor' )?.getCurrentPost();

	return (
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					urlQueryArgs={ {
						post_id: currentPost?.id,
					} }
					LoadingResponsePlaceholder={ () => <Spinner /> }
					EmptyResponsePlaceholder={ () =>
						<p className={ 'llms-block-empty' }>{ __( 'Back to: (Parent Course) — visible on lessons only.', 'lifterlms' ) }</p>
					}
				/>
			</Disabled>
		</div>
	);
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
	save: () => null,
} );
