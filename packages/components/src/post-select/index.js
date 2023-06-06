import { __ } from '@wordpress/i18n';
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
		posts.forEach( ( course ) => {
			options.push( {
				label: course.title.rendered + ' (ID: ' + course.id + ')',
				value: course.id,
			} );
		} );
	}

	if ( llmsPostTypes.includes( currentPostType ) ) {
		options.unshift( {
			label: __( 'Inherit from current ', 'lifterlms' ) + getPostTypeName( currentPostType ),
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
	const helpText = __( 'Select the ', 'lifterlms' ) + postTypeName + __( ' to associate with this block.', 'lifterlms' );

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
