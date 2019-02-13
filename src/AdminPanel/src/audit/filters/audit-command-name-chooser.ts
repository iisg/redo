import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {twoWay} from "../../common/components/binding-mode";
import {AuditEntryRepository} from "../audit-entry-repository";

const PLUGIN_ENTRY_PREFIX = 'resource_workflow_plugin-';

@autoinject
export class AuditCommandNameChooser {
  @bindable(twoWay) commandNames;
  @bindable resourceId: number;
  availableCommandNames: string[];

  constructor(private auditEntryRepository: AuditEntryRepository, private i18n: I18N) {
  }

  async attached() {
    let params: StringMap<any> = {};
    if (this.resourceId) {
      params.onlyResource = true;
    }
    this.availableCommandNames = await this.auditEntryRepository.getCommandNames(params);
  }

  operationLabel(operationName) {
    if (operationName.indexOf(PLUGIN_ENTRY_PREFIX) === 0) {
      return this.i18n.tr('Plugin') + ': ' + this.i18n.tr('plugins::' + operationName.substr(PLUGIN_ENTRY_PREFIX.length) + '//label');
    } else {
      return this.i18n.tr('audit_commands::' + operationName);
    }
  }
}
