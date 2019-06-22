// https://gist.github.com/gordonbrander/2230317
export function generateId(prefix = '_', length = 9) {
  return prefix + Math.random().toString(36).substr(2, length);
}

// https://stackoverflow.com/a/9251169/1937994
export function stringToHtml(str: string): string {
  const $e: JQuery = $('<textarea>');
  $e.text(str);
  return $e.html().replace(/[\n\r]/g, '<br>');
}

export function normalizeEntityName(name: string): string {
  return name.replace(/([a-z])([A-Z])/g, '$1 $2').replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
}
