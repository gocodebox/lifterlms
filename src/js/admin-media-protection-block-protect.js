( function( wp ) {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, useState, useEffect, useRef, createInterpolateElement } = wp.element;
	const { ToolbarButton, Modal, Button, Flex, FlexItem, Notice, ExternalLink } = wp.components;
	const { BlockControls } = wp.blockEditor;
	const { apiFetch } = wp;

	const supportedMediaBlocks = [
		'core/image',
		'core/video',
		'core/audio',
		'core/file',
	];

	const getUrlAttr = ( blockName ) => {
		switch ( blockName ) {
			case 'core/image':
				return 'url';
			case 'core/audio':
				return 'src';
			case 'core/video':
				return 'src';
			case 'core/file':
				return 'href';
			default:
				return 'url';
		}
	};

	const withProtectImageToolbar = createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const warningText = createInterpolateElement(
				LLMS.l10n.translate(
					'This media is not protected. If you select a product here, the media will be moved to the protected uploads directory and existing links to the media will no longer work. <link>Learn More</link>'
				),
				{
					link: (
						<ExternalLink
							href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page"
						/>
					),
				}
			);

			// We don't have a media ID if "insert from URL" is used.
			if ( ! props.attributes || ! props.attributes.id ) {
				return <BlockEdit { ...props } />;
			}

			if ( ! supportedMediaBlocks.includes( props.name ) ) {
				return <BlockEdit { ...props } />;
			}

			const [ isModalOpen, setModalOpen ] = useState( false );
			const [ productTitle, setProductTitle ] = useState( null );
			const [ originalFile, setOriginalFile ] = useState( '' );
			const [ showUnprotectWarning, setShowUnprotectWarning ] = useState( false );
			const selectRef = useRef( null );

			useEffect( () => {
				if ( isModalOpen && selectRef.current ) {
					jQuery( selectRef.current ).llmsPostsSelect2();
					const handleChange = function() {
						const val = jQuery( this ).val();
						setShowUnprotectWarning( ! val );
					};
					const $el = jQuery( selectRef.current );
					$el.on( 'change', handleChange );
					return () => {
						$el.off( 'change', handleChange );
					};
				}
			}, [ isModalOpen ] );

			const handleProtectImage = () => {
				const rawValue = jQuery( selectRef.current ).val();
				// An empty string from a cleared select2 means "unprotect this media".
				// The REST update callback treats an empty value as a request to unprotect.
				const selectedId = rawValue && '' !== rawValue ? rawValue : 0;

				apiFetch( {
					path: `/wp/v2/media/${ props.attributes.id }`,
					method: 'POST',
					data: {
						_llms_media_protection_product_id: selectedId
					}
				} ).then( ( updatedMedia ) => {
					const urlAttr = getUrlAttr( props.name );
					props.setAttributes( { [ urlAttr ]: updatedMedia.source_url } );
					setProductTitle( null );
					setOriginalFile( '' );
					setShowUnprotectWarning( false );
				} ).catch( ( err ) => {
					console.error( 'Error updating media meta:', err );
				} );
				setModalOpen( false );
			};

			useEffect( () => {
				if ( ! isModalOpen ) {
					return;
				}

				apiFetch( {
					path: `/wp/v2/media/${ props.attributes.id }?context=edit`,
				} )
					.then( ( media ) => {
						const productId = media._llms_media_protection_product_id;
						if ( ! productId ) {
							setProductTitle( null );      // media isn’t protected yet
							setOriginalFile( '' );
							return;
						}

						setOriginalFile( media._llms_media_protection_original_file || '' );

						const tryEndpoints = [ 'courses', 'memberships' ];
						( async () => {
							for ( const type of tryEndpoints ) {
								try {
									const product = await apiFetch( {
										path: `/llms/v1/${ type }/${ productId }?context=edit`,
									} );
									setProductTitle( product.title.raw );
									return;
								} catch ( er ) {
									// keep trying
								}
							}
							setProductTitle( '(unknown)' );   // couldn’t load it
						} )();
					} )
					.catch( ( er ) => {
						console.error( 'Unable to read media meta', er );
						setProductTitle( null );
						setOriginalFile( '' );
					} );
			}, [ isModalOpen, props.attributes.id ] );

			return (
				<Fragment>
					<BlockEdit { ...props } />
					<BlockControls group="inline">
						<ToolbarButton
							icon="lock"
							label="Protect Image"
							onClick={ () => setModalOpen( true ) }
						/>
					</BlockControls>

					{ isModalOpen && (
						<Modal
							title={ LLMS.l10n.translate( 'Select Course or Membership' ) }
							onRequestClose={() => setModalOpen(false)}
						>
							<Flex direction="column" gap={4}>
								<FlexItem>
									<label htmlFor="llms-protect-image-select">{ LLMS.l10n.translate( 'Select a Course or Membership to protect this image:' ) }</label>
								</FlexItem>

								<FlexItem>
									{ ! props.attributes[ getUrlAttr( props.name ) ].includes( 'llms_media_id' ) &&
										<Notice status="warning" isDismissible={false}>
											{ warningText }
										</Notice>
									}

								<select
									id="llms-protect-image-select"
									ref={ selectRef }
									className='llms-block-protect llms-posts-select2'
									data-no-view-button='true'
									data-allow_clear='true'
									data-post-type='course,llms_membership'
									></select>
								</FlexItem>
								{ productTitle && (
									<FlexItem>
										{ LLMS.l10n.translate( 'Currently protected by:' ) }&nbsp;
										<strong>{ productTitle }</strong>
									</FlexItem>
								) }
								{ originalFile && (
									<FlexItem>
										{ LLMS.l10n.translate( 'Originally located in:' ) }&nbsp;
										<code>wp-content/uploads/{ originalFile }</code>
									</FlexItem>
								) }
								{ showUnprotectWarning && originalFile && (
									<FlexItem>
										<Notice status="warning" isDismissible={false}>
											{ LLMS.l10n.translate( 'Clearing the selected product will move this file back to its original public location. Existing protected links to this media will no longer work.' ) }
										</Notice>
									</FlexItem>
								) }
								<FlexItem>
									<Button
										isPrimary
										onClick={ () => {
											handleProtectImage();
										} }
									>
										{ productTitle ? LLMS.l10n.translate( 'Update Protection' ) : LLMS.l10n.translate( 'Protect Image' ) }
									</Button>
								</FlexItem>
							</Flex>
						</Modal>
					)}
				</Fragment>
			);
		};
	}, 'withProtectImageToolbar');

	addFilter(
		'editor.BlockEdit',
		'my-plugin/with-protect-image-toolbar',
		withProtectImageToolbar
	);
})(window.wp);
