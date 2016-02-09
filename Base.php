<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @copyright 2010 onwards James McQuillan (http://pdyn.net)
 * @author James McQuillan <james@pdyn.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
		return (string)$this->val();
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
