const CACHED_VALUE_KEY = '_cachedValue';

export function cachedResponse(expirationTimeMs = 60000) {
  return (target: any, propertyName: string, descriptor: TypedPropertyDescriptor<any>) => {
    if (descriptor.value) {
      const originalMethod = descriptor.value;
      let fn = function (...args: any[]) {
        const argumentsHash = getCachedArgumentsHash(args);
        if (!fn[CACHED_VALUE_KEY][argumentsHash]) {
          fn[CACHED_VALUE_KEY][argumentsHash] = originalMethod.apply(this, args);
          setTimeout(() => clearCachedResponse(fn, argumentsHash), expirationTimeMs);
        }
        return fn[CACHED_VALUE_KEY][argumentsHash];
      };
      fn[CACHED_VALUE_KEY] = {};
      descriptor.value = fn;
      return descriptor;
    }
    else {
      throw "Only put a cachedResponse decorator on a method.";
    }
  };
}

export function getCachedArgumentsHash(args: any[]): string {
  return JSON.stringify(args);
}

export function clearCachedResponse(method, argumentsHash: string = undefined) {
  if (method[CACHED_VALUE_KEY]) {
    if (argumentsHash) {
      delete method[CACHED_VALUE_KEY][argumentsHash];
    } else {
      method[CACHED_VALUE_KEY] = {};
    }
  }
}
