<?php

namespace helvete\ApiRequester;

/**
 * Class for requesting remote APIs
 */
class Client {

	const LIB_VERSION = '0.48';

	const UA_COMP = 'Mozilla/5.0';
	const UA_DEFAULT = 'DEFAULT';

	const BASE64_PATTERN = '%^[a-zA-Z0-9/+]*={0,2}$%';

	const METHOD_GET = "GET";
	const METHOD_POST = "POST";
	const METHOD_PUT = "PUT";
	const METHOD_DELETE = "DELETE";
	const METHOD_OPTIONS = "OPTIONS";
	const METHOD_TRACE = "TRACE";

	/**
	 * Request method
	 */
	protected $_method;

	/**
	 * Request headers
	 */
	protected $_headers = array();

	/**
	 * Request URL
	 */
	protected $_url;

	/**
	 * Request string
	 */
	protected $_requestString = '';

	/**
	 * Follow redirects flag
	 */
	protected $_followRedirect = true;

	/**
	 * Data from stdin
	 */
	protected $_stdinData = false;

	/**
	 * Class construct
	 */
	public function __construct(
		$optionsFile,
		$ua = self::UA_DEFAULT,
		$stdin = false
	) {
		if (empty($optionsFile)) {
			throw new \Exception('Options file name not supplied');
		}
		if (!file_exists($optionsFile)) {
			throw new \Exception('Options file does not exist');
		}
		$this->_stdinData = $stdin;
		$this->_parseOptions($optionsFile);
		$this->setUserAgent($ua);
	}


	/**
	 * Set user agent header
	 *	provide self::UA_DEFAULT for a default one
	 *
	 * @param  string	$agent
	 * @return void
	 */
	public function setUserAgent($agent = null) {
		if (is_null($agent) || $agent === true) {
			return;
		}
		$s = '%s (compatible; ApiRequester/%s) github.com/helvete/request';
		$uaString = sprintf($s, static::UA_COMP, static::LIB_VERSION);
		$this->_headers[] = $agent === static::UA_DEFAULT
			? "User-Agent: {$uaString}"
			: $agent;
	}


	/**
	 * Parse options from options file
	 *
	 * @param  string	$optionsFile
	 * @return self
	 */
	protected function _parseOptions($optionsFile) {
		$allowedKeys = array(
			'METHOD',
			'HEADERS',
			'URL',
			'REQUEST_STRING'
		);

		$handle = fopen($optionsFile, "r");

		// handle readability
		if (!$handle) {
			throw new \Exception('Options file is unreadable');
		}
		$processing = '';
		while (($line = fgets($handle)) !== false) {

			// trim lines in file to allow indentation
			$key = trim($line);

			// allow hash comments within options file, skip empty lines
			if (substr($key, 0, 1) === '#' || !strlen($key)) {
				continue;
			}
			// process keys in options file
			if (in_array($key, $allowedKeys)) {
				if (!empty($processing)) {
					unset($allowedKeys[$processing]);
				}
				$processing = $key;

				continue;
			}
			if ($processing === 'REQUEST_STRING'
				&& ($this->_method === 'GET' || $this->_stdinData)
			) {
				continue;
			}
			// process data in options file
			switch ($processing) {
			case 'HEADERS':
				$this->_headers[] = $this->handleBase64Auth($key);
				break;
			case 'REQUEST_STRING':
				$this->_requestString .= "{$line}";
				break;
			case 'METHOD':
				$key = strtoupper($key);
			case 'URL':
				$propertyName = "_" . strtolower($processing);
				$this->$propertyName = $key;
				break;
			default:
				throw new \Exception('Unknown options file key');
				break;
			}
		}
		fclose($handle);

		return $this;
	}


	/**
	 * Do the actual job
	 *
	 * @return string
	 */
	public function request() {
		$ch = curl_init($this->_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followRedirect);

		switch ($this->_method) {
		case self::METHOD_POST:
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getReqStr());
			break;
		case self::METHOD_PUT:
		case self::METHOD_DELETE:
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getReqStr());
		case self::METHOD_OPTIONS:
		case self::METHOD_TRACE:
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_method);
			break;
		case self::METHOD_GET:
		default:
			break;
		}

		return new ResultDto($ch);
	}


	/**
	 * Get request string for logging purposes
	 *
	 * @return string
	 */
	public function getReqStr() {
		return $this->_stdinData ?: $this->_requestString;
	}


	/**
	 * Get request url for logging purposes
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->_url;
	}


	/**
	 * Get request mthod for logging purposes
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->_method ?: self::METHOD_GET;
	}


	/**
	 * Get request mthod for logging purposes
	 *
	 * @return string
	 */
	public function getHeaders() {
		return $this->_headers;
	}


	/**
	 * Change redirect flag status
	 *
	 * @param  bool $followRedirect
	 * @return void
	 */
	public function setFollowRedirect($followRedirect) {
		$this->_followRedirect = (bool)$followRedirect;
		return $this;
	}


	/**
	 * Attempt to base64 basic auth string in a form `username:password`
	 *
	 * @param  string	$header
	 * @return string
	 */
	protected function handleBase64Auth($header)
	{
		@list($hName, $hType, $hCred) = explode(' ', $header);
		if (strtolower($hName) !== 'authorization:') {
			return $header;
		}
		if (strtolower($hType) !== 'basic') {
			return $header;
		}
		if (preg_match(static::BASE64_PATTERN, $hCred)) {
			return $header;
		}
		return "{$hName} {$hType} " . base64_encode($hCred);
	}
}
