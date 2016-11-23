<?php
namespace Repeka\Application\Serialization;

use JMS\Serializer\JsonSerializationVisitor as JsonSerializationVisitorBase;

/**
 * @see https://github.com/schmittjoh/JMSSerializerBundle/issues/373#issuecomment-115238225
 */
class FixJsonArraySerializationVisitor extends JsonSerializationVisitorBase {
    public function getResult() {
        if ($this->getRoot() instanceof \ArrayObject) {
            $this->setRoot((array)$this->getRoot());
        }
        return parent::getResult();
    }
}
