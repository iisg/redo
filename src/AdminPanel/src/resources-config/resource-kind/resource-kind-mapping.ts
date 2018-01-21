import {autoinject} from "aurelia-dependency-injection";
import {AdvancedMapper, ArrayMapper} from "common/dto/mappers";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {AutoMapper} from "common/dto/auto-mapper";
import {maps} from "common/dto/decorators";
import {Metadata} from "../metadata/metadata";
import {TypeRegistry} from "common/dto/registry";
import {SystemMetadata} from "../metadata/system-metadata";
import {ResourceKind} from "./resource-kind";

@autoinject
@maps('WorkflowId')
export class WorkflowIdMapper extends AdvancedMapper<Workflow> {
  constructor(private workflowRepository: WorkflowRepository, private autoMapper: AutoMapper<Workflow>) {
    super();
  }

  fromBackendProperty(key: string, dto: Object, resourceKind: Object): Promise<Workflow> {
    const workflowId = dto[key + 'Id'];
    return this.isEmpty(workflowId) ? Promise.resolve(undefined) : this.workflowRepository.get(workflowId);
  }

  toBackendProperty(key: string, resourceKind: ResourceKind, dto: Object): void {
    const workflow = resourceKind.workflow;
    dto[key + 'Id'] = this.isEmpty(workflow) ? undefined : workflow.id;
  }

  protected clone(workflow: Workflow): Workflow {
    return this.autoMapper.nullSafeClone(workflow);
  }
}

@autoinject
@maps('Metadata[]')
class MetadataListMapper extends ArrayMapper<Metadata> {
  constructor(typeRegistry: TypeRegistry) {
    super(typeRegistry.getMapperByType(Metadata.NAME), typeRegistry.getFactoryByType(Metadata.NAME));
  }

  fromBackendValue(items: any[]): Promise<Metadata[]> {
    return super.fromBackendValue(items).then(items => {
      const parentMetadata = items.filter(v => v.baseId === SystemMetadata.PARENT.baseId);
      return !parentMetadata.length ? [SystemMetadata.PARENT].concat(items) : items;
    });
  }

  toBackendValue(items: Metadata[]): any[] {
    return super.toBackendValue(items);
  }
}
