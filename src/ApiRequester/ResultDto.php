<?php

namespace helvete\ApiRequester;

class ResultDto {

	private $isOk = true;
	private $statusCode;
	private $responseBody;
	private $headers = array();

	public function __construct($curlHandle) {
		$this->setHeaderParserCallBack($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		$this->statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
		if ($this->responseBody === false) {
			$this->responseBody = json_encode(array(
				'errtype' => 'CURL error',
				'error' => curl_error($curlHandle),
				'errno' => curl_errno($curlHandle),
			));
			$this->isOk = false;
		}
	}
	public function getStatusCode() {
		return $this->statusCode;
	}
	public function getResponseBody($autoInflate = false) {
		if (!$autoInflate || !array_key_exists('content-encoding', $this->headers)) {
			return $this->responseBody;
		}
		switch ($this->headers['content-encoding']) {
		case 'gzip':
			return gzdecode($this->responseBody);
		case 'deflate':
			return gzuncompress($this->responseBody);
		default:
			return $this->responseBody;
		}
	}
	public function getHeaders() {
		return $this->headers;
	}
	public function isOk() {
		return $this->isOk;
	}
	protected function setHeaderParserCallBack($curlHandle) {
		curl_setopt($curlHandle, CURLOPT_HEADERFUNCTION,
			function($curl, $header) {
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) {
					return $len;
				}
				$this->headers[strtolower(trim($header[0]))] = trim($header[1]);
				return $len;
			}
		);
	}
}
