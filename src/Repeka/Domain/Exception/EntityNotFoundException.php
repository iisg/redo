<?php
namespace Repeka\Domain\Exception;

use Doctrine\ORM\EntityRepository;

class EntityNotFoundException extends DomainException {
    /**
     * @param string|EntityRepository $entityNameOrRepository
     * @param mixed $query
     */
    public function __construct($entityNameOrRepository, $query, \Exception $previous = null) {
        $entityName = $this->getEntityName($entityNameOrRepository);
        $formattedQuery = $this->getFormattedQuery($query);
        parent::__construct("Not found: $entityName $formattedQuery", $previous);
        $this->setCode(404);
    }

    private function getEntityName($entityNameOrRepository): string {
        if (is_string($entityNameOrRepository)) {
            $className = $entityNameOrRepository;
        } else {
            $className = (new \ReflectionClass($entityNameOrRepository))->getShortName();
        }
        $entityName = preg_replace('/(Doctrine)?Repository$/', '', $className);
        return $entityName;
    }

    private function getFormattedQuery($query): string {
        if (is_string($query)) {
            return '"' . $query . '"';
        } elseif (is_int($query)) {
            return "#$query";
        } else {
            return (string) $query;
        }
    }
}
