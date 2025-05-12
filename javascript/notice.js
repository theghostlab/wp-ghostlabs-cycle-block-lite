import {ROUTE_NAMESPACE} from './constants';

const checkElement = async selector => {
    while ( document.querySelector(selector) === null) {
        await new Promise( resolve =>  requestAnimationFrame(resolve) )
    }
    return document.querySelector(selector);
};

const dismissNotice = selector => {

    if(!selector) return;

    selector.addEventListener('click', () => {

        wp.apiRequest({
            path: `${ROUTE_NAMESPACE}/dismiss-upgrade-notice`,
            data:{
                dismissNotice:true
            },
            type:'POST'
        })
            .catch(error => {
                console.error(error);
            })
    })
}

document.addEventListener('DOMContentLoaded', () =>{

    checkElement(`#ghostlabs-cycle-notice .notice-dismiss`).then((selector) => {
        dismissNotice(selector);
    });
});