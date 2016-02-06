<?php
namespace pdyn\datatype\tests;

/**
 * Test sanitize class;
 *
 * @group pdyn
 * @group pdyn_datatype
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
