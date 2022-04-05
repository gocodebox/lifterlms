
import {
	BaseControl,
	Button,
	ButtonGroup,
	Flex,
	FlexItem,
	Modal,
	SelectControl,
	TabPanel,
	TextControl,
	ToggleControl,
	Tooltip,
} from '@wordpress/components';
import { lazy, Suspense, useState, useMemo } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import { ConfirmButton } from '@lifterlms/components';

import AccessPlanPermalink from './permalink';
import { getTitle } from '../utils';

const TABS = [
	{
		name: 'display',
		title: __( 'Display', 'lifterlms' ),
		componentFile: 'display',
	},
	{
		name: 'price',
		title: __( 'Price', 'lifterlms' ),
		componentFile: 'price',
	},
	{
		name: 'access',
		title: __( 'Content Access', 'lifterlms' ),
		componentFile: 'access',
	},
	{
		name: 'extra',
		title: __( 'Additional Settings', 'lifterlms' ),
		componentFile: 'extra',
	},
];

function getTabComponent( componentFile ) {
	return lazy( () => import( `./tabs/${ componentFile }` ) )
}

function TabContent( { tab, accessPlan, updatePlan } ) {

	const { name, componentFile } = tab,
		TabComponent = useMemo( () => getTabComponent( componentFile ), [ componentFile ] );

	return (
		<Suspense fallback={ <div>{ __( 'Loading…', 'lifterlms' ) }</div> }>
			<div className={ `llms-access-plan--edit--${ name }` } style={ { margin: '24px 32px' } }>
				<TabComponent { ...{ accessPlan, updatePlan } } />
			</div>
		</Suspense>
	);

}

export default function( { accessPlan, updatePlan, isOpen, setIsOpen, deletePlan, closeOnDelete = true } ) {

	if ( ! isOpen ) {
		return null;
	}

	const { id } = accessPlan;

	return (
		<Modal
			className="llms-access-plan--edit"
			title={ sprintf( __( 'Editing access plan "%1$s" (#%2$d)', 'lifterlms' ), getTitle( accessPlan ), id ) }
			onRequestClose={ () => setIsOpen( false ) }
			style={ { minWidth: '560px' } }
		>

			<div className="llms-access-plan--edit--main" style={ { margin: '-24px -32px 0' } }>

				<TabPanel
					className="llms-access-plan--edit--tabs"
					activeClass="is-active"
					tabs={ TABS }
				>
					{ ( tab ) => <TabContent { ...{ tab, accessPlan, updatePlan } } /> }
				</TabPanel>
			</div>

			<footer
				className="llms-access-plan--edit--footer"
				style={ { margin: '24px -32px -24px', padding: '24px 32px', borderTop: '1px solid #ddd' } }>

				<Flex>
					<FlexItem>
						<AccessPlanPermalink { ...{ accessPlan } } />
					</FlexItem>

					<FlexItem>
						<ConfirmButton
							isDestructive
							onClick={ async () => {
								await deletePlan();
								if ( closeOnDelete ) {
									setIsOpen( false );
								}
							} }
						>
							{__( 'Delete', 'lifterlms' ) }
						</ConfirmButton>
						<Button
							style={ { marginLeft: '5px' } }
							onClick={ () => setIsOpen( false ) }
						>
							{ __( 'Close', 'lifterlms' ) }
						</Button>
					</FlexItem>
				</Flex>
			</footer>
		</Modal>
	);
}
