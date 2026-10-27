// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach.
const { __ } = window.wp.i18n;

/**
 * "Already own Cycle Block Pro?" — the way in for somebody who has already
 * bought.
 *
 * It sits **above** the pitch but stays quiet: a single row until opened. A
 * buyer finds it without scrolling, and nobody else has it shouting at them.
 *
 * 🔴 **It explains, and it does nothing itself.** Until 2026-10-08 this panel
 * took a key, posted it to this edition's own route, installed Cycle Block Pro
 * and switched the plugins over. WordPress.org refused that flow for Undercard,
 * which was built from this one: Guideline 8 forbids installing a premium
 * version of the same plugin, and the review's "Changing Active Plugins" item
 * forbids a plugin switching plugins on or off. Now the buyer takes three
 * steps, and each happens on a screen this plugin does not own — their account
 * to download, core's uploader to install, core's Activate link to switch over.
 * Pro's own entry header switches this edition off. `NoInDashboardInstallTest`
 * holds the ban on the PHP side; the old client is kept at the tag
 * `reference/in-place-upgrade-free-2026-10-08`. ⚠️ Do not restore it here.
 *
 * ## A native `<details>`, so there is no state at all
 *
 * The old panel was an XState machine behind a hand-built disclosure: a button
 * carrying `aria-expanded`/`aria-controls`, a `hidden` region, focus routing,
 * a live region. Every one of those existed to serve the form, and the form is
 * gone. `<details>` is closed by default, operable from the keyboard, announced
 * as expandable by every screen reader, and needs no script to open.
 *
 * ⚠️ **The `<summary>` keeps its disclosure role.** The pill inside it is a
 * `<span>`, not a `<button>`: a button inside a summary is a second control
 * nested in the first, and a screen reader announces the pair as one thing that
 * cannot be told apart. The pill only *looks* like the old button.
 *
 * ## The links come from PHP, and the uploader may not come at all
 *
 * `accountUrl` is the backend's account page, on the `--release-host` axis
 * (`Services\Upgrade\AccountEndpoints`). `uploadUrl` is core's uploader, or
 * `null` where `upload_plugins` refuses this user — a subsite administrator on
 * a network, or anyone on a site with file changes turned off. A link to a
 * screen that answers "Sorry, you are not allowed" is worse than the sentence
 * telling them who to ask, so that is what renders instead.
 *
 * ⚠️ **No literal fallback for either link.** Where PHP did not supply one, the
 * step renders as text. A guessed destination resolves, looks right, and sends
 * the buyer somewhere that is not their account.
 */
const UpgradePanel = ( { accountUrl, uploadUrl } ) => (
	<section
		data-gl-handoff
		className="rounded-[10px] border border-gray-200 bg-white px-7 py-[18px]"
	>
		<details className="group">
			{ /*
			  * `list-none` and the `::-webkit-details-marker` rule in
			  * settings.src.css remove the disclosure triangle: the pill is the
			  * affordance, and a triangle beside it would be a second one.
			  *
			  * ⚠️ The focus ring is declared, not inherited. The summary is the
			  * one control here, and `accessibility.spec.ts` compares its painted
			  * resting and focused styles (WCAG 2.4.7) — the same test that caught
			  * the hero CTA showing nothing on focus.
			  */ }
			<summary className="flex cursor-pointer list-none items-center justify-between gap-6 rounded-md focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-500">
				<h2 className="text-sm font-semibold text-gray-900">
					{ __( 'Already own Cycle Block Pro?', 'ghostlabs-cycle-block-lite' ) }
				</h2>
				<span className="shrink-0 rounded-full border-2 border-primary-400 px-5 py-2 text-sm font-semibold text-primary-500 transition group-hover:bg-primary-50/20">
					{ __( 'Have a license?', 'ghostlabs-cycle-block-lite' ) }
				</span>
			</summary>

			<div className="mt-4 text-sm text-gray-700">
				<p className="text-gray-500">
					{ __(
						'Cycle Block Pro is a separate plugin. When you activate it, it switches this plugin off, and every block, schedule and setting stays exactly as it is.',
						'ghostlabs-cycle-block-lite'
					) }
				</p>

				<ol className="mt-3 list-decimal space-y-1.5 pl-5">
					<li>
						{ accountUrl ? (
							<a
								href={ accountUrl }
								target="_blank"
								rel="noopener noreferrer"
								className="font-semibold text-primary-500 underline hover:text-primary-400"
							>
								{ __(
									'Download Cycle Block Pro from your account',
									'ghostlabs-cycle-block-lite'
								) }
								{ /* ⚠️ Not decoration: a new tab without the
								     warning is a WCAG 3.2.5 failure. */ }
								<span className="sr-only">
									{ ' ' }
									{ __( '(opens in a new tab)', 'ghostlabs-cycle-block-lite' ) }
								</span>
							</a>
						) : (
							__(
								'Download Cycle Block Pro from your GhostLabs account.',
								'ghostlabs-cycle-block-lite'
							)
						) }
					</li>

					<li>
						{ uploadUrl ? (
							<>
								<a
									href={ uploadUrl }
									className="font-semibold text-primary-500 underline hover:text-primary-400"
								>
									{ __( 'Upload it to this site', 'ghostlabs-cycle-block-lite' ) }
								</a>{ ' ' }
								{ /*
								  * ⚠️ No menu path here. WordPress renamed "Add New Plugin" to "Add
								  * Plugin" between our 6.8 floor and 7.1, so any path is wrong on one
								  * of them. The link already lands on the uploader.
								  */ }
								{ __(
									'with WordPress’s plugin uploader.',
									'ghostlabs-cycle-block-lite'
								) }
							</>
						) : (
							__(
								"Upload it with WordPress’s plugin uploader. Your account can't upload plugins here, so ask whoever manages plugins on this site to upload it for you.",
								'ghostlabs-cycle-block-lite'
							)
						) }
					</li>

					<li>
						{ __(
							'Activate Cycle Block Pro, then enter your license key in its settings.',
							'ghostlabs-cycle-block-lite'
						) }
					</li>
				</ol>
			</div>
		</details>
	</section>
);

export default UpgradePanel;
