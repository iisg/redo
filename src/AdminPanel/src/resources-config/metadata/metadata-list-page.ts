import {MetadataList} from "./metadata-list";

export class MetadataListPage {
    metadataList: MetadataList;
    private parameters: any;

    activate(parameters: any) {
        this.parameters = parameters;
        if (this.metadataList) {
            this.bind();
        }
    }

    bind() {
        this.metadataList.activate(this.parameters);
    }
}
