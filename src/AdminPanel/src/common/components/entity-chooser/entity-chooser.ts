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
  };
  private valueUpdated: boolean;
  private notFoundEntity: Entity;

  entitiesChanged() {
    this.notFoundEntity = undefined;
    this.selectEquivalentEntity();
    this.updateValues();
    if (this.valueUpdated && !this.filter) {
      super.valueChanged();
    }
  }

  valueChanged(newValue?: Entity, previousValue?: Entity) {
    if (!this.valueUpdated) {
      const entityFoundOrNotYetVerified = !this.notFoundEntity || (this.value && (this.value as Entity).id) != this.notFoundEntity.id;
      const entityNotYetSelected = !newValue || !previousValue || newValue.id != previousValue.id;
      if (entityFoundOrNotYetVerified && entityNotYetSelected) {
        this.selectEquivalentEntity();
        if (this.filter) {
          this.updateValues();
        } else {
          super.valueChanged();
        }
      }
    } else {
      this.valueUpdated = false;
    }
  }

  private selectEquivalentEntity() {
    if (this.value && this.entities) {
      const value = this.entities.find(entity => entity.id == (this.value as Entity).id);
      if (!value) {
        this.notFoundEntity = this.value as Entity;
      }
      this.valueUpdated = this.value != value;
      this.value = value;
    }
  }
}

type Entity = { id: number };
