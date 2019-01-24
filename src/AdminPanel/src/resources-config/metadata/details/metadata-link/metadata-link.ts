import {bindable} from "aurelia-templating";
import {MetadataRepository} from "../../metadata-repository";
import {cachedResponse, forSeconds} from "../../../../common/repository/cached-response";
import {Metadata} from "../../metadata";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class MetadataLink {
  @bindable id: number;
  metadata: Metadata;
  loading: boolean = false;

  constructor(private metadataRepository: MetadataRepository) {
  }

  idChanged(): void {
    if (this.id) {
      this.loading = true;
      this.fetchMetadata(this.id)
        .then(metadata => this.metadata = metadata)
        .finally(() => this.loading = false);
    }
  }

  @cachedResponse(forSeconds(30))
  private fetchMetadata(id: number): Promise<Metadata> {
    return this.metadataRepository.get(id, true);
  }
}
