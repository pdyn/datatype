<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @copyright 2010 onwards James McQuillan (http://pdyn.net)
 * @author James McQuillan <james@pdyn.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace pdyn\datatype;

/**
 * Class for dealing with URLs.
 */
class Url extends \pdyn\datatype\Base {
	/** @var string The scheme of the URL. */
	protected $scheme = 'http';

	/** @var string The host of the URL. */
	protected $host = '';

	/** @var string The user accessing the URL. */
	protected $user = null;

	/** @var string The password used by the user. */
	protected $pass = null;

	/** @var string The port of the URL. */
	protected $port = null;

	/** @var string The path of the URL. */
	protected $path = '';

	/** @var array The query of the URL. */
	protected $query = [];

	/**
	 * Constructor.
	 *
	 * @param string $text The text you want to work with.
	 */
	public function __construct($url) {
		if (static::validate($url) !== true) {
			throw new \Exception('Bad URL passed to URL datatype.', 400);
		}

		$urlparse = parse_url($url);

		$this->scheme = (isset($urlparse['scheme'])) ? $urlparse['scheme'] : 'http';
		$this->host = $urlparse['host'];

		if (isset($urlparse['user'])) {
			$this->user = $urlparse['user'];
		}

		if (isset($urlparse['pass'])) {
			$this->pass = $urlparse['pass'];
		}

		if (isset($urlparse['port'])) {
			$this->port = $urlparse['port'];
		}

		if (isset($urlparse['path'])) {
			$this->path = $urlparse['path'];
		}

		if (isset($urlparse['query'])) {
			parse_str($urlparse['query'], $this->query);
		}
	}

	/**
	 * Get the value of the datatype.
	 *
	 * @return mixed The raw value.
	 */
	public function val() {
		$return = $this->scheme.'://';

		if ($this->user !== null) {
			$return .= $this->user;
			if ($this->pass !== null) {
				$return .= ':'.$this->pass;
			}
			$return .= '@';
		}
		$return .= $this->host;

		if ($this->port !== null) {
			$return .= ':'.$this->port;
		}

		$return .= $this->path;
		if (!empty($this->query)) {
			$return .= '?'.http_build_query($this->query);
		}
		return $return;
	}

	/**
	 * Add CSRF-protection token to URL.
	 */
	public function addcsrftok() {
		$csrftok = (!empty($_COOKIE['CSRF_TOK'])) ? $_COOKIE['CSRF_TOK'] : '';
		$this->addquery(['CSRF_TOK' => $csrftok]);
	}

	/**
	 * Add a query to the URL.
	 *
	 * @param string|array $query Either a string query in the form key=value or an array in the form ['key' => 'value']
	 */
	public function addquery($query) {
		if (is_string($query)) {
			parse_str($query, $query);
		}
		if (is_array($query)) {
			$this->query = array_merge($this->query, $query);
		} else {
			throw new \Exception('Bad query string passed to addquery', 400);
		}
	}

	/**
	 * Remove a query from the URL.
	 *
	 * @param string $key The key of the query to remove.
	 */
	public function removequery($key) {
		if (isset($this->query[$key])) {
			unset($this->query[$key]);
		}
	}

	/**
	 * Gets the domain name of the URL.
	 *
	 * @param bool $includesubdomains Whether to include any subdomains in the URL, or just the main domain name.
	 */
	public function get_domain($includesubdomains = true) {
		preg_match('#(?:http|https)://(?:www\.)?([^/]+)/?#i', $this->val(), $matches);
		$domain = (!empty($matches[1])) ? $matches[1] : '';

		if ($includesubdomains === false) {
			$parts = explode('.', $domain);
			return implode('.', array_slice($parts, -2));
		} else {
			return $domain;
		}
	}

	/**
	 * Validate datatype.
	 *
	 * @param mixed $input Value to validate.
	 * @return bool Value is valid or not.
	 */
	public static function validate($input) {
		if (is_string($input) && filter_var($input, FILTER_VALIDATE_URL) !== false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This takes an ambiguous URL (i.e. relative or absolute) and ensures it is an absolute URL
	 * (ex. "/pics/img.jpg" found at http://google.com becomes "http://google.com/pics/img.jpg")
	 *
	 * @param string $i The ambiguous URL (ex. http://google.com, /pics/img.jpg, or img.jpg)
	 * @param string $sourceurl The URL of the page that referenced $i
	 * @param bool $source_prevalidated A flag to indicate whether $sourceurl has already been through \pdyn\datatype\Url::validate().
	 *                                  This is mainly a time-saving feature as this function can be called A LOT.
	 * @return string The absolute URL
	 */
	public static function make_absolute($input, $sourceurl = null, $source_prevalidated = false) {
		if (static::validate($input) !== true) {

			// Check for absolute urls without the http. i.e. //example.com/index.php.
			if (mb_strpos($input, '//') === 0) {
				if (static::validate('http:'.$input) === true) {
					return 'http:'.$input;
				}
			}

			if (mb_strpos($input, 'data:') === 0) {
				return $input;
			}

			// Handle sourceurl validation - we allow prevalidation from parent functions to speed up code.
			if ((empty($sourceurl)) || ($source_prevalidated === false && static::validate($sourceurl) === false)) {
				return 'http://'.$input;
			}

			$srcurlparts = parse_url($sourceurl);

			// We have a relative URL - or an invalid protocol.
			if (mb_substr($input, 0, 1) === '/' && isset($srcurlparts['host'])) {
				// We have a URL like /test.jpg, so we just add the parent domain name.
				$input = $srcurlparts['host'].$input;
			} else {
				$srcbase = $srcurlparts['host'];
				if (isset($srcurlparts['path'])) {
					$lastchunk = mb_substr($srcurlparts['path'], mb_strrpos($srcurlparts['path'], '/'));
					if (mb_strpos($lastchunk, '.') !== false) {
						$srcbase .= dirname($srcurlparts['path']);
					} else {
						$srcbase .= $srcurlparts['path'];
					}
				}

				if (mb_substr($srcbase, -1) != '/') {
					$srcbase .= '/';
				}

				// We have a URL like test.jpg or ../test.jpg so we have to add the path to the originating HTML page.
				$input = $srcbase.$input;

				// Resolve ./
				$input = str_replace('/./', '/', $input);

				// Resolve ../
				$last_i = '';
				while ($input !== $last_i) {
					$last_i = $input;
					$input = preg_replace('#/([^/])+/\.\./#iUms', '/', $input);
				}
			}
			$input = $srcurlparts['scheme'].'://'.$input;
		}

		return $input;
	}

	/**
	 * Normalize a partial URL string to a full URL.
	 *
	 * @param string $uri A partial URL string. Ex. google.com
	 * @param bool $xri Whether the URL is an xri
	 * @return string The normalized URL.
	 */
	public static function normalize($uri, $xri = false) {
		if (empty($uri)) {
			return false;
		}

		$uri = trim($uri);


		if ($uri{0} === '/' && $uri{1} === '/') {
			// URIs like '//example.com';
			$uri = 'http:'.$uri;
		}

		$uparts = @parse_url($uri);
		if (!$uparts) {
			return false;
		}

		$validXRIfirstChars = ['=', '@', '+', '$', '!', '('];

		if (!empty($uparts['scheme']) && $uparts['scheme'] === 'xri') {
			$uri = mb_substr($uri, 6);
			$uparts['scheme'] = '';
		}

		if (empty($uparts['scheme'])) {
			// If we have no start scheme and start with a valid XRI global context, just return.
			if (in_array(mb_substr($uri, 0, 1), $validXRIfirstChars, true)) {
				return ($xri === true) ? $uri : false;
			}
			$uri = 'http://'.$uri;
			$uparts = parse_url($uri);
		} elseif (empty($uparts['host'])) {
			$uri = 'http://'.$uri;
			$uparts = parse_url($uri);
		}

		$uri = $uparts['scheme'].'://';
		if (!empty($uparts['user'])) {
			$userpart = $uparts['user'];
		}
		if (!empty($uparts['pass'])) {
			$userpart .= ':'.$uparts['pass'];
		}
		if (!empty($uparts['user'])) {
			$uri .= $userpart.'@';
		}
		$uri .= $uparts['host'];
		if (!empty($uparts['port'])) {
			$uri .= ':'.$uparts['port'];
		}
		$uri .= (!empty($uparts['path'])) ? $uparts['path'] : '/';
		if (!empty($uparts['query'])) {
			$uri .= '?'.$uparts['query'];
		}

		return $uri;
	}

	/**
	 * Get a "clean" representation of a URL. Strip the protocol and clean it up. Use for display purposes only!
	 *
	 * @param string $url The URL to clean.
	 * @return string The cleaned URL.
	 */
	public static function get_clean_url($url) {
		$url = preg_replace('#^(.+)://#iUms', '', $url);
		$url = rtrim($url, '/');
		return $url;
	}
}
