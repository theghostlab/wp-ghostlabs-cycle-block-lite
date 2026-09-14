import runCustomEvents from "@tier/cycle/custom-events";

const BLOCK_ID = /^[A-Za-z0-9_-]+$/;

const singlePreviewStyle = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const ghostlabsTesting = urlParams.get("ghostlabs_testing");
    const previewClass = "ghostlabs-cycle-single-preview"

    if (ghostlabsTesting === null || !BLOCK_ID.test(ghostlabsTesting)) {
        Array.from(document.body.querySelectorAll(`.theghostlab-cycle-entry`)).forEach( cycle => {
            cycle.classList.remove(previewClass)
        } )
        return;
    }

    let cycle = null;

    try {
        cycle = document.body.querySelector(`.theghostlab-cycle-entry[data-block-id='${ghostlabsTesting}']`);
    } catch (error) {
        return;
    }

    if(!cycle) return;

    cycle.classList.add(previewClass)
}

singlePreviewStyle();

runCustomEvents();
