import {ViewMode} from "../../../../../custom_typings/eonasdan-bootstrap-datetimepicker";

export enum DateMode {
  YEAR = 'year',
  MONTH = 'month',
  DAY = 'day',
  DATE_TIME = 'date_time',
  RANGE = 'range'
}

export const inputDateConfig = {
  [DateMode.YEAR]: {
    options: {
      allowInputToggle: true,
      useCurrent: false,
      viewMode: 'years' as ViewMode,
      format: 'YYYY'
    },
    format: 'YYYY',
  },
  [DateMode.MONTH]: {
    options: {
      allowInputToggle: true,
      useCurrent: false,
      viewMode: 'months' as ViewMode,
      format: 'MM.YYYY'
    },
    format: 'MM.YYYY',
  },
  [DateMode.DAY]: {
    options: {
      allowInputToggle: true,
      useCurrent: false,
      viewMode: 'days' as ViewMode,
      format: 'DD.MM.YYYY'
    },
    format: 'DD.MM.YYYY',
  },
  [DateMode.DATE_TIME]: {
    options: {
      allowInputToggle: true,
      useCurrent: false,
      format: 'DD.MM.YYYY, HH:mm:ss'
    },
    format: 'DD.MM.YYYY, HH:mm:ss',
  },
};

export class FlexibleDateContent {
  mode: DateMode;
  rangeMode: DateMode;
  from: string;
  to: string;
  displayValue: string;
}
