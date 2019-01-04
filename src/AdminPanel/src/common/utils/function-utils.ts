export function noop(): void {
}

export type VoidFunction = () => void;

/**
 * @see https://stackoverflow.com/a/35228455
 */
export function debouncePromise(inner, ms = 0) {
  let timer = undefined;
  let resolves = [];

  return function (...args) {
    clearTimeout(timer);
    timer = setTimeout(() => {
      let result = inner(...args);
      resolves.forEach(r => r(result));
      resolves = [];
    }, ms);

    return new Promise(r => resolves.push(r));
  };
}
