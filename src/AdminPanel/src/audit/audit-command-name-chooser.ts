import {AuditEntryRepository} from "./audit-entry-repository";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "../common/components/binding-mode";
import {bindable} from "aurelia-templating";

@autoinject
export class AuditCommandNameChooser {
  private availableCommandNames: string[];

  @bindable(twoWay) commandNames;

  constructor(private auditEntryRepository: AuditEntryRepository) {
  }

  async attached() {
    this.availableCommandNames = await this.auditEntryRepository.getCommandNames();
  }
}
