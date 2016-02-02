<?php
namespace pdyn\datatype;

class Time extends \DateTime {
	const YYYYMMDD = 'Ymd';
	const longDate = 'l, F j, Y';
	const longDateTime = 'l, F j, Y H:i:s';
	const mysql = 'r';
	const standards = 'r';
	const YYYY = 'Y';
	const MM = 'm';
	const DD = 'd';

	public function __construct($time = 'now', \DateTimeZone $timezone = null) {
		if ((string)(int)$time === (string)$time) {
			$time = '@'.$time;
		}
		return parent::__construct($time, $timezone);
	}

	/**
	 * Magic method for string usage.
	 *
	 * @return string The result of the val() method.
	 */
	public function __toString() {
		return $this->getTimestamp();
	}

	/**
	 * Create a time object from a human-readable date string, parseable by strtotime().
	 *
	 * @param string $stringdate String date.
	 * @return \pdyn\datatype\Time Time object.
	 */
	public static function createFromString($stringdate) {
		return new static(strtotime($stringdate));
	}

	/**
	 * Get a string showing the difference between the stored time and the current (or specified) time.
	 *
	 * This generates strings like "5 minutes ago", "10 hours ago", etc.
	 *
	 * @param string $datestyle If the difference is greater than 7 days, it will fall back to a formatted date using this format.
	 * @param int $now Specify a timestamp to be used as "now".
	 * @return string The generated relative time string.
	 */
	public function get_relative_time($datestyle = null, $now = null) {
		if ($datestyle === 'long') {
			$datestyle = 'l, F j, Y H:i:s';
		}

		$time = $this->getTimestamp();
		if (empty($time)) {
			return 'Never';
		}

		if (empty($now) || !is_int($now)) {
			$now = time();
		}

		$str = [
			'second' => '%d second',
			'seconds' => '%d seconds',
			'minute' => '%d minute',
			'minutes' => '%d minutes',
			'hour' => '%d hour',
			'hours' => '%d hours',
			'day' => '%d day',
			'days' => '%d days',
			'fromnow' => ' from now',
			'ago' => ' ago',
		];

		$str = [
			'second' => '%ds',
			'seconds' => '%ds',
			'minute' => '%dm',
			'minutes' => '%dm',
			'hour' => '%dh',
			'hours' => '%dh',
			'day' => '%dd',
			'days' => '%dd',
			'fromnow' => '',
			'ago' => '',
		];

		$diff = $now - $time;
		$diffabs = abs($diff);
		$relative = true;
		$out = '';

		if ($relative === true) {
			if ($diffabs == 0) {
				$out = 'now';
			} elseif ($diffabs < 60) {
				// Second accuracy.
				$out = ($diffabs == 1) ? sprintf($str['second'], $diffabs) : sprintf($str['seconds'], $diffabs);
			} elseif ($diffabs >= 60 && $diffabs < 3600) {
				// Minute accuracy.
				$mins = (int)round($diffabs / 60);
				$out = ($mins === 1) ? sprintf($str['minute'], $mins) : sprintf($str['minutes'], $mins);
			} elseif ($diffabs >= 3600 && $diffabs < 86400) {
				// Hour accuracy.
				$hrs = (int)round($diffabs / 60 / 60);
				$out = ($hrs == 1) ? sprintf($str['hour'], $hrs) : sprintf($str['hours'], $hrs);
			} elseif ($diffabs >= 86400 && $diffabs < 604800) {
				// Day accuracy.
				$days = round($diffabs / 60 / 60 / 24);
				$out = ($days == 1) ? sprintf($str['day'], $days) : sprintf($str['days'], $days);
			}
		}
		if (!empty($out)) {
			if ($diff > 0) {
				$out .= $str['ago'];
			} elseif ($diff < 0) {
				$out .= $str['fromnow'];
			}
		} else {
			if (empty($datestyle)) {
				$datestyle = (date('Y', $now) === date('Y', $time)) ? 'M d' : 'M d Y';
			}
			$out = date($datestyle, $time);
		}
		return $out;
	}

	/**
	 * Get a standardized date stamp.
	 *
	 * @return string A formatted, standardized date stamp.
	 */
	public function getDatestamp() {
		return $this->format(static::standards);
	}
}
