import { WRAPPER_CLASSNAME, CLASSNAME, SIZE_SMALL } from './constants';

/**
 * CSS Styles for the components.
 *
 * @type {string}
 */
export const STYLES = `
	.${WRAPPER_CLASSNAME} {
		background: rgba( 250, 250, 250, 0.7 );
		bottom: 0;
		display: none;
		left: 0;
		position: absolute;
		right: 0;
		top: 0;
		z-index: 2;
	}

	.${CLASSNAME} {
		animation: llms-spinning 1.5s linear infinite;
		box-sizing: border-box;
		border: 4px solid #313131;
		border-radius: 50%;
		height: 40px;
		left: 50%;
		margin-left: -20px;
		margin-top: -20px;
		position: absolute;
		top: 50%;
		width: 40px;

	}

	.${CLASSNAME}.${SIZE_SMALL} {
		border-width: 2px;
		height: 20px;
		margin-left: -10px;
		margin-top: -10px;
		width: 20px;
	}

	@keyframes llms-spinning {
		0% {
			transform: rotate( 0deg )
		}
		50% {
			border-radius: 5%;
		}
		100% {
			transform: rotate( 220deg) 
		}
	}
`;
