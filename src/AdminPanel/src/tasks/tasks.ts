import {autoinject} from "aurelia-dependency-injection";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {TaskRepository} from "./task-repository";
import {TaskCollection, TaskStatus} from "./task-collection";
import {ResourcesListFilters} from "resources/list/resources-list-filters";
import {PageResult} from "resources/page-result";
import {Resource} from "resources/resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {TaskCollectionsQuery} from "tasks/task-collection-query";
import {unique} from "common/utils/array-utils";
import {Router} from "aurelia-router";
import {getQueryParameters} from "common/utils/url-utils";
import {LocalStorage} from "common/utils/local-storage";
import {traverseDoubleMap, updateDoubleMapValue} from "common/utils/object-utils";
import {Alert} from "common/dialog/alert";
import {I18N} from "aurelia-i18n";

@autoinject
export class Tasks {
  resourceClasses: string[] = [];
  resourceKindsByClass: StringMap<ResourceKind[]> = {};
  fetching: boolean = true;
  resultsPerPage = 10;
  tasksDoubleMap: StringMap<StringMap<TaskList>> = {};
  filtersDoubleMap: StringMap<StringMap<ResourcesListFilters>> = {};

  constructor(private taskRepository: TaskRepository,
              private resourceKindRepository: ResourceKindRepository,
              private router: Router,
              private alert: Alert,
              private i18n: I18N) {
  }

  bind() {
    this.synchronizeLocalStorageAndUrl();
    this.fetchTasks(this.filtersDoubleMap, 'all')
      .then((taskCollections: TaskCollection[]) => {
        this.resourceClasses = unique(taskCollections.map(collection => collection.resourceClass));
      })
      .then(async () => {
        for (const resourceClass of this.resourceClasses) {
          const resourceKinds = await this.resourceKindRepository.getListQuery().filterByResourceClasses(resourceClass).get();
          this.resourceKindsByClass[resourceClass] = resourceKinds;
        }
      })
      .finally(() => this.fetching = false);
  }

  private synchronizeLocalStorageAndUrl() {
    const urlFiltersDoubleMap = this.getAllUrlFilters();
    let localStorageFiltersDoubleMap = this.getAllLocalStorageFilters();
    traverseDoubleMap(urlFiltersDoubleMap, (resourceClass, taskStatus, filter) => {
      const localStorageFilter = {};
      if (filter.sortBy) {
        localStorageFilter['sortBy'] = filter.sortBy;
      }
      if (filter.resultsPerPage) {
        localStorageFilter['resultsPerPage'] = filter.resultsPerPage;
      }
      localStorageFiltersDoubleMap = this.assignFilters(
        localStorageFiltersDoubleMap, localStorageFilter as ResourcesListFilters, resourceClass, taskStatus as TaskStatus);
      this.filtersDoubleMap = this.assignFilters(this.filtersDoubleMap, filter, resourceClass, taskStatus as TaskStatus);
    });
    LocalStorage.set('task-filters', localStorageFiltersDoubleMap);
    traverseDoubleMap(localStorageFiltersDoubleMap, (resourceClass, taskStatus, filter) => {
      this.filtersDoubleMap = this.assignFilters(this.filtersDoubleMap, filter, resourceClass, taskStatus as TaskStatus);
    });
    this.replaceFiltersInUrl(this.filtersDoubleMap);
  }

  filtersChanged([resourceClass, taskStatus]: [string, TaskStatus], filters: ResourcesListFilters) {
    this.tasksDoubleMap[resourceClass][taskStatus].fetching = true;
    Promise.resolve(this.updateFilters(resourceClass, taskStatus, filters))
      .then(() => this.fetchTasks({[resourceClass]: {[taskStatus]: filters}}, 'queried'))
      .finally(() => this.tasksDoubleMap[resourceClass][taskStatus].fetching = false);
  }

