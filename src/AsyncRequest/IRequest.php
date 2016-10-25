<?php

namespace AsyncRequest;

interface IRequest
{
	/**
	 * Returns cURL handle
	 * @return resource CURL handle
	 */
	public function getHandle();

	/**
	 * Creates own implementation of response
	 * @param string $curlResponse
	 * @return mixed Custom response object
	 */
	public function createResponse(string $curlResponse);
}
