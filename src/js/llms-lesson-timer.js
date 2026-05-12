/**
 * Lesson Time Tracker
 *
 * Heartbeat-based time tracking for lesson minimum time requirements.
 *
 * @since [version]
 */
( function() {

	var settings = window.llms_lesson_timer || {};
	if ( ! settings.session_token ) {
		return;
	}

	var token              = settings.session_token,
		requiredSeconds    = parseInt( settings.required_seconds, 10 ) || 0,
		accumulated        = parseInt( settings.accumulated, 10 ) || 0,
		heartbeatInterval  = ( parseInt( settings.heartbeat_interval, 10 ) || 30 ) * 1000,
		displayFormat      = settings.display_format || '{CURRENT_TIME}',
		nonce              = settings.nonce || '',
		ajaxUrl            = settings.ajax_url || '',
		timerEl            = document.getElementById( 'llms-lesson-timer-display' ),
		markCompleteBtn    = document.querySelector( '.llms-complete-lesson-mark-button' ),
		heartbeatTimer     = null,
		displayTimer       = null,
		lastAckTime        = Date.now(),
		localAccumulated   = accumulated,
		lastTickTime       = Date.now(),
		stopped            = false,
		disconnectBannerEl = null;

	/**
	 * Format seconds as H:MM:SS.
	 */
	function formatTime( totalSeconds ) {
		totalSeconds = Math.max( 0, Math.floor( totalSeconds ) );
		var h = Math.floor( totalSeconds / 3600 ),
			m = Math.floor( ( totalSeconds % 3600 ) / 60 ),
			s = totalSeconds % 60;
		return h + ':' + ( m < 10 ? '0' : '' ) + m + ':' + ( s < 10 ? '0' : '' ) + s;
	}

	/**
	 * Update the display element.
	 */
	function updateDisplay() {
		if ( ! timerEl ) {
			return;
		}

		var text = displayFormat
			.replace( '{CURRENT_TIME}', formatTime( localAccumulated ) )
			.replace( '{MINIMUM_TIME}', formatTime( requiredSeconds ) );

		if ( requiredSeconds <= 0 ) {
			text = formatTime( localAccumulated );
		}

		timerEl.textContent = text;

		if ( requiredSeconds > 0 && localAccumulated >= requiredSeconds ) {
			timerEl.classList.add( 'llms-time-met' );
		}
	}

	/**
	 * Tick the local counter between heartbeats.
	 */
	function tick() {
		if ( stopped ) {
			return;
		}
		var now = Date.now();
		localAccumulated += Math.round( ( now - lastTickTime ) / 1000 );
		lastTickTime = now;
		updateDisplay();
		checkMarkComplete();
	}

	/**
	 * Show/enable the mark complete button if time is met.
	 */
	function checkMarkComplete() {
		if ( ! markCompleteBtn ) {
			return;
		}
		if ( requiredSeconds <= 0 || localAccumulated >= requiredSeconds ) {
			markCompleteBtn.disabled = false;
			markCompleteBtn.classList.remove( 'llms-lesson-time-disabled' );
		}
	}

	/**
	 * Show the session-expired modal.
	 */
	function showModal( message, buttonText, buttonAction ) {
		stopped = true;
		clearInterval( heartbeatTimer );
		clearInterval( displayTimer );

		var overlay = document.createElement( 'div' );
		overlay.className = 'llms-lesson-timer-modal-overlay';

		var modal = document.createElement( 'div' );
		modal.className = 'llms-lesson-timer-modal';

		var msg = document.createElement( 'p' );
		msg.textContent = message;
		modal.appendChild( msg );

		var btn = document.createElement( 'button' );
		btn.className = 'llms-button-primary';
		btn.textContent = buttonText;
		btn.addEventListener( 'click', buttonAction );
		modal.appendChild( btn );

		overlay.appendChild( modal );
		document.body.appendChild( overlay );
	}

	/**
	 * Show the disconnect warning banner.
	 */
	function showDisconnectBanner() {
		if ( disconnectBannerEl ) {
			return;
		}
		disconnectBannerEl = document.createElement( 'div' );
		disconnectBannerEl.className = 'llms-lesson-timer-disconnect-banner';
		disconnectBannerEl.textContent = settings.i18n_disconnect || 'Your time on this lesson has not been recorded for the past several minutes. Please check your internet connection. Time spent while disconnected may not be credited.';

		var timerContainer = document.getElementById( 'llms-lesson-timer' );
		if ( timerContainer ) {
			timerContainer.parentNode.insertBefore( disconnectBannerEl, timerContainer.nextSibling );
		} else {
			document.body.insertBefore( disconnectBannerEl, document.body.firstChild );
		}
	}

	/**
	 * Hide the disconnect banner.
	 */
	function hideDisconnectBanner() {
		if ( disconnectBannerEl ) {
			disconnectBannerEl.remove();
			disconnectBannerEl = null;
		}
	}

	/**
	 * beforeunload handler.
	 */
	function onBeforeUnload( e ) {
		e.preventDefault();
		e.returnValue = '';
		return '';
	}

	/**
	 * Manage the beforeunload warning based on last ack time.
	 */
	function updateBeforeUnloadState() {
		var elapsed = Date.now() - lastAckTime;
		if ( elapsed > heartbeatInterval ) {
			window.addEventListener( 'beforeunload', onBeforeUnload );
		} else {
			window.removeEventListener( 'beforeunload', onBeforeUnload );
		}

		if ( elapsed > 300000 ) {
			showDisconnectBanner();
		} else {
			hideDisconnectBanner();
		}
	}

	/**
	 * Send a heartbeat to the server.
	 */
	function sendHeartbeat() {
		if ( stopped ) {
			return;
		}

		var data = new FormData();
		data.append( 'action', 'lesson_time_heartbeat' );
		data.append( 'nonce', nonce );
		data.append( 'session_token', token );

		fetch( ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( result ) {
				if ( result.success && result.data ) {
					lastAckTime = Date.now();
					accumulated = result.data.total;
					localAccumulated = accumulated;
					lastTickTime = Date.now();
					updateDisplay();
					updateBeforeUnloadState();

					if ( result.data.met ) {
						checkMarkComplete();
					}
				} else if ( result.data && result.data.code ) {
					if ( 'session_superseded' === result.data.code ) {
						showModal(
							settings.i18n_superseded || 'Your session on this lesson has expired because you opened another timed lesson or this lesson in a different tab. Please reload this page to continue.',
							settings.i18n_reload || 'Reload Page',
							function() { window.location.reload(); }
						);
					} else if ( 'not_logged_in' === result.data.code ) {
						showModal(
							settings.i18n_logged_out || 'Your session has expired. Please log in again to continue tracking your time on this lesson.',
							settings.i18n_login || 'Log In',
							function() { window.location.href = settings.login_url || '/wp-login.php'; }
						);
					}
				}
			} )
			.catch( function() {
				updateBeforeUnloadState();
			} );
	}

	/**
	 * Send end-session beacon on page unload.
	 */
	function sendEndBeacon() {
		if ( stopped ) {
			return;
		}
		var data = new FormData();
		data.append( 'action', 'lesson_time_end' );
		data.append( 'nonce', nonce );
		data.append( 'session_token', token );

		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( ajaxUrl, data );
		}
	}

	/**
	 * Initialize.
	 */
	function init() {
		updateDisplay();

		if ( markCompleteBtn && requiredSeconds > 0 && localAccumulated < requiredSeconds ) {
			markCompleteBtn.disabled = true;
			markCompleteBtn.classList.add( 'llms-lesson-time-disabled' );
		}

		heartbeatTimer = setInterval( sendHeartbeat, heartbeatInterval );

		displayTimer = setInterval( tick, 1000 );

		window.addEventListener( 'pagehide', sendEndBeacon );

		setInterval( updateBeforeUnloadState, 5000 );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
