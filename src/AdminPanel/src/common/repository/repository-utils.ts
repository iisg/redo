import {deepCopy} from "../utils/object-utils";

export function propertyNamesToCamelCase(data: any): any {
  data = deepCopy(data);
  let underscorePropertyNames = Object.keys(data).filter(name => name.indexOf('_') > 0);
  for (let underscorePropertyName of underscorePropertyNames) {
    let camelCasePropertyName = underscoreToCamelCase(underscorePropertyName);
    data[camelCasePropertyName] = data[underscorePropertyName];
    delete data[underscorePropertyName];
  }
  return data;
}

// http://stackoverflow.com/a/6661013/878514
export function underscoreToCamelCase(s: string): string {
  return s.replace(/(^_+|_+$)/g, '').replace(/_+([a-z])/g, (_, letter) => letter.toUpperCase());
}
