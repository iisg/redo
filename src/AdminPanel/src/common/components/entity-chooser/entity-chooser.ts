import {bindable} from "aurelia-templating";
import {bindingMode, computedFrom} from "aurelia-binding";

export class EntityChooser {
  @bindable entities: Entity[] = [];
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Entity;
  @bindable excludeEntities: Entity[]|AnyMap<Entity>;
  @bindable({defaultBindingMode: bindingMode.twoWay}) containsItems: boolean;

  @computedFrom('entities.length', 'excludeEntities.length')
  get values(): Entity[] {
    const excludedEntities = this.getExcludedEntitiesArray();
    const blacklistedIds = excludedEntities.map(entity => entity.baseId || entity.id);
    const result = (this.entities || []).filter(entity => blacklistedIds.indexOf(entity.id) == -1);
    this.containsItems = (result.length > 0);
    return result;
  }

  private getExcludedEntitiesArray(): Entity[] {
    if (this.excludeEntities == undefined) {
      return [];
    } else if (Array.isArray(this.excludeEntities)) {
      return this.excludeEntities as Entity[];
    } else { // assume object
      return Object.keys(this.excludeEntities).map(key => this.excludeEntities[key]);  // Object.values not available in TS and browsers
    }
  }
}

type Entity = {id: number, baseId?: number};
