/**
 * Dismiss the "Welcome Guide" in the block editor (if it's active)
 *
 * @since [version]
 *
 * @return {Void}
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
