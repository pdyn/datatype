<?php
namespace pdyn\datatype;

/**
 * Class for working with HTML strings.
 */
class Html extends \pdyn\datatype\Base {

	/** Regex to extract IMG tags. */
	const IMG_EXTRACT = '#<img[^>]*src=(\'|\")([^>]*)\1[^>]*/?>#iU';

	/** Regex to extract HTML tags */
	const EXTRACT_HTMLTAG = '<(/?\w+)(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^>\s]+))?)+\s*|\s*)/?>';

	/** Regex to extract HTML tags */
	const EXTRACT_HTMLTAG_ALLINFO = '<(/?\w+)((\s+\w+(\s*=\s*(".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>';

	/** Regex to match HTML tags */
	const MATCH_HTMLTAG = '</?\w+(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>';

	/** @var string The HTML */
	protected $val = '';

	/** @var A URL the HTML was found at. Used for resolving relative URLs within HTML. */
	protected $sourceurl = null;

	/**
	 * Constructor.
	 *
	 * @param string $html An HTML string to use.
	 * @param string $sourceurl A URL the HTML was found at. Used for resolving relative URLs within HTML.
	 */
	public function __construct($html, $sourceurl = null) {
		$this->val = $html;
		$this->sourceurl = (\pdyn\datatype\Url::validate($sourceurl) === true) ? $sourceurl : null;
	}

	/**
	 * Returns an array of all XHTML void elements - elements which dont need closing tags.
	 *
	 * @return array Array of XHTML void elements.
	 */
	public static function get_void_elements() {
		return [
			'area' => 'area',
			'base' => 'base',
			'br' => 'br',
			'col' => 'col',
			'command' => 'command',
			'embed' => 'embed',
			'hr' => 'hr',
			'img' => 'img',
			'input' => 'input',
			'keygen' => 'keygen',
			'link' => 'link',
			'meta' => 'meta',
			'param' => 'param',
			'source' => 'source',
			'track' => 'track',
			'wbr' => 'wbr'
		];
	}

	/**
	 * Get a list of safe HTML tags for use by $this->clean()
	 *
	 * @return array An array of safe HTML tags.
	 */
	public static function get_allowed_tags() {
		return [
			'strong' => [],
			'em' => [],
			'b' => [],
			'u' => [],
			'i' => [],
			'a' => ['href' => 'uri'],
			'p' => ['opts' => ['max' => 2]],
			'br' => ['opts' => ['max' => 6]],
			'p' => [],
			'br' => [],
			'ul' => [],
			'li' => [],
			'hr' => [],
			'img' => ['src' => 'uri', 'alt' => 'txt', 'title' => 'txt'],
			'h1' => [],
			'h2' => [],
			'h3' => [],
			'h4' => [],
			'h5' => [],
		];
	}

	/**
	 * Extract all occurrances of an HTML tag.
	 *
	 * @param string $tag The tag to extract. i.e. "a", "img", "div", etc.
	 * @return array Numeric array of all occurrances.
	 *                   Values are arrays with indexes "attr" and "context".
	 *                       attr: Array of attributes. name=>value.
	 *                       content: Any content of the tag.
	 */
	public function extract_tag($tag, $regex = null) {
		$res = [];
		$regexes = [
			'self_close' => '<\s*'.$tag.'\s*((?:\w+\s*=\s*"[^"]*"|\'[^\']*\'|[^"\'\s>]*\s*)*)\s*\/>',
			'with_content' => '<\s*'.$tag.'(.*)>(.*)<\s*\/\s*'.$tag.'\s*>',
			'self_close_badform' => '<\s*'.$tag.'\s*((?:\w+\s*=\s*"[^"]*"|\'[^\']*\'|[^"\'\s>]*\s*)*)\s*>',
		];
		$html = $this->val;
		foreach ($regexes as $regex) {
			preg_match_all('#'.$regex.'#iU', $html, $matches, PREG_SET_ORDER);
			if (!empty($matches) && is_array($matches)) {
				foreach ($matches as $match) {
					$attrs = [];
					$html = str_replace($match[0], '', $html);
					if (!empty($match[1])) {
						//thanks to Gumbo @ http://stackoverflow.com/questions/1083792/php-split-a-string-of-html-attributes-into-an-indexed-array
						$regex = '#([\w-]+)\s*=\s*("[^"]*"|\'[^\']*\'|[^"\'\s>]*)#';
						preg_match_all($regex, $match[1], $atrmatches, PREG_SET_ORDER);
						if (!empty($atrmatches) && is_array($atrmatches)) {
							foreach ($atrmatches as $atrmatch) {
								if (isset($atrmatch[2])) {
									$attrs[trim($atrmatch[1])] = trim($atrmatch[2], "\x00..\x1F'\"");
								}
							}
						}
					}

					$res[] = [
						'attrs' => (!empty($attrs)) ? $attrs : [],
						'content' => (!empty($match[2])) ? trim($match[2]) : ''
					];
				}
			}
		}
		return $res;
	}

