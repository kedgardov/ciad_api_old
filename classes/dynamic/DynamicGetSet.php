<?php

trait DynamicGetSet {
    public function __call($name, $arguments) {
        if (preg_match('/^get(.+)/', $name, $matches)) {
            $property = lcfirst($matches[1]);
            if (property_exists($this, $property)) {
                return $this->$property;
            }
            throw new Exception("Property $property does not exist");
        } elseif (preg_match('/^set(.+)/', $name, $matches)) {
            $property = lcfirst($matches[1]);
            if (property_exists($this, $property)) {
                $this->$property = htmlspecialchars($arguments[0], ENT_QUOTES, 'UTF-8');
                return;
            }
            throw new Exception("Property $property does not exist");
        }
        throw new BadMethodCallException("Method $name does not exist");
    }
}
?>
