<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Domain;

use Ramsey\Uuid\Uuid as BaseUUID;
use Repeka\CoreModule\Domain\Assert\Assertion;

class UUID {
    const VALID_PATTERN = '/' . BaseUUID::VALID_PATTERN . '/';
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id) {
        Assertion::regex($id, self::VALID_PATTERN, 'Invalid UUID format');
        $this->id = $id;
    }

    public function __toString() : string {
        return $this->id;
    }
}
