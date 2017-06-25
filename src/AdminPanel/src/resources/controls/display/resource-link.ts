import {bindable} from "aurelia-templating";

export class ResourceLink {
  @bindable id: number;

  private routerParams = {id: undefined};

  idChanged(): void {
    this.routerParams.id = this.id;
  }
}
