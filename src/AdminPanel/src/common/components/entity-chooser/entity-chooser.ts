import {autoinject} from "aurelia-framework";
import {bindable, customElement, useView} from "aurelia-templating";
import {DropdownSelect} from "common/components/dropdown-select/dropdown-select";
import {fromView} from "../binding-mode";

@useView('common/components/dropdown-select/dropdown-select.html')
@customElement('entity-chooser')
@autoinject
export class EntityChooser extends DropdownSelect {
  @bindable({changeHandler: 'updateValues'}) entities: Entity[];
  @bindable({changeHandler: 'updateValues'}) filter: (entity: Entity) => boolean;
  @bindable(fromView) updateValues = () => {
    if (this.entities && this.filter) {
      this.values = this.entities.filter(this.filter);
    } else {
      this.values = this.entities;
    }
  }

  valueChanged() {
    if (this.filter) {
      this.updateValues();
    } else {
      super.valueChanged();
    }
  }
}

type Entity = { id: number };
