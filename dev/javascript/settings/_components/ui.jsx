/**
 * The presentational primitives this settings screen is built from.
 *
 * Copied from `@theghostlab/ghostlabs-wp-settings-ui` rather than imported,
 * which is the point of the file.
 *
 * ⚠️ **This edition is published to WordPress.org with its source, and that
 * package is not publicly installable** — it lives in a private registry, so
 * `pnpm install` fails for anyone outside the organisation and the settings
 * bundle cannot be rebuilt from the published sources. A repository whose whole
 * claim is "these sources produced these binaries" cannot depend on something
 * the reader is unable to fetch.
 *
 * What was actually being imported was eight bits of markup with Tailwind
 * classes on them — no state, no behaviour, nothing that benefits from being
 * shared. So they are here, and this edition builds from public packages alone.
 *
 * Keep them in step with the package by hand if its markup changes. That is a
 * real cost, and it is smaller than the alternative: the sibling plugins that
 * mount the full shell still import it, and they are not published this way.
 *
 * Classes are verbatim from the package so the two screens look identical, and
 * `PanelTitle` renders an `h2` for the same reason it does there — passing a
 * title to a plain div sets an HTML `title` attribute, which is a hover tooltip
 * and invisible to the accessibility tree. `accessibility.spec.ts` pins that.
 */

// A plain join, not the package's twMerge: nothing here passes a className, so
// there are no conflicting Tailwind classes to resolve. If something starts
// passing one, reach for tailwind-merge rather than letting a later class
// silently lose to an earlier one.
const cx = ( ...classes ) => classes.filter( Boolean ).join( ' ' );

export const HeaderContainer = ( props ) => (
	<div className="bg-white py-10 px-5 lg:px-10">
		<div { ...props } className="max-w-screen-full" />
	</div>
);

export const HeaderTitle = ( props ) => (
	<h1 { ...props } className="text-2xl font-semibold" />
);

export const Navigation = ( props ) => (
	<div className="overflow-hidden overflow-x-scroll">
		<div className="px-5 lg:px-10 border-b border-gray-300">
			<div className="flex justify-between items-center space-x-4">
				<nav { ...props } className="-mb-px flex space-x-4" />
			</div>
		</div>
	</div>
);

export const NavigationItem = ( { active, className, ...props } ) => (
	<button
		{ ...props }
		className={ cx(
			'border-transparent text-gray-500 hover:border-accent-600 hover:text-accent-600 whitespace-nowrap py-4 px-4 md:px-6 border-b-2 text-sm focus:ring-0',
			active && 'border-accent-600 text-accent-600',
			className
		) }
	/>
);

export const PanelContainer = ( { className, ...props } ) => (
	<div { ...props } className={ cx( 'mx-5 lg:mx-20 my-14 max-w-screen-lg', className ) } />
);

export const Panel = ( { className, ...props } ) => (
	<div { ...props } className={ cx( 'bg-gray-200', className ) } />
);

export const PanelHeader = ( { className, ...props } ) => (
	<header
		{ ...props }
		className={ cx( 'mb-3 flex justify-between items-center', className ) }
	/>
);

export const PanelTitle = ( { tag: Tag = 'h2', className, ...props } ) => (
	<Tag { ...props } className={ cx( 'text-2xl text-gray-800 antialiased', className ) } />
);
