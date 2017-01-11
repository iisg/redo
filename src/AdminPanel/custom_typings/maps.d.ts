interface StringMap<T> {
  [key: string]: T;
}

interface StringStringMap extends StringMap<string> {
}

interface StringAnyMap extends StringMap<any> {
}