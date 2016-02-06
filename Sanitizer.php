<?php
namespace pdyn\datatype;

/**
 * Sanitize values.
 */
class Sanitizer {
	/**
	 * Sanitize a string to ensure it contains only letters and numbers.
	 *
	 * @param string $s The input string.
	 * @return string The output string.
	 */
	public static function alphanum($s) {
		return (is_string($s) || is_numeric($s))
			? preg_replace('/[^a-z0-9]+/iu', '', $s)
			: '';
	}
}
