export class BasenameValueConverter implements ToViewValueConverter {
  toView(filePath: string): string {
    const parts = filePath.split(/[\\\/]/g);
    return parts[parts.length - 1];
  }
}
