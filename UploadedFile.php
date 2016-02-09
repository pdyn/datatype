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
 * A datatype class defining an uploaded file.
 */
class UploadedFile extends \pdyn\datatype\Base {
	/** @var string The stored name of the file. */
	protected $stored_filename = '';

	/** @var string The original name of the file. */
	protected $orig_filename = '';

	/**
	 * Constructor.
	 *
	 * @param string $stored_filename The stored name of the file.
	 * @param string $orig_filename The original name of the file.
	 */
	public function __construct($stored_filename, $orig_filename = '') {
		if (\pdyn\datatype\Validator::filename($stored_filename) !== true) {
			throw new \Exception('Invalid filename received', 400);
		}
		if (!empty($orig_filename) && !is_scalar($orig_filename)) {
			throw new \Exception('Uploaded file orig filename must be scalar or empty', 400);
		}
		$this->stored_filename = \pdyn\filesystem\FilesystemUtils::sanitize_filename($stored_filename);
		$this->orig_filename = \pdyn\filesystem\FilesystemUtils::sanitize_filename($orig_filename);
	}

	/**
	 * Analyze the file for MIME type.
	 *
	 * @return string The mime type.
	 */
	public function get_analyzed_mimetype() {
		return \pdyn\filesystem\FilesystemUtils::get_mime_type($this->stored_filename);
	}

	/**
	 * Get the extension of a filename.
	 *
	 * @param string $filename The filename.
	 * @return string The extension.
	 */
	protected function get_extension($filename) {
		$lastdot = mb_strrpos($filename, '.');
		if (!empty($lastdot)) {
			return mb_substr($filename, $lastdot + 1);
		} else {
			return null;
		}
	}

	/**
	 * Get the extension of the original filename.
	 *
	 * @return string The extension of the original filename.
	 */
	public function get_original_extension() {
		return $this->get_extension($this->orig_filename);
	}

	/**
	 * Get the extension of the current filename.
	 *
	 * @return string The extension of the current filename.
	 */
	public function get_file_extension() {
		return $this->get_extension($this->stored_filename);
	}

	/**
	 * Get the currrent filename of the file.
	 *
	 * @return string The current filename (full-path) of the file.
	 */
	public function get_filename() {
		return $this->stored_filename;
	}

	/**
	 * Get the original filename of the uploaded file.
	 *
	 * @return string The original filename of the uploaded file.
	 */
	public function get_original_filename() {
		return $this->orig_filename;
	}
}
