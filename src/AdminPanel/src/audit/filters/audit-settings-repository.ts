import {autoinject} from "aurelia-dependency-injection";
import {FrontendConfig} from "../../config/FrontendConfig";
import {AuditSettings} from "./audit-settings";

@autoinject
export class AuditSettingsRepository {
  public getList(): AuditSettings[] {
    return FrontendConfig.get('audit');
  }

  public getIds(): string[] {
    return this.getList().map(setting => setting.id);
  }
}
