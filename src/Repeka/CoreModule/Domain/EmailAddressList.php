<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Domain;

use Countable;
use Iterator;

final class EmailAddressList implements Countable, Iterator {
    /**
     * @var EmailAddress[]
     */
    private $list = [];
    /**
     * @var int
     */
    private $currentIndex = 0;

    public function add(EmailAddress $emailAddress) {
        $this->list[] = $emailAddress;
    }

    public function remove(EmailAddress $emailAddress) {
        $key = array_search($emailAddress, $this->list, true);
        if ($key !== false) {
            unset($this->list[$key]);
            $this->list = array_values($this->list);
        }
    }

    public function count() : int {
        return count($this->list);
    }

    public function current() : EmailAddress {
        return $this->list[$this->currentIndex];
    }

    public function next() {
        $this->currentIndex++;
    }

    public function key() : int {
        return $this->currentIndex;
    }

    public function valid() : bool {
        return isset($this->list[$this->currentIndex]);
    }

    public function rewind() {
        $this->currentIndex = 0;
    }
}
