<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Entity\HasResourceClass;

/**
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
abstract class ResourceClassAwareCommand extends AbstractCommand implements HasResourceClass {
    /** @var string */
    protected $resourceClass;

    /** @param HasResourceClass|string $subject */
    public function __construct($subject) {
        $this->resourceClass = $subject instanceof HasResourceClass ? $subject->getResourceClass() : $subject;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
