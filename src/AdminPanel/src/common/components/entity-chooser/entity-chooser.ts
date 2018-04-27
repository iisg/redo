import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "../binding-mode";

export class EntityChooser {
  @bindable entities: Entity[] = [];
  @bindable(twoWay) value: Entity;
  @bindable(twoWay) containsItems: boolean;
  @bindable filter: (entity: Entity) => boolean;
  @bindable(twoWay) shouldRefreshResults: boolean = false;

  @computedFrom('shouldRefreshResults', 'entities.length', 'filter', 'value')
  get values(): Entity[] {
    const result = (this.entities || []).filter(this.filter || (() => true));
    this.containsItems = (result.length > 0);
    this.shouldRefreshResults = false;
    return result;
  }
}

type Entity = { id: number };
