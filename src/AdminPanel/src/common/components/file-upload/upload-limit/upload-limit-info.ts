import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class UploadLimitInfo {
  private limit: { file: number };

  constructor(config: Configure) {
    this.limit = config.get('max_upload_size');
  }
}
