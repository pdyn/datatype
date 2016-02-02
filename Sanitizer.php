<?php
namespace pdyn\datatype;

/**
 * Sanitize values.
 */
class Sanitizer {
	/**
	 * Sanitize a filename.
	 *
	 * Specifically, this removes directory traversal via ./ and ../
	 *
	 * @param string $i The input value.
	 * @param boolean $allow_subdirs Whether to allow forward directory traversal (i.e. subdirectories).
	 * @return string The sanitized string.
	 */
	public static function filename($i, $allow_subdirs = false) {
		$replacements = ['../', './'];
		if ($allow_subdirs === false) {
			$replacements[] = '/';
		}
		return str_replace($replacements, '', $i);
	}

	/**
	 * Sanitize a file path, removing directory traversal.
	 *
	 * @param string $i The input value.
	 * @return string The sanitized value.
	 */
	public static function filepath($i) {
		$replacements = ['../', './'];
		return str_replace($replacements, '', $i);
	}

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
