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
}
