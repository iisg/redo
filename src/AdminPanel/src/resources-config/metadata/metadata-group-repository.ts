import {autoinject} from "aurelia-dependency-injection";
import {MetadataGroup} from "./metadata";
import {FrontendConfig} from "../../config/FrontendConfig";

@autoinject
export class MetadataGroupRepository {
  public getList(): MetadataGroup[] {
    return FrontendConfig.get('metadata_groups');
  }

  public getIds(): string[] {
    return this.getList().map(group => group.id);

  }
}
