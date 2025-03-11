( function( wp ) {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, useState } = wp.element;
	const { ToolbarButton, Modal } = wp.components;
	const { BlockControls } = wp.blockEditor;
	const { apiFetch } = wp;

	const withProtectImageToolbar = createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			if ( props.name !== 'core/image' ) {
				return <BlockEdit { ...props } />;
			}

			const [ isModalOpen, setModalOpen ] = useState( false );

			const handleSelect = ( selectedId ) => {
				apiFetch( {
					path: `/wp/v2/media/${ props.attributes.id }`,
					method: 'POST',
					data: {
						meta: { _llms_media_protection_product_id: selectedId }
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
							title="Select Course or Membership"
							onRequestClose={ () => setModalOpen( false ) }
						>
							<p>Select a Course or Membership to protect this image:</p>
							{/* Dynamically fetch and list your Courses/Memberships here */}
							<ToolbarButton onClick={ () => handleSelect( 101 ) }>
								Course 101
							</ToolbarButton>
							<ToolbarButton onClick={ () => handleSelect( 202 ) }>
								Membership 202
							</ToolbarButton>
						</Modal>
					) }
				</Fragment>
			);
		};
	}, 'withProtectImageToolbar' );

	addFilter(
		'editor.BlockEdit',
		'my-plugin/with-protect-image-toolbar',
		withProtectImageToolbar
	);
} )( window.wp );
