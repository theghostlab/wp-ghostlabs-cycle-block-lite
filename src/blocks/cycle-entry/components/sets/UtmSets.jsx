import UtmSetButton from "../../../../../javascript/_components/utm/UtmSetButton";

export default function UtmSets() {

    const setLink = theghostlab_cycle_vars.promoLink;

    return (
        <UtmSetButton
            style={{marginBlock: '1rem'}}
            setLink={setLink}
        />
    )
}