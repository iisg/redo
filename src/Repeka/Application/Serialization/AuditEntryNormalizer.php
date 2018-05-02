<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class AuditEntryNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    /**
     * @param $entry AuditEntry
     * @inheritdoc
     */
    public function normalize($entry, $format = null, array $context = []) {
        $data = [
            'id' => $entry->getId(),
            'commandName' => $entry->getCommandName(),
            'user' => $this->normalizer->normalize($entry->getUser()),
            'data' => $this->emptyArrayAsObject($entry->getData()),
            'createdAt' => $this->normalizer->normalize($entry->getCreatedAt()),
            'successful' => $entry->isSuccessful(),
        ];
        if (isset($context['customColumns']) && @count($context['customColumns'])) {
            $data['customColumns'] = $this->evaluateCustomColumns($entry, $context['customColumns']);
        }
        return $data;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof AuditEntry;
    }

    private function evaluateCustomColumns(AuditEntry $entry, array $customColumns) {
        $data = $entry->getData();
        if (isset($data['resource'])) {
            $evaluated = [];
            $contents = ResourceContents::fromArray($data['resource']['contents']);
            foreach ($customColumns as $displayStrategy) {
                $evaluated[$displayStrategy] = $this->displayStrategyEvaluator->render($contents, $displayStrategy);
            }
            return $evaluated;
        } else {
            return [];
        }
    }
}
