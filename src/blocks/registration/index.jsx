import { registerBlockType } from '@wordpress/blocks';
import {
	Disabled
} from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import blockJson from './block.json';

registerBlockType( blockJson, {
	edit: ( { attributes } ) => {
		const blockProps = useBlockProps();

		return <>
			<div
				{ ...blockProps }
			>
				<p
					style={ {
						padding: '16px',
						margin: '0 0 8px',
						lineHeight: '1',
						border: '1px solid #e0e0e0',
						fontSize: '16px',
					} }
				>
					{ __( 'Displays the LifterLMS registration form to logged out users.', 'lifterlms' ) }
				</p>

				{ 1 === 2 &&
				  <Disabled>
					  <ServerSideRender
						  block={ blockJson.name }
						  attributes={ attributes }
						  LoadingResponsePlaceholder={ () =>
							  <p>{ __( 'Loading...', 'lifterlms' ) }</p>
						  }
						  ErrorResponsePlaceholder={ () =>
							  <p>{ __( 'Error loading content. Please check block settings are valid.', 'lifterlms' ) }</p>
						  }
						  EmptyResponsePlaceholder={ () =>
							  <p>{ __( 'Displays LifterLMS register form.', 'lifterlms' ) }</p>
						  }

					  />
				  </Disabled>
				}
			</div>
		</>;
	}
} );
