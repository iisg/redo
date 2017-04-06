// https://gist.github.com/yrezgui/5653591#gistcomment-1226799
export class SizeFormatValueConverter {
  toView(size: number, precision: number = 0) {
    let units = [
      'bytes',
      'KB',
      'MB',
      'GB',
      'TB'
    ];

    if (isNaN(size) || !isFinite(size) || precision < 0) {
      return '';
    }

    let unit = 0;
    while (size >= 1024) {
      size /= 1024;
      unit++;
    }

    return size.toFixed(+precision) + ' ' + units[unit];
  }
}
