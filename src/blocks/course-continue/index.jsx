// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	Disabled,
	Spinner,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from '@wordpress/element';

// Internal dependencies.
import blockJson from './block.json';
import Icon from './icon.jsx';
import { usePostOptions, PostSelect } from '../../../packages/components/src/post-select';

const Edit = ( props ) => {
	const { attributes } = props;
	const blockProps = useBlockProps();
	const courseOptions = usePostOptions();

	const memoizedServerSideRender = useMemo( () => {
		let emptyPlaceholder = __( 'No progress data found for this course. This block will not be displayed.', 'lifterlms' );

		if ( ! attributes.course_id && courseOptions.length > 0 ) {
			emptyPlaceholder = __( 'No course selected. Please choose a Course from the block sidebar panel.', 'lifterlms' );
		}

		return <ServerSideRender
			block={ blockJson.name }
			attributes={ attributes }
			LoadingResponsePlaceholder={ () =>
				<Spinner />
			}
			ErrorResponsePlaceholder={ () =>
				<p className={ 'llms-block-error' }>{ __( 'Error loading content. Please check block settings are valid. This block will not be displayed.', 'lifterlms' ) }</p>
			}
			EmptyResponsePlaceholder={ () =>
				<p className={ 'llms-block-empty' }>{ emptyPlaceholder }</p>
			}
		/>;
	}, [ attributes ] );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Course Continue Settings', 'lifterlms' ) }
				>
					<PostSelect { ...props } />
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					{ memoizedServerSideRender }
				</Disabled>
			</div>
		</>
	);
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
