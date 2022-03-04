// External deps.
import styled from '@emotion/styled';

// WP deps.
import { BaseControl } from '@wordpress/components';

/**
 * A <BaseControl> component with styles targeting the <BaseSearchControl> components within it.
 *
 * Addresses issues arising from WP core styles loaded in the block editor that create visual
 * issues with our components.
 *
 * @since [version]
 */
export const StyledBaseControl = styled( BaseControl )`
	width: 100%;
	& .llms-search-control__input:focus {
		box-shadow: none;
	}
	& .llms-search-control__menu {
		background: #fff !important;
		z-index: 9999999 !important;
	}
	& .llms-search-control__value-container {
		width: 100%;
	}
`;
