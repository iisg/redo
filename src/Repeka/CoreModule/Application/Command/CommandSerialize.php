<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Application\Command;

trait CommandSerialize {
    final public function serialize() : string {
        return serialize(get_object_vars($this));
    }

    final public function unserialize($serialized) {
        $data = $this->unserialize($serialized);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
}
