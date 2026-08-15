// Resolved by webpack's `@tier` alias to the compiling edition's src/tier/.
// An edition with per-variation analytics binds them here; one without
// Events layer and answers with a no-op, so none of that code is bundled there.
import runCustomEvents from "@tier/cycle/custom-events";

const singlePreviewStyle = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const ghostlabsTesting = urlParams.get("ghostlabs_testing");
    const previewClass = "ghostlabs-cycle-single-preview"

    if (ghostlabsTesting === null) {
        Array.from(document.body.querySelectorAll(`.theghostlab-cycle-entry`)).forEach( cycle => {
            cycle.classList.remove(previewClass)
        } )
        return;
    }

    const cycle = document.body.querySelector(`.theghostlab-cycle-entry[data-block-id='${ghostlabsTesting}']`);

    if(!cycle) return;

    cycle.classList.add(previewClass)
}

singlePreviewStyle();

runCustomEvents();
