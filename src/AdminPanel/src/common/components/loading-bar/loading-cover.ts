import {bindable} from "aurelia-templating";

export class LoadingCover {
  @bindable loading: boolean;
  @bindable debounce: Number = 300;

  private timeout;
  private isLoading: boolean = false;

  loadingChanged() {
    if (this.loading && !this.timeout) {
      this.timeout = setTimeout(() => {
        this.isLoading = true;
        this.timeout = undefined;
      }, this.debounce);
    } else if (!this.loading) {
      if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = undefined;
      }
      this.isLoading = false;
    }
  }
}
