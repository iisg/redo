import {EventAggregator, Subscription} from "aurelia-event-aggregator";

export class DetailsViewTabs {
  private defaultTabId: string;
  private tabIds: string[] = [];
  private listeners: Subscription[] = [];

  public tabs: DetailsViewTab[] = [];
  public activeTabId: string;

  public constructor(private eventAggregator: EventAggregator, private onTabChange: () => void = () => undefined) {
  }

  public addTab(id: string, label: string | (() => string)): this {
    this.tabIds.push(id);
    const labelFactory = (typeof label === 'function' ? label : () => label) as () => string;
    this.tabs.push({id, labelFactory, label: labelFactory(), active: false});
    if (!this.defaultTabId) {
      this.setDefaultTabId(id);
    }
    this.listeners.push(this.eventAggregator.subscribe(`aurelia-plugins:tabs:tab-clicked:${id}`, () => {
      this.updateTabs(id);
      this.onTabChange();
    }));
    return this;
  }

  public setDefaultTabId(id: string): this {
    this.defaultTabId = id;
    if (!this.activeTabId) {
      this.activateTab(id);
    }
    return this;
  }

  public activateTab(id: string): this {
    if (!this.tabExists(id)) {
      id = this.defaultTabId;
    }
    const tabChanged = this.activeTabId != id;
    if (tabChanged) {
      $('#' + this.activeTabId).removeClass('active'); // It seems that aurelia-plugins-tabs itself does it only when other tab is clicked.
    }
    this.updateTabs(id);
    if (tabChanged) {
      $('#' + this.activeTabId).addClass('active'); // Because aurelia-plugins-tabs doesn't seem to do it when simply changing `tab.active`
                                                    // value and changing the whole list would be required.
      this.onTabChange();
    }
    return this;
  }

  private updateTabs(activeTabId: string) {
      this.activeTabId = activeTabId;
      this.tabs.forEach(tab => tab.active = tab.id == activeTabId);
      this.updateLabels();
  }

  public clear(): this {
    this.listeners.forEach(listener => listener.dispose());
    this.listeners = [];
    this.tabs = [];
    this.tabIds = [];
    return this;
  }

  public tabExists(tabId: string): boolean {
    return this.tabIds.indexOf(tabId) >= 0;
  }

  public updateLabels(): void {
    this.tabs.forEach(tab => tab.label = tab.labelFactory());
  }
}

interface DetailsViewTab {
  id: string;
  label: string;
  labelFactory: () => string;
  active: boolean;
}
