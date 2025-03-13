( function( wp ) {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, useState, useEffect, useRef } = wp.element;
	const { ToolbarButton, Modal, Button, Flex, FlexItem } = wp.components;
	const { BlockControls } = wp.blockEditor;
	const { apiFetch } = wp;

	const withProtectImageToolbar = createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			if ( props.name !== 'core/image' ) {
				return <BlockEdit { ...props } />;
			}

			const [ isModalOpen, setModalOpen ] = useState( false );
			const [ selectedId, setSelectedId ] = useState( null );
			const selectRef = useRef( null );

			useEffect( () => {
				if ( isModalOpen && selectRef.current ) {
					jQuery( selectRef.current ).llmsPostsSelect2();
				}
			}, [ isModalOpen ] );

			const handleProtectImage = () => {
				const selectedId = jQuery( selectRef.current ).val();

				apiFetch( {
					path: `/wp/v2/media/${ props.attributes.id }`,
					method: 'POST',
					data: {
						_llms_media_protection_product_id: selectedId
					}
				} ).then( ( updatedMedia ) => {
					props.setAttributes( { url: updatedMedia.source_url } );
				} ).catch( ( err ) => {
					console.error( 'Error updating media meta:', err );
				} );
				setModalOpen( false );
			};

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
								<select
									id="llms-protect-image-select"
									ref={ selectRef }
									className='llms-block-protect llms-posts-select2'
									data-no-view-button='true'
									data-allow_clear='false'
									data-post-type='course,llms_membership'
									></select>
								</FlexItem>
								<FlexItem>
									<Button
										isPrimary
										onClick={ () => {
											handleProtectImage();
										} }
									>
										Protect Image
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
