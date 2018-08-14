import {autoinject} from 'aurelia-dependency-injection';
import {BindingEngine, Disposable} from 'aurelia-binding';
import {I18N} from 'aurelia-i18n';
import {twoWay} from "../binding-mode";
import {bindable, ComponentAttached} from "aurelia-templating";
import * as $ from "jquery";
import "jquery.fancytree/dist/modules/jquery.fancytree.glyph";
import {removeValue} from 'common/utils/array-utils';

@autoinject
export class TreeView implements ComponentAttached {
  @bindable(twoWay) selectedKeys: string[];
  @bindable loadSubtree: ((obj: {request: LoadSubtreeRequest}) => Promise<TreeItem>);

  tree: Element;
  searchValue: string = '';
  private lastSearchedValue: string = '';
  private selectedKeysSubscription: Disposable;
  private initialized: boolean = false;
  private fancytree: Fancytree.Fancytree;
  searchPending: boolean = false;

  constructor(private bindingEngine: BindingEngine, private i18n: I18N) {
  }

  attached() {
    this.fancytree = this.createTree();
    this.subscribeSelectedKeys();
    this.initialized = true;
  }

  detached() {
    this.disposeSelectedKeysSubscription();
  }

  private createTree(): Fancytree.Fancytree {
    const options = this.treeOptions();
    $(this.tree).fancytree(options);
    return $(this.tree).fancytree("getTree");
  }

  private treeOptions(): FancytreeOptionsExtended {
    return {
      // do not activate paging node when using arrows on keyboard
      autoActivate: false,
      checkbox: true,
      icon: false,
      debugLevel: 0,
      source: this.loadSubtree({request: {pagination: {page: 1}}})
        .then((treeRoot: TreeItem) => {
          this.processItemsRecursive(treeRoot);
          return treeRoot.children;
        }),
      lazyLoad: (event: JQueryEventObject, data: Fancytree.EventData) => {
        data.result = this.lazyLoad(data.node);
      },
      types: {
        paging: {},
        loadingPagePlaceholder: {}
      },
      select: (event: JQueryEventObject, data: Fancytree.EventData) => this.onNodeSelect(data.node),
      strings: {
        loading: this.i18n.tr("Loading") + '...',
        loadError: this.i18n.tr('Load error!')
      },
      activate: (event: JQueryEventObject, data: Fancytree.EventData) => {
        if ((data.node as any).type === "paging") {
          this.onPagingNodeActivate(data.node);
        } else {
          data.node.toggleSelected();
          data.node.setActive(false);
        }
      },
    };
  }

  searchKeys(rootKey: string|undefined) {
    this.searchPending = true;
    this.lastSearchedValue = this.searchValue;
    const request: LoadSubtreeRequest = {rootKey, pagination: {page: 1}, term: this.lastSearchedValue};
    this.loadSubtree({request})
      .then((subtreeRoot: TreeItem) => {
        this.processItemsRecursive(subtreeRoot);
        return subtreeRoot;
      })
      .then(subtreeRoot => {
        const node: Fancytree.FancytreeNode = !rootKey
          ? this.fancytree.getRootNode()
          : this.fancytree.getNodeByKey(subtreeRoot.key);
        node.removeChildren();
        node.addChildren(subtreeRoot.children);
      })
      .then(_ => this.searchPending = false);
  }

  private lazyLoad(node: Fancytree.FancytreeNode) {
    const request: LoadSubtreeRequest = {rootKey: node.key, pagination: {page: 1}, term: this.lastSearchedValue};
    return this.loadSubtree({request})
      .then((subtree: TreeItem) => {
        node.data.pagination = subtree.childrenPagination;
        this.processItemsRecursive(subtree);
        return subtree.children;
      });
  }

  private onNodeSelect(node: Fancytree.FancytreeNode) {
    if (node.isSelected()) {
      if (!this.selectedKeys.includes(node.key)) {
        this.selectedKeys.push(node.key);
      }
    } else {
      removeValue(this.selectedKeys, node.key);
    }
  }

