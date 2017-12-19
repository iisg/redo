<?php
namespace Repeka\Domain\XmlImport\Mapping;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\XmlImport\Expression\ValueExpression;

class Mapping {
    /** @var Metadata */
    private $metadata;
    /** @var string */
    private $cssSelector;
    /** @var ValueExpression */
    private $expression;
    /** @var string */
    private $configKey;

    public function __construct(Metadata $metadata, string $cssSelector, ValueExpression $expression, string $configKey) {
        $this->metadata = $metadata;
        $this->cssSelector = $cssSelector;
        $this->expression = $expression;
        $this->configKey = $configKey;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }

    public function getCssSelector(): string {
        return $this->cssSelector;
    }

    public function getExpression(): ValueExpression {
        return $this->expression;
    }

    public function getConfigKey(): string {
        return $this->configKey;
    }
}
