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

namespace pdyn\datatype\tests;

/**
 * Test sanitize class;
 *
 * @group pdyn
 * @group pdyn_datatype
 * @codeCoverageIgnore
 */
class SanitizeTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test alphanum sanitization.
	 */
	public function test_sanitize_alphanum() {
		$dirty = ['1a2B3c', '()&@(*&(*$', '123$%%aBc', '', true, false, null, [], new \stdClass, 0, 1, 2];
		$clean = ['1a2B3c', '', '123aBc', '', '', '', '', '', '', 0, 1, 2];

		foreach ($dirty as $i => $val) {
			$expected = $clean[$i];
			$actual = \pdyn\datatype\Sanitizer::alphanum($val);
			$this->assertEquals($expected, $actual);
		}
	}

	/**
	 * Dataprovider for test_set_db_ver().
	 *
	 * @return array Array of arrays of test parameters.
	 */
	/*
	public function dataprovider_test_set_db_ver() {
		return [
			[true, false, '1.5'],
			[false, false, '1.5'],
			[null, false, '1.5'],
			['', false, '1.5'],
			[[], false, '1.5'],
			['1.2.3!@#$%^&*()+={}[]:;\'"<>,.?/`~4test', true, '1.2.3.4test'],
			['1.2test', true, '1.2test'],
			['2beta', true, '2beta'],
		];
	}
	*/

	/**
	 * Tests setting and getting the database version.
	 *
	 * Tests $DB->set_db_ver() and $DB->get_db_ver();
	 *
	 * @dataProvider dataprovider_test_set_db_ver
	 */
	/*
	public function test_set_db_ver($new_version, $expected_return, $expected_version) {
		return false;
		$actual_return = $this->DB->set_db_ver($new_version);
		$actual_version = $this->DB->get_db_ver();
		$this->assertEquals($expected_return, $actual_return);
		$this->assertEquals($expected_version, $actual_version);
	}
	*/
}
