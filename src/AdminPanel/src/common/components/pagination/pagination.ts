import {observable} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";

export class Pagination {
    @bindable totalNumberOfElements: number;
    @bindable(twoWay) elementsPerPage = 10;
    @bindable(twoWay) currentPageNumber = 1;
    @bindable maximumAdditionalPageNumbers = 200;
    @bindable elementsPerPageDropdownOptions = [10, 100, 1000];
    @observable selectedElementsPerPageDropdownOption = this.elementsPerPage;
    numberOfPages = 1;

    @bindable @booleanAttribute hideElementsPerPageDropdown: boolean;

    bind() {
        this.selectedElementsPerPageDropdownOption = this.elementsPerPage;
        this.calculateNumberOfPages();
    }

    elementsPerPageChanged() {
        this.selectedElementsPerPageDropdownOption = this.elementsPerPage;
        this.calculateNumberOfPages();
    }

    totalNumberOfElementsChanged() {
        this.calculateNumberOfPages();
    }

    calculateNumberOfPages() {
        this.numberOfPages = this.totalNumberOfElements != undefined && this.elementsPerPage ?
            Math.ceil(this.totalNumberOfElements / this.elementsPerPage) : 1;
    }

    selectedElementsPerPageDropdownOptionChanged(newValue: number, previousValue: number) {
        if (this.elementsPerPage != newValue) {
            let currentPageNumber = this.currentPageNumber;
            this.elementsPerPage = newValue;
            this.currentPageNumber = Math.floor((currentPageNumber - 1) * previousValue / newValue) + 1;
        }
    }

    additionalPageNumbers(from: number, to: number, maximumNumberOfElements: number, prioritizeLastElements?: boolean) {
        let length = to - from + 1;
        if (length > maximumNumberOfElements) {
        length = maximumNumberOfElements;
            if (prioritizeLastElements) {
                from = to - maximumNumberOfElements + 1;
            }
        }
        return Array.from({length: length}, (x, i) => i + from);
    }

    pageIdentifier(pageNumber: number, elementsPerPage: number) {
        let numberOfTheLastElementOnThePage = pageNumber * elementsPerPage;
        let numberOfTheFirstElementOnThePage = numberOfTheLastElementOnThePage - elementsPerPage + 1;
        return numberOfTheFirstElementOnThePage + '\u200E…' + numberOfTheLastElementOnThePage;
    }
}
