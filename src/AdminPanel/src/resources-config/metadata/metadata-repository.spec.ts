import {MetadataRepository} from "./metadata-repository";
import {HttpClient} from "aurelia-http-client";
import {Metadata, MetadataConstraints} from "./metadata";
import {ResourceKind} from "../resource-kind/resource-kind";

describe(MetadataRepository.name, () => {
  let metadataRepository: MetadataRepository;
  let httpClient: HttpClient;

  beforeEach(() => {
    httpClient = new HttpClient();
    metadataRepository = new MetadataRepository(httpClient);
  });

  it("converts data object to Metadata", () => {
    let data = {
      id: 1,
      name: 'Nazwa',
      label: {"PL": "Labelka"},
      description: {"PL": "DESC"},
      placeholder: {"PL": "Placeholder"},
    };
    let metadata = metadataRepository.toEntity(data);
    expect(Metadata.prototype.isPrototypeOf(metadata)).toBeTruthy();
    expect(metadata.id).toEqual(data.id);
    expect(metadata.name).toEqual(data.name);
    expect(metadata.label).toEqual(data.label);
    expect(metadata.placeholder).toEqual(data.placeholder);
    expect(metadata.description).toEqual(data.description);
  });

  it("converts list of data to list of Metadata", done => {
    spyOn(httpClient, 'get').and.returnValue(Promise.resolve({
      content: [
        {id: 1, name: 'Nazwa 1'},
        {id: 2, name: 'Nazwa 2'},
      ],
    }));
    metadataRepository.getList().then(metadataList => {
      expect(metadataList.length).toBe(2);
      expect(Metadata.prototype.isPrototypeOf(metadataList[0])).toBeTruthy();
      expect(Metadata.prototype.isPrototypeOf(metadataList[1])).toBeTruthy();
      expect(metadataList[0].id).toBe(1);
      expect(metadataList[1].id).toBe(2);
      done();
    });
  });

  describe('relationship resourceKind constraints', () => {
    it('replaces entity constraints with IDs', () => {
      const metadata = new Metadata();
      metadata.control = 'relationship';
      const resourceKind = new ResourceKind();
      resourceKind.id = 1;
      metadata.constraints = new MetadataConstraints({resourceKind: [resourceKind]});
      const result = metadataRepository.toBackend(metadata);
      const resourceKindConstraints = result['constraints']['resourceKind'];
      expect(resourceKindConstraints.length).toBe(1);
      expect(resourceKindConstraints[0]).toBe(resourceKind.id);
    });

    it('leaves IDs in constraints unchanged', () => {
      const metadata = new Metadata();
      metadata.control = 'relationship';
      metadata.constraints = new MetadataConstraints({resourceKind: []});
      metadata.constraints = new MetadataConstraints({
        resourceKind: [
          new ResourceKind(), 123
        ]
      });
      const result = metadataRepository.toBackend(metadata);
      const resourceKindConstraints = result['constraints']['resourceKind'];
      expect(resourceKindConstraints.length).toBe(2);
      expect(resourceKindConstraints[1]).toBe(123);
    });
  });
});
