import {autoinject} from "aurelia-dependency-injection";
import {DialogService} from "aurelia-dialog";

@autoinject
export class Modal {
  constructor(private dialog: DialogService) {
  }

  open(viewModel: Object, model?: any): Promise<any> {
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
    // noinspection TypeScriptUnresolvedFunction
    return this.dialog.open({viewModel, model}).whenClosed(
      response => response.wasCancelled ? Promise.reject('cancelled') : Promise.resolve(response.output)
    );
  }
}
