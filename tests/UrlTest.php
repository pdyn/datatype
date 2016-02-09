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
 * Test Text.
 *
 * @group pdyn
 * @group pdyn_datatype
 * @codeCoverageIgnore
 */
class UrlTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Dataprovider for test_makeAbsoluteUrl.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_makeAbsoluteUrl() {
		return [
			[
				'http://example.com',
				'',
				'http://example.com'
			],
			[
				'example.com',
				'',
				'http://example.com'
			],
			[
				'http://example2.com',
				'http://example.com',
				'http://example2.com'
			],
			[
				'/one/two/three.html',
				'http://example.com',
				'http://example.com/one/two/three.html'
			],
			[
				'/two/three.html',
				'http://example.com/one/',
				'http://example.com/two/three.html'
			],
			[
				'../two/three.html',
				'http://example.com/one/',
				'http://example.com/two/three.html'
			],
			[
				'../../three/four.html',
				'http://example.com/one/two/',
				'http://example.com/three/four.html'
			],
			[
				'two/three.html',
				'http://example.com/one/',
				'http://example.com/one/two/three.html'
			],
			[
				'//example.com',
				'http://example.com/',
				'http://example.com'
			],
			[
				'test/index.php',
				'http://example.com/one/index.php',
				'http://example.com/one/test/index.php'
			],
			[
				'./test/index.php',
				'http://example.com/one/index.php',
				'http://example.com/one/test/index.php'
			],
			[
				'./../test/somefile.php',
				'http://example.com/one/two.three/index.php',
				'http://example.com/one/test/somefile.php'
			],
			[
				'.././test/somefile.php',
				'http://example.com/one/two.three/index.php',
				'http://example.com/one/test/somefile.php'
			],
		];
	}

	/**
	 * Test make_absolute_url function.
	 *
	 * @dataProvider dataprovider_makeAbsoluteUrl
	 * @param string $i The input URL
	 * @param string $pageurl The source page url.
	 * @param string $expected The expected output
	 */
	public function test_makeAbsoluteUrl($i, $pageurl, $expected) {
		$actual = \pdyn\datatype\Url::make_absolute($i, $pageurl);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Dataprovider for test_addquery.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_addquery() {
		return [
			[
				'http://example.com',
				['key' => 'value'],
				'http://example.com?key=value',
			],
			[
				'http://example.com',
				['key' => 'value', 'key2' => 'value2'],
				'http://example.com?key=value&key2=value2',
			],
			[
				'http://example.com',
				'key=value',
				'http://example.com?key=value',
			],
			[
				'http://example.com',
				'key=value&key2=value2',
				'http://example.com?key=value&key2=value2',
			],
			[
				'http://example.com?key=value',
				['key2' => 'value2'],
				'http://example.com?key=value&key2=value2',
			],
			[
				'http://example.com?key=value',
				['key2' => 'value2', 'key3' => 'value3'],
				'http://example.com?key=value&key2=value2&key3=value3',
			],
			[
				'http://example.com?key=value',
				'key2=value2',
				'http://example.com?key=value&key2=value2',
			],
			[
				'http://example.com?key=value',
				'key2=value2&key3=value3',
				'http://example.com?key=value&key2=value2&key3=value3',
			],
		];
	}

	/**
	 * Test addquery function.
	 *
	 * @dataProvider dataprovider_addquery
	 */
	public function test_addquery($url, $query, $expected) {
		$url = new \pdyn\datatype\Url($url);
		$url->addquery($query);
		$this->assertEquals($expected, (string)$url);
	}

	/**
	 * Dataprovider for test_removequery.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_removequery() {
		return [
			[
				'http://example.com',
				'test',
				'http://example.com',
			],
			[
				'http://example.com?key=value',
				'key',
				'http://example.com',
			],
			[
				'http://example.com?key=value',
				'key2',
				'http://example.com?key=value',
			],
			[
				'http://example.com?key=value&key2=value2',
				'key',
				'http://example.com?key2=value2',
			],
			[
				'http://example.com?key=value&key2=value2',
				'key3',
				'http://example.com?key=value&key2=value2',
			],
		];
	}

	/**
	 * Test removequery function.
	 *
	 * @dataProvider dataprovider_removequery
	 */
	public function test_removequery($url, $querykey, $expected) {
		$url = new \pdyn\datatype\Url($url);
		$url->removequery($querykey);
		$this->assertEquals($expected, (string)$url);
	}

	/**
	 * Get an array of valid data for this datatype.
	 *
	 * @return array Array of valid data.
	 */
	protected function get_valid_data() {
		return [
			'http://example.com',
			'https://example.com',
			'http://example.com/',
			'http://example.com/one/two',
			'http://example.com/one/two/test.php',
			'http://james@example.com/one/two/test.php',
			'http://james:1234@example.com/one/two/test.php',
			'http://james:1234@example.com:445/one',
		];
	}

	/**
	 * Get an array of invalid data for this datatype.
	 *
	 * @return array Array of invalid data.
	 */
	protected function get_invalid_data() {
		return [0, '0', true, false, null, array(), new \stdClass, 1.2, '1.0', 'test', -1, '-1'];
	}

	/**
	 * Dataprovider for test_validate.
	 *
	 * @return array Array of tests.
	 */
	public function dataprovider_validate() {
		$valid = $this->get_valid_data();
		$invalid = $this->get_invalid_data();

		$return = [];
		foreach ($valid as $data) {
			$return[] = [$data, true];
		}
		foreach ($invalid as $data) {
			$return[] = [$data, false];
		}
		return $return;
	}

	/**
	 * Test validate id.
	 *
	 * @dataProvider dataprovider_validate
	 */
	public function test_validate($test, $expected) {
		$this->assertEquals($expected, \pdyn\datatype\Url::validate($test));
	}

	/**
	 * Dataprovider for test_construct.
	 *
	 * @return array Array of tests.
	 */
	public function dataprovider_construct() {
		$valid = $this->get_valid_data();
		$return = [];
		foreach ($valid as $data) {
			$return[] = [$data];
		}
		return $return;
	}

	/**
	 * Test successful construction with valid data.
	 *
	 * @dataProvider dataprovider_construct
	 */
	public function test_construct($data) {
		$id = new \pdyn\datatype\Url($data);
		$this->assertEquals($data, (string)$id);
	}

	/**
	 * Dataprovider for test_construct.
	 *
	 * @return array Array of tests.
	 */
	public function dataprovider_invaliddata() {
		$valid = $this->get_invalid_data();
		$return = [];
		foreach ($valid as $data) {
			$return[] = [$data];
		}
		return $return;
	}

	/**
	 * @expectedException \Exception
	 * @dataProvider dataprovider_invaliddata
	 */
	public function test_throwsExceptionOnInvalidId($data) {
		$id = new \pdyn\datatype\Url($data);
	}
}
