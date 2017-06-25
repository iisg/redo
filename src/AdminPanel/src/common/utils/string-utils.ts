// https://gist.github.com/gordonbrander/2230317
export function generateId(prefix = '_', length = 9) {
  return prefix + Math.random().toString(36).substr(2, length);
}

export function firstLineWithContent(str: string): string {
  const lines = str
    .split(/[\r\n]+/g)
    .map(str => str.trim())
    .filter(str => str.length > 0);
  return lines.concat('')[0];
}

export function trimToLengthBetweenWords(str: string, length: number): string {
  str = str.trim().replace(/\s+/g, ' ');
  if (str.length <= length) {
    return str;
  }
  const hardCutStr = str.substr(0, length);
  const lastSpaceIndex = hardCutStr.lastIndexOf(' ');
  return (lastSpaceIndex == -1)
    ? hardCutStr.substr(0, length - 1) + '…'
    : hardCutStr.substr(0, lastSpaceIndex) + '…';
}
