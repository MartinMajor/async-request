<?php

namespace AsyncRequest\Tests;

use AsyncRequest\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

	public function testRequest()
	{
		$request = new Request('http://www.example.com');
		$curlResponse = curl_exec($request->getHandle());
		$response = $request->createResponse($curlResponse);

		$this->assertInstanceOf('AsyncRequest\Response', $response);
		$this->assertEquals(200, $response->getHttpCode());
		$this->assertEquals(null, $response->getError());
		$this->assertNotEmpty($response->getHeaders());
		$this->assertContains('This domain is established', $response->getBody());
	}

}
