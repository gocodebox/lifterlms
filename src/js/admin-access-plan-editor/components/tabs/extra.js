// WP deps.
import {
	BaseControl,
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

// LLMS deps.
import { PostSearchControl } from '@lifterlms/components';

// Internal deps.
import { getRedirectOptions } from '../../utils';


export default function( { accessPlan, updatePlan } ) {

	const {
			availability_restrictions: availabilityRestrictionsVal,
			redirect_page: redirectPageVal,
			redirect_type: redirectTypeVal,
			redirect_url: redirectUrlVal,
			redirect_forced: redirectForcedVal,
			sku: skuVal,
		} = accessPlan,
		{ getCurrentPostType } = useSelect( editorStore ),
		hasAvailabilityRestrictions = availabilityRestrictionsVal.length > 0;

	return (
		<>

			<TextControl
				label={ __( 'SKU', 'lifterlms' ) }
				value={ skuVal }
				onChange={ ( sku ) => updatePlan( { sku } ) }
			/>

			<hr />

			{ 'course' === getCurrentPostType() && (
				<>
					<PostSearchControl
						isClearable
						isMulti
						postType="llms_membership"
						label={ __( 'Membership Restrictions', 'lifterlms' ) }
						placeholder={ __( 'Search for a membership…', 'lifterlms' ) }
						selectedValue={ availabilityRestrictionsVal }
						onUpdate={ ( selected ) => {
							updatePlan( { availability_restrictions: selected.map( ( { id } ) => id ) } )
						} }
						help={
							hasAvailabilityRestrictions ? __( 'The access plan is only available to members active in at least one of the selected memberhips.', 'lifterlms' ) : __( 'The access plan is available to everyone.', 'lifterlms' )
						}
					/>
					<hr />
				</>
			) }

			{ hasAvailabilityRestrictions && (
				<BaseControl
					id="llms-access-plan--trial-status"
					label={__ ( 'Override Membership Redirects', 'lifterlms' ) }
				>
					<ToggleControl
						label={
							redirectForcedVal ?
								__( 'Overriding membership redirects in favor of the plan settings below', 'lifterlms' ):
								__( 'Using the default membership redirect settings', 'lifterlms' )
						}
						className="llms-access-plan--trial-enabled"
						id="llms-access-plan--trial-enabled"
						checked={ redirectForcedVal }
						onChange={ ( redirect_forced ) => updatePlan( { redirect_forced } ) }
					/>
				</BaseControl>

			) }

			{ ( ! hasAvailabilityRestrictions || redirectForcedVal ) && (
				<Flex>
					<FlexItem>
						<SelectControl
							label={ __( 'Checkout Redirect', 'lifterlms' ) }
							value={ redirectTypeVal }
							onChange={ ( redirect_type ) => updatePlan( { redirect_type } ) }
							options={ getRedirectOptions() }
						/>
					</FlexItem>



					{ 'self' !== redirectTypeVal && (
						<FlexItem style={ { flex: 1 } }>
							{ 'page' === redirectTypeVal && (
								<PostSearchControl
									isClearable
									postType="pages"
									label={ __( 'Redirect Page', 'lifterlms' ) }
									placeholder={ __( 'Search for a page…', 'lifterlms' ) }
									selectedValue={ redirectPageVal ? [ redirectPageVal ] : [] }
									onUpdate={ ( obj ) => {
										const id = obj?.id || null;
										updatePlan( { redirect_page: id } )
									} }
								/>
							) }
							{ 'url' === redirectTypeVal && (
								<TextControl
									label={ __( 'Redirect URL', 'lifterlms' ) }
									type="url"
									value={ redirectUrlVal }
									onChange={ ( redirect_url ) => updatePlan( { redirect_url } ) }
								/>
							) }
						</FlexItem>
					) }
				</Flex>
			) }

		</>
	);
}
