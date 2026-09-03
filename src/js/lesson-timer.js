/**
 * Lesson Time Tracker
 *
 * Heartbeat-based time tracking for lesson minimum time requirements.
 *
 * @since 10.1.0
 */
import '../scss/lesson-timer.scss';

( function() {

	var settings = window.llms_lesson_timer || {};
	if ( ! settings.session_token ) {
		return;
	}

	var token              = settings.session_token,
		requiredSeconds    = parseInt( settings.required_seconds, 10 ) || 0,
		hasMinimum         = !! settings.has_minimum,
		accumulated        = parseInt( settings.accumulated, 10 ) || 0,
		heartbeatInterval  = ( parseInt( settings.heartbeat_interval, 10 ) || 30 ) * 1000,
		displayFormat      = settings.display_format || '{CURRENT_TIME}',
		nonce              = settings.nonce || '',
		ajaxUrl            = settings.ajax_url || '',
		timerEl            = document.getElementById( 'llms-lesson-timer-display' ),
		heartbeatTimer     = null,
		displayTimer       = null,
		localAccumulated   = accumulated,
		lastTickTime       = Date.now(),
		stopped            = false,
		buttonsEnabled     = false,
		metHeartbeatSent   = false,
		heartbeatInFlight  = false;

	/**
	 * Apply a callback to all instances of mark-complete buttons, take-quiz buttons,
	 * and mark-complete form submit inputs. Handles Focus Mode which renders duplicate copies.
	 */
	function eachActionButton( callback ) {
		document.querySelectorAll( '.llms-complete-lesson-form [type="submit"]' ).forEach( callback );
		document.querySelectorAll( '#llms_start_quiz, [id="llms_start_quiz"]' ).forEach( callback );
		document.querySelectorAll( '#llms-start-assignment, [id="llms-start-assignment"]' ).forEach( callback );
	}

	/**
	 * Whether another add-on still has a progression lock on the element.
	 *
	 * Locks are `data-llms-lock-{id}` attributes (e.g. `data-llms-lock-video` from
	 * Advanced Videos). The button must stay disabled until every lock is gone.
	 */
	function hasProgressionLock( el ) {
		var i, name;
		if ( ! el || ! el.attributes ) {
			return false;
		}
		for ( i = 0; i < el.attributes.length; i++ ) {
			name = el.attributes[ i ].name;
			if ( 0 === name.indexOf( 'data-llms-lock-' ) ) {
				return true;
			}
		}
		return false;
	}

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
		if ( ! timerEl || ! hasMinimum ) {
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
	 * Enable the action buttons. Only called once the server has confirmed the
	 * minimum time is met, so the completion request can't be rejected server-side.
	 * Other add-on locks (data-llms-lock-*) are left in place so those requirements
	 * can still keep the button disabled.
	 */
	function enableButtons() {
		if ( buttonsEnabled ) {
			return;
		}
		buttonsEnabled = true;
		eachActionButton( function( btn ) {
			btn.removeAttribute( 'data-llms-lock-time' );
			btn.classList.remove( 'llms-lesson-time-disabled' );
			if ( ! hasProgressionLock( btn ) ) {
				btn.disabled = false;
			}
		} );
		document.dispatchEvent( new CustomEvent( 'llms-lesson-time-met' ) );
	}

	/**
	 * When the local counter reaches the minimum, force an immediate heartbeat so
	 * the server-persisted total catches up before the button is enabled. The
	 * button is enabled from the heartbeat response (see sendHeartbeat), not here,
	 * otherwise a click landing before the first scheduled heartbeat is rejected.
	 */
	function checkMarkComplete() {
		if ( ! hasMinimum || buttonsEnabled ) {
			return;
		}

		var met = requiredSeconds <= 0 || localAccumulated >= requiredSeconds;

		if ( met && ! metHeartbeatSent ) {
			metHeartbeatSent = true;
			// Reset the cadence so the scheduled heartbeat that may be due at this
			// same instant (e.g. when the minimum is a multiple of the interval)
			// doesn't also fire. The in-flight guard in sendHeartbeat covers the
			// race where the scheduled beat already started before this ran.
			clearInterval( heartbeatTimer );
			sendHeartbeat();
			heartbeatTimer = setInterval( sendHeartbeat, heartbeatInterval );
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
		btn.className = 'llms-button-primary wp-element-button';
		btn.textContent = buttonText;
		btn.addEventListener( 'click', buttonAction );
		modal.appendChild( btn );

		overlay.appendChild( modal );
		document.body.appendChild( overlay );
	}

	/**
	 * Send a heartbeat to the server.
	 */
	function sendHeartbeat() {
		if ( stopped || heartbeatInFlight ) {
			return;
		}

		heartbeatInFlight = true;

		var data = new FormData();
		data.append( 'action', 'lesson_time_heartbeat' );
		data.append( '_ajax_nonce', nonce );
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
				heartbeatInFlight = false;

				if ( result.success && result.data ) {
					accumulated = result.data.total;
					localAccumulated = accumulated;
					lastTickTime = Date.now();
					updateDisplay();

					if ( hasMinimum && result.data.met ) {
						enableButtons();
					} else if ( hasMinimum ) {
						// Server hasn't credited enough yet; allow the next tick to retry the forced heartbeat.
						metHeartbeatSent = false;
					}
				} else if ( hasMinimum ) {
					metHeartbeatSent = false;
					var code = ( result.data && result.data.code ) || result.code || '';
					if ( 'session_superseded' === code ) {
						showModal(
							LLMS.l10n.translate( 'Your session on this lesson has expired because you opened another timed lesson or this lesson in a different tab. Please reload this page to continue.' ),
							LLMS.l10n.translate( 'Reload Page' ),
							function() { window.location.reload(); }
						);
					} else if ( 'not_logged_in' === code ) {
						showModal(
							LLMS.l10n.translate( 'Your session has expired. Please log in again to continue tracking your time on this lesson.' ),
							LLMS.l10n.translate( 'Log In' ),
							function() { window.location.href = settings.login_url || '/wp-login.php'; }
						);
					}
				}
			} )
			.catch( function() {
				heartbeatInFlight = false;
				if ( hasMinimum && ! buttonsEnabled ) {
					metHeartbeatSent = false;
				}
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
		data.append( '_ajax_nonce', nonce );
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

		if ( hasMinimum && requiredSeconds > 0 && localAccumulated < requiredSeconds ) {
			eachActionButton( function( btn ) {
				btn.disabled = true;
				btn.classList.add( 'llms-lesson-time-disabled' );
				btn.setAttribute( 'data-llms-lock-time', '1' );
			} );
		} else if ( hasMinimum ) {
			// Already met at load: prior sessions are persisted server-side, so the button is safe to leave enabled.
			buttonsEnabled = true;
		}

		heartbeatTimer = setInterval( sendHeartbeat, heartbeatInterval );

		if ( hasMinimum ) {
			displayTimer = setInterval( tick, 1000 );
		}

		window.addEventListener( 'pagehide', sendEndBeacon );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
