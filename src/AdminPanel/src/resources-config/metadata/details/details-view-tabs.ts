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
      this.setActiveTabId(id);
      this.onTabChange();
    }));
    return this;
  }

  public setDefaultTabId(tabId: string): this {
    this.defaultTabId = tabId;
    if (!this.activeTabId) {
      this.setActiveTabId(tabId);
    }
    return this;
  }

  public setActiveTabId(activeTabId: string): this {
    this.tabs.forEach(tab => tab.active = false);
    const requestedTabId = activeTabId;
    if (!this.tabExists(activeTabId)) {
      activeTabId = this.defaultTabId;
    }
    this.tabs.find(tab => tab.id == activeTabId).active = true;
    this.activeTabId = activeTabId;
    if (requestedTabId && requestedTabId != activeTabId) {
      this.onTabChange();
    }
    this.updateLabels();
    return this;
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
