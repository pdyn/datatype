<?php
namespace pdyn\datatype\tests;

/**
 * Test HTML.
 *
 * @group pdyn
 * @group pdyn_datatype
 */
class HtmlTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Dataprovider for test_extract_htmltag.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_tag() {
		return [
			[
				'<b>Test</b>',
				'b',
				[
					[
						'attrs' => [],
						'content' => 'Test',
					],
				],
			],
			[
				'Test<b test1="test2">Test',
				'b',
				[
					[
						'attrs' => [
							'test1' => 'test2',
						],
						'content' => '',
					],
				],
			],
			[
				'Test<b test-one="test2"/>Test',
				'b',
				[
					[
						'attrs' => [
							'test-one' => 'test2',
						],
						'content' => '',
					],
				],
			],
			[
				'<b>Test</b><b>Test2</b>',
				'b',
				[
					[
						'attrs' => [],
						'content' => 'Test',
					],
					[
						'attrs' => [],
						'content' => 'Test2',
					],
				],
			],
			[
				'<i>Test</i><b>Test2</b><i>Test3</i><b>Test4</b><i>Test4</i>',
				'b',
				[
					[
						'attrs' => [],
						'content' => 'Test2',
					],
					[
						'attrs' => [],
						'content' => 'Test4',
					],
				],
			],
			[
				'<a href="http://example.com" alt="Test1" style="display:block">Test2</a><b>Test3</b><i>Test4</i>',
				'a',
				[
					[
						'attrs' => [
							'alt' => 'Test1',
							'style' => 'display:block',
							'href' => 'http://example.com',
						],
						'content' => 'Test2',
					],
				],
			],
			[
				'<a href="http://example.com/test1.html" alt="Test2" style="display:block">Test3</a><b>Test4</b>'
					.'<a href="http://example.com/test5.html" alt="Test6" style="display:block">Test7</a><i>Test8</i>',
				'a',
				[
					[
						'attrs' => [
							'href' => 'http://example.com/test1.html',
							'alt' => 'Test2',
							'style' => 'display:block',
						],
						'content' => 'Test3',
					],
					[
						'attrs' => [
							'href' => 'http://example.com/test5.html',
							'alt' => 'Test6',
							'style' => 'display:block',
						],
						'content' => 'Test7',
					],
				],
			],
			[
				'<i>Test</i><b alt="test">Test2</b><i>Test3</i><b>Test4</b><i>Test4</i>',
				'b',
				[
					[
						'attrs' => [
							'alt' => 'test',
						],
						'content' => 'Test2',
					],
					[
						'attrs' => [],
						'content' => 'Test4',
					],
				],
			],
			[
				'<i>Test</i><b alt="test"><i>Test2</i></b><i>Test3</i><b>Test4</b><i>Test4</i>',
				'b',
				[
					[
						'attrs' => [
							'alt' => 'test',
						],
						'content' => '<i>Test2</i>',
					],
					[
						'attrs' => [],
						'content' => 'Test4',
					],
				],
			],
		];
	}

	/**
	 * Test extract_htmltag function.
	 *
	 * @dataProvider dataprovider_extract_tag
	 * @param string $html HTML string to extract tags from.
	 * @param string $tag The tag to extract.
	 * @param array $expected Expected array of extracted html tag information.
	 */
	public function test_extract_tag($html, $tag, $expected) {
		$html = new \pdyn\datatype\Html($html);
		$result = $html->extract_tag($tag);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test get_void_elements function.
	 */
	public function test_get_void_elements() {
		$voidelements = \pdyn\datatype\Html::get_void_elements();
		$this->assertNotEmpty($voidelements);
		$this->assertInternalType('array', $voidelements);
	}

	/**
	 * Test get_allowed_tags function.
	 */
	public function test_get_allowed_tags() {
		$allowedtags = \pdyn\datatype\Html::get_allowed_tags();
		$this->assertNotEmpty($allowedtags);
		$this->assertInternalType('array', $allowedtags);
	}

	/**
	 * Test close_unclosed_tags function.
	 */
	public function test_close_tags() {
		$html = file_get_contents(__DIR__.'/fixtures/htmlunclosedtags.html');
		$expected = file_get_contents(__DIR__.'/fixtures/htmlunclosedtags_fixed.html');
		$html = new \pdyn\datatype\Html($html);
		$html->close_tags();
		$this->assertEquals($expected, $html->val());
	}

	/**
	 * Test escape_html function.
	 */
	public function test_escape_html() {
		$str = '<b>Test</b>';
		$html = \pdyn\datatype\Html::escape_html($str);
		$expected_html = '&lt;b&gt;Test&lt;/b&gt;';
		$this->assertEquals($expected_html, $html);
	}

	/**
	 * Dataprovider for test_truncate.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_truncate() {
		return [
			[
				'Test One Two Three',
				'20',
				'Test One Two Three',
			],
			[
				'Test One Two Three',
				'8',
				'Test One...'
			],
			[
				'<b>Test One Two Three</b>',
				'8',
				'<b>Test One...</b>'
			],
			[
				'<b><img src="test"/>Test One Two Three</b>',
				'8',
				'<b><img src="test"/>Test One...</b>'
			],
			[
				'<b><img src="test"/><a href="test.php">Test One Two</a> Three</b>',
				'8',
				'<b><img src="test"/><a href="test.php">Test One...</a></b>'
			],
			[
				'<b><img src="test"/><a href="test.php">Test One Two</a> Three</b>',
				'20',
				'<b><img src="test"/><a href="test.php">Test One Two</a> Three</b>',
			],
		];
	}

	/**
	 * Test truncate function.
	 *
	 * @dataProvider dataprovider_truncate
	 * @param string $html The input html.
	 * @param int $length The length to truncate to.
	 * @param string $expected The expected output text.
	 */
	public function test_truncate($html, $length, $expected) {
		$html = new \pdyn\datatype\Html($html);
		$html->truncate($length);
		$this->assertEquals($expected, $html->val());
	}

	/**
	 * Dataprovider for test_clean.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_clean() {
		return array(
			array(
				'Test One Two Three',
				'http://example.com',
				'Test One Two Three',
				[],
			),
			array(
				'<b>Test One Two Three</b>',
				'http://example.com',
				'Test One Two Three',
				[],
			),
			array(
				'<b><i>Test One Two Three</i></b>',
				'http://example.com',
				'<b>Test One Two Three</b>',
				array(
					'b' => []
				),
			),
			array(
				'<b alt="test"><i>Test One Two Three</i></b>',
				'http://example.com',
				'<b>Test One Two Three</b>',
				array(
					'b' => []
				),
			),
			array(
				'<b alt="test" style="font-size:1000rem"><i>Test One Two Three</i></b>',
				'http://example.com',
				'<b alt="test">Test One Two Three</b>',
				array(
					'b' => array('alt' => 'txt')
				),
			),
			array(
				'<b alt="test" style="font-size:1000rem"><i><p>Test</p><p>One</p><p>Two</p><p>Three</p></i></b>',
				'http://example.com',
				'<b alt="test"><p>Test</p>OneTwoThree</b>',
				array(
					'b' => array('alt' => 'txt'),
					'p' => array('opts' => array('max' => 1))
				),
			),
			array(
				'<b>Test One Two Three<img src="test"></img></b>',
				'http://example.com',
				'<b>Test One Two Three<img src="test"/></b>',
				array(
					'b' => array('alt' => 'txt'),
					'img' => array('src' => 'txt')
				),
			),
			array(
				'<b>Test One Two Three<img src="test"/></b>',
				'http://example.com',
				'<b>Test One Two Three<img src="http://example.com/test"/></b>',
				array(
					'b' => array('alt' => 'txt'),
					'img' => array('src' => 'uri')
				),
			),
			array(
				'<b>Test One Two Three<img src="/test2"/></b>',
				'http://example.com/test/',
				'<b>Test One Two Three<img src="http://example.com/test2"/></b>',
				array(
					'b' => array('alt' => 'txt'),
					'img' => array('src' => 'uri')
				),
			),
			array(
				'<b>Test One Two Three<img src="../test2"/></b>',
				'http://example.com/test/',
				'<b>Test One Two Three<img src="http://example.com/test2"/></b>',
				array(
					'b' => array('alt' => 'txt'),
					'img' => array('src' => 'uri')
				),
			),
		);
	}

	/**
	 * Test clean function.
	 *
	 * @dataProvider dataprovider_clean
	 * @param string $html The input html.
	 * @param string $sourceurl The source URL for the text (to resolve relative URLs).
	 * @param string $expected The expected output text.
	 * @param array $allowedtags An array of allowed HTML tags.
	 */
	public function test_clean($html, $sourceurl, $expected, $allowedtags) {
		$html = new \pdyn\datatype\Html($html, $sourceurl);
		$html->clean($allowedtags);
		$this->assertEquals($expected, $html->val());
	}

	/**
	 * Dataprovider for test_extract_images.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_images() {
		return array(
			array(
				'',
				'http://example.com',
				[]
			),
			array(
				'<img src="http://example.com/test.jpg" />',
				'http://example.com',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="http://example.com/test.jpg"></img>',
				'http://example.com',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="http://example.com/test.jpg">',
				'http://example.com',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="test.jpg"/>',
				'http://example.com',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="/test.jpg"/>',
				'http://example.com/test/',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="../test2/test.jpg"/>',
				'http://example.com/test/',
				array(
					'http://example.com/test2/test.jpg'
				)
			),
			array(
				'<img src="test.jpg" alt="test"/>',
				'http://example.com',
				array(
					'http://example.com/test.jpg'
				)
			),
			array(
				'<img src="test.jpg"/><img src="test2.jpg"/><img src="test3.jpg"/>',
				'http://example.com',
				array(
					'http://example.com/test.jpg',
					'http://example.com/test2.jpg',
					'http://example.com/test3.jpg'
				)
			),
			array(
				'This is a test <div><a href="test.html"><img src="test1234.jpg"/></a>This is a test.',
				'http://example.com',
				array(
					'http://example.com/test1234.jpg',
				)
			),
			array(
				'<img src="http://pdyn.net/image444.jpg"/>This is a test <div><a href="test.html"><img src="test1234.jpg"/></a>'
					.'This is a test.<img src="http://exmaple.com/one.jpg"/>',
				'http://example.com',
				array(
					'http://pdyn.net/image444.jpg',
					'http://example.com/test1234.jpg',
					'http://exmaple.com/one.jpg',
				)
			),
		);
	}

	/**
	 * Test extract_images function.
	 *
	 * @dataProvider dataprovider_extract_images
	 * @param string $html HTML string to extract images from.
	 * @param string $sourceurl The URL the HTML was found at (Used to resolve relative URLs)
	 * @param array $expected Expected array of extracted images.
	 */
	public function test_extract_images($html, $sourceurl, $expected) {
		$html = new \pdyn\datatype\Html($html, $sourceurl);
		$images = $html->extract_images();
		$this->assertEquals($expected, $images);
	}

	/**
	 * Dataprovider for test_extract_metatags.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_metatags() {
		return array(
			array(
				'',
				[],
			),
			array(
				'This is a test',
				[],
			),
			array(
				'<b>This is a test</b>',
				[],
			),
			array(
				'<meta name="test" content="test2"/>',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<META name="test" content="test2"/>',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<MeTa name="test" content="test2"/>',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<meta name="test" content="test2"></meta>',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<meta name="test" content="test2">',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<meta name="test" content="test2" http-equiv="test3"/>',
				array(
					'test' => 'test2',
					'test3' => 'test2',
				),
			),
			array(
				'<meta content="test2" http-equiv="test3"/>',
				array(
					'test3' => 'test2',
				),
			),
			array(
				'<html><head><meta name="test" content="test2"/></head><body><h1>Hello World!</h1></body></html>',
				array(
					'test' => 'test2',
				),
			),
			array(
				'<html><head><meta name="test" content="test2"/><meta name="test3" content="test4"/></head><body><h1>Hello World!</h1></body></html>',
				array(
					'test' => 'test2',
					'test3' => 'test4',
				),
			),
		);
	}

	/**
	 * Test extract_metatags function.
	 *
	 * @dataProvider dataprovider_extract_metatags
	 * @param string $html HTML string to extract from.
	 * @param array $expected Expected array of extracted information.
	 */
	public function test_extract_metatags($html, $expected) {
		$html = new \pdyn\datatype\Html($html);
		$result = $html->extract_metatags();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Dataprovider for test_extract_rssfeeds.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_rssfeeds() {
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
				'<link type="application/rss+xml" href="feed.xml"/>',
				[
					[
						'type' => 'application/rss+xml',
						'href' => 'feed.xml',
					],
				],
			],
			[
				'<link type="application/rss+xml" href="feed.xml" title="test"/>',
				[
					[
						'type' => 'application/rss+xml',
						'href' => 'feed.xml',
						'title' => 'test',
					],
				],
			],
			[
				'<link type="application/rss+xml" href="feed.xml" title="test"/><link type="application/rss+xml" href="feed2.xml" title="test2"/>',
				[
					[
						'type' => 'application/rss+xml',
						'href' => 'feed.xml',
						'title' => 'test',
					],
					[
						'type' => 'application/rss+xml',
						'href' => 'feed2.xml',
						'title' => 'test2',
					],
				],
			],
			[
				'<link type="application/rss+xml" href="feed.xml" title="test"/>'
				.'<link type="application/xml" href="feed2.xml" title="test2"/>'
				.'<link type="application/atom+xml" href="feed3.xml" title="test3"/>'
				.'<link type="text/xml" href="feed4.xml" title="test4"/>'
				.'<link type="text/html" href="somethingelse.html" title="test5"/>'
				.'<link type="application/rss+xml" title="test6"/>'
				.'<link href="test7" title="test4"/>',
				[
					[
						'type' => 'application/rss+xml',
						'href' => 'feed.xml',
						'title' => 'test',
					],
					[
						'type' => 'application/xml',
						'href' => 'feed2.xml',
						'title' => 'test2',
					],
					[
						'type' => 'application/atom+xml',
						'href' => 'feed3.xml',
						'title' => 'test3',
					],
					[
						'type' => 'text/xml',
						'href' => 'feed4.xml',
						'title' => 'test4',
					],
				],
			],
		];
	}

	/**
	 * Test extract_rssfeeds function.
	 *
	 * @dataProvider dataprovider_extract_rssfeeds
	 * @param string $html HTML string to extract from.
	 * @param array $expected Expected array of extracted information.
	 */
	public function test_extract_rssfeeds($html, $expected) {
		$html = new \pdyn\datatype\Html($html);
		$result = $html->extract_rssfeeds();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Dataprovider for test_extract_opengraph.
	 *
	 * @return array Array of arrays of test parameters.
	 */
	public function dataprovider_extract_opengraph() {
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
				'<meta property="test" content="test2"/>',
				[],
			],
			[
				'<meta property="og:test" content="test2"/>',
				[
					'test' => 'test2',
				],
			],
			[
				'<meta content="test2" property="og:test" />',
				[
					'test' => 'test2',
				],
			],
			[
				'<meta property="og:test1" content="test2" /><meta content="test3" property="og:test4" />',
				[
					'test1' => 'test2',
					'test4' => 'test3',
				],
			],
		];
	}

	/**
	 * Test extract_opengraph function.
	 *
	 * @dataProvider dataprovider_extract_opengraph
	 * @param string $html HTML string to extract from.
	 * @param array $expected Expected array of extracted information.
	 */
	public function test_extract_opengraph($html, $expected) {
		$html = new \pdyn\datatype\Html($html);
		$result = $html->extract_opengraph();
		$this->assertEquals($expected, $result);
	}
}
