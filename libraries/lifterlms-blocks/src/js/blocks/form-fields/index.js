/**
 * Export all fields in the fields library.
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// Hooks.
import './reusable';

// Confirm Field Group.
import * as confirmGroup from './fields/confirm-group';

// Default Fields.
import * as checkboxes from './fields/checkboxes';
import * as radio from './fields/radio';
import * as select from './fields/select';
import * as text from './fields/text';
import * as textarea from './fields/textarea';

// Composed Fields.
import * as redeemVoucher from './fields/redeem-voucher';

import * as userDisplayName from './fields/user-display-name';
import * as userLogin from './fields/user-login';
import * as userEmail from './fields/user-email';
import * as userFirstName from './fields/user-first-name';
import * as userLastName from './fields/user-last-name';
import * as userNames from './fields/user-names';
import * as userPassword from './fields/user-password';

// User Address Fields.
import * as userAddress from './fields/user-address';
import * as userAddressStreet from './fields/user-address-street';
import * as userAddressStreetPrimary from './fields/user-address-street-primary';
import * as userAddressStreetSecondary from './fields/user-address-street-secondary';
import * as userAddressCity from './fields/user-address-city';
import * as userAddressCountry from './fields/user-address-country';
import * as userAddressRegion from './fields/user-address-region';
import * as userAddressState from './fields/user-address-state';
import * as userAddressPostalCode from './fields/user-address-postal-code';

import * as userPhone from './fields/user-phone';

export {
	confirmGroup,
	checkboxes,
	radio,
	select,
	text,
	textarea,
	redeemVoucher,
	userAddress,
	userAddressStreet,
	userAddressStreetPrimary,
	userAddressStreetSecondary,
	userAddressCity,
	userAddressCountry,
	userAddressRegion,
	userAddressState,
	userAddressPostalCode,
	userDisplayName,
	userEmail,
	userFirstName,
	userLastName,
	userLogin,
	userNames,
	userPassword,
	userPhone,
};
