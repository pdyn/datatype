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
 * Validates different datatypes.
 */
class Validator {

	/**
	 * Validate a value is a positive integer.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid positive integer or not.
	 */
	public static function pos_int($input) {
		return (static::intlike($input) && (int)$input >= 0);
	}

	/**
	 * Validate a value is an empty string.
	 *
	 * @param mixed $str The value to validate.
	 * @return bool Whether the value is an empty string or not.
	 */
	public static function empty_str($str) {
		if (!is_string($str)) {
			return false;
		}
		$str = str_replace('&nbsp;', ' ', $str);
		return (trim($str) === '') ? true : false;
	}

	/**
	 * Validate a value is a string containing only letters.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a string containing only letters.
	 */
	public static function alpha($input) {
		return ctype_alpha($input);
	}

	/**
	 * Validate a value is a timestamp.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid timestamp or not.
	 */
	public static function timestamp($input) {
		return (static::intlike($input) && $input > 0) ? true : false;
	}

	/**
	 * Validate a value is a filename.
	 *
	 * @param mixed $filename The value to validate.
	 * @return bool Whether the value is a valid filename or not.
	 */
	public static function filename($filename) {
		//basically will approve any string as long as there's no directory traversal - ../, /, etc
		if (is_string($filename) && mb_strpos($filename, '..') === false && mb_strpos($filename, '/') === false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Validate a value is an email address.
	 *
	 * @param mixed $input The value to validate.
	 * @param bool $do_dns_check Whether to also validate the domain exists.
	 * @return bool Whether the value is a valid email address or not.
	 */
	public static function email($input, $do_dns_check = false) {
		$valid = false;
		if (is_string($input) && filter_var($input, FILTER_VALIDATE_EMAIL) !== false) {
			$valid = true;
		}
		if ($valid === true && $do_dns_check === true) {
			$domain = mb_substr($input, (mb_strrpos($input, '@') + 1));
			if (!(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 * Validate a value is a float.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid float or not.
	 */
	public static function float($input) {
		return is_numeric($input);
	}

	/**
	 * Validate a value is a boolean value.
	 *
	 * Note: This returns true for boolean-like values, ex (string)"1", (string)"0", (int)1, (int)0.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid boolean or not.
	 */
	public static function bool($input) {
		return self::boollike($input);
	}

	/**
	 * Validate a value is a boolean value.
	 *
	 * Note: This returns true for boolean-like values, ex (string)"1", (string)"0", (int)1, (int)0.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid boolean or not.
	 */
	public static function boollike($input) {
		if (is_bool($input) || $input === '0' || $input === '1' || $input === 0 || $input === 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Validate a value is a scalar string-like value. This can include ints and floats, but not bools.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid string-like value or not.
	 */
	public static function stringlike($input) {
		return (is_string($input) || is_numeric($input));
	}


	/**
	 * Validate a value is a boolean integer, i.e. 1 or 0.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid boolean integer or not.
	 */
	public static function boolint($input) {
		return ($input === 0 || $input === 1);
	}

	/**
	 * Validate a value is an integer-like value.
	 *
	 * Note: This will validate strings as long as the string contains only an integer.
	 * Ex. (string)"1" will validate, (string)"1cat" will not.
	 *
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid integer-like value or not.
	 */
	public static function intlike($input) {
		try {
			return ($input !== true && (string)(int)$input === (string)$input);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Validate a value is a mime type.
	 *
	 * @param mixed $input The value to validate.
	 * @param string $validate_against_type Validate against a certain file category. Currently "image" is supported.
	 * @return bool Whether the value is a valid mime type or not.
	 */
	public static function mime($input, $validate_against_type='') {
		$valid_types = ['application', 'audio', 'image', 'message', 'multipart', 'text', 'video'];

		if (mb_stripos($input, '/') === false) {
			//malformed mime type
			return false;
		}

		$iparts = explode('/', $input);
		if (count($iparts) !== 2) {
			//malformed mime type
			return false;
		}
		$type = $iparts[0];
		$subtype = $iparts[1];

		if (!in_array($type, $valid_types, true)) {
			//bad type portion of mime type
			return false;
		}

		if (empty($validate_against_type)) {
			//well formed mime type, if we're not validating against a specific type, return valid;
			return true;
		}

		if ($validate_against_type === 'image') {
			$valid_subtypes_image = ['gif', 'jpeg', 'pjpeg', 'png', 'svg+xml', 'tiff', 'vnd.microsoft.icon'];
			if ($type !== 'image') {
				//mime type does not have an image type
				return false;
			}
			//validate subtype
			if (!in_array($subtype, $valid_subtypes_image, true)) {
				//subtype was not a valid image subtype.
				return false;
			}
			return true; //passed tests, valid image mimetype
		}
	}

	/**
	 * Validate a value is a date.
	 *
	 * @param string $f The date format to validate against.
	 * @param mixed $input The value to validate.
	 * @return bool Whether the value is a valid date or not.
	 */
	public static function date($f, $input) {
		if ($f === 'YYYYMMDD') {
			// Test input type.
			if (!is_string($input) && !is_int($input)) {
				return false;
			}

			// Test length.
			$strlen = mb_strlen($input);
			if ($strlen !== 8) {
				return false;
			}

			// Verify all numbers.
			if (is_string($input)) {
				if ((string)(int)$input !== $input) {
					return false;
				}
			}

			// Year - we'll accept any 4 digit year.
			$y = (int)mb_substr($input, 0, 4);

			// Test month.
			$m = (int)mb_substr($input, 4, 2);
			if ($m < 1 || $m > 12) {
				return false;
			}

			// Test day.
			$d = (int)mb_substr($input, 6, 2);
			if ($d < 1 || $d > 31) {
				return false;
			}

			return true;
		}
	}
}
