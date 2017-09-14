import {ResourceKind} from "./resource-kind";

export class SystemResourceKinds {
  static readonly USER: ResourceKind = $.extend(new ResourceKind(), {id: -1});
}
