<?php

namespace AsyncRequest;

class Request implements IRequest
{

	/** @var string */
	protected $url;

	/** @var resource cURL handler */
	protected $handle;

	/**
	 * @param string $url The URL to fetch.
	 */
	public function __construct(string $url)
	{
		$this->url = $url;
		$this->handle = curl_init($url);
		$this->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->setOption(CURLOPT_HEADER, true);
		$this->setOption(CURLOPT_FOLLOWLOCATION, true);
	}

	/**
	 * Sets cURL option
	 * @param int $curlOption
	 * @param mixed $value
	 */
	public function setOption(int $curlOption, $value): void
	{
		curl_setopt($this->handle, $curlOption, $value);
	}

	/**
	 * @internal
	 * @return resource
	 */
	public function getHandle()
	{
		return $this->handle;
	}

	/**
	 * @internal
	 * @param string $curlResponse
	 * @return Response
	 */
	public function createResponse(string $curlResponse)
	{
		$error = curl_error($this->handle);
		$error = $error === '' ? null : $error;

		$httpCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

		$headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
		$header = trim(substr($curlResponse, 0, $headerSize));
		$headers = preg_split('~\r\n|\n|\r~', $header);

		$body = substr($curlResponse, $headerSize);

		return new Response($this->url, $error, $httpCode, $headers, $body);
	}

	/**
	 * Closes cURL resource and frees the memory.
	 */
	public function __destruct()
	{
		if (isset($this->handle)) {
			curl_close($this->handle);
		}
	}

}
