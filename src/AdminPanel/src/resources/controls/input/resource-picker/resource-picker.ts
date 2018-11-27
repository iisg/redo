import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {observable, BindingEngine, Disposable} from "aurelia-binding";
import {twoWay} from "common/components/binding-mode";
import {SystemMetadata} from 'resources-config/metadata/system-metadata';
import {MetadataValue} from './../../../metadata-value';
import {ResourceTreeQuery} from './../../../resource-tree-query';
import {ResourceRepository} from "resources/resource-repository";
import {Resource} from "resources/resource";
import {ResourceLabelValueConverter} from './../../../details/resource-label-value-converter';
import {deepCopy, isObject} from "../../../../common/utils/object-utils";
import {LoadSubtreeRequest, TreeItem} from '../../../../common/components/tree-view/tree-view';
import {remove, debounce} from "lodash";
import {Alert} from "../../../../common/dialog/alert";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourcePicker implements ComponentAttached, ComponentDetached {
  @bindable(twoWay) resourceIds: MetadataValue[];
  @bindable resourceKindIds: number[] = [];
  @bindable contentsFilter: NumberMap<string> = {};
  @bindable resourceClass: string;
  @bindable disabled: boolean = false;

  @observable selectedKeys: string[] = [];

  private selectedKeysSubscription: Disposable;
  private resourceListSubscription: Disposable;

  private readonly TOP_LEVEL_RESULTS = 8;
  private readonly DEPTH = 1;
  private readonly SIBLINGS = 4;

  constructor(private resourceRepository: ResourceRepository,
              private resourceLabelValueConverter: ResourceLabelValueConverter,
              private bindingEngine: BindingEngine,
              private alert: Alert,
              private i18n: I18N) {
  }

  attached() {
    this.subscribeSelectedKeys();
    this.subscribeResourceIds();
    this.updateKeysToValues();
  }

  detached() {
    this.disposeResourceListSubscription();
    this.disposeSelectedKeysSubscription();
  }

  private subscribeSelectedKeys() {
    this.disposeSelectedKeysSubscription();
    this.selectedKeysSubscription = this.bindingEngine
      .collectionObserver(this.selectedKeys)
      .subscribe(changes => this.selectedKeysModified(changes));
  }

  private disposeSelectedKeysSubscription(): void {
    if (this.selectedKeysSubscription !== undefined) {
      this.selectedKeysSubscription.dispose();
      this.selectedKeysSubscription = undefined;
    }
  }

  private subscribeResourceIds() {
    this.disposeResourceListSubscription();
    this.resourceListSubscription = this.bindingEngine
      .collectionObserver(this.resourceIds)
      .subscribe(changes => this.resourceIdsModified(changes));
  }

  private disposeResourceListSubscription() {
    if (this.resourceListSubscription !== undefined) {
      this.resourceListSubscription.dispose();
      this.resourceListSubscription = undefined;
    }
  }

  loadSubtree(request: LoadSubtreeRequest): Promise<TreeItem> {
    const isSearching = !!request.term;
    const rootId = request.rootKey && +request.rootKey || undefined;
    const contentsFilter = isObject(this.contentsFilter) ? deepCopy(this.contentsFilter) : {};
    const resultsPerPage = rootId && this.SIBLINGS || this.TOP_LEVEL_RESULTS;
    const query = this.createQuery(rootId, request.pagination.page, request.term, contentsFilter, resultsPerPage);
    return query.get()
    .then(tree => {
      const items = this.treeify(tree.tree, rootId).sort((a, b) => +a.key - +b.key);
      const rootItem: TreeItem = {
        children: items,
        title: 'root',
        key: request.rootKey || 'root',
        childrenPagination: {more: false, page: request.pagination.page}
      };
      this.processItem(rootItem, tree.matching, isSearching, this.SIBLINGS, resultsPerPage, isSearching ? Infinity : this.DEPTH);
      return rootItem;
    }).catch(() => {
        const title = this.i18n.tr("Invalid request");
        const text = this.i18n.tr("The searched phrase is incorrect");
        this.alert.show({type: 'error'}, title, text);
        return {} as TreeItem;
      });
  }

  private createQuery(rootId, page, term, contentsFilter, resultsPerPage): ResourceTreeQuery {
    let query = this.resourceRepository.getTreeQuery();
    if (this.resourceKindIds.length > 0) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    if (this.resourceClass) {
      query.filterByResourceClasses(this.resourceClass);
    }
    query.filterBySiblingsNumber(this.SIBLINGS)
      .setCurrentPageNumber(page)
      .setResultsPerPage(resultsPerPage)
      .oneMoreElements();
    if (rootId) {
      query.forRootId(rootId);
    }
    if (term) {
      contentsFilter[SystemMetadata.RESOURCE_LABEL.id] = term;
    } else {
      query.includeWithinDepth(this.DEPTH + 1);
    }
    query.filterByContents(contentsFilter);
    return query;
  }

  private treeify(resources: Resource[], rootId?: number): TreeItem[] {
    const firstLevelItems: TreeItem[] = [];
    const lookup: {[id: string]: TreeItem} = {};

    for (const resource of resources) {
      const parentId = this.getParentId(resource);
      if (!lookup.hasOwnProperty(resource.id)) {
        lookup[resource.id] = this.emptyItem() as TreeItem;
      }
      lookup[resource.id].title = this.resourceToTitle(resource);
      lookup[resource.id].key = '' + resource.id;

      const item = lookup[resource.id];
      if (parentId === rootId) {
        firstLevelItems.push(item);
      } else {
        if (!lookup.hasOwnProperty(parentId)) {
          lookup[parentId] = this.emptyItem() as TreeItem;
        }
        lookup[parentId].children.push(item);
      }
    }
    return firstLevelItems;
  }

  private emptyItem() {
    return {
      children: [],
      lazy: false,
      childrenPagination: {more: false, page: 1},
      expanded: false
    };
  }

  selectedKeysModified(changes) {
    changes.forEach(change => {
      if (change.addedCount > 0) {
        const addedKeys = this.selectedKeys.slice(change.index, change.index + change.addedCount);
        const alreadyAddedKeys = this.resourceIds.map(value => '' + value.value).filter(key => addedKeys.includes(key));
        addedKeys.filter(key => !alreadyAddedKeys.includes(key)).forEach(key => this.resourceIds.push(new MetadataValue(+key)));
      }
      remove(this.resourceIds, (value => change.removed.includes('' + value.value)));
    });
  }

  resourceIdsModified(changes) {
    if (this.selectedKeys.length !== this.resourceIds.length) {
      this.updateKeysToValues();
    }
  }

  private updateKeysToValues() {
    this.selectedKeys.splice(0, this.selectedKeys.length);
    const keys = this.resourceIds.map(value => '' + value.value);
    this.selectedKeys.push(...keys);
  }

  private processItem(item: TreeItem, matching: Array<Number>,
                      expand: boolean, maxSiblings: number, maxInFirstLevel: number, maxDepth: number,
                      curDepth: number = 0) {
    if (matching.includes(+item.key)) {
      item.selectable = true;
    }
    if (expand) {
      item.expanded = true;
    }
    if (!item.children) {
      return;
    }
    if (curDepth >= maxDepth) {
      if (item.children.length > 0) {
        item.lazy = true;
      }
      item.children = undefined;
      return;
    }
    // remove resource with highest id when query returned more resources than requested,
    // indicating there's more resources to load
    item.children.sort((a, b) => +a.key - +b.key);
    if (item.children.length > maxSiblings && curDepth > 0 || item.children.length > maxInFirstLevel) {
      item.children.pop();
      item.childrenPagination.more = true;
    }
    item.children.forEach(child => {
      this.processItem(child, matching, expand, maxSiblings, maxInFirstLevel, maxDepth, curDepth + 1);
    });
  }

  private getParentId(resource: Resource): number|undefined {
    const parentMetadata = resource.contents[SystemMetadata.PARENT.id];
    return parentMetadata && parentMetadata[0] && parentMetadata[0].value || undefined;
  }

  private resourceToTitle(resource: Resource) {
    return this.resourceLabelValueConverter.toView(resource);
  }
}
