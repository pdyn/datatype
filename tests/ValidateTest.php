<?php
namespace pdyn\datatype\tests;

/**
 * Test validate class.
 *
 * @group pdyn
 * @group pdyn_datatype
 * @codeCoverageIgnore
 */
class ValidateTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Test validate empty_str.
	 */
	public function test_validate_empty_str() {
		$invalid = array(true, false, null, array(), new \stdClass, 1, 1.2, '1', 'test');
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::empty_str($val));
		}

		$valid = array('', ' ', '  ', '&nbsp;');
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::empty_str($val));
		}
	}

	/**
	 * Test validate intlike.
	 */
	public function test_validate_intlike() {
		$invalid = array(true, false, null, array(), new \stdClass, 1.4, '1.5');
		foreach ($invalid as $i => $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::intlike($val), 'failed for test '.$i);
		}

		$valid = array(1, 0, '0', '1', -1, '-1');
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::intlike($val));
		}
	}

	/**
	 * Test validate pos_int.
	 */
	public function test_validate_pos_int() {
		$valid = array(0, 1, 1000, '0', '10');
		foreach ($valid as $val) {
			$this->assertTrue(\pdyn\datatype\Validator::pos_int($val));
		}

		$invalid = array(true, false, null, array(), new \stdClass, 1.2, '1.0', 'test', -1, '-1');
		foreach ($invalid as $val) {
			$this->assertFalse(\pdyn\datatype\Validator::pos_int($val));
		}
	}

	/**
	 * Test validate boollike.
	 */
	public function test_validate_boollike() {
		$invalid = array(null, array(), new \stdClass, 1.4, '1.5', 2, 4, '2', '5', '-1', -1);
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::boollike($val));
		}

		$valid = array(true, false, 1, 0, '1', '0');
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::boollike($val));
		}
	}

	/**
	 * Test validate stringlike.
	 */
	public function test_stringlike() {
		$invalid = array(true, false, null, array(), new \stdClass);
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::stringlike($val));
		}

		$valid = array('test', 1, 1.0, '0', '1');
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::stringlike($val));
		}
	}

	/**
	 * Test validate filename.
	 */
	public function test_validate_filename() {
		$invalid = array(true, false, null, array(), new \stdClass, '../test', '/etc/passwd');
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::filename($val));
		}
		$valid = array('test', 'test.txt', '1');
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::filename($val));
		}
	}

	/**
	 * Test validate url.
	 */
	public function test_validate_url() {
		$invalid = array(true, false, null, array(), new \stdClass, 1, 1.2, '1', 'test', 'james@example.com', 'example.com');
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Url::validate($val));
		}

		$protocols = array('http://', 'https://', 'ftp://');
		$prefixes = array('', 'james@', 'james:1234@');
		$domains = array('example.com', 'one.example.com', 'one.two.example.com');
		$suffixes = array('', ':80', ':8080');
		$files = array('', '/', '/one', '/one/', '/one/two', '/one/two/', '/test.html', '/one/test.html', '/one/two/test.html');

		foreach ($protocols as $a) {
			foreach ($prefixes as $b) {
				foreach ($domains as $c) {
					foreach ($suffixes as $d) {
						foreach ($files as $e) {
							$this->assertEquals(true, \pdyn\datatype\Url::validate($a.$b.$c.$d.$e));
						}
					}
				}
			}
		}
	}

	/**
	 * Test validate email.
	 */
	public function test_validate_email() {
		$invalid = array(
			true, false, null, array(), new \stdClass, 1, 1.2, '1', 'test',
			'james.example.com',
			'james.@example.com',
			'Abc..123@example.com',
			'A@b@c@example.com',
			'a"b(c)d,e:f;g<h>i[j\k]l@example.com',
			'just"not"right@example.com',
			'this is"not\allowed@example.com',
			'this\ still\"not\\allowed@example.com',
		);
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::email($val));
		}

		$valid = array(
			'james@example.com',
			'james.one@example.com',
			'james.one.two.three.four@five.example.com',
			'james-one@example.com',
			'james+one@example.com',
			'user@[127.0.0.1]',
			'user@[1.2.3.4]',
			'user@[IPv6:2001:db8:1ff::a0b:dbd0]',
			//'"james.one two"@example.com',
			'"very.unusual.@.unusual.com"@example.com',
			'"very.(),:;<>[]\".VERY.\"very@\\ \"very\".unusual"@strange.example.com',
			//'0@a',
			//'postbox@com',
			'!#$%&\'*+-/=?^_`{}|~@example.org',
			//'"()<>[]:,;@\\\"!#$%&\'*+-/=?^_`{}| ~  ? ^_`{}|~.a"@example.org',
			'""@example.org',
		);
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::email($val));
		}
	}

	/**
	 * Test validate date.
	 */
	public function test_validate_date() {
		$validate = new \pdyn\datatype\Validator(); //just to get phpunit to register full code coverage

		//test \pdyn\datatype\Validator::date
		$invalid = array(true, false, array(), null, new \stdClass, '', 'a', 1, 1.0, '12345', 12345, 'YYYYMMDD', '20120140',
					'20121501', 'aaaa0101', '2012aa01', '201201aa', '20120001', '20120100');
		foreach ($invalid as $val) {
			$this->assertEquals(false, \pdyn\datatype\Validator::date('YYYYMMDD', $val));
		}

		$valid = array('20120101', 20120101);
		foreach ($valid as $val) {
			$this->assertEquals(true, \pdyn\datatype\Validator::date('YYYYMMDD', $val));
		}
	}

	/**
	 * Test validate boolint.
	 */
	public function test_boolint() {
		$valid = array(0, 1);
		foreach ($valid as $val) {
			$this->assertTrue(\pdyn\datatype\Validator::boolint($val));
		}

		$invalid = array(true, false, null, array(), new \stdClass, 1.2, '1.0', 'test', -1, '-1', '1', '0');
		foreach ($invalid as $val) {
			$this->assertFalse(\pdyn\datatype\Validator::boolint($val));
		}
	}
}
