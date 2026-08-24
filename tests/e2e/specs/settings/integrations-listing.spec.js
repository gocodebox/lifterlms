/**
 * Settings Integrations listing
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { visitSettingsPage } from '../../utils/index.js';

test.describe( 'Settings/IntegrationsListing', () => {

	test( 'Integrations table shows catalog columns, core rows, and exclusions', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitSettingsPage( page, { tab: 'integrations' } );

		const table = page.locator( '.llms-integrations-table' );
		await expect( table ).toBeVisible();

		const header = table.locator( 'thead' );
		await expect( header ).toContainText( 'Integration' );
		await expect( header ).toContainText( 'Description' );
		await expect( header ).toContainText( 'Installed' );
		await expect( header ).toContainText( 'Activated' );
		await expect( header ).toContainText( 'Enabled' );
		await expect( header ).toContainText( 'Documentation' );
		await expect( header ).not.toContainText( 'Integration ID' );
		await expect( header ).not.toContainText( 'Integration Settings' );

		await expect( table ).toContainText( 'LifterLMS BuddyPress' );
		await expect( table ).toContainText( 'LifterLMS bbPress' );

		await expect( table ).not.toContainText( 'Powerpack' );
		await expect( table ).not.toContainText( 'Office Hours' );
		await expect( table ).not.toContainText( 'LifterLMS Helper' );
		await expect( table.locator( 'tbody' ) ).not.toContainText( 'Stripe' );

		const kitCell = table.locator( 'tbody td' ).filter( { hasText: /Kit/ } );
		if ( await kitCell.count() ) {
			await expect( kitCell.first() ).toContainText( /Kit/ );
		}

		await expect( table ).toContainText( 'View Docs' );
		await expect( table ).toContainText( 'Learn More' );
		await expect( table ).toContainText( 'LifterLMS Advanced Videos' );

		expect( await table.locator( 'tbody tr' ).count() ).toBeGreaterThan( 2 );
	} );

	test( 'Checkout table mirrors columns and lists Stripe from the catalog', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/' );
		await visitSettingsPage( page, { tab: 'checkout' } );

		const table = page.locator( '.llms-gateway-table' );
		await expect( table ).toBeVisible();

		const header = table.locator( 'thead' );
		await expect( header ).toContainText( 'Gateway' );
		await expect( header ).toContainText( 'Description' );
		await expect( header ).toContainText( 'Installed' );
		await expect( header ).toContainText( 'Activated' );
		await expect( header ).toContainText( 'Enabled' );
		await expect( header ).toContainText( 'Documentation' );
		await expect( header ).not.toContainText( 'Gateway ID' );

		await expect( table ).toContainText( 'Manual' );

		const stripe = table.locator( 'tbody tr', { hasText: /Stripe/ } );
		if ( await stripe.count() ) {
			await expect( stripe.first() ).toBeVisible();
		}
	} );

} );
