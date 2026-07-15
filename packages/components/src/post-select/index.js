import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { PanelRow, ComboboxControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { decodeEntities } from '@wordpress/html-entities';

export const llmsPostTypes = [
	'course',
	'lesson',
	'llms_quiz',
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

	const options = [];

	if ( ! llmsPostTypes.includes( currentPostType ) ) {
		options.push( {
			label: __( 'Select course', 'lifterlms' ),
			value: 0,
		} );
	}

	if ( posts?.length ) {
		posts.forEach( ( post ) => {
			options.push( {
				label: decodeEntities( post.title.rendered ) + ' (ID: ' + post.id + ')',
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
	const currentPostType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );
	const [ posts, setPosts ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ searchTerm, setSearchTerm ] = useState( '' );

	const postTypeName = getPostTypeName( postType );
	const postTypeTitle = getPostTypeName( postType, 'title' );

	const defaultOption = {
		label: llmsPostTypes.includes( currentPostType )
			? sprintf(
				// Translators: %s = Post type name.
				__( 'Inherit from current %s', 'lifterlms' ),
				getPostTypeName( currentPostType )
			)
			: sprintf(
				// Translators: %s = Post type name.
				__( 'Select %s', 'lifterlms' ),
				postTypeName
			),
		value: '0',
	};

	const toOption = ( post ) => {
		return {
			label: decodeEntities( post.title.rendered ) + ' (ID: ' + post.id + ')',
			value: post.id.toString(),
		};
	};

	// Fetch posts from the API based on the search term.
	const fetchPosts = ( term, value = 0 ) => {
		setIsLoading( true );

		apiFetch( {
			path: `/wp/v2/${ postType }?per_page=10&search=${ encodeURIComponent( term ) }`,
		} )
			.then( ( results ) => {
				const options = results.map( toOption );
				setPosts( options );

				// Ensure the currently saved selection is always available as an option.
				if ( value && ! options.some( ( option ) => option.value === value.toString() ) ) {
					apiFetch( { path: `/wp/v2/${ postType }?include=${ value }` } )
						.then( ( found ) => {
							setPosts( [ ...options, ...found.map( toOption ) ] );
						} )
						.catch( () => {} );
				}
			} )
			.catch( () => setPosts( [] ) )
			.finally( () => setIsLoading( false ) );
	};

	const selectedValue = attributes?.[ attribute ];

	useEffect( () => {
		const timeout = setTimeout( () => fetchPosts( searchTerm, selectedValue ), 300 );
		return () => clearTimeout( timeout );
	}, [ searchTerm, selectedValue, postType ] );

	const helpText = sprintf(
		// Translators: %s = Post type name.
		__( 'Select the %s to associate with this block.', 'lifterlms' ),
		postTypeName
	);

	return <PanelRow>
		<ComboboxControl
			label={ postTypeTitle }
			help={ helpText }
			value={ String( selectedValue ?? 0 ) }
			options={ [ defaultOption, ...posts ] }
			onChange={ ( value ) => {
				setAttributes( {
					[ attribute ]: parseInt( value, 10 ) || 0,
				} );
			} }
			onFilterValueChange={ setSearchTerm }
			isLoading={ isLoading }
			allowReset={ false }
		/>
	</PanelRow>;
};
