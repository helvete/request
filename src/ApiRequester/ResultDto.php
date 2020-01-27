<?php

namespace helvete\ApiRequester;

class ResultDto {

    private $isOk = false;
    private $statusCode;
    private $responseBody;
    private $headers = array();

    public function __construct($curlHandle) {
        $this->setHeaderParserCallBack($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		if ($this->responseBody === false) {
			return;
		}
		$this->statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $this->isOk = true;
    }
    public function getStatusCode() {
        return $this->statusCode;
    }
    public function getResponseBody() {
        return $this->responseBody;
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
