<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Domain;

use Repeka\CoreModule\Domain\Assert\Assertion;

final class EmailAddress {
    /**
     * @var string
     */
    private $address;

    public function __construct(string $address) {
        $this->isValidEmailAddress($address);
        $this->address = $address;
    }

    private function isValidEmailAddress(string $address) {
        Assertion::email($address, sprintf("Email address: %s is invalid", $address));
    }

    public function __toString() : string {
        return $this->address;
    }

    public function getDomainFromEmailAddress() : string {
        return explode('@', $this->address)[1];
    }

    public function isInDomain(string $domain) : bool {
        return preg_match('#' . $domain . '$#', $this->getDomainFromEmailAddress()) === 1;
    }
}