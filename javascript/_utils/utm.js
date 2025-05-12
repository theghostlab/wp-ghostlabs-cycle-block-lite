const unsetDefaultOption = options => options.reduce((a,c) => {

    if( c.hasOwnProperty('disabled') ) {
        delete c.disabled;
    }

    a.push(c);

    return a;
},[]);

const setDefaultOption = options => options.reduce((a,c) => {

    if( c.value === 'null' ) {
        c.disabled = true
    }

    a.push(c);

    return a;
},[]);

const camelToSnake = input => input.replace(/[A-Z]/g, match => `_${match.toLowerCase()}`);

const getSet = (ulid, options) => options.find(option => option.ulid === ulid);

const updateSet = (set, utmCodes) => {
    const snakeCaseKeys = Object.keys(utmCodes).map(camelToSnake);

    const filteredSet = Object.fromEntries(
        Object.entries(set)
            .filter(([key]) => snakeCaseKeys.includes(camelToSnake(key)))
    );

    return Object.fromEntries(Object.entries(filteredSet).map(set => {
        set[0] = camelToSnake(set[0]);

        return set;
    }));
};

const createSetLink = adminUrl => new URL("options-general.php?page=theghostlab-cycle-settings&tab=utm", adminUrl).toString();

export {
    createSetLink,
    unsetDefaultOption,
    setDefaultOption,
    getSet,
    updateSet
}