import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState, useEffect, useRef } from '@wordpress/element';
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
		postTypeAttribute = null,
	}
) => {
	const postTypes = Array.isArray( postType ) ? postType : [ postType ];
	const postTypesKey = postTypes.join( ',' );
	const isMulti = postTypes.length > 1;

	const currentPostType = useSelect( ( select ) => select( 'core/editor' )?.getCurrentPostType(), [] );
	const [ posts, setPosts ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const abortRef = useRef( null );

	const postTypeName = postTypes.map( ( type ) => getPostTypeName( type ) ).join( ', ' );
	const postTypeTitle = postTypes.map( ( type ) => getPostTypeName( type, 'title' ) ).join( ' / ' );

	const defaultOption = {
		label: llmsPostTypes.includes( currentPostType )
			? sprintf(
				// Translators: %s = Post type name.
				__( 'Inherit from current %s', 'lifterlms' ),
				getPostTypeName( currentPostType )
			)
			: sprintf(
				// Translators: %s = Post type name(s).
				__( 'Select %s', 'lifterlms' ),
				postTypeName
			),
		value: '0',
	};

	// Always show the ID; when multiple post types are searched, prefix it with the type name to disambiguate.
	const toOption = ( post, type ) => {
		const suffix = isMulti
			? ' (' + getPostTypeName( type, 'title' ) + ', ID: ' + post.id + ')'
			: ' (ID: ' + post.id + ')';

		return {
			label: decodeEntities( post.title.rendered ) + suffix,
			value: post.id.toString(),
			postType: type,
		};
	};

	// Fetch posts from the API based on the search term.
	const fetchPosts = ( term, value = 0 ) => {
		setIsLoading( true );

		// Abort an in-flight request so a slower earlier query can't overwrite a newer one.
		if ( abortRef.current ) {
			abortRef.current.abort();
		}
		const controller = 'undefined' !== typeof AbortController ? new AbortController() : null;
		abortRef.current = controller;

		Promise.all(
			postTypes.map( ( type ) =>
				apiFetch( {
					path: `/wp/v2/${ type }?per_page=10&search=${ encodeURIComponent( term ) }`,
					signal: controller?.signal,
				} ).then( ( results ) => results.map( ( post ) => toOption( post, type ) ) )
			)
		)
			.then( ( groups ) => {
				const options = groups.flat();
				setPosts( options );

				// Ensure the currently saved selection is always available as an option.
				if ( value && ! options.some( ( option ) => option.value === value.toString() ) ) {
					const includeType = ( postTypeAttribute && attributes?.[ postTypeAttribute ] ) || postTypes[ 0 ];
					apiFetch( { path: `/wp/v2/${ includeType }?include=${ value }` } )
						.then( ( found ) => {
							setPosts( [ ...options, ...found.map( ( post ) => toOption( post, includeType ) ) ] );
						} )
						.catch( () => {} );
				}
			} )
			.catch( ( error ) => {
				if ( 'AbortError' !== error?.name ) {
					setPosts( [] );
				}
			} )
			.finally( () => {
				// Only the most recent request should clear the loading state.
				if ( abortRef.current === controller ) {
					setIsLoading( false );
				}
			} );
	};

	const selectedValue = attributes?.[ attribute ];

	useEffect( () => {
		const timeout = setTimeout( () => fetchPosts( searchTerm, selectedValue ), 300 );
		return () => clearTimeout( timeout );
	}, [ searchTerm, selectedValue, postTypesKey ] );

	const helpText = sprintf(
		// Translators: %s = Post type name(s).
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
				const selected = posts.find( ( option ) => option.value === value );
				const newAttributes = {
					[ attribute ]: parseInt( value, 10 ) || 0,
				};

				if ( postTypeAttribute && selected?.postType ) {
					newAttributes[ postTypeAttribute ] = selected.postType;
				}

				setAttributes( newAttributes );
			} }
			onFilterValueChange={ setSearchTerm }
			isLoading={ isLoading }
			allowReset={ false }
		/>
	</PanelRow>;
};
