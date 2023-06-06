import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelRow, SelectControl } from '@wordpress/components';

export const llmsPostTypes = [
	'course',
	'lesson',
	'llms_quiz',
	'llms_membership',
];

export const getPostTypeName = ( slug, format = 'name' ) => {
	const name = slug?.replace( 'llms_', '' );
	const title = name.charAt( 0 ).toUpperCase() + name.slice( 1 );

	return format === 'name' ? name : title;
};

export const useLlmsPostType = () => {
	const postType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );

	return llmsPostTypes.includes( postType );
};

export const usePostOptions = ( postType = 'course' ) => {
	const { posts, currentPostType } = useSelect( ( select ) => {
		return {
			posts: select( 'core' ).getEntityRecords( 'postType', postType ),
			currentPostType: select( 'core/editor' )?.getCurrentPostType(),
		};
	}, [] );

	const postTypeName = getPostTypeName( postType );

	const options = [];

	if ( ! llmsPostTypes.includes( currentPostType ) ) {
		options.push( {
			label: __( 'Select ', 'lifterlms' ) + postTypeName,
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

	if ( llmsPostTypes.includes( currentPostType ) ) {
		options.unshift( {
			label: sprintf(
				// Translators: %s = Post type name.
				__( 'Inherit from current %s', 'lifterlms' ),
				getPostTypeName( currentPostType )
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
