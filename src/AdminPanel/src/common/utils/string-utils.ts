// https://gist.github.com/gordonbrander/2230317
export function generateId() {
  return '_' + Math.random().toString(36).substr(2, 9);
}
