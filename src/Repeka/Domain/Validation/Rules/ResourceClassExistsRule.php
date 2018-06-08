<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ResourceClassExistsRule extends AbstractRule {
    /** @var string[] */
    private $resourceClasses;

    public function __construct(array $resourceClasses) {
        $this->resourceClasses = $resourceClasses;
    }

    public function validate($input): bool {
        return Validator::in($this->resourceClasses)->validate($input);
    }
}
