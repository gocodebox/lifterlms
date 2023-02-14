import { registerBlockType } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

const Edit = ( { attributes } ) => {
	const blockProps = useBlockProps();

	return <>
		<div
			{ ...blockProps }
		>
			<Disabled>
				<ServerSideRender
					block={ blockJson.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () =>
						<p>{ __( 'Loading…', 'lifterlms' ) }</p>
					}
					ErrorResponsePlaceholder={ () =>
						<p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
					}
					EmptyResponsePlaceholder={ () =>
						<p>{ __( 'Displays LifterLMS register form.', 'lifterlms' ) }</p>
					}

				/>
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	Edit,
} );
