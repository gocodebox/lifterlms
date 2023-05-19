// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	PanelRow,
	Disabled,
	RangeControl,
	ToggleControl,
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
import { useCourseOptions, useLlmsPostType, CourseSelect } from '../../../packages/components/src/course-select';

const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps();
	const isLlmsPostType = useLlmsPostType();
	const courseOptions = useCourseOptions();

	const memoizedServerSideRender = useMemo( () => {
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
				<p className={ 'llms-block-empty' }>{ __( 'Author not found. This block will not be displayed.', 'lifterlms' ) }</p>
			}
		/>;
	}, [ attributes ] );

	if ( ! attributes.course_id && ! isLlmsPostType ) {
		setAttributes( {
			course_id: courseOptions?.[ 0 ]?.value,
		} );
	}

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Course Author Settings', 'lifterlms' ) }>
				<PanelRow>
					<RangeControl
						label={ __( 'Avatar Size', 'lifterlms' ) }
						help={ __( 'The size of the avatar in pixels.', 'lifterlms' ) }
						value={ attributes.avatar_size }
						onChange={ ( size ) => setAttributes( {
							avatar_size: parseInt( size ),
						} ) }
						min={ 0 }
						max={ 300 }
						allowReset={ true }
						resetFallbackValue={ blockJson.attributes.avatar_size.default }
						default={ blockJson.attributes.avatar_size.default }
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Bio', 'lifterlms' ) }
						help={ attributes?.bio ? __( 'Author bio is displayed.', 'lifterlms' ) : __( 'Author bio is hidden.', 'lifterlms' ) }
						checked={ attributes.bio === 'yes' }
						onChange={ ( value ) => setAttributes( {
							bio: value ? 'yes' : 'no',
						} ) }
					/>
				</PanelRow>
				<CourseSelect { ...props } />
			</PanelBody>
		</InspectorControls>
		<div { ...blockProps }>
			<Disabled>
				{ memoizedServerSideRender }
			</Disabled>
		</div>
	</>;
};

registerBlockType( blockJson, {
	icon: Icon,
	edit: Edit,
} );
