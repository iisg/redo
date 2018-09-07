import * as moment from "moment";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DateValueConverter implements ToViewValueConverter {
  toView(modelValue: any, format: string = 'DD.MM.YYYY'): string {
    return moment(modelValue).format(format);
  }
}
