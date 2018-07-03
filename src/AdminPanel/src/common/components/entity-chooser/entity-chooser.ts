import {autoinject} from "aurelia-framework";
import {bindable, customElement, useView} from "aurelia-templating";
import {DropdownSelect} from "common/components/dropdown-select/dropdown-select";
import {fromView} from "../binding-mode";

@useView('common/components/dropdown-select/dropdown-select.html')
@customElement('entity-chooser')
@autoinject
export class EntityChooser extends DropdownSelect {
  @bindable entities: Entity[];
  @bindable({changeHandler: 'updateValues'}) filter: (entity: Entity) => boolean;
  @bindable(fromView) updateValues = () => {
    if (this.entities && this.filter) {
      this.values = this.entities.filter(this.filter);
    } else {
      this.values = this.entities;
    }
  }
  private valueUpdated: boolean;

  entitiesChanged() {
    this.selectEquivalentEntity();
    this.updateValues();
    if (this.valueUpdated && !this.filter) {
      super.valueChanged();
    }
  }

  valueChanged() {
    if (!this.valueUpdated) {
      this.selectEquivalentEntity();
      if (this.filter) {
        this.updateValues();
      } else {
        super.valueChanged();
      }
    } else {
      this.valueUpdated = false;
    }
  }

  private selectEquivalentEntity() {
    if (this.entities && this.value) {
      this.valueUpdated = true;
      this.value = this.entities.find(entity => entity.id == (this.value as Entity).id);
    }
  }
}

type Entity = { id: number };
