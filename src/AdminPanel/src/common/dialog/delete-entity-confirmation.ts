import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Alert, AlertOptions} from "./alert";
import {MultilingualText} from "../../resources-config/metadata/metadata";
import {InCurrentLanguageValueConverter} from "../../resources-config/multilingual-field/in-current-language";
import {isObject} from "../utils/object-utils";

@autoinject
export class DeleteEntityConfirmation {
  constructor(private alert: Alert, private i18n: I18N, private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  confirm(entityName: string, caption: number | string | MultilingualText): Promise<string> {
    if (Number.isInteger(caption)) {
      caption = `#${caption}`;
    } else if (isObject(caption)) {
      caption = this.inCurrentLanguage.toView(caption as MultilingualText);
    }
    const translatedEntityType = this.i18n.tr('entity_types::' + entityName, {context: 'accusative'});
    const title = this.i18n.tr('Confirm deletion');
    const text = this.i18n.tr('Are you sure you want to delete {{entityType}} {{caption}}?', {entity: translatedEntityType, caption});
    const options: AlertOptions = {
      type: 'question',
      confirmButtonText: this.i18n.tr('Delete'),
      confirmButtonClass: 'danger'
    };
    return this.alert.show(options, title, text);
  }
}
