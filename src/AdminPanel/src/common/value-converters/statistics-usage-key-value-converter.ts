import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class StatisticsUsageKeyValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(usageKey: string): string {
    if (!usageKey) {
      return usageKey;
    }
    return this.i18n.tr(`statistics_labels::${usageKey}`);
  }
}
