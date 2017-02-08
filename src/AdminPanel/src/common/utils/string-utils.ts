// https://gist.github.com/gordonbrander/2230317
export function generateId(prefix = '_', length = 9) {
  return prefix + Math.random().toString(36).substr(2, length);
}
