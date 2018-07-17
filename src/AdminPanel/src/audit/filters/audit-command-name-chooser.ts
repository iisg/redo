import {AuditEntryRepository} from "../audit-entry-repository";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "../../common/components/binding-mode";
import {bindable} from "aurelia-templating";

@autoinject
export class AuditCommandNameChooser {
  private availableCommandNames: string[];

  @bindable(twoWay) commandNames;
  @bindable resourceId: number;

  constructor(private auditEntryRepository: AuditEntryRepository) {
  }

  async attached() {
    let params: StringMap<any> = {};
    if (this.resourceId) {
      params.onlyResource = true;
    }
    this.availableCommandNames = await this.auditEntryRepository.getCommandNames(params);
  }
}
