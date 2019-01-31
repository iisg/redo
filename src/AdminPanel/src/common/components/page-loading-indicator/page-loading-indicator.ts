import {bindable, noView} from "aurelia-framework";

@noView
export class PageLoadingIndicator {
  @bindable loading = false;

  attached() {
    NProgress.configure({parent: 'page-loading-indicator', showSpinner: false});
  }

  loadingChanged(newValue: boolean) {
    if (newValue) {
      NProgress.start();
    } else {
      NProgress.done();
    }
  }
}
