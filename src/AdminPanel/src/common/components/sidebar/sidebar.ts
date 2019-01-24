import {observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {LocalStorage} from "common/utils/local-storage";
import {I18nParams} from "config/i18n";

@autoinject
export class Sidebar {
  title: string;

  private static readonly COLLAPSED_SIDEBAR_KEY = 'collapsedSidebar';

  @observable collapsed: boolean;

  constructor(i18n: I18N, i18nParams: I18nParams) {
    this.collapsed = LocalStorage.get(Sidebar.COLLAPSED_SIDEBAR_KEY) || false;
    this.title = i18n.tr("adminPanelName", {applicationName: i18nParams.applicationName});
  }

  collapsedChanged(newValue: boolean, oldValue: boolean) {
    if (oldValue != undefined) {
      newValue ? LocalStorage.set(Sidebar.COLLAPSED_SIDEBAR_KEY, newValue)
        : LocalStorage.remove(Sidebar.COLLAPSED_SIDEBAR_KEY);
    }
  }
}
