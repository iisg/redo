import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "../binding-mode";

export class EntityChooser {
  @bindable entities: Entity[] = [];
  @bindable(twoWay) value: Entity;
  @bindable(twoWay) containsItems: boolean;
  @bindable filter: (entity: Entity) => boolean;

  @computedFrom('entities.length', 'filter', 'value')
  get values(): Entity[] {
    const result = (this.entities || []).filter(this.filter || (() => true));
    this.containsItems = (result.length > 0);
    return result;
  }
}

type Entity = { id: number };
