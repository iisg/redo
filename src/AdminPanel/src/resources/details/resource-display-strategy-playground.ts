import {debounce} from "lodash";
import {ResourceRepository} from "../resource-repository";
import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceDisplayStrategyPlayground {
  @bindable resource: Resource;

  evaluating: boolean = false;
  template: string = '';
  result: string = '';

  constructor(private resourceRepository: ResourceRepository) {
  }

  evaluate = debounce(() => {
    if (this.template) {
      this.evaluating = true;
      this.resourceRepository.evaluateDisplayStrategy(this.resource.id, this.template)
        .then(result => this.result = result)
        .finally(() => this.evaluating = false);
    } else {
      this.result = '';
    }
  }, 500);

  async rerenderDisplayStrategies() {
    await this.resourceRepository.get(this.resource.id, false, r => r.withHeader('EvaluateDisplayStrategies', 'true'));
    window.location.assign(window.location.toString());
  }
}
