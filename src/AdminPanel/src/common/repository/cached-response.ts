const CACHED_VALUE_KEY = '_cachedValue';

export function cachedResponse(expirationTimeMs = 60000) {
  return (target: any, propertyName: string, descriptor: TypedPropertyDescriptor<any>) => {
    if (descriptor.value) {
      const originalMethod = descriptor.value;
      let fn = function (...args: any[]) {
        if (!fn[CACHED_VALUE_KEY]) {
          fn[CACHED_VALUE_KEY] = originalMethod.apply(this, args);
          setTimeout(() => clearCachedResponse(fn), expirationTimeMs);
        }
        return fn[CACHED_VALUE_KEY];
      };
      descriptor.value = fn;
      return descriptor;
    }
    else {
      throw "Only put a cachedResponse decorator on a method.";
    }
  };
}

export function clearCachedResponse(method) {
  delete method[CACHED_VALUE_KEY];
}