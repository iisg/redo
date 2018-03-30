import {automapped, map} from "../../../common/dto/decorators";
import {CopyMapper} from "../../../common/dto/mappers";

@automapped
export class WorkflowPlugin {
  static NAME = 'WorkflowPlugin';

  @map name: string;
  @map(CopyMapper) configurationOptions: WorkflowPluginConfigurationOption[];
}

export interface WorkflowPluginConfigurationOption {
  id: string;
  control: string;
}
