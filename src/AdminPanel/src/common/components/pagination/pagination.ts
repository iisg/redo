import {computedFrom, observable} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";

export class Pagination {
    @bindable totalNumberOfElements: number;
    @bindable(twoWay) elementsPerPage = 10;
    @bindable(twoWay) currentPageNumber = 1;
    @bindable baseNumberOfPageNumbers = 7;
    @bindable elementsPerPageDropdownOptions = [10, 100, 1000];
    @observable selectedElementsPerPageDropdownOption = this.elementsPerPage;
    maximumAdditionalPageNumbers = 0;
    numberOfAdditionalPageNumbersBeforeCurrentPageNumber = 0;
    numberOfAdditionalPageNumbersAfterCurrentPageNumber = 0;
    numberOfPages = 1;

    @bindable @booleanAttribute hideElementsPerPageDropdown: boolean;

    bind() {
        this.selectedElementsPerPageDropdownOption = this.elementsPerPage;
        this.calculateNumberOfPages();
    }

    totalNumberOfElementsChanged() {
        this.calculateNumberOfPages();
    }

    elementsPerPageChanged() {
        this.selectedElementsPerPageDropdownOption = this.elementsPerPage;
        this.calculateNumberOfPages();
    }

    calculateNumberOfPages() {
        this.numberOfPages = this.totalNumberOfElements != undefined && this.elementsPerPage ?
            Math.ceil(this.totalNumberOfElements / this.elementsPerPage) : 1;
        this.calculateMaximumAdditionalPageNumbers();
    }

    currentPageNumberChanged() {
        this.calculateMaximumAdditionalPageNumbers();
    }

    calculateMaximumAdditionalPageNumbers() {
        this.maximumAdditionalPageNumbers = this.baseNumberOfPageNumbers - 1
            - (this.currentPageNumber > 1 ? 1 : 0)
            - (this.currentPageNumber > 2 ? 1 : 0)
            - (this.currentPageNumber < this.numberOfPages - 1 ? 1 : 0)
            - (this.currentPageNumber < this.numberOfPages ? 1 : 0);
        this.numberOfAdditionalPageNumbersBeforeCurrentPageNumber = this.maximumAdditionalPageNumbers / 2;
        this.numberOfAdditionalPageNumbersAfterCurrentPageNumber = this.maximumAdditionalPageNumbers / 2;
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

    @computedFrom('currentPageNumber', 'elementsPerPage', 'numberOfAdditionalPageNumbersAfterCurrentPageNumber')
    get additionalPageNumbersBeforeCurrentPageNumber() {
        const additionalPageNumbers = this.additionalPageNumbers(2, this.currentPageNumber - 2,
            this.maximumAdditionalPageNumbers - this.numberOfAdditionalPageNumbersAfterCurrentPageNumber, true);
        this.numberOfAdditionalPageNumbersBeforeCurrentPageNumber = additionalPageNumbers.length;
        return additionalPageNumbers;
    }

    @computedFrom('currentPageNumber', 'elementsPerPage', 'numberOfAdditionalPageNumbersBeforeCurrentPageNumber')
    get additionalPageNumbersAfterCurrentPageNumber() {
        const additionalPageNumbers = this.additionalPageNumbers(this.currentPageNumber + 2, this.numberOfPages - 1,
            this.maximumAdditionalPageNumbers - this.numberOfAdditionalPageNumbersBeforeCurrentPageNumber);
        this.numberOfAdditionalPageNumbersAfterCurrentPageNumber = additionalPageNumbers.length;
        return additionalPageNumbers;
    }

    pageIdentifier(pageNumber: number, elementsPerPage: number) {
        let numberOfTheLastElementOnThePage;
        let numberOfTheFirstElementOnThePage;
        if (this.totalNumberOfElements && pageNumber === this.numberOfPages) {
            numberOfTheLastElementOnThePage = this.totalNumberOfElements;
            numberOfTheFirstElementOnThePage = (pageNumber - 1) * elementsPerPage + 1;
        } else {
            numberOfTheLastElementOnThePage = pageNumber * elementsPerPage;
            numberOfTheFirstElementOnThePage = numberOfTheLastElementOnThePage - elementsPerPage + 1;
        }
        return numberOfTheFirstElementOnThePage + '\u200E…' + numberOfTheLastElementOnThePage;
    }
}
