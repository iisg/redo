import {autoinject} from "aurelia-dependency-injection";
import {FrontendConfig} from "../../../../config/FrontendConfig";

@autoinject
export class UploadLimitInfo {
  private limit: { file: number };

  constructor() {
    this.limit = FrontendConfig.get('max_upload_size');
  }
}
