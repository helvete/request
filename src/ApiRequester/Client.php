<?php

namespace helvete\ApiRequester;

/**
 * Class for requesting remote APIs
 */
class Client {

	const LIB_VERSION = '0.4';

	const UA_COMP = 'Mozilla/5.0';
	const UA_DEFAULT = 'DEFAULT';

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
	 * Class construct
	 */
	public function __construct($optionsFile = null, $ua = self::UA_DEFAULT) {
		if (empty($optionsFile)) {
			throw new \Exception('Options file name not supplied');
		}
		if (!file_exists($optionsFile)) {
			throw new \Exception('Options file does not exist');
		}
		$this->_parseOptions($optionsFile);
		$this->setUserAgent($ua);
	}


	/**
	 * Set user agent header
	 *  provide self::UA_DEFAULT for a default one
	 *
	 * @param  string   $agent
	 * @return void
	 */
	public function setUserAgent($agent = null) {
		if (is_null($agent)) {
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
			if ($processing === 'REQUEST_STRING' && $this->_method === 'GET') {
				continue;
			}
			// process data in options file
			switch ($processing) {
			case ('HEADERS'):
				$this->_headers[] = $key;
				break;
			case ('REQUEST_STRING'):
				$this->_requestString .= "{$line}";
				break;
			case ('METHOD'):
				$key = strtoupper($key);
			case ('URL'):
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
		case "GET":
			break;
		case "POST":
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestString);
			break;
		case "PUT":
		case "DELETE":
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestString);
		case "OPTIONS":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_method);
			break;
		default:
			// other methods not implemented yet, sorry
			throw new \Exception('NIY');
		}
        $result = new ResultDto($ch);
		if (!$result->isOk()) {
			return $this->_handleError($ch);
		}

		return $result;
	}


	/**
	 * Handle request error
	 *
	 * @param  resource	$curlHandle
	 * @return string
	 */
	public function _handleError($curlHandle) {
		$curlError = array();
		if (curl_error($curlHandle)) {
			$curlError = array(
				'curl error' => curl_error($curlHandle),
				'error nr' => curl_errno($curlHandle),
			);
		}
		$json = json_encode(array(
			'result' => 'FAILURE',
			'url' => $this->_url,
			'Code' => curl_getinfo($curlHandle, CURLINFO_HTTP_CODE),
		) + $curlError);

		return array(666 => $json);
	}


	/**
	 * Get request string for logging purposes
	 *
	 * @return string
	 */
	public function getReqStr() {
		return $this->_requestString;
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
		return $this->_method;
	}


	/**
	 * Change redirect flag status
	 *
	 * @param  bool	$followRedirect
	 * @return void
	 */
	public function setFollowRedirect($followRedirect) {
		$this->_followRedirect = (bool)$followRedirect;
		return $this;
	}
}
