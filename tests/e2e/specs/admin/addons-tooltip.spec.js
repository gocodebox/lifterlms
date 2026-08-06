/**
 * Test Add-ons screen tooltips are not clipped by card overflow.
 *
 * @see https://github.com/gocodebox/lifterlms/issues/3301
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Whether a tip positioned like `.tip--bottom-left` would be clipped by an
 * ancestor with `overflow: hidden|clip`.
 *
 * @param {import('@playwright/test').Locator} tipLink Details link locator.
 * @return {Promise<boolean>}
 */
async function isBottomTipClippedByOverflow( tipLink ) {
	return tipLink.evaluate( ( el ) => {
		const mirror = document.createElement( 'div' );
		mirror.textContent = el.getAttribute( 'data-tip' ) || '';
		mirror.setAttribute( 'aria-hidden', 'true' );
		mirror.style.cssText = [
			'position: absolute',
			'top: calc(100% + 6px)',
			'right: -10px',
			'font-size: 13px',
			'line-height: 1.2',
			'padding: 8px',
			'max-width: 300px',
			'width: max-content',
			'visibility: hidden',
			'pointer-events: none',
		].join( ';' );

		const previousPosition = el.style.position;
		if ( getComputedStyle( el ).position === 'static' ) {
			el.style.position = 'relative';
		}
		el.appendChild( mirror );

		const tipRect = mirror.getBoundingClientRect();
		let clipped = false;
		let node = el.parentElement;

		while ( node && node !== document.documentElement ) {
			const { overflow, overflowX, overflowY } = getComputedStyle( node );
			const clips = ( value ) => value === 'hidden' || value === 'clip';

			if ( clips( overflow ) || clips( overflowX ) || clips( overflowY ) ) {
				const parentRect = node.getBoundingClientRect();
				const epsilon = 0.5;
				if (
					tipRect.bottom > parentRect.bottom + epsilon ||
					tipRect.top < parentRect.top - epsilon ||
					tipRect.right > parentRect.right + epsilon ||
					tipRect.left < parentRect.left - epsilon
				) {
					clipped = true;
					break;
				}
			}

			node = node.parentElement;
		}

		mirror.remove();
		el.style.position = previousPosition;

		return clipped;
	} );
}

test.describe( 'Admin/AddOnsTooltip', () => {

	test( 'View add-on details tooltip is fully visible on hover', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php', 'page=llms-add-ons&section=all' );

		const tipLink = page.locator( 'a.open-plugin-details-modal[data-tip]' ).first();
		await expect( tipLink ).toBeVisible( { timeout: 30000 } );

		await tipLink.scrollIntoViewIfNeeded();
		await tipLink.hover();

		const tipStyles = await tipLink.evaluate( ( el ) => {
			const before = getComputedStyle( el, '::before' );
			return {
				content: before.content,
				opacity: before.opacity,
				visibility: before.visibility,
			};
		} );

		expect( tipStyles.visibility ).toBe( 'visible' );
		expect( parseFloat( tipStyles.opacity ) ).toBeGreaterThan( 0 );
		expect( tipStyles.content ).toContain( 'View add-on details' );

		expect( await isBottomTipClippedByOverflow( tipLink ) ).toBe( false );
	} );

} );