	/**
	 * Extract images from HTML.
	 *
	 * @return array Array of image URLs from the HTML.
	 */
	public function extract_images($orderbysize = false, \pdyn\cache\CacheInterface $cache = null) {
		$images = $this->extract_tag('img');
		$output = [];
		foreach ($images as $image) {
			if (empty($image['attrs']['src'])) {
				continue;
			}
			$imgurl = $image['attrs']['src'];

			// Resolve relative URLs.
			if (!empty($this->sourceurl)) {
				$imgurl = \pdyn\datatype\Url::make_absolute($imgurl, $this->sourceurl);
			}

			$output[$imgurl] = $imgurl;
		}

		$output = array_keys($output);
		// TODO: we need more protection here.
		/*
		if (false && $orderbysize === true && !empty($cache) && $cache instanceof \pdyn\cache\CacheInterface) {
			$imgSizes = [];
			$i = 0;
			$httpclient = new \pdyn\httpclient\HttpClient();
			foreach ($images as $img) {
				$imgRes = \pdyn\http\HttpResource::instance($img, $cache, $httpclient);
				$imgSizes[$img] = $imgRes->get_filesize();
				$i++;
				if ($i >= 10) {
					break;
				}
			}
			arsort($imgSizes);
			$images = array_keys($imgSizes);
		}
		*/

		return $output;
	}

	/**
	 * Extract metatags from HTML.
	 *
	 * @return array Array of META and LINK tags, indexed by name, http-equiv, or link REL.
	 */
	public function extract_metatags() {
		$output = [];
		$metatags = $this->extract_tag('meta');
		foreach ($metatags as $metatag) {
			if (empty($metatag['attrs']['content']) || (empty($metatag['attrs']['name']) && empty($metatag['attrs']['http-equiv']))) {
				continue;
			}
			if (!empty($metatag['attrs']['name'])) {
				$output[$metatag['attrs']['name']] = $metatag['attrs']['content'];
			}
			if (!empty($metatag['attrs']['http-equiv'])) {
				$output[$metatag['attrs']['http-equiv']] = $metatag['attrs']['content'];
			}
		}

		$linktags = $this->extract_tag('link');
		foreach ($linktags as $linktag) {
			if (empty($linktag['attrs']['rel']) || empty($linktag['attrs']['href'])) {
				continue;
			}
			$output['link'][$linktag['attrs']['rel']] = $linktag['attrs']['href'];
		}

		return $output;
	}

	/**
	 * Extract RSS feeds from LINK tags.
	 *
	 * @return array Array of RSS feed URLs
	 */
	public function extract_rssfeeds() {
		$output = [];
		$linktags = $this->extract_tag('link');
		foreach ($linktags as $linktag) {
			if (empty($linktag['attrs']['type']) || empty($linktag['attrs']['href'])) {
				continue;
			}
			$validtypes = ['application/xml', 'application/rss+xml', 'application/atom+xml', 'text/xml'];
			if (in_array($linktag['attrs']['type'], $validtypes)) {
				$output[$linktag['attrs']['href']] = $linktag['attrs'];
			}
		}
		$output = array_values($output);
		return $output;
	}

	/**
	 * Extract OpenGraph data.
	 *
	 * @return array Array of OpenGraph information.
	 */
	public function extract_opengraph() {
		$output = [];
		$metatags = $this->extract_tag('meta');
		foreach ($metatags as $metatag) {
			if (empty($metatag['attrs']['property']) || empty($metatag['attrs']['content'])) {
				continue;
			}
			if (mb_strpos($metatag['attrs']['property'], 'og:') !== 0) {
				continue;
			}
			$output[mb_substr($metatag['attrs']['property'], 3)] = $metatag['attrs']['content'];
		}
		return $output;
	}

