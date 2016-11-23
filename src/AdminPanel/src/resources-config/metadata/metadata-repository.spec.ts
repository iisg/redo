import {MetadataRepository} from "./metadata-repository";
import {HttpClient} from "aurelia-http-client";
import {Metadata} from "./metadata";

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
});
