// missing helper called when unknown variable has been used: https://stackoverflow.com/a/25631909/878514
export function helperMissing() {
  const options = arguments[arguments.length - 1];
  return '{{' + options.name + '}}';
}

export function oneValue(metadata, whichOne = 0) {
  const index = typeof whichOne == 'number' ? whichOne : 0;
  if (metadata) {
    if (metadata[index]) {
      return metadata[index].value;
    } else if (metadata.value) {
      return metadata.value;
    }
  }
  return '';
}

export function allValues(metadata, separator) {
  if (Array.isArray(metadata)) {
    const sep = typeof separator == 'string' ? separator : ', ';
    return metadata.map(oneValue).join(sep);
  }
  return '';
}
