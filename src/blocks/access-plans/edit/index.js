import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element'
import { createBlocksFromInnerBlocksTemplate, } from '@wordpress/blocks';
import { RichText, useBlockProps, useInnerBlocksProps, store as blockEditorStore, withColors } from '@wordpress/block-editor';
import { useSelect, withSelect, useDispatch } from '@wordpress/data'
import { store as editorStore } from '@wordpress/editor';
import { Flex, FlexItem, Dashicon, Placeholder, ToggleControl, Spinner, } from '@wordpress/components';

import { orderBy, isEqual } from 'lodash';

// import { PostSearchControl } from '@lifterlms/components';

import { accessPlanStore } from '@lifterlms/data';

import metadata from '../block.json';

import { icon, ALLOWED_BLOCKS } from '../constants';

const { title } = metadata;

import EditPlan from './edit-plan';
import Inspect from './inspect';

function Setup( {
	attributes,
	setAttributes,
} ) {

	const blockProps = useBlockProps(),
		{ useCurrentPost, postId, plans } = attributes;
			// console.log( useCurrentPost );
	return (
		<div { ...blockProps }>
			<Placeholder
				label={ title }
				icon={ <Dashicon icon={ icon.src } style={ { color: icon.foreground } } /> }
				instructions={ __( 'Select a course or membership to get started.', 'lifterlms' ) }
			>
			<ToggleControl
				label={
					useCurrentPost ?
						__( 'Use plans from this course', 'lifterlms' ):
						__( 'Select plans', 'lifterlms' )
				}
				// className="llms-access-plan--trial-enabled"
				// id="llms-access-plan--trial-enabled"
				checked={ useCurrentPost }
				onChange={ ( newVal ) => setAttributes( { useCurrentPost: newVal } ) }
			/>

			</Placeholder>
		</div>
	);

}

function Edit( {
	attributes,
	setAttributes,
	clientId,

	// From withColors.
	accentColor,
	setAccentColor,
	accentTextColor,
	setAccentTextColor,
} ) {

	const blockProps = useBlockProps(),
		{ plansPerRow } = attributes;

	const { arePlansLoading, plans } = useSelect( ( select ) => {
		const { getCurrentPostId } = select( editorStore ),
			{ getEditedPlans, isLoading } = select( accessPlanStore ),
			query = { post_id: getCurrentPostId() },
			plans = getEditedPlans( query );
			return {
				plans: plans ? orderBy( plans, 'menu_order', 'asc' ) : [],
				arePlansLoading: isLoading( query ),
			};
	} );


	// console.log( blockProps );

	return (
		<>
			{ arePlansLoading && (
				<div { ...blockProps } style={ { textAlign: 'center', padding: '20px 0' } }>
					<Spinner />
				</div>
			) }
			{ ! arePlansLoading && (
				<>
					<Inspect { ...{
						attributes,
						setAttributes,
						accentColor,
						setAccentColor,
						accentTextColor,
						setAccentTextColor,
					} } />
					<div { ...blockProps }>
						<Flex className="llms-ap-list--wrap">
							{ plans.map( ( plan ) => <EditPlan { ...{ plan, plansPerRow, accentColor, accentTextColor } } /> ) }
						</Flex>
					</div>
				</>
			) }
		</>
	);
}


export default withColors(
	{ accentColor: 'color' },
	{ accentTextColor: 'color' },
)( Edit );