	/**
	 * Closes unclosed HTML tags.
	 *
	 * @param array $m An optional array of pre-extracted tags. This is useful when using this function with another function that
	 *                 performs tag extraction.
	 * @return string The input string with all HTML tags closed.
	 */
	public function close_tags($m = array()) {
		$void_elements = static::get_void_elements();

		// Extract tags if no pre-extracted array is available.
		if (empty($m)) {
			preg_match_all('#'.static::EXTRACT_HTMLTAG.'#iums', $this->val, $m);
		}

		$tags = [];
		$tag_counts = array_count_values($m[1]);
		foreach ($tag_counts as $t => $c) {
			if ($t[0] !== '/' && in_array($t, $void_elements, true)) {
				continue;
			}
			if ($t[0] !== '/') {
				if (isset($tags[$t])) {
					$tags[$t] += $c;
				} else {
					$tags[$t] = $c;
				}
			} else {
				$base = mb_substr($t, 1);
				if (isset($tags[$base])) {
					$tags[$base] -= $c;
				}
				if (isset($tags[$base]) && $tags[$base] < 0) {
					unset($tags[$base]);
				}
			}
		}
		$tags = array_reverse($tags, true); //$tags is in order found from left to right, reversing should nest tags properly...
		foreach ($tags as $tag => $unclosed_count) {
			if ($unclosed_count <= 0) {
				continue;
			}
			$this->val .= str_repeat('</'.$tag.'>', $unclosed_count);
		}
	}

	/**
	 * HTML-Safe text truncate.
	 *
	 * This will truncate HTML to a specific amount of visible-characters and handle the associated tag manipulation.
	 *
	 * @param int $length The length of visible characters. DOES NOT include tags.
	 * @param bool $close_unclosed_tags Whether to close open tags after truncation.
	 * @return string The truncated text.
	 */
	public function truncate($length, $close_unclosed_tags=true) {
		$encoding_fallback = 'ISO-8859-1';
		$truncation_indicator = '...';

		// Detect encoding, or use fallback.
		if (!$encoding = mb_detect_encoding($this->val)) {
			$encoding = $encoding_fallback;
		}

		// Validate encoding, or use fallback.
		$encoding = (mb_check_encoding($this->val, $encoding))
			? $encoding
			: $encoding_fallback;

		$valid_encodings = [
			'ISO-8859-1',
			'ISO-8859-15',
			'UTF-8',
			'cp866',
			'cp1251',
			'cp1252',
			'KOI8-R',
			'BIG5',
			'GB2312',
			'BIG5-HKSCS',
			'Shift_JIS',
			'EUC-JP'
		];
		// Htmlentities can't handle ascii encoding.
		$htmlentity_encoding = (in_array($encoding, $valid_encodings, true)) ? $encoding : 'ISO-8859-1';
		$preg_utf8_modifier = ($encoding === 'UTF-8') ? 'u' : '';

		preg_match_all('#('.static::MATCH_HTMLTAG.')#i'.$preg_utf8_modifier, $this->val, $htmlmatches);
		$i_split = preg_split('#'.static::MATCH_HTMLTAG.'#i'.$preg_utf8_modifier, $this->val);

		if (empty($htmlmatches[1])) {
			if (mb_strlen($this->val, 'UTF-8') <= $length) {
				return;
			}
			// If there's no html we don't have to do all this fancy junk.
			$this->val = mb_substr($this->val, 0, $length, $encoding).$truncation_indicator;
			return;
		}

		// Calculate text-only length.
		$txt_len = 0;
		foreach ($i_split as $i => $val) {
			$txt_len += mb_strlen($val, 'UTF-8');
		}
		if ($txt_len <= $length) {
			return;
		}

		// Do shorten.
		$cur_count = 0;
		$i_split_shortened = [];
		foreach ($i_split as $i => $val) {
			$val = html_entity_decode($val, 0, $htmlentity_encoding);
			if (($cur_count + mb_strlen($val, $encoding)) >= $length) {
				$i_split_shortened[$i] = mb_substr($val, 0, ($length - $cur_count), $encoding);
				$i_split_shortened[$i] = htmlentities($i_split_shortened[$i], 0, $htmlentity_encoding, false);
				break;
			} else {
				$i_split_shortened[$i] = $val;
				$i_split_shortened[$i] = htmlentities($val, 0, $htmlentity_encoding, false);
				$cur_count += mb_strlen($val, $encoding);
			}
		}

		// Add back in full HTML.
		$this->val = '';
		foreach ($i_split_shortened as $i => $txt) {
			if (isset($htmlmatches[1][($i - 1)])) {
				$this->val .= $htmlmatches[1][($i - 1)].$txt;
			}
		}

		$this->val .= $truncation_indicator;

		// Close open html tags.
		if ($close_unclosed_tags === true) {
			$this->close_tags();
		}

	}

