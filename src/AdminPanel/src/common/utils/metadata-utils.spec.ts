import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Metadata} from "resources-config/metadata/metadata";
import {getMergedBriefMetadata} from "./metadata-utils";

describe('metadata-utils', () => {
  describe('getMergedBriefMetadata', () => {
    function metadataMock(id: number, shownInBrief: boolean): Metadata {
      const metadata = new Metadata();
      metadata.id = id;
      metadata.shownInBrief = shownInBrief;
      return metadata;
    }

    function resourceKindMock(id: number, metadataList: Metadata[]): ResourceKind {
      const resourceKind = new ResourceKind();
      resourceKind.id = id;
      resourceKind.metadataList = metadataList;
      return resourceKind;
    }

    it('replaces non-brief metadata with brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = metadataMock(0, false);
      const metadata2 = metadataMock(1, true);
      testResourceKindList[0] = resourceKindMock(0, [metadata1, metadata2]);
      const metadata3 = metadataMock(0, true);
      testResourceKindList[1] = resourceKindMock(1, [metadata3]);
      const expectedMetadataList = [metadata3, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });

    it('does not replace brief metadata with non-brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = metadataMock(0, true);
      const metadata2 = metadataMock(1, true);
      testResourceKindList[0] = resourceKindMock(0, [metadata1, metadata2]);
      const metadata3 = metadataMock(0, false);
      testResourceKindList[1] = resourceKindMock(1, [metadata3]);
      const expectedMetadataList = [metadata1, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });
  });
});
