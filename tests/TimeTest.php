<?php
namespace pdyn\datatype;

use \pdyn\datatype\Time;

/**
 * Test TimeUtils.
 *
 * @group pdyn
 * @group pdyn_datatype
 */
class TimeTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Tests the process function.
	 */
	public function test_get_relative_time() {
		$now = time();
		$tests = [
			($now + 3601) => '60 minutes from now',
			($now + 3600) => '60 minutes from now',
			($now + 121) => '2 minutes from now',
			($now + 120) => '2 minutes from now',
			($now + 61) => '1 minute from now',
			($now + 60) => '1 minute from now',
			($now + 32) => '32 seconds from now',
			($now + 1) => '1 second from now',
			($now) => 'now',
			($now - 1) => '1 second ago',
			($now - 43) => '43 seconds ago',
			($now - 59) => '59 seconds ago',
			($now - 60) => '1 minute ago',
			($now - 61) => '1 minute ago',
			($now - 120) => '2 minutes ago',
			($now - 121) => '2 minutes ago',
			($now - (3599)) => '60 minutes ago',
			($now - (3600)) => '1 hour ago',
			($now - (3601)) => '1 hour ago',
			($now - (7200)) => '2 hours ago',
			($now - (7201)) => '2 hours ago',
			($now - (86399)) => '24 hours ago',
			($now - (86400)) => '1 day ago',
			($now - (86401)) => '1 day ago',
			($now - (172800)) => '2 days ago',
			($now - (172801)) => '2 days ago',
			($now - (604799)) => '7 days ago',
			($now - (604800)) => date('Ymd', ($now - (604800))),
			0 => 'Never',
		];
		$tests = [
			($now + 3601) => '1h',
			($now + 3600) => '1h',
			($now + 121) => '2m',
			($now + 120) => '2m',
			($now + 61) => '1m',
			($now + 60) => '1m',
			($now + 32) => '32s',
			($now + 1) => '1s',
			($now) => 'now',
			($now - 1) => '1s',
			($now - 43) => '43s',
			($now - 59) => '59s',
			($now - 60) => '1m',
			($now - 61) => '1m',
			($now - 120) => '2m',
			($now - 121) => '2m',
			($now - (3599)) => '60m',
			($now - (3600)) => '1h',
			($now - (3601)) => '1h',
			($now - (7200)) => '2h',
			($now - (7201)) => '2h',
			($now - (86399)) => '24h',
			($now - (86400)) => '1d',
			($now - (86401)) => '1d',
			($now - (172800)) => '2d',
			($now - (172801)) => '2d',
			($now - (604799)) => '7d',
			($now - (604800)) => date('M d', ($now - (604800))),
			0 => 'Never',
		];

		foreach ($tests as $timestamp => $expected) {
			$time = new Time($timestamp);
			$this->assertEquals($expected, $time->get_relative_time());
		}
	}
}
