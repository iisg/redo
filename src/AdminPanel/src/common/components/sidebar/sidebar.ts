import {observable} from "aurelia-binding";

export class Sidebar {
    private static readonly COLLAPSED_SIDEBAR_KEY = 'collapsedSidebar';

    @observable collapsed: boolean;

    constructor() {
        try {
            this.collapsed = localStorage[Sidebar.COLLAPSED_SIDEBAR_KEY] || false;
        } catch (exception) {}
    }

    collapsedChanged(newValue: boolean, oldValue: boolean) {
        if (oldValue != undefined) {
            try {
                newValue ? localStorage[Sidebar.COLLAPSED_SIDEBAR_KEY] = newValue
                : localStorage.removeItem(Sidebar.COLLAPSED_SIDEBAR_KEY);
            } catch (exception) {}
        }
    }
}
