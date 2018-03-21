import * as moment from "moment";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DateValueConverter implements ToViewValueConverter {
  toView(modelValue: any, format: string = 'L'): string {
    return moment(modelValue).format(format);
  }
}
