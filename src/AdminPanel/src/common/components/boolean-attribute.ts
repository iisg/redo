/**
 * Decorator for boolean attributes of components.
 * Providing an attribute without a value, eg. like this:
 *   <foo disabled>
 * Causes `disabled` to be assigned with a value of '' (empty string), which is falsy.
 * This decorator can be used together with @bindable to automatically correct these values to true:
 *   @bindable @booleanAttribute disabled: boolean;
 * It will also convert all assigned values to proper booleans.
 */
export function booleanAttribute(prototype: any, propertyName: string) {
  const methodName = propertyName + 'Changed';
  const originalMethod: ChangeHandler<boolean> = prototype[methodName] || (() => undefined);
  prototype[methodName] = function booleanAttributeChanged(newValue: boolean|'', oldValue: boolean) {
    // this prototype-level assignment won't trigger @bindable's setter, so xxxChanged() won't fire again
    this[propertyName] = normalizeToBoolean(newValue);
    originalMethod.apply(this, [this[propertyName], normalizeToBoolean(oldValue)]);
  };
}

function normalizeToBoolean(value: boolean|''): boolean {
  return (value === '') ? true : !!value;
}

type ChangeHandler<T> = (newValue: T, oldValue: T) => void;
