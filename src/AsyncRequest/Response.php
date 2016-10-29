<?php

namespace AsyncRequest;

class Response
{

	/** @var string */
	private $url;

	/** @var ?string */
	private $error;

	/** @var int */
	private $httpCode;

	/** @var array */
	private $headers;

	/** @var string */
	private $body;

	/**
	 * Response constructor.
	 * @param string $url
	 * @param ?string $error
	 * @param int $httpCode
	 * @param array $headers
	 * @param string $body
	 */
	public function __construct($url, $error, $httpCode, array $headers, $body)
	{
		$this->url = $url;
		$this->error = $error;
		$this->httpCode = $httpCode;
		$this->headers = $headers;
		$this->body = $body;
	}

	/**
	 * Returns URL of request.
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Returns cURL error string or null if there were no error.
	 * @return ?string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns HTTP status code.
	 * @return int
	 */
	public function getHttpCode()
	{
		return $this->httpCode;
	}

	/**
	 * Returns array of headers.
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Returns string with HTML body.
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Checks if there was cURL error or request was unsuccessful according to status code.
	 * @return bool
	 */
	public function hasError()
	{
		return $this->getError() !== null || $this->getHttpCode() >= 400;
	}

}
