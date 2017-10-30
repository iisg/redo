import {autoinject} from "aurelia-dependency-injection";
import {AdvancedMapper, ArrayMapper} from "common/dto/mappers";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {AutoMapper} from "common/dto/auto-mapper";
import {maps} from "common/dto/decorators";
import {Metadata} from "../metadata/metadata";
import {TypeRegistry} from "common/dto/registry";
import {SystemMetadata} from "../metadata/system-metadata";

@autoinject
export class WorkflowIdMapper extends AdvancedMapper<Workflow> {
  constructor(private workflowRepository: WorkflowRepository, private autoMapper: AutoMapper<Workflow>) {
    super();
  }

  fromBackendProperty(key: string, dto: Object, workflow: Object): Promise<Workflow> {
    const dtoKey = key + 'Id';
    const workflowId = dto[dtoKey];
    return this.isEmpty(workflowId) ? Promise.resolve(undefined) : this.workflowRepository.get(workflowId);
  }

  toBackendProperty(key: string, workflow: Workflow, dto: Object): void {
    const dtoKey = key + 'Id';
    dto[dtoKey] = this.isEmpty(workflow) ? undefined : workflow.id;
  }

  protected clone(workflow: Workflow): Workflow {
    return this.autoMapper.nullSafeClone(workflow);
  }
}

@autoinject
@maps('Metadata[]')
class MetadataListMapper extends ArrayMapper<Metadata> {
  constructor(typeRegistry: TypeRegistry) {
    super(typeRegistry.getMapperByType(Metadata.name), typeRegistry.getFactoryByType(Metadata.name));
  }

  fromBackendValue(items: any[]): Promise<Metadata[]> {
    return super.fromBackendValue(items).then(items => [SystemMetadata.PARENT].concat(items));
  }

  toBackendValue(items: Metadata[]): any[] {
    items = items.filter(item => item.id >= 0);
    return super.toBackendValue(items);
  }
}
