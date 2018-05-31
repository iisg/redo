import {EventAggregator, Subscription} from "aurelia-event-aggregator";

export class DetailsViewTabs {
  private defaultTabId: string;
  private tabIds: string[] = [];
  private listeners: Subscription[] = [];

  public tabs: DetailsViewTab[] = [];
  public activeTabId: string;

  public constructor(private ea: EventAggregator, private onTabChange: () => void = () => undefined) {
  }

  public addTab: (tab: DetailsViewTab) => this = (tab: DetailsViewTab) => {
    this.tabIds.push(tab.id);
    this.tabs.push(tab);
    if (!this.defaultTabId) {
      this.setDefaultTabId(tab.id);
    }
    this.listeners.push(this.ea.subscribe(`aurelia-plugins:tabs:tab-clicked:${tab.id}`, () => {
      this.setActiveTabId(tab.id);
      this.onTabChange();
    }));
    return this;
  };

  public setDefaultTabId: (tabId: string) => this = (tabId: string) => {
    this.defaultTabId = tabId;
    if (!this.activeTabId) {
      this.setActiveTabId(tabId);
    }
    return this;
  };

  public setActiveTabId: (activeTabId: string) => this = (activeTabId: string) => {
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
    return this;
  };

  public clear: () => this = () => {
    this.listeners.forEach(listener => listener.dispose());
    this.listeners = [];
    this.tabs = [];
    this.tabIds = [];
    return this;
  };

  public tabExists = (tabId: string) => {
    return this.tabIds.indexOf(tabId) >= 0;
  };
}

export interface DetailsViewTab {
  id: string;
  label: string;
  active?: boolean;
}