  private onPagingNodeActivate(node: Fancytree.FancytreeNode) {
    const page = node.data.nextPage;
    const parentNode = node.parent;
    const isTopLevel = parentNode.parent == undefined;
    const rootKey = isTopLevel ? undefined : node.parent.key;

    Promise.resolve(parentNode.removeChild(node))
      .then(_ => parentNode.addNode(this.createPagePlaceholderNode(parentNode.key, page)))
      .then(placeholderNode =>
        this.loadSubtree({request: {rootKey, pagination: {page}, term: this.lastSearchedValue}})
          .then(subtree => ({placeholderNode, subtree}))
      )
      .then(({placeholderNode, subtree}) => {
        this.processItemsRecursive(subtree);
        return {placeholderNode, subtree};
      })
      .then(({placeholderNode, subtree}) => {
        parentNode.removeChild(placeholderNode);
        parentNode.addChildren(subtree.children as Fancytree.NodeData[]);
    });
  }

  private processItemsRecursive(item: TreeItem & Fancytree.NodeData) {
    if (item.selectable) {
      item.selected = this.selectedKeys.includes(item.key);
    } else {
      item.checkbox = false;
    }
    if (!item.childrenPagination || !item.children) {
      return;
    }
    if (item.childrenPagination.more) {
      item.children.push(this.createPagingNode(item.key, item.childrenPagination.page));
    }
    item.children.forEach(child => this.processItemsRecursive(child));
  }

  private setKeysSelected(keys: string[], flag: boolean) {
    keys.map(key => this.fancytree.getNodeByKey(key))
      .filter(node => !!node)
      .forEach(node => node.setSelected(flag));
  }

  private createPagingNode(parentKey, page): any {
    return {
      title: this.i18n.tr('Load more'),
      key: `paging_${parentKey}_${page}`,
      nextPage: page + 1,
      parentKey,
      type: "paging"
    };
  }

  private createPagePlaceholderNode(parentKey, page): any {
    return {
      title: this.i18n.tr('Loading') + '...',
      key: `page_placeholder_${parentKey}_${page}`,
      type: "loadingPagePlaceholder",
      checkbox: false
    };
  }

  private subscribeSelectedKeys() {
    this.disposeSelectedKeysSubscription();
    this.selectedKeysSubscription = this.bindingEngine
      .collectionObserver(this.selectedKeys)
      .subscribe((changes) => this.selectedKeysModified(changes));
  }

  private disposeSelectedKeysSubscription(): void {
    if (this.selectedKeysSubscription !== undefined) {
      this.selectedKeysSubscription.dispose();
      this.selectedKeysSubscription = undefined;
    }
  }

  selectedKeysChanged(newSelectedKeys: string[]) {
    this.subscribeSelectedKeys();
    if (!this.initialized) {
      return;
    }
    const previouslySelected: Fancytree.FancytreeNode[] = this.fancytree.getSelectedNodes();
    previouslySelected.forEach(node => node.setSelected(false));
    this.setKeysSelected(newSelectedKeys, true);
  }

  private selectedKeysModified(changes: any[]) {
    changes.forEach(change => {
      if (change.addedCount > 0) {
        const addedKeys = this.selectedKeys.slice(change.index, change.index + change.addedCount);
        this.setKeysSelected(addedKeys, true);
      }
      this.setKeysSelected(change.removed, false);
    });
  }
}

interface FancytreeOptionsExtended extends Fancytree.FancytreeOptions,
  Fancytree.NodeTypesExtensionOptions, Fancytree.GlyphExtensionOptions {
}

export interface TreeItem {
  title: string;
  key: string;
  children?: TreeItem[];
  childrenPagination?: {page: number, more: boolean};
  lazy?: boolean;
  expanded?: boolean;
  selectable?: boolean;
  checkbox?: any;
}

export interface LoadSubtreeRequest {
  rootKey?: string;
  pagination: {page: number};
  term?: string;
}