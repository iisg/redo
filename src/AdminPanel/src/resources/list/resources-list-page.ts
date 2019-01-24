import {ResourcesList} from "./resources-list";

export class ResourcesListPage {
  resourcesList: ResourcesList;
  private parameters: any;

  activate(parameters: any) {
    this.parameters = parameters;
    if (this.resourcesList) {
      this.bind();
    }
  }

  bind() {
    this.resourcesList.activate(this.parameters);
  }
}
