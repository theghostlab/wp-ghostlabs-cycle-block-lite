import { assign, fromPromise, setup } from 'xstate';

// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach.
const { __ } = window.wp.i18n;

/**
 * A thrown apiFetch error, mapped to the same failure shape the endpoint
 * answers.
 *
 * The 404 arm is not an error path so much as a SUCCESS echo: the upgrade
 * endpoint lives under the free tier's own REST namespace, so the moment the
 * swap completes the route ceases to exist. A submit racing that (a second
 * tab, a double-click that beat the disable) lands here.
 */
const failureFromThrown = ( error ) => {
	if ( error?.data?.status === 404 ) {
		return {
			noticeCode: 'gone',
			message: __(
				'This site looks like it has already switched to Cycle Block Pro. Open the Plugins screen to finish activating it, or reload this page.',
				'ghostlabs-cycle-block-lite'
			),
		};
	}

	return {
		noticeCode: 'network',
		message:
			error?.message ||
			__(
				'The upgrade request could not be sent. Check your connection and try again.',
				'ghostlabs-cycle-block-lite'
			),
	};
};

/**
 * The upgrade flow's state, and nothing else — no DOM, no navigation. The
 * component owns focus and the redirect; this machine owns the one rule the
 * accessibility audit made non-negotiable: every failure is ASSIGNED into
 * context, where the status region can render it. A failure that only reaches
 * the console is a failure the screen shows as silence.
 *
 * Two ways in, one flow: SUBMIT carries a pasted licence key, CLAIM carries
 * the one-shot token a checkout redirect brought back. The server redeems the
 * claim and then runs the same upgrade, so both events share every state.
 *
 * @param {Object}   deps
 * @param {Function} deps.submit Called with { licenseKey } or { claimToken };
 *                               resolves to the endpoint's UpgradeResult
 *                               payload.
 */
export const createUpgradeMachine = ( { submit } ) =>
	setup( {
		actors: {
			submit: fromPromise( ( { input } ) => submit( input ) ),
		},
	} ).createMachine( {
		id: 'ghostlabs-free-upgrade',
		initial: 'idle',
		context: {
			message: '',
			noticeCode: '',
			activationUrl: null,
		},
		states: {
			idle: {
				on: { SUBMIT: 'submitting', CLAIM: 'submitting' },
			},
			submitting: {
				// A fresh attempt clears the previous failure, so the status
				// region never shows a stale error over a retry in flight.
				entry: assign( { message: () => '', noticeCode: () => '' } ),
				invoke: {
					src: 'submit',
					input: ( { event } ) => ( {
						licenseKey: event.licenseKey ?? null,
						claimToken: event.claimToken ?? null,
					} ),
					onDone: [
						{
							guard: ( { event } ) => event.output?.ok === true,
							target: 'redirecting',
							actions: assign( {
								activationUrl: ( { event } ) =>
									event.output.activationUrl,
							} ),
						},
						{
							target: 'failed',
							actions: assign( {
								message: ( { event } ) =>
									event.output?.message ?? '',
								noticeCode: ( { event } ) =>
									event.output?.noticeCode ?? '',
							} ),
						},
					],
					onError: {
						target: 'failed',
						actions: assign( ( { event } ) =>
							failureFromThrown( event.error )
						),
					},
				},
			},
			failed: {
				on: { SUBMIT: 'submitting', CLAIM: 'submitting' },
			},
			redirecting: {
				type: 'final',
			},
		},
	} );
