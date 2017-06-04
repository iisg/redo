interface StringMap<T> {
  [key: string]: T;
}

interface NumberMap<T> {
  [key: number]: T;
}

interface AnyMap<T> {
  [key: string]: T;
  [key: number]: T;
}

interface StringStringMap extends StringMap<string> {
}

interface StringArrayMap extends StringMap<any[]> {
}
