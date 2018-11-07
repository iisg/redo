import {bindable} from "aurelia-templating";

export class SearchBar {
  @bindable displayAsSmaller = false;
  @bindable phrase = '';
  @bindable url = '/search/%s';
  @bindable advancedSearchUrl = '/search';
  @bindable metadataSubsets;
  @bindable selectedMetadataSubsetIds = '*';

  navigateToSearchUrl() {
    if (this.phrase) {
      let targetUrl = this.url.replace("%s", this.phrase);
      if (this.selectedMetadataSubsetIds != '*') {
        targetUrl += '?metadataSubset=' + this.selectedMetadataSubsetIds;
      }
      window.location.assign(targetUrl);
    }
  }

  metadataSubsetsChanged() {
    if (!this.metadataSubsets.find(option => option.ids == '*')) {
      this.metadataSubsets.unshift({ids: '*', label: 'Wszędzie'});
    }
  }
}
