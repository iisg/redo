import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Alert, AlertOptions} from "./alert";

@autoinject
export class DeleteEntityConfirmation {
  constructor(private alert: Alert, private i18n: I18N) {
  }

  confirm(entityName: string, id: number|string): Promise<string> {
    if (Number.isInteger(id)) {
      id = `#${id}`;
    }
    const translatedEntityType = this.i18n.tr('entityTypes//' + entityName);
    const title = this.i18n.tr('Confirm deletion');
    const text = this.i18n.tr('Are you sure you want to delete {{entityType}} {{id}}?', {entity: translatedEntityType, id});
    const options: AlertOptions = {
      type: 'question',
      confirmButtonText: this.i18n.tr('Delete'),
      confirmButtonClass: 'danger'
    };
    return this.alert.show(options, title, text);
  }
}
