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
 * A datatype class defining a database ID value.
 */
class Id extends \pdyn\datatype\Base {
	/** @var int An ID value. */
	protected $val;

	/**
	 * Constructor.
	 *
	 * @param int $val An ID value.
	 */
	public function __construct($val) {
		if (\pdyn\datatype\Id::validate($val) !== true) {
			throw new \Exception('Bad ID Received', 406);
		}
		$this->val = (int)$val;
	}

	/**
	 * Validate datatype.
	 *
	 * @param mixed $input Value to validate.
	 * @return bool Value is valid or not.
	 */
	public static function validate($input) {
		return (\pdyn\datatype\Validator::intlike($input) && (int)$input >= 0);
	}
}
