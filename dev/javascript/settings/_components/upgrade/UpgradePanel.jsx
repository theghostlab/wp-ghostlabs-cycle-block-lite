import { useEffect, useRef, useState } from 'react';
import { useMachine } from '@xstate/react';
import {
	FieldContent,
	FieldNotice,
	FieldTitle,
	FormField,
	Input,
} from '../ui';
import { createUpgradeMachine } from '../../_machines/upgradeMachine';

// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach.
const { __ } = window.wp.i18n;

const FORM_ID = 'ghostlabs-upgrade-form';

/**
 * The one thing said about an empty key, rendered twice — once visibly beside
 * the field, once into the status region so it is actually announced.
 *
 * ⚠️ A thunk, not a constant string. This module is evaluated at import time and
 * `__()` has to run at render time, or the message bakes in whatever locale
 * happened to be registered first — the same trap `intervals.js` and
 * `TierMatrix` both document.
 */
const MISSING_KEY_MESSAGE = () =>
	__(
		'Enter the license key from your receipt email.',
		'ghostlabs-cycle-block-lite'
	);

/**
 * The submit and claim buttons, in their two states.
 *
 * ⚠️ **The busy variant is not `opacity`, and it cannot be.** These buttons used
 * `disabled:opacity-60`, which composited white on ≈#a089be — 2.75:1 as rendered.
 * That was legal only because 1.4.3 exempts "an inactive user interface
 * component", and the moment `disabled` became `aria-disabled` (see `inFlight`
 * below) the exemption went with it: the control is still exposed as active. A
 * lighter solid keeps the label at 4.7:1 and reads as inactive for the same
 * reason the opacity did.
 *
 * ⚠️ Not `hover:bg-primary-400` — that tone already means "you are about to
 * click this", and a busy button is the opposite claim.
 *
 * `cursor-wait` rather than `cursor-not-allowed`: nothing is disallowed here,
 * something is running.
 */
const BUTTON_BASE =
	'rounded-md px-5 py-2.5 text-sm font-semibold text-white transition';

const buttonClass = ( inFlight ) =>
	`${ BUTTON_BASE } ${
		inFlight
			? 'bg-primary-300 cursor-wait'
			: 'bg-primary-500 hover:bg-primary-400'
	}`;

/**
 * The query arg a checkout redirect brings back. Only ever a one-shot claim
 * token — never a key — and nothing runs on its presence alone: the admin
 * clicks "Complete upgrade" before any request fires.
 */
const CLAIM_ARG = 'cycle_upgrade_claim';

/**
 * `ClaimExchanger`'s verdict that the link is spent — expired, already used,
 * or minted for another domain, all deliberately indistinguishable server-side.
 *
 * ⚠️ **The one failure that hands over to key entry.** Every other one leaves
 * the field collapsed: past the exchange the key is already redeemed and
 * saved, so pasting it runs the identical orchestrator and fails identically,
 * with the token now spent. Revealing the field there offers a path that
 * cannot work.
 *
 * ⚠️ It is also why the field cannot simply be PREFILLED on the return: the
 * plugin holds a claim token, not a key, and turning one into the other means
 * redeeming on page load — spending a single-use, 600-second, domain-bound
 * token on a render that a refresh would repeat.
 */
const CLAIM_GONE = 'claim_gone';

// No leading slash, matching how the shared shell builds its endpoint paths
// from the same namespace.
const submitUpgrade = ( restNamespace ) => ( { licenseKey, claimToken } ) =>
	claimToken
		? window.wp.apiFetch( {
				path: `${ restNamespace }/claim-upgrade`,
				method: 'POST',
				data: { claimToken },
		  } )
		: window.wp.apiFetch( {
				path: `${ restNamespace }/upgrade`,
				method: 'POST',
				data: { licenseKey },
		  } );

