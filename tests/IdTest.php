<?php
namespace pdyn\datatype;

use \pdyn\datatype\Id;

/**
 * Test Id.
 *
 * @group pdyn
 * @group pdyn_datatype
 * @codeCoverageIgnore
 */
class IdTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Get an array of valid data for this datatype.
	 *
	 * @return array Array of valid data.
	 */
	protected function get_valid_data() {
		return [1, 1000, '10'];
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
		$this->assertEquals($expected, \pdyn\datatype\Id::validate($test));
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
		$id = new \pdyn\datatype\Id($data);
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
		$id = new \pdyn\datatype\Id($data);
	}
}
