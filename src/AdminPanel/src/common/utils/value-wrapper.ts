import {BindingEngine, Disposable} from "aurelia-binding";

export class ValueWrapper<T> {
  value: T;

  private subscriptions: Disposable[] = [];

  onChange(bindingEngine: BindingEngine, callback: (newValue: T, oldValue: T) => void): void {
    const subscription = bindingEngine.propertyObserver(this, 'value').subscribe(callback);
    this.subscriptions.push(subscription);
  }

  cancelChangeSubscriptions(): void {
    this.subscriptions.forEach(subscription => subscription.dispose());
    this.subscriptions = [];
  }
}