	/**
	 * Clean an HTML string.
	 *
	 * Performs the following functions:
	 *     - Removes any unsafe tags.
	 *     - For allowed tags that specify URL attributes, transforms relative URLs to absolute URLs.
	 *
	 * @param array|bool $allowed_tags An array of allow tags, or true for the internal list of safe tags.
	 * @return string The cleaned HTML string.
	 */
	public function clean($allowed_tags = true) {
		static $abs_urls = [];

		preg_match_all('#'.static::EXTRACT_HTMLTAG.'#iums', $this->val, $input_html);
		if (empty($input_html[0])) {
			$this->val = static::escape_html($this->val);
			return;
		}

		$input_text = preg_split('#'.static::EXTRACT_HTMLTAG.'#iums', $this->val);

		if ($allowed_tags === true || !is_array($allowed_tags)) {
			$allowed_tags = static::get_allowed_tags();
		}

		$void_elements = static::get_void_elements();

		$this->val = '';
		$tag_count = [];

		// Everything in this loop gets called a lot, reduce wherever posible.
		foreach ($input_html[1] as $i => $tag) {

			$closingtag = false;
			if ($tag{0} === '/') {
				$closingtag = true;
				$tag = mb_substr($tag, 1);
			}

			$tag = (!isset($allowed_tags[$tag])) ? mb_strtolower($tag) : $tag;

			if (isset($allowed_tags[$tag])) {

				// Clean up allowed tags.
				if ($closingtag === true) {
					// Add a closing tag, or remove needless closing tags for void elements.
					$html = (isset($void_elements[$tag])) ? '' : '</'.$tag.'>';
				} else {

					if (isset($allowed_tags[$tag]['opts']['max']) && isset($tag_count[$tag]) && $tag_count[$tag] >= $allowed_tags[$tag]['opts']['max']) {
						unset($allowed_tags[$tag]);
						unset($input_html[1][$i]);
						continue;
					}

					$html = '<'.$tag;
					foreach ($allowed_tags[$tag] as $attr => $type) {
						if ($attr === 'opts') {
							// Opts are options in how to treat the tag (max count, etc), do not treat as an attribute
							continue;
						}

						if (mb_strpos($input_html[0][$i], $attr) !== false) {
							preg_match('#'.$attr.'\s*=\s*("((?U).*)?"|\'((?U).*)?\'|([^>\s]+))\s*#iums', $input_html[0][$i], $m_attr);
							if (!empty($m_attr)) {
								$attr_val = end($m_attr);
								$html .= ' '.$attr.'="';
								if ($type === 'uri') {
									// Fix spaces in URLs.
									$attr_val = str_replace(' ', '+', $attr_val);
									if (!isset($abs_urls[$attr_val])) {
										$abs_urls[$attr_val] = (\pdyn\datatype\Url::validate($attr_val))
												? $attr_val
												: \pdyn\datatype\Url::make_absolute($attr_val, $this->sourceurl, true);
									}
									$html .= $abs_urls[$attr_val];
								} else {
									$html .= htmlspecialchars($attr_val, ENT_QUOTES, 'UTF-8', false);
								}
								$html .= '"';
							}
						}
					}

					if (isset($void_elements[$tag])) {
						$html .= '/';
					}

					$html .= '>';

					if (isset($tag_count[$tag])) {
						$tag_count[$tag]++;
					} else {
						$tag_count[$tag] = 1;
					}
				}
			} else {
				/* here we're removing a disallowed tag from the $input_html array so we can use the array for close_unclosed_tags
				this allows tells close_unclosed_tags which tags remain in the html and lets it do it's job without
				having to do another tag discovery regex on the text so basically, this is just a timesaving feature. */
				unset($input_html[1][$i]);
				$html = '';
			}
			$this->val .= htmlspecialchars($input_text[$i], ENT_COMPAT, 'UTF-8', false).$html;
		}

		//if (isset($input_text[$i+1])) $this->val.=htmlentities($input_text[$i+1],ENT_QUOTES,'UTF-8',false);
		if (isset($input_text[$i + 1])) {
			$this->val .= $input_text[$i + 1];
		}
		$this->close_tags($input_html);
		$this->val = trim($this->val);
	}

	/**
	 * Escape all HTML characters so they're visible in output text.
	 *
	 * @param string $html The input HTML
	 * @return string The output HTML.
	 */
	public static function escape_html($html) {
		return htmlspecialchars($html, ENT_QUOTES, 'UTF-8', false);
	}

	public static function unescape_html($html) {
		return html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	}
}
