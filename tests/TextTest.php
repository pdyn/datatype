<?php
namespace pdyn\datatype\tests;

/**
 * Test Text.
 *
 * @group pdyn
 * @group pdyn_datatype
 */
class TextTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Dataprovider for text_extract_hashtags
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_hashtags() {
		return [
			[
				'',
				[],
			],
			[
				'Test One Two Three',
				[],
			],
			[
				'#Test One Two Three',
				[
					'#test',
				],
			],
			[
				'#Test One #Two Three',
				[
					'#test',
					'#two',
				],
			],
			[
				'#Test One #Test #Two Three #Test',
				[
					'#test',
					'#test',
					'#two',
					'#test',
				],
			],
			[
				'#Test One #Test #Two Three #Test',
				[
					'#test-',
					'#test-',
					'#two--',
					'#test-',
				],
				true,
			],
		];
	}

	/**
	 * Test extract_hashtags function.
	 *
	 * @dataProvider dataprovider_extract_hashtags
	 * @param string $text Test text.
	 * @param array $expected Array of expected output.
	 * @param bool $pad Whether to pad short hashtags for mysql FULLTEXT search.
	 */
	public function test_extract_hashtags($text, $expected, $pad = false) {
		$plaintext = new \pdyn\datatype\Text($text);
		$hashtags = $plaintext->extract_hashtags($pad);
		$this->assertEquals($expected, $hashtags);
	}

	/**
	 * Dataprovider for text_sanitize
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_sanitize() {
		return [
			[
				'',
				'',
			],
			[
				'Test One Two Three',
				'Test One Two Three',
			],
			[
				'<b>Test One Two Three</b>',
				'Test One Two Three',
			],
		];
	}
	/**
	 * Test sanitize function.
	 *
	 * @dataProvider dataprovider_sanitize
	 * @param string $text Test text.
	 * @param string $expected Expected output.
	 */
	public function test_sanitize($text, $expected) {
		$plaintext = new \pdyn\datatype\Text($text);
		$plaintext->sanitize();
		$this->assertEquals($expected, $plaintext->val());
	}

	/**
	 * Dataprovider for text_truncate
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_truncate() {
		return [
			[
				'',
				100,
				'',
			],
			[
				'Test One Two Three',
				100,
				'Test One Two Three',
			],
			[
				'Test One Two Three',
				6,
				'Test O...',
			],
			[
				'Test O&lt;ne Two Three',
				7,
				'Test O<...',
			],
			[
				'&lt;&gt;&lt;&gt;&lt;&gt;',
				4,
				'<><>...',
			],
			[
				'abc&lt;',
				4,
				'abc<',
			],
		];
	}
	/**
	 * Test truncate function.
	 *
	 * @dataProvider dataprovider_truncate
	 * @param string $text Test text.
	 * @param int $length Desired length.
	 * @param array $expected Expected output.
	 */
	public function test_truncate($text, $length, $expected) {
		$plaintext = new \pdyn\datatype\Text($text);
		$plaintext->truncate($length);
		$this->assertEquals($expected, $plaintext->val());
	}
}
