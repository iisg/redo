export class BlobToUrlValueConverter implements ToViewValueConverter {
  toView(blob) {
    let file = new File([blob], 'filename', {type: blob.type, lastModified: blob.lastModifiedDate});
    return URL.createObjectURL(file);
  }
}
