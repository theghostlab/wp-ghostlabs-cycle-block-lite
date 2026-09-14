import { forwardRef } from 'react';

const cx = ( ...classes ) => classes.filter( Boolean ).join( ' ' );

export const Input = forwardRef( ( { className, ...props }, ref ) => (
	<input
		ref={ ref }
		className={ cx(
			'w-full lg:max-w-md text-gray-700 mt-2 lg:mt-0 py-1.5 px-2.5 text-sm border border-gray-400 focus:border-accent-600',
			className
		) }
		{ ...props }
	/>
) );

const NOTICE_TYPES = {
	default: 'text-gray-800',
	alert: 'text-red-600',
	error: 'text-red-600',
	warning: 'text-orange-700',
	success: 'text-green-700',
	info: 'text-gray-500',
};

export const Notice = ( { type = 'default', className, ...props } ) => (
	<div
		{ ...props }
		className={ cx(
			'text-xs leading-normal font-semibold',
			NOTICE_TYPES[ type ] ?? NOTICE_TYPES.default,
			className
		) }
	/>
);

export const FieldNotice = ( { type, ...props } ) => (
	<Notice { ...props } type={ type } className="mt-2" />
);

export const Field = ( { tag: Tag = 'div', className, ...props } ) => (
	<Tag
		{ ...props }
		className={ cx(
			'block bg-white py-5 px-5 lg:px-7 ring-1 ring-gray-200',
			className
		) }
	/>
);

export const FieldTitle = ( props ) => (
	<div { ...props } className="lg:w-3/12 lg:pr-5 text-gray-800 text-sm font-medium" />
);

export const FieldContent = ( props ) => (
	<div { ...props } className="lg:w-9/12 mt-2 lg:mt-0 space-y-2" />
);

export const FormField = ( { ...props } ) => (
	<Field tag="label">
		<div { ...props } className="lg:flex lg:items-baseline" />
	</Field>
);
