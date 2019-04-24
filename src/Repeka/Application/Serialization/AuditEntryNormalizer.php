<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\UseCase\Audit\AuditEntryCustomColumnsEvaluator;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class AuditEntryNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var AuditEntryCustomColumnsEvaluator */
    private $auditEntryCustomColumnsEvaluator;

    public function __construct(AuditEntryCustomColumnsEvaluator $auditEntryCustomColumnsEvaluator) {
        $this->auditEntryCustomColumnsEvaluator = $auditEntryCustomColumnsEvaluator;
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
            $data['customColumns'] = $this->auditEntryCustomColumnsEvaluator->evaluateCustomColumns($entry, $context['customColumns']);
        }
        return $data;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof AuditEntry;
    }
}
