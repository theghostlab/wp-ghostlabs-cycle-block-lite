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