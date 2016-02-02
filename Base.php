<?php
namespace pdyn\datatype;

/**
 * A base datatype class.
 *
 * Datatype classes are intended to enforce a particular datatype. They provide validation and allow for type-hinting.
 */
abstract class Base {
	/** @var mixed The internal value of the datatype. */
	protected $val = 0;

	/**
	 * Get the value of the datatype.
	 *
	 * @return mixed The raw value.
	 */
	public function val() {
		return $this->val;
	}

	/**
	 * Magic method for string usage.
	 *
	 * @return string The result of the val() method.
	 */
	public function __toString() {
		return $this->val();
	}

	/**
	 * Get a protected/private property.
	 *
	 * @param string $name The name of the property.
	 * @return mixed The value of the property.
	 */
	public function __get($name) {
		return (isset($this->$name)) ? $this->$name : null;
	}

	/**
	 * Determine if a protected/private property is set.
	 *
	 * @param string $name The name of the property.
	 * @return bool Whether the property is set.
	 */
	public function __isset($name) {
		return (isset($this->$name)) ? true : false;
	}
}
