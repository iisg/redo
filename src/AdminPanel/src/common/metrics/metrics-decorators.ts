import {MetricsCollector} from "./metrics-collector";

export function metricIncrement(name: string) {
  return (target: Object, propertyKey: string, descriptor: TypedPropertyDescriptor<any>) => {
    let originalMethod = descriptor.value;
    descriptor.value = function (...args: any[]) {
      MetricsCollector.increment(name);
      return originalMethod.apply(this, args);
    };
    return descriptor;
  };
}

export function metricTime(name: string) {
  return (target: Object, propertyKey: string, descriptor: TypedPropertyDescriptor<any>) => {
    let originalMethod = descriptor.value;
    descriptor.value = function (...args: any[]) {
      MetricsCollector.timeStart(name);
      let result = originalMethod.apply(this, args);
      if (result && result.finally) {
        result.finally(() => MetricsCollector.timeEnd(name));
      } else if (result && result.then) {
        result.then(() => MetricsCollector.timeEnd(name));
      } else {
        MetricsCollector.timeEnd(name);
      }
      return result;
    };
    return descriptor;
  };
}
