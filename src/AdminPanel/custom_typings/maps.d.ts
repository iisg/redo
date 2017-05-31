interface StringMap<T> {
  [key: string]: T;
}

interface AnyMap<T> {
  [key: string]: T;
  [key: number]: T;
}

interface StringStringMap extends StringMap<string> {
}

interface StringAnyMap extends StringMap<any> {
}
