import {parseQueryString} from "aurelia-path";
import {Router} from "aurelia-router";

export function getQueryParameters(router?: Router) {
    const currentInstruction = router && router.currentInstruction;
    if (currentInstruction) {
        return currentInstruction.queryParams;
    }
    const href = window.location.href;
    return parseQueryString(href.slice(href.indexOf('?')));
}
