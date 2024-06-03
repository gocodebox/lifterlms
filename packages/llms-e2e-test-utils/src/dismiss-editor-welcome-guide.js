/**
 * Dismiss the "Welcome Guide" in the block editor (if it's active)
 *
 * @since 2.2.0
 *
 * @return {void}
 */
export async function dismissEditorWelcomeGuide() {
	const isWelcomeGuideActive = await page.evaluate( () =>
		wp.data.select( 'core/edit-post' ).isFeatureActive( 'welcomeGuide' )
	);
	if ( isWelcomeGuideActive ) {
		await page.evaluate( () =>
			wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' )
		);
	}
}
