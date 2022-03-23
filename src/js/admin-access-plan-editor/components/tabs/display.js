
import {
	Flex,
	FlexItem,
	TextControl,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import VisibilityOptionsControl from '../controls/visibility-options';
import { getTitle } from '../../utils';

export default function( { accessPlan, updatePlan } ) {

	const {
		visibility,
		enroll_text: enrollTextVal,
		sku: skuVal,
	} = accessPlan;

	return (
		<>
			<Flex>
				<FlexItem style={ { flex: 2 } }>
					<TextControl
						label={ __( 'Title', 'lifterlms' ) }
						value={ getTitle( accessPlan ) }
						onChange={ ( title ) => updatePlan( { title } ) }
					/>
				</FlexItem>

				<FlexItem style={ { flex: 1 } }>
					<TextControl
						label={ __( 'Button Text', 'lifterlms' ) }
						value={ enrollTextVal }
						onChange={ ( enroll_text ) => updatePlan( { enroll_text } ) }
					/>
				</FlexItem>
			</Flex>

			<hr />

			<Flex>
				<FlexItem>
					<VisibilityOptionsControl
						selected={ visibility }
						updatePlan={ updatePlan }
					/>
				</FlexItem>
			</Flex>
		</>
	);
}