  fetchTasks(filtersDoubleMap: StringMap<StringMap<ResourcesListFilters>>, getCollections: 'all' | 'queried') {
    let query = this.taskRepository.getCollectionsQuery();
    if (getCollections === 'queried') {
      query = query.onlyQueriedCollections();
    }
    traverseDoubleMap(filtersDoubleMap, (resourceClass, taskStatus, filters) => {
      const collectionQuery = this.createCollectionQuery(filters);
      query.addSingleCollectionQuery(resourceClass, taskStatus as TaskStatus, collectionQuery);
    });
    return query.get().then((taskCollections: TaskCollection[]) => {
      for (const taskCollection of taskCollections) {
        const filters = this.getFilters(taskCollection.resourceClass, taskCollection.taskStatus);
        filters.currentPage = taskCollection.tasks.page;
        updateDoubleMapValue(this.tasksDoubleMap, taskCollection.resourceClass, taskCollection.taskStatus, (taskList: TaskList) => ({
          resources: taskCollection.tasks,
          filters: filters,
          fetching: taskList && taskList.fetching || false,
        }));
      }
      return taskCollections;
    })
      .catch(() => {
        const title = this.i18n.tr("Invalid request");
        const text = this.i18n.tr("The searched phrase is incorrect");
        this.alert.show({type: 'error'}, title, text);
      });
  }

  private createCollectionQuery(filters: ResourcesListFilters) {
    let query = TaskCollectionsQuery.getSingleCollectionQuery();
    if (filters.contents && Object.values(filters.contents).find(value => value != undefined)) {
      query = query.filterByContents(filters.contents);
    }
    if (filters.places && filters.places.length) {
      query = query.filterByWorkflowPlacesIds(filters.places);
    }
    if (filters.kindIds && filters.kindIds.length) {
      query = query.filterByResourceKindIds(filters.kindIds);
    }
    query = query.sortByMetadataIds(filters.sortBy)
      .setResultsPerPage(filters.resultsPerPage)
      .setCurrentPageNumber(filters.currentPage);
    return query;
  }

  private getFilters(resourceClass: string, taskStatus: TaskStatus): ResourcesListFilters {
    return this.filtersDoubleMap[resourceClass] && this.filtersDoubleMap[resourceClass][taskStatus] || new ResourcesListFilters();
  }

  private getAllUrlFilters(): StringMap<StringMap<ResourcesListFilters>> {
    return JSON.parse(getQueryParameters(this.router).filters || '{}');
  }

  private getAllLocalStorageFilters(): StringMap<StringMap<ResourcesListFilters>> {
    return LocalStorage.get('task-filters') || {};
  }

  private updateFilters(resourceClass: string, taskStatus: TaskStatus, filters: ResourcesListFilters) {
    const urlFiltersDoubleMap = this.getAllUrlFilters();
    this.replaceFiltersInUrl(this.assignFilters(urlFiltersDoubleMap, filters, resourceClass, taskStatus));
    const localStorageFilters = this.getAllLocalStorageFilters();
    LocalStorage.set('task-filters', this.assignFilters(localStorageFilters, {
      resultsPerPage: filters.resultsPerPage,
      sortBy: filters.sortBy
    } as ResourcesListFilters, resourceClass, taskStatus));
    this.tasksDoubleMap[resourceClass][taskStatus].filters = filters;
  }

  private assignFilters(filters: StringMap<StringMap<ResourcesListFilters>>,
                        filter: ResourcesListFilters,
                        resourceClass: string,
                        taskStatus: TaskStatus): StringMap<StringMap<ResourcesListFilters>> {
    return updateDoubleMapValue(filters, resourceClass, taskStatus, (existingFilters: ResourcesListFilters) =>
      Object.assign(existingFilters || {}, filter)
    );
  }

  private replaceFiltersInUrl(filters: StringMap<StringMap<ResourcesListFilters>>) {
    const params = getQueryParameters(this.router);
    params.filters = JSON.stringify(filters);
    this.router.navigateToRoute('tasks', params, {trigger: false, replace: true});
  }
}

interface TaskList {
  resources: PageResult<Resource>;
  filters: ResourcesListFilters;
  fetching: boolean;
}
