// https://gist.github.com/thomseddon/3511330
export class FilesizeValueConverter implements ToViewValueConverter {
  toView(bytes: any, precision: number = 1): string {
    if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) {
      return '-';
    }
    if (bytes == 0) {
      return '0 B';
    }
    const units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
    const unit = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, Math.floor(unit))).toFixed(precision) + ' ' + units[unit];
  }
}
