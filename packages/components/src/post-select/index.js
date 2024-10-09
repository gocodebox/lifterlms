import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelRow, SelectControl } from '@wordpress/components';

export const llmsPostTypes = [
	'course',
	'lesson',
	'llms_quiz'
];

export const getPostTypeName = ( slug = 'course', format = 'name' ) => {
	const name = slug?.replace( 'llms_', '' );
	const title = name?.charAt( 0 )?.toUpperCase() + name?.slice( 1 );

	return format === 'name' ? name : title;
};

export const useLlmsPostType = () => {
	const postType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );

	return llmsPostTypes.includes( postType );
};

export const usePostOptions = ( postType = 'course' ) => {
	const queryArgs = {
		per_page: 100,
		status: 'publish',
	};

	const { posts, currentPostType } = useSelect( ( select ) => {
		return {
			posts: select( 'core' ).getEntityRecords( 'postType', postType, queryArgs ),
			currentPostType: select( 'core/editor' )?.getCurrentPostType(),
		};
	}, [] );

	const options = [];

	const isSingleCourseTemplate = useSelect( ( select ) => {
		return select( 'core/edit-site' )?.getEditedPostId( 'template' )?.includes( 'single-course' );
	} );

	const postTypeName = getPostTypeName( postType );

	if ( ! llmsPostTypes.includes( currentPostType ) && ! isSingleCourseTemplate ) {
		options.push( {
			label: __( 'Select ', 'lifterlms' ) + postTypeName,
			value: 0,
		} );
	}

	if ( llmsPostTypes.includes( currentPostType ) || isSingleCourseTemplate ) {
		options.unshift( {
			label: sprintf(
				// Translators: %s = Post type name.
				__( 'Inherit from current %s', 'lifterlms' ),
				postTypeName
			),
			value: 0,
		} );
	}

	if ( ! options?.length ) {
		options.push( {
			label: __( 'Loading', 'lifterlms' ),
			value: 0,
		} );
	}

	if ( posts?.length ) {
		posts.forEach( ( post ) => {
			options.push( {
				label: post.title.rendered + ' (ID: ' + post.id + ')',
				value: post.id,
			} );
		} );
	}

	return options;
};

export const PostSelect = (
	{
		attributes,
		setAttributes,
		postType = 'course',
		attribute = 'course_id',
	}
) => {
	const options = usePostOptions( postType );
	const postTypeName = getPostTypeName( postType );
	const postTypeTitle = getPostTypeName( postType, 'title' );

	const helpText = sprintf(
		// Translators: %s = Post type name.
		__( 'Select the %s to associate with this block.', 'lifterlms' ),
		postTypeName
	);

	return <PanelRow>
		<SelectControl
			label={ postTypeTitle }
			help={ helpText }
			value={ attributes?.[ attribute ] ?? options?.[ 0 ]?.value }
			options={ options }
			onChange={ ( value ) => {
				setAttributes( {
					[ attribute ]: parseInt( value, 10 ),
				} );
			} }
		/>
	</PanelRow>;
};
