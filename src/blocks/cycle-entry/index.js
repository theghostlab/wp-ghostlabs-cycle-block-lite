import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';
import metadata from './block.json';
import './style.scss'

registerBlockType( metadata.name, {
    icon: <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <rect x="15" y="6" width="20" height="26" rx="2.5" stroke="#1E1E1E" strokeWidth="2.6"/>
        <rect x="11" y="10" width="20" height="26" rx="2.5" stroke="#1E1E1E" strokeWidth="2.6" fill="#FFFFFF"/>
        <rect x="7" y="14" width="20" height="26" rx="2.5" fill="#1E1E1E"/>
        <rect x="11" y="20" width="12" height="2.6" rx="1.3" fill="#FFFFFF"/>
        <rect x="11" y="25.5" width="12" height="2.6" rx="1.3" fill="#FFFFFF"/>
        <rect x="11" y="31" width="7" height="2.6" rx="1.3" fill="#FFFFFF"/>
    </svg>,
    edit,
    save
});