import * as nprogress from "nprogress";
import {noView, bindable} from "aurelia-framework";

@noView(['nprogress/nprogress.css'])
export class PageLoadingIndicator {
    @bindable loading = false;

    attached() {
        NProgress.configure({parent: 'page-loading-indicator'});
    }

    loadingChanged(newValue: boolean) {
        if (newValue) {
            nprogress.start();
        } else {
            nprogress.done();
        }
    }
}
