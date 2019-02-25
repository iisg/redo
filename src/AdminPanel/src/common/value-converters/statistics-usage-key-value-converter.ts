import {FrontendConfig} from "../../config/FrontendConfig";
import {InCurrentLanguageValueConverter} from "../../resources-config/multilingual-field/in-current-language";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class StatisticsUsageKeyValueConverter implements ToViewValueConverter {
  constructor(private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(usageKey: string): string {
    if (!usageKey) {
      return usageKey;
    }
    const statisticsConfig = FrontendConfig.get('statistics') as any[];
    const config = statisticsConfig.filter(value => value['usageKey'] === usageKey);
    return config.length
      ? this.inCurrentLanguage.toView(config[0]['label'])
      : usageKey;
  }
}