/**
 * "Already own Pro?" — the licence entry.
 *
 * The key field hides behind a "Have a license?" button: a form most visitors
 * have no key for would only add noise to a screen they came to for something
 * else. Collapsed, it costs one row.
 * Disclosure mechanics per the audit's rules — the button keeps a stable
 * accessible name with state in aria-expanded alone, the region is tied to it
 * with aria-controls, and focus lands in the input when it opens and returns
 * there after a failure.
 *
 * One always-present role="status" paragraph is the flow's single
 * busy/success/failure signal, rendered from machine context — never from a
 * console. On success the machine parks in `redirecting` and the effect below
 * hands the browser to core's activation URL; the swap continues there, which
 * is why this component never needs to handle "done".
 *
 * ⚠️ **Nothing here is ever `disabled`, and the client-side error goes through
 * that same status region.** Both are 2026-08-26 fixes and both are load-bearing
 * — see `inFlight` and the `status` chain below. The short version: disabling
 * the focused control loses a keyboard user's place in the document, and an
 * error announced only by moving focus is an error nobody hears when focus was
 * already there.
 */
const UpgradePanel = ( { restNamespace } ) => {
	const [ machine ] = useState( () =>
		createUpgradeMachine( { submit: submitUpgrade( restNamespace ) } )
	);
	const [ state, send ] = useMachine( machine );

	// Read once at mount: the token a checkout redirect carried back. It decides
	// what this panel IS — a completion screen rather than the disclosure — but
	// fires nothing; the explicit click below is the CSRF stance.
	const [ claimToken ] = useState( () =>
		new URLSearchParams( window.location.search ).get( CLAIM_ARG )
	);

	/**
	 * ⚠️ **Starts closed even with a token, and that is the fix.** It used to
	 * initialise to `Boolean( claimToken )`, which auto-revealed the manual
	 * form on the checkout return AND made the disclosure's focus effect fire
	 * at mount. The buyer landed on copy reading "no key entry needed" with
	 * the caret in a key entry field, and the button they came back for above
	 * it in the tab order.
	 */
	const [ revealed, setRevealed ] = useState( false );

	/**
	 * ⚠️ **Sticky, and it has to be.** Derived live from `noticeCode` this
	 * would flip back the moment a pasted key failed for its own reason —
	 * putting the claim button back on screen, offering a retry on a token
	 * the server has already told us is spent.
	 */
	const [ claimDead, setClaimDead ] = useState( false );
	const [ licenseKey, setLicenseKey ] = useState( '' );
	const [ missingKey, setMissingKey ] = useState( false );
	const inputRef = useRef( null );
	const claimRef = useRef( null );

	const busy = state.matches( 'submitting' );
	const failed = state.matches( 'failed' );
	const redirecting = state.matches( 'redirecting' );

	const claimGone = failed && state.context.noticeCode === CLAIM_GONE;

	/**
	 * Whether the completion CTA is on screen — and the single source for both
	 * the render below and the focus routing, so focus can never be sent to a
	 * control that is not there.
	 */
	const claimOffered = Boolean( claimToken ) && ! claimDead && ! claimGone;

	/**
	 * A request is out, or the browser is on its way to the activation URL.
	 *
	 * 🔴 **Refused through `aria-disabled`, never `disabled`.** Disabling the
	 * focused submit button hands focus to `<body>`, so the next Tab restarts at
	 * the top of the document (WCAG 2.4.3) — and this is not a moment: the
	 * request verifies the key against the licence server, downloads the Pro
	 * package and runs the WordPress installer, with only the disclosure button
	 * and two adverts left focusable behind it.
	 *
	 * ⚠️ **It is the same failure `CycleSettings.releaseFocus` exists to refuse**,
	 * caused by `disabled` rather than by `blur()`, over a much longer wait. The
	 * comment there is the reason this one exists.
	 *
	 * `aria-disabled` announces the refusal without removing the control, so the
	 * guards in `onSubmit` and the claim handler are what actually enforce it —
	 * the attribute is advisory and the browser will still fire the click.
	 */
	const inFlight = busy || redirecting;

	/**
	 * Arrival from checkout: focus the one action the buyer came back to take.
	 * `claimToken` is read once at mount, so this runs once.
	 */
	useEffect( () => {
		if ( claimToken ) {
			claimRef.current?.focus();
		}
	}, [ claimToken ] );

	/**
	 * A reveal puts the caret where the key goes. Mount can no longer reach
	 * this — `revealed` starts false in every case now — so it fires only for
	 * a real open: the disclosure button, or the dead-claim handover below.
	 */
	useEffect( () => {
		if ( revealed ) {
			inputRef.current?.focus();
		}
	}, [ revealed ] );

	/**
	 * The dead-claim handover: record it (stickily) and open the form — the
	 * only failure that makes key entry the way forward.
	 */
	useEffect( () => {
		if ( claimGone ) {
			setClaimDead( true );
			setRevealed( true );
		}
	}, [ claimGone ] );

	/**
	 * A failure returns focus to whichever control can still act on it: the
	 * retry while the claim is still offered, the field once it is not.
	 */
	useEffect( () => {
		if ( ! failed ) {
			return;
		}

		if ( claimOffered ) {
			claimRef.current?.focus();
		} else {
			inputRef.current?.focus();
		}
	}, [ failed, claimOffered ] );

	useEffect( () => {
		if ( redirecting && state.context.activationUrl ) {
			window.location.assign( state.context.activationUrl );
		}
	}, [ redirecting, state.context.activationUrl ] );

	/**
	 * ⚠️ **`missingKey` belongs in here, and leaving it out made the commonest
	 * path silent.** The empty-key answer used to be the visible notice plus
	 * `inputRef.current?.focus()` — and that focus call does nothing when focus
	 * is already in the input, which is exactly where it is when the form was
	 * submitted by pressing Enter in the field. Nothing moved, so nothing was
	 * re-read, and no live region carried it: the error appeared on screen and
	 * was announced to nobody (WCAG 3.3.1 / 4.1.3).
	 *
	 * Submitting from the BUTTON hid it, because focus genuinely moves and the
	 * message rides along inside the field's wrapping label.
	 *
	 * ⚠️ Last in the chain on purpose: a request in flight or a failure from the
	 * server both outrank a stale client-side complaint.
	 */
	let status = '';

	if ( busy ) {
		status = __(
			'Verifying your key and installing Cycle Block Pro…',
			'ghostlabs-cycle-block-lite'
		);
	} else if ( redirecting ) {
		status = __(
			'Done — activating Cycle Block Pro…',
			'ghostlabs-cycle-block-lite'
		);
	} else if ( failed ) {
		status = state.context.message;
	} else if ( missingKey ) {
		status = MISSING_KEY_MESSAGE();
	}

	/**
	 * ⚠️ **Three pairs, not one pair plus a button swap.** The completion
	 * promise stops being true the moment the claim dies, and leaving it up
	 * there only relocates the contradiction this whole change removes: copy
	 * saying "no key entry needed" above a key entry field.
	 *
	 * ⚠️ Resolved at render time, never hoisted to a module constant — `__()`
	 * would otherwise bake in whichever locale registered first.
	 */
	let heading = __( 'Already own Cycle Block Pro?', 'ghostlabs-cycle-block-lite' );
	let subheading = __(
		'Paste your license key and this site switches to Pro in place — every block, schedule and setting stays exactly as it is.',
		'ghostlabs-cycle-block-lite'
	);

	if ( claimToken ) {
		heading = __( 'Finish your upgrade', 'ghostlabs-cycle-block-lite' );
		subheading = claimOffered
			? __(
					'Your purchase is complete. One click and this site switches to Pro in place — no key entry needed, and every block, schedule and setting stays exactly as it is.',
					'ghostlabs-cycle-block-lite'
			  )
			: __(
					'This site switches to Pro in place — every block, schedule and setting stays exactly as it is.',
					'ghostlabs-cycle-block-lite'
			  );
	}

	const onSubmit = ( event ) => {
		event.preventDefault();

		// `aria-disabled` announces the refusal; it does not enforce it, so the
		// handler has to. See `inFlight`.
		if ( inFlight ) {
			return;
		}

		if ( licenseKey.trim() === '' ) {
			setMissingKey( true );
			// Still worth doing for the path where the submit BUTTON was
			// activated — it puts the caret where the fix goes. It is simply
			// not the announcement, which is what it was mistaken for.
			inputRef.current?.focus();

			return;
		}

		setMissingKey( false );
		send( { type: 'SUBMIT', licenseKey: licenseKey.trim() } );
	};

	return (
		<div className="rounded-[10px] border border-gray-200 bg-white px-7 py-[18px]">
			{ /*
			  * The quiet half of the screen, and deliberately quiet: someone with
			  * a key is looking for this, and someone without one is not helped by
			  * being asked. It is a single row until opened — the pitch below gets
			  * the weight, this gets a sentence and a button.
			  *
			  * It sits ABOVE the pitch (design F3) but stays visually quiet, so a
			  * buyer returning from checkout finds it without scrolling and nobody
			  * else has it shouting at them.
			  */ }
			<div className="flex items-center justify-between gap-6">
				<div>
					<h2 className="text-sm font-semibold text-gray-900">
						{ heading }
					</h2>
					<p className="mt-0.5 text-sm text-gray-500">{ subheading }</p>
				</div>

				<button
					type="button"
					className="shrink-0 rounded-full border-2 border-primary-400 px-5 py-2 text-sm font-semibold text-primary-500 transition hover:bg-primary-50/20"
					aria-expanded={ revealed }
					aria-controls={ FORM_ID }
					onClick={ () => setRevealed( ( open ) => ! open ) }
				>
					{ __( 'Have a license?', 'ghostlabs-cycle-block-lite' ) }
				</button>
			</div>

			{ /*
			  * The completion CTA, and it sits OUTSIDE the disclosure region on
			  * purpose: the key form stays collapsed behind "Have a license?" while
			  * this is on screen. Rendered from `claimOffered`, the same flag the
			  * failure focus routing reads.
			  */ }
			{ claimOffered && (
				<div className="mt-5">
					<button
						ref={ claimRef }
						type="button"
						className={ buttonClass( inFlight ) }
						aria-disabled={ inFlight }
						onClick={ () => {
							if ( inFlight ) {
								return;
							}

							send( { type: 'CLAIM', claimToken } );
						} }
					>
						{ __( 'Complete upgrade', 'ghostlabs-cycle-block-lite' ) }
					</button>
				</div>
			) }

			<div id={ FORM_ID } hidden={ ! revealed } className="mt-6">
					<form onSubmit={ onSubmit } aria-busy={ busy }>
						<FormField>
							<FieldTitle>
								{ __( 'License key', 'ghostlabs-cycle-block-lite' ) }
							</FieldTitle>
							<FieldContent>
								{ /*
								  * `readOnly` rather than `disabled` for the
								  * same reason the buttons carry
								  * `aria-disabled` — see `inFlight`. A disabled
								  * input leaves the tab order, so the field a
								  * keyboard user is standing in vanishes
								  * underneath them.
								  *
								  * ⚠️ `aria-invalid` is the whole programmatic
								  * signal here, and there is deliberately no
								  * `aria-describedby`: `FormField` wraps title
								  * and control in one `<label>`, so the notice
								  * below is already part of the field's
								  * accessible NAME. Pointing a description at
								  * it too would have the message read twice on
								  * every focus.
								  */ }
								<Input
									ref={ inputRef }
									type="text"
									autoComplete="off"
									spellCheck={ false }
									value={ licenseKey }
									readOnly={ inFlight }
									aria-invalid={ missingKey }
									onChange={ ( event ) => {
										setMissingKey( false );
										setLicenseKey( event.target.value );
									} }
								/>
								{ missingKey && (
									<FieldNotice type="error">
										{ MISSING_KEY_MESSAGE() }
									</FieldNotice>
								) }
							</FieldContent>
						</FormField>

						<div className="mt-6 flex items-center gap-x-2">
							<button
								type="submit"
								className={ buttonClass( inFlight ) }
								aria-disabled={ inFlight }
							>
								{ __(
									'Verify and upgrade',
									'ghostlabs-cycle-block-lite'
								) }
							</button>
						</div>
					</form>
			</div>

			{ /*
			  * ⚠️ Not optional and not decorative. Every outcome of the upgrade —
			  * a refused key, an unreachable service, a site that cannot write to
			  * its own plugins directory — is announced here and nowhere else. A
			  * live region that is absent, hidden, or only added to the DOM once
			  * there is something to say is a region screen readers never
			  * announce. It renders empty and stays in the page.
			  */ }
			<p role="status" className="mt-4 text-sm text-gray-700">
				{ status }
			</p>
		</div>
	);
};

export default UpgradePanel;
