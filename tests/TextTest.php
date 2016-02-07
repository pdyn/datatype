<?php
namespace pdyn\datatype\tests;

/**
 * Test Text.
 *
 * @group pdyn
 * @group pdyn_datatype
 * @codeCoverageIgnore
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

	/**
	 * Dataprovider for test_removeWhitespace
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_removeWhitespace() {
		return [
			[
				"a\nb\rc\td\0e f",
				'abcdef',
			],
		];
	}

	/**
	 * Tests remove_whitespace function.
	 *
	 * @dataProvider dataprovider_removeWhitespace
	 * @param string $text Test text.
	 * @param string $expected Expected outcome.
	 */
	public function test_removeWhitespace($text, $expected) {
		$text = new \pdyn\datatype\Text($text);
		$text->remove_whitespace();
		$this->assertEquals($expected, $text->val());
	}

	/**
	 * Dataprovider for test_generateColor
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_generateColor() {
		$return = [];
		for ($i = 0; $i < 10; $i++) {
			$return[] = [sha1(uniqid(true))];
		}
		return $return;
	}

	/**
	 * Tests generate_color function.
	 *
	 * @dataProvider dataprovider_generateColor
	 */
	public function test_generateColor($text) {
		$text = new \pdyn\datatype\Text($text);

		$rgb = $text->generate_color();
		$this->assertInternalType('array', $rgb);
		$this->assertArrayHasKey('r', $rgb);
		$this->assertArrayHasKey('g', $rgb);
		$this->assertArrayHasKey('b', $rgb);
		$this->assertTrue(is_int($rgb['r']));
		$this->assertTrue(($rgb['r'] >= 0 && $rgb['r'] <= 255));
		$this->assertTrue(is_int($rgb['g']));
		$this->assertTrue(($rgb['g'] >= 0 && $rgb['g'] <= 255));
		$this->assertTrue(is_int($rgb['b']));
		$this->assertTrue(($rgb['b'] >= 0 && $rgb['b'] <= 255));

		$hex = $text->generate_color(true);
		$this->assertInternalType('string', $hex);
		$hexlen = strlen($hex);
		$this->assertTrue($hexlen === 6);
		for ($i = 0; $i < $hexlen; $i++) {
			$validhex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
			$this->assertTrue(in_array($hex{$i}, $validhex));
		}
	}

	/**
	 * Dataprovider for test_makeSlug
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_makeSlug() {
		return [
			'lowercase' => [
				'TEST',
				'test',
			],
			'reserved' => [
				'me',
				'me_1',
			],
			'reserved2' => [
				'type',
				'type_1',
			],
			'domain' => [
				'example.com',
				'example',
			],
			'quotes' => [
				'James\'s',
				'jamess',
			],
			'nonalphanum' => [
				'this is a test!',
				'this_is_a_test',
			],
		];
	}

	/**
	 * Tests make_slug function.
	 *
	 * @dataProvider dataprovider_makeSlug
	 */
	public function test_makeSlug($text, $expected) {
		$actual = \pdyn\datatype\Text::make_slug($text);
		$this->assertEquals($expected, $actual);
	}
}
