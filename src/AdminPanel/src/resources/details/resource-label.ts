import {bindable} from "aurelia-templating";
import {ResourceRepository} from "../resource-repository";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {LoadEvent} from "../../common/events/load-event";

@autoinject
export class ResourceLabel {
  @bindable id: number;
  @bindable resource: Resource;

  loading: boolean = false;

  constructor(private resourceRepository: ResourceRepository, private hasRole: HasRoleValueConverter, private element: Element) {
  }

  idChanged(): void {
    if (this.id) {
      this.loading = true;
      this.resourceRepository.getTeaser(this.id)
        .then(resource => this.resource = resource)
        .finally(() => this.loading = false);
    }
  }

  resourceChanged() {
    this.element.dispatchEvent(LoadEvent.newInstance(this.resource));
  }
}
