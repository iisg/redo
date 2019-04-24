import {automapped, map} from "common/dto/decorators";
import {User} from "users/user";
import {IdentityMapper} from "common/dto/mappers";

@automapped
export class AuditEntry {
  static NAME = 'AuditEntry';

  @map id: number;
  @map commandName: string;
  @map successful: boolean;
  @map data: StringMap<any>;
  @map('Date') createdAt: Date;
  @map(IdentityMapper) user: User;
  @map customColumns: StringStringMap;
}
