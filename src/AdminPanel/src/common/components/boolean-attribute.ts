import {noop} from "common/utils/function-utils";

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
  const originalMethod: ChangeHandler<boolean> = prototype[methodName] || noop;
  prototype[methodName] = function booleanAttributeChanged(newValue: boolean | '', oldValue: boolean) {
    // 1. If developer's original change handler doesn't expect any arguments, they may want to call it without them. (see tests)
    //    If that's the case, we don't want to accidentally set value to false.
    // 2. We also want to avoid updating value if developer calls change handler manually with a non-current value.
    if (newValue == this[propertyName]) {
      // this prototype-level assignment won't trigger @bindable's setter, so xxxChanged() won't fire again
      this[propertyName] = normalizeToBoolean(newValue);
    }
    originalMethod.apply(this, [this[propertyName], normalizeToBoolean(oldValue)]);
  };
}

function normalizeToBoolean(value: boolean | ''): boolean {
  return (value === '') ? true : !!value;
}

type ChangeHandler<T> = (newValue: T, oldValue: T) => void;
