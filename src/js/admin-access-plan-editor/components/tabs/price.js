// WP Deps.
import {
	BaseControl,
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// LLMS Deps.
import { DatePickerControl, ButtonGroupControl } from '@lifterlms/components';

// Internal Deps.
import PricingTypeOptionsControl from '../controls/pricing-type-options';
import {
	getFrequencyOptions,
	getPricingType,
	getPeriodOptions,
	getLengthOptions,
	getTitle,
} from '../../utils';

export default function( { accessPlan, updatePlan } ) {

	const {
			frequency: frequencyVal,
			length: lengthVal,
			period: periodVal,
			price: priceVal,
			sale_date_end: saleDateEnd,
			sale_date_start: saleDateStart,
			sale_enabled: saleEnabled,
			sale_price: salePriceVal,
			trial_enabled: trialEnabled,
			trial_price: trialPriceVal,
			trial_length: trialLengthVal,
			trial_period: trialPeriodVal,
			trial_text: trialTextVal
		} = accessPlan,
		pricingType = getPricingType( accessPlan );

	return (
		<>
			<PricingTypeOptionsControl
				selected={ pricingType }
				updatePlan={ updatePlan }
			/>

			{ 'free' !== pricingType && (
				<>
					<hr />
					<Flex>
						<FlexItem style={ { maxWidth: '140px' } }>
							<TextControl
								label={ __( 'Price', 'lifterlms' ) }
								type="number"
								min="0"
								step="0.01"
								value={ priceVal }
								onChange={ ( price ) => updatePlan( { price } ) }
							/>
						</FlexItem>

						{ 0 !== frequencyVal && (
							<>
								<FlexItem>
									<SelectControl
										label={ __( 'Frequency', 'lifterlms' ) }
										value={ frequencyVal }
										onChange={ ( frequency ) => updatePlan( { frequency } ) }
										options={ getFrequencyOptions() }
									/>
								</FlexItem>

								<FlexItem>
									<SelectControl
										label={ __( 'Period', 'lifterlms' ) }
										value={ periodVal }
										onChange={ ( period ) => updatePlan( { period } ) }
										options={ getPeriodOptions() }
									/>
								</FlexItem>

								<FlexItem>
									<SelectControl
										label={ __( 'Length', 'lifterlms' ) }
										value={ lengthVal }
										onChange={ ( length ) => updatePlan( { length } ) }
										options={ getLengthOptions( periodVal ) }
									/>
								</FlexItem>
							</>
						) }
					</Flex>
				</>
			) }


			{ 0 !== frequencyVal && (
				<>
					<hr />
					<Flex align="top">
						<FlexItem>
							<BaseControl
								id="llms-access-plan--trial-status"
								label={__ ( 'Trial / Deposit', 'lifterlms' ) }
							>
								<ToggleControl
									label={ __( 'Status', 'lifterlms' ) }
									className="llms-access-plan--trial-enabled"
									id="llms-access-plan--trial-enabled"
									checked={ trialEnabled }
									onChange={ ( trial_enabled ) => updatePlan( { trial_enabled } ) }
								/>
							</BaseControl>
						</FlexItem>

						{ trialEnabled && (
							<>
								<FlexItem style={ { maxWidth: '140px' } }>
									<TextControl
										label={ __( 'Price', 'lifterlms' ) }
										type="number"
										min="0"
										step="0.01"
										value={ trialPriceVal }
										onChange={ ( trial_price ) => updatePlan( { trial_price } ) }
									/>
								</FlexItem>

								<FlexItem  style={ { maxWidth: '80px' } }>
									<TextControl
										label={ __( 'Length', 'lifterlms' ) }
										type="number"
										min="0"
										step="1"
										value={ trialLengthVal }
										onChange={ ( trial_length ) => updatePlan( { trial_length } ) }
									/>
								</FlexItem>

								<FlexItem>
									<SelectControl
										label={ __( 'Period', 'lifterlms' ) }
										value={ trialPeriodVal }
										onChange={ ( trial_period ) => updatePlan( { trial_period } ) }
										options={ getPeriodOptions( 1 !== parseInt( trialLengthVal, 10 ) ) }
									/>
								</FlexItem>
							</>
						) }

					</Flex>
				</>
			) }

			{ 'free' !== pricingType && (
				<>
					<hr />
					<Flex align="top">
						<FlexItem>
							<BaseControl
								id="llms-access-plan--sale-status"
								label={__ ( 'Sale Pricing', 'lifterlms' ) }
							>
								<ToggleControl
									label={ __( 'Status', 'lifterlms' ) }
									className="llms-access-plan--trial-enabled"
									id="llms-access-plan--trial-enabled"
									checked={ saleEnabled }
									onChange={ ( sale_enabled ) => updatePlan( { sale_enabled } ) }
								/>
							</BaseControl>
						</FlexItem>

						{ saleEnabled && (
							<>
								<FlexItem style={ { maxWidth: '140px' } }>
									<TextControl
										label={ __( 'Price', 'lifterlms' ) }
										type="number"
										min="0"
										step="0.01"
										value={ salePriceVal }
										onChange={ ( sale_price ) => updatePlan( { sale_price } ) }
									/>
								</FlexItem>

								<FlexItem>
									<DatePickerControl
										allowEmpty
										label={ __( 'Sale Start Date', 'lifterlms' ) }
										className="llms-access-plan--sale-date--start"
										id="llms-access-plan--sale-date--start"
										onChange={ ( sale_date_start ) => updatePlan( { sale_date_start } ) }
										isInvalidDate={ ( date ) => {
											if ( saleDateEnd ) {
												return new Date( date ).getTime() > new Date( saleDateEnd ).getTime();
											}
											return false;
										} }
										currentDate={ saleDateStart }
									/>
								</FlexItem>

								<FlexItem>
									<DatePickerControl
										allowEmpty
										label={ __( 'Sale End Date', 'lifterlms' ) }
										className="llms-access-plan--sale-date--end"
										id="llms-access-plan--sale-date--end"
										onChange={ ( sale_date_end ) => updatePlan( { sale_date_end } ) }
										isInvalidDate={ ( date ) => {
											if ( saleDateStart ) {
												return new Date( date ).getTime() < new Date( saleDateStart ).getTime();
											}
											return false;
										} }
										currentDate={ saleDateEnd }
									/>
								</FlexItem>
							</>
						) }
					</Flex>
				</>
			) }

		</>
	);
}
