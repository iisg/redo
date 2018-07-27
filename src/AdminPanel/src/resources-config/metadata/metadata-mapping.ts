import {autoinject} from "aurelia-dependency-injection";
import {CopyMapper} from "common/dto/mappers";
import {ResourceKind} from "../resource-kind/resource-kind";
import {AutoMapper} from "common/dto/auto-mapper";
import {Metadata} from "./metadata";
import {deepCopy} from "../../common/utils/object-utils";
import {MinMaxValue} from "./metadata-min-max-value";
import {MetadataControl} from "./metadata-control";

export class MetadataMapper extends AutoMapper<Metadata> {
  toBackendValue(entity: Metadata): Object {
    if (entity.control != MetadataControl.RELATIONSHIP) {
      delete entity.constraints['resourceKind'];
    }
    return super.toBackendValue(entity) as any;
  }
}

@autoinject
export class ResourceKindConstraintMapper extends CopyMapper<Array<ResourceKind | number>> {
  constructor(private autoMapper: AutoMapper<ResourceKind>) {
    super();
  }

  toBackendValue(items: Array<ResourceKind | number>): number[] {
    return items.map(item => (item as ResourceKind).id || (item as number));
  }

  protected clone(items: Array<ResourceKind | number>): Array<ResourceKind | number> {
    return items.map(item => (item instanceof ResourceKind) ? this.autoMapper.nullSafeClone(item) : item);
  }
}

@autoinject
export class MinMaxConstraintMapper extends CopyMapper<MinMaxValue> {
  protected clone(entity: MinMaxValue): MinMaxValue {
    if (entity.min == undefined && entity.max == undefined) {
      entity = new MinMaxValue();
    }
    return deepCopy(entity);
  }
}
