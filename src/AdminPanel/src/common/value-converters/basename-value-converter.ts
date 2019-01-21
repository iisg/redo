export class BasenameValueConverter implements ToViewValueConverter {
  toView(filePath: string): string {
    if (!filePath) {
      return filePath;
    }
    const parts = filePath.split(/[\\\/]/g);
    return parts[parts.length - 1];
  }
}
