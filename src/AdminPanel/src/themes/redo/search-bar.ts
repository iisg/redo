import {bindable} from "aurelia-templating";

export class SearchBar {
  @bindable displayAsSmaller = false;
  @bindable phrase = '';
  @bindable url = '/search/%s';
  @bindable advancedSearchUrl = '/advanced-search';
  @bindable metadataSubsets = [];
  @bindable selectedMetadataSubset = {ids: '*'};

  navigateToSearchUrl() {
    let targetUrl = this.url.replace("%s", this.phrase);
    if (this.selectedMetadataSubset && this.selectedMetadataSubset.ids != '*') {
      targetUrl += '?metadataSubset=' + this.selectedMetadataSubset.ids;
    }
    window.location.assign(targetUrl);
  }

  metadataSubsetsChanged() {
    const allOption = this.metadataSubsets.find(option => option.ids == '*');
    if (!allOption) {
      this.metadataSubsets.unshift({ids: '*', label: 'Wszędzie'});
    }
  }

  metadataSubsetComparator(a, b) {
    return a.ids == b.ids;
  }
}
