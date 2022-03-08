/**
 * Deletes all blocks in the editor.
 *
 * @since 3.3.0
 *
 * @return {Promise} Promise from page.evaluate().
 */
export async function clearBlocks() {
	return page.evaluate( () => {
		const
			blockEditorStore = 'core/block-editor',
			{ select, dispatch } = window.wp.data,
			{ removeBlocks } = dispatch( blockEditorStore ),
			{ getBlocks } = select( blockEditorStore );

		return removeBlocks( getBlocks().map( ( { clientId } ) => clientId ) );
	} );
}
