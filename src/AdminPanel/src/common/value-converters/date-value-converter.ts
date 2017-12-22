import * as moment from "moment";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DateValueConverter implements ToViewValueConverter {
  toView(modelValue: any): string {
    return moment(modelValue).format('L');
  }
}
