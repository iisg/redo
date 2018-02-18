export class ToArrayValueConverter implements ToViewValueConverter {
  toView(object: object): any[] {
    const array = [];
    for (let key in object) {
      if (object.hasOwnProperty(key)) {
        array.push({key, value: object[key]});
      }
    }
    return array;
  }
}
