import {autoinject} from "aurelia-dependency-injection";
import {FrontendConfig} from "../../config/FrontendConfig";
import {MetadataGroup} from "./metadata";

@autoinject
export class MetadataGroupRepository {
  public getList(): MetadataGroup[] {
    return FrontendConfig.get('metadata_groups');
  }

  public getIds(): string[] {
    return this.getList().map(group => group.id);
  }
}
