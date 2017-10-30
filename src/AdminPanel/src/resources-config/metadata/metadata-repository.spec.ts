import {MetadataRepository} from "./metadata-repository";
import {HttpClient} from "aurelia-http-client";
import {EntitySerializer} from "common/dto/entity-serializer";
import {TypeRegistry} from "common/dto/registry";
import Spy = jasmine.Spy;

describe(MetadataRepository.name, () => {
  let httpClient: HttpClient;
  let serializer: EntitySerializer;
  let metadataRepository: MetadataRepository;

  beforeEach(() => {
    httpClient = new HttpClient();
    serializer = new EntitySerializer(new TypeRegistry(undefined));
    metadataRepository = new MetadataRepository(httpClient, serializer);
  });

  it("converts list of data to list of Metadata", done => {
    spyOn(httpClient, 'get').and.returnValue(Promise.resolve({
      content: [
        {id: 1, name: 'Nazwa 1'},
        {id: 2, name: 'Nazwa 2'},
      ],
    }));
    spyOn(serializer, 'deserialize');
    metadataRepository.getList().then(() => {
      expect((serializer.deserialize as Spy).calls.count()).toBe(2);
    }).then(done);
  });
});
