import { Icon } from '@wordpress/components';

const ShuffleIcon = () => {

    return (
        <Icon
            icon={() => (<svg xmlns="http://www.w3.org/2000/svg" className="icon icon-tabler icon-tabler-arrows-shuffle" width="44" height="44" viewBox="0 -1 24 24" strokeWidth="1.5" stroke="#2c3e50" fill="none" strokeLinecap="round" strokeLinejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M18 4l3 3l-3 3" />
                    <path d="M18 20l3 -3l-3 -3" />
                    <path d="M3 7h3a5 5 0 0 1 5 5a5 5 0 0 0 5 5h5" />
                    <path d="M21 7h-5a4.978 4.978 0 0 0 -3 1m-4 8a4.984 4.984 0 0 1 -3 1h-3" />
                    </svg>)}
        />
    )
}

export default ShuffleIcon