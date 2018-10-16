import {ResourceKindsList} from "./resource-kinds-list";

export class ResourceKindsListPage {
    resourceKindsList: ResourceKindsList;
    private parameters: any;

    activate(parameters: any) {
        this.parameters = parameters;
        if (this.resourceKindsList) {
            this.bind();
        }
    }

    bind() {
        this.resourceKindsList.activate(this.parameters);
    }
}
