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
 * Class for dealing wth plain text.
 */
class Text extends \pdyn\datatype\Base {
	/** @var string The current text being worked with. */
	protected $val = '';

	/**
	 * Constructor.
	 *
	 * @param string $text The text you want to work with.
	 */
	public function __construct($text) {
		$this->val = (string)$text;
	}

	/**
	 * Extracts hashtags from text.
	 *
	 * @param bool $pad_for_ft_search If using the output for mysql fulltext search, use this to pad
	 *                                short hashtags to meet the minimum search length.
	 * @return array Array of found hashtags.
	 */
	public function extract_hashtags($pad_for_ft_search=false) {
		preg_match_all('@(#[a-z0-9]+)@iu', $this->val, $matches);
		foreach ($matches[1] as $i => $tag) {
			$matches[1][$i] = mb_strtolower($tag);
		}
		if ($pad_for_ft_search === true) {
			$tags = [];
			foreach ($matches[1] as $i => $tag) {
				$tags[] = (mb_strlen($tag) < 6)
					? str_pad($tag, 6, '-', STR_PAD_RIGHT)
					: $tag;
			}
			return $tags;
		} else {
			return $matches[1];
		}
	}

	/**
	 * Sanitize the text for display.
	 *
	 * @param bool $striptags If true, will remove all html tags. If false, will just htmlentities() them.
	 */
	public function sanitize($striptags = true) {
		if (!is_numeric($this->val)) {
			if ($striptags === true) {
				$this->val = htmlspecialchars(strip_tags($this->val), ENT_QUOTES, 'UTF-8', false);
			} else {
				$this->val = htmlentities($this->val, ENT_HTML401, 'UTF-8', false);
			}
		}
	}

	/**
	 * Truncate the text to a specific display length.
	 *
	 * Note: This decodes HTML entities so they are not affected.
	 *
	 * @param int $length The length to truncate to.
	 */
	public function truncate($length) {
		$this->val = html_entity_decode($this->val, ENT_QUOTES, 'UTF-8');
		if (mb_strlen($this->val) > $length) {
			$this->val = mb_substr($this->val, 0, $length).'...';
		}
	}

	/**
	 * Remove all whitespace from the string.
	 */
	public function remove_whitespace() {
		$needle = ["\n", "\r", "\t", "\x0B", "\0", ' '];
		for ($i = 0; $i <= 31; $i++) {
			$needle[] = html_entity_decode('&#'.str_pad($i, 2, '0', STR_PAD_LEFT).';'); //add ASCII meta characters
		}
		$replace = '';
		$this->val = str_replace($needle, $replace, $this->val);
	}

	/**
	 * Generate a color based on a string.
	 *
	 * @return array Array of color components.
	 */
	public function generate_color($hex = false) {
		$max_intensity = 200;

		$colors = abs(crc32($this->val));
		$colors = mb_substr($colors, 0, 9);
		$colors = str_pad($colors, 9, '5');

		// Shuffle.
		$colorsparts = [
			$colors[0].$colors[3].$colors[6],
			$colors[1].$colors[4].$colors[7],
			$colors[2].$colors[5].$colors[8],
		];

		$colors = implode('', $colorsparts);

		$bgcolor = [
			'r' => (int)round((mb_substr($colors, 3, 3) / 1000) * 255),
			'g' => (int)round((mb_substr($colors, 0, 3) / 1000) * 255),
			'b' => (int)round((mb_substr($colors, 6, 3) / 1000) * 255),
		];

		foreach ($bgcolor as $c => $val) {
			if ($val > $max_intensity) {
				$bgcolor[$c] = $max_intensity;
			}
		}

		if ($hex === true) {
			$output = str_pad(dechex($bgcolor['r']), 2, '0', STR_PAD_LEFT);
			$output .= str_pad(dechex($bgcolor['g']), 2, '0', STR_PAD_LEFT);
			$output .= str_pad(dechex($bgcolor['b']), 2, '0', STR_PAD_LEFT);
			return $output;
		} else {
			return $bgcolor;
		}
	}

	/**
	 * Force UTF-8 encoding on a string.
	 *
	 * @param string $s A string of questionable encoding.
	 * @return string A UTF-8 string.
	 */
	public static function force_utf8($s) {
		$encoding = mb_detect_encoding($s);
		if ($encoding === 'UTF-8') {
			return $s;
		} else {
			if (empty($encoding)) {
				$encoding = 'ISO-8859-1,ASCII';
			}
			return mb_convert_encoding($s, 'UTF-8', $encoding);
		}
	}

	/**
	 * Force all items in an array into UTF-8.
	 *
	 * @param array $ar Array to convert.
	 * @return array Converted array.
	 */
	public static function force_utf8_array(array $ar) {
		$arutf8 = [];
		foreach ($ar as $k => $v) {
			$kutf8 = static::force_utf8($k);
			if (is_array($v)) {
				$arutf8[$kutf8] = static::force_utf8_array($v);
			} elseif (is_string($v)) {
				$arutf8[$kutf8] = static::force_utf8($v);
			} else {
				$arutf8[$kutf8] = $v;
			}
			unset($ar[$k]);
		}
		return $arutf8;
	}

	/**
	 * Force a value to UTF-8, then serialize.
	 *
	 * This is used before storing serialized values in the database. Since our DbDrivers convert all strings to UTF-8,
	 * they can damage seralized data. For example, if non-utf8 text is contained in a serialized array, the offset recorded in the
	 * serialized string may be not reflect the length after conversion to utf8.
	 *
	 * @param mixed $input A value to force to UTF-8 then serialize.
	 * @return string A serialized UTF-8 value.
	 */
	public static function utf8safe_serialize($input) {
		if (is_array($input)) {
			return serialize(\pdyn\datatype\Text::force_utf8_array($input));
		} elseif (is_string($input)) {
			return serialize(\pdyn\datatype\Text::force_utf8($input));
		} else {
			return serialize($input);
		}
	}

	/**
	 * Generates a url-safe representation of string $s. Useful for generating URLs from strings (posts, pages, albums, etc)
	 * Makes the following changes:
	 *     Removed common TLDs because they look weird when converted
	 *     Removes quotes and periods.
	 *     Converts any non-alphanumeric character to an underscore.
	 *     Converts string to lowercase
	 *     Removed starting and ending underscores.
	 *
	 * @param string $s The incoming string
	 * @return string The transformed string
	 */
	public static function make_slug($s) {
		$reserved = ['me', 'type'];
		if (in_array($s, $reserved, true)) {
			$s .= '_1';
		}

		// Remove common TLDs because they look weird when you get something like CNN.com => cnncom :/
		$s = preg_replace('/\.com|\.org|\.net /iu', '', $s);

		// Remove quotes first because it's dumb to have ex. "Joe's" convert to "joe_s", "joes" is better :)
		$s = preg_replace('/[\'\".]+/iu', '', $s);

		$s = preg_replace('/[^a-z0-9]+/iu', '_', $s);
		$s = mb_strtolower($s);
		$s = trim($s, '_');
		return $s;
	}
}
