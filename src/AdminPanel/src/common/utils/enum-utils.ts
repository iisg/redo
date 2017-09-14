// assumes enum values are sequential and zero-based
export function successor<T>(state: T, enumType: any): T {
  const stateIndex = state as any as number;
  const nextStateIndex = stateIndex + 1;
  const next = (nextStateIndex in enumType) ? nextStateIndex : 0;
  return next as any as T;
}
