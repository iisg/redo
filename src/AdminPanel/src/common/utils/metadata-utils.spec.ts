import {getMergedBriefMetadata} from "./metadata-utils";
import {Metadata} from "../../resources-config/metadata/metadata";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
describe('metadata-utils', () => {
  describe('getMergedBriefMetadata', () => {
    it('replaces non-brief metadata with brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = new Metadata();
      metadata1.baseId = 0;
      metadata1.shownInBrief = false;
      const metadata2 = new Metadata();
      metadata2.baseId = 1;
      metadata2.shownInBrief = true;
      testResourceKindList[0] = new ResourceKind();
      testResourceKindList[0].id = 0;
      testResourceKindList[0].metadataList = [metadata1, metadata2];
      const metadata3 = new Metadata();
      metadata3.baseId = 0;
      metadata3.shownInBrief = true;
      testResourceKindList[1] = new ResourceKind();
      testResourceKindList[1].id = 1;
      testResourceKindList[1].metadataList = [metadata3];
      const expectedMetadataList = [metadata3, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });
    it('does not replace brief metadata with non-brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = new Metadata();
      metadata1.baseId = 0;
      metadata1.shownInBrief = true;
      const metadata2 = new Metadata();
      metadata2.baseId = 1;
      metadata2.shownInBrief = true;
      testResourceKindList[0] = new ResourceKind();
      testResourceKindList[0].id = 0;
      testResourceKindList[0].metadataList = [metadata1, metadata2];
      const metadata3 = new Metadata();
      metadata3.baseId = 0;
      metadata3.shownInBrief = false;
      testResourceKindList[1] = new ResourceKind();
      testResourceKindList[1].id = 1;
      testResourceKindList[1].metadataList = [metadata3];
      const expectedMetadataList = [metadata1, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });
  });
});
