/**
 * LifterLMS Forms Document Settings Slot / Fill
 *
 * Allows 3rd party access to the form document settings sidebar plugin
 *
 * @since 1.12.0
 * @version 1.12.0
 */

import { createSlotFill } from '@wordpress/components';

const { Fill, Slot } = createSlotFill( 'LLMSFormDocSettings' ),
	/**
	 * LLMSFormDocSettings Slot Fill object
	 *
	 * @since 1.6.0
	 *
	 * @param {Object} options
	 * @param {Array<Object>} options.children Children components.
	 * @return {Fill} Fill component
	 */
	LLMSFormDocSettings = ( { children } ) => <Fill>{ children }</Fill>;

LLMSFormDocSettings.Slot = Slot;

// Expose globally for 3rd party access.
window.llms.plugins = window.llms.plugins || {};
window.llms.plugins.LLMSFormDocSettings = LLMSFormDocSettings;

// Export it.
export default LLMSFormDocSettings;
