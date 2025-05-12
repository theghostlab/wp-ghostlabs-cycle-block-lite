import { Icon } from '@wordpress/components';

const RepeatIcon = () => {

    return (
        <Icon
            icon={() => (<svg xmlns="http://www.w3.org/2000/svg" className="icon icon-tabler icon-tabler-repeat" width="44" height="44" viewBox="0 -1 24 24" strokeWidth="1.5" stroke="#2c3e50" fill="none" strokeLinecap="round" strokeLinejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3" />
                <path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3" />
            </svg>)}
        />
    )
}

export default RepeatIcon