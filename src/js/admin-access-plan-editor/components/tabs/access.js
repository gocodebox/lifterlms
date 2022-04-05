
import {
	BaseControl,
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { isInTheFuture } from '@wordpress/date';
import { __ } from '@wordpress/i18n';

import { DatePickerControl } from '@lifterlms/components';

import AccessExpirationOptionsControl from '../controls/access-expiration-options';
import { getPeriodOptions } from '../../utils';

export default function( { accessPlan, updatePlan } ) {

	const {
			access_expiration: accessExpirationVal,
			access_length: accessLengthVal,
			access_period: accessPeriodVal,
			access_expires: accessExpiresVal,
		} = accessPlan,
		dateAccessExpires = accessExpiresVal ? new Date( accessExpiresVal ) : new Date( new Date().setHours( 23, 59, 59, 999 ) )

	return (
		<>
		<Flex>
			<FlexItem>
				<AccessExpirationOptionsControl
					selected={ accessExpirationVal }
					updatePlan={ updatePlan }
				/>
			</FlexItem>

			{ 'limited-period' === accessExpirationVal && (
				<FlexItem>
					<BaseControl label={ __( 'Access Expires After', 'lifterlms' )} className="llms-access-plan--access-expiration-period" id="llms-access-plan--access-expiration-period">
						<Flex>
							<FlexItem>
								<TextControl
									id="llms-access-plan--access-expiration-period"
									hideLabelFromVision
									label={ __( 'Access expiration length', 'lifterlms' ) }
									type="number"
									step="1"
									min="1"
									value={ accessLengthVal }
									onChange={ ( access_length ) => updatePlan( { access_length } ) }
								/>
							</FlexItem>

							<FlexItem>
								<SelectControl
									hideLabelFromVision
									label={ __( 'Access expiration period', 'lifterlms' ) }
									value={ accessPeriodVal }
									onChange={ ( access_period ) => updatePlan( { access_period } ) }
									options={ getPeriodOptions( accessLengthVal >= 2 ) }
								/>
							</FlexItem>
						</Flex>
					</BaseControl>
				</FlexItem>
			) }

			{ 'limited-date' === accessExpirationVal && (
				<FlexItem>
					<DatePickerControl
						label={ __( 'Access Expiration Date', 'lifterlms' ) }
						className="llms-access-plan--access-expiration-date"
						id="llms-access-plan--access-expiration-date"
						onChange={ ( access_expires ) => updatePlan( { access_expires } ) }
						isInvalidDate={ ( date ) => ! isInTheFuture( date ) }
						currentDate={ dateAccessExpires }
					/>
				</FlexItem>
			) }
		</Flex>
		</>
	);
}
