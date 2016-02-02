<?php
namespace pdyn\datatype;

/**
 * A datatype class defining a database ID value.
 */
class Id extends \pdyn\datatype\Base {
	/** @var int An ID value. */
	protected $val;

	/**
	 * Constructor.
	 *
	 * @param int $val An ID value.
	 */
	public function __construct($val) {
		if (\pdyn\datatype\Id::validate($val) !== true) {
			throw new \Exception('Bad ID Received', 406);
		}
		$this->val = (int)$val;
	}

	/**
	 * Validate datatype.
	 *
	 * @param mixed $input Value to validate.
	 * @return bool Value is valid or not.
	 */
	public static function validate($input) {
		return (\pdyn\datatype\Validator::intlike($input) && (int)$input >= 1);
	}
}
