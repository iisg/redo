import {BindingEngine, Disposable} from "aurelia-binding";

export class MetadataValue {
  value: any;
  submetadata: NumberMap<MetadataValue[]> = {};

  private changeListeners: Map<() => any, Disposable> = new Map();

  constructor(value: any = undefined) {
    this.value = value;
  }

  onChange(bindingEngine: BindingEngine, callback: () => any) {
    const disposable = bindingEngine.propertyObserver(this, 'value').subscribe(callback);
    this.changeListeners.set(callback, disposable);
  }

  clearChangeListener(callback: () => any = undefined) {
    if (callback) {
      this.changeListeners.get(callback).dispose();
      this.changeListeners.delete(callback);
    } else {
      this.changeListeners.forEach(disposable => disposable.dispose());
      this.changeListeners.clear();
    }
  }
}
