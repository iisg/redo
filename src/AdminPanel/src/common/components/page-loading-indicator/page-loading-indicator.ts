import {bindable, noView} from "aurelia-framework";
import * as nprogress from "nprogress";

@noView(['nprogress/nprogress.css'])
export class PageLoadingIndicator {
    @bindable loading = false;

    attached() {
        NProgress.configure({parent: 'page-loading-indicator', showSpinner: false});
    }

    loadingChanged(newValue: boolean) {
        if (newValue) {
            nprogress.start();
        } else {
            nprogress.done();
        }
    }
}
