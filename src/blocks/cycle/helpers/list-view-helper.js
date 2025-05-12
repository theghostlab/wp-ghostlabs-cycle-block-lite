const checkElement = async selector => {
    while ( document.querySelector(selector) === null) {
        await new Promise( resolve =>  requestAnimationFrame(resolve) )
    }
    return document.querySelector(selector);
};

const enumerate = selector => {

    let nextSibling = selector.nextElementSibling;

    while(nextSibling) {

        const ariaPosinset = nextSibling.getAttribute('aria-posinset');
        const button = nextSibling.querySelector('.block-editor-list-view-block-select-button__title');

        const cyclePosition = nextSibling.innerHTML.search(`aria-label="Cycle"`);
        const variationPosition = nextSibling.innerHTML.search(`aria-label="Variation"`);

        if( cyclePosition === -1 && variationPosition ) {
            button.innerHTML = `Variation #${ariaPosinset}`;
        }

        nextSibling = nextSibling.nextElementSibling;
    }
}

const debounce = (func, timeout = 300) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

const memoize = (fn, callback) => {
    let cache = {};
    return (...args) => {
        let n = args[0];  // just taking one argument here
        if (n in cache) {

            callback(cache[n])
            return cache[n];

        } else {

            let result = fn(args);
            cache[n] = result;

            callback(result)
            return result;
        }
    }
}

const enumerateVariations = clientId => {

    checkElement(`.block-editor-list-view-leaf[data-block="${clientId}"]`)
        .then(enumerate)
        .catch(error => console.error(error));
}

const setIsExpanded = isExpanded => isExpanded;

const memoizeSetIsExpanded = debounce(memoize(setIsExpanded, ([isExpanded, clientId]) => {

    if( isExpanded ) {
        enumerateVariations(clientId)
    }
}), 3);

const checkExpanded = (el, clientId) => {

    if( !el ) return;

    const config = { attributes: true, childList: true, subtree: true };

    const callback = mutationList => {

        for (const mutation of mutationList) {

            if (mutation.type === "attributes") {

                const isExpanded = el.getAttribute(`aria-expanded`);

                memoizeSetIsExpanded(isExpanded, clientId);
            }
        }

        observer.disconnect();
    }

    const observer = new MutationObserver(callback);

    observer.observe(el, config);
}

const enumerateOpenListViewVariations = clientId => {
    checkElement(`.block-editor-list-view-leaf[data-block="${clientId}"]`)
        .then(selector => {
            const config = { attributes: true, childList: true, subtree: true };

            const callback = mutationList => {

                for (const mutation of mutationList) {

                    if (mutation.type === "attributes") {

                        const isExpanded = selector.dataset?.expanded === 'true';
                        memoizeSetIsExpanded(isExpanded, clientId);
                    }
                }

                observer.disconnect();
            }

            const observer = new MutationObserver(callback);

            observer.observe(selector, config);

        })
        .catch(err => console.error(err))
}

const enumeratePaginatedVariations = clientId => {
    checkElement(`.block-editor-list-view-leaf[data-block="${clientId}"]`)
        .then(selector => {

            const config = { attributes: true, childList: true, subtree: true };

            const callback = mutationList => {

                for (const mutation of mutationList) {

                    if (mutation.type === "attributes") {

                        const isExpanded = selector.dataset?.expanded === 'true';

                        if( isExpanded ) {
                            enumerateVariations(clientId);
                        }
                    }
                }

                observer.disconnect();
            }

            const observer = new MutationObserver(callback);

            observer.observe(selector, config);

        })
        .catch(err => console.error(err))
}

const enumerateListViewVariations = clientId => {
    checkElement(`.block-editor-list-view-leaf[data-block="${clientId}"]`)
        .then(selector => {

            const button = selector.querySelector('.block-editor-list-view-block-select-button');

            if(button) {
                button.addEventListener('click', e => {

                    let a;

                    if( e.target.tagName.toLowerCase() === 'path' ) {
                        a = e.target.closest('a');
                    }

                    if( e.target.tagName.toLowerCase() === 'svg' ) {
                        a = e.target.closest('a');
                    }

                    checkExpanded(a,clientId);
                })
            }
        })

}

const enumerateEntriesFromLogs = clientId => {

    wp.data.dispatch('core/edit-post').setIsListViewOpened(true);

    checkElement(`.block-editor-list-view-leaf[data-block="${clientId}"]`)
        .then(selector => {

            const config = { attributes: true, childList: true, subtree: true };

            const callback = mutationList => {
                const rows = [];

                for (const mutation of mutationList) {

                    if (mutation.type === "attributes") {
                        enumerateVariations(clientId);
                    }
                }
            }

            const observer = new MutationObserver(callback);

            observer.observe(selector, config);
        });
}

export {
    enumerateVariations,
    enumerateEntriesFromLogs,
    enumerateListViewVariations,
    enumerateOpenListViewVariations,
    enumeratePaginatedVariations,
}