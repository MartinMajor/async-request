<?php

namespace AsyncRequest\Tests;

use AsyncRequest\AsyncRequest;
use AsyncRequest\Request;
use AsyncRequest\Response;
use PHPUnit\Framework\TestCase;

class AsyncRequestTest extends TestCase
{

	protected $urls = [
		'http://www.example.com',
		'http://www.example.org',
	];

	public function testBasic(): void
	{
		$downloaded = 0;

		$callback = function (Response $response) use (&$downloaded) {
			$this->assertContains('This domain is established', $response->getBody());
			$downloaded++;
		};

		$asyncRequest = new AsyncRequest();
		foreach ($this->urls as $url) {
			$asyncRequest->enqueue(new Request($url), $callback);
		}

		$this->assertEquals(2, $asyncRequest->count());

		$asyncRequest->run();

		$this->assertEquals(0, $asyncRequest->count());
		$this->assertEquals(2, $downloaded);
	}

	public function testPriorityAndParallelLimit(): void
	{
		$order = [];

		$callback = function (Response $response, AsyncRequest $asyncRequest) use (&$order) {
			$asyncRequest->enqueueWithPriority(2, new Request($this->urls[0]), function() use (&$order) {
				$order[] = 'inside';
			});
			$order[] = 'outside';
		};

		$asyncRequest = new AsyncRequest();
		$asyncRequest->setParallelLimit(1);
		foreach ($this->urls as $url) {
			$asyncRequest->enqueue(new Request($url), $callback);
		}
		$asyncRequest->run();

		$this->assertEquals(['outside', 'inside', 'outside', 'inside'], $order);
	}

	public function testNoPages(): void
	{
		$asyncRequest = new AsyncRequest();
		$asyncRequest->run();
		$asyncRequest->processCompleted();
	}

	/**
	 * @expectedException \AsyncRequest\Exception
	 */
	public function testWaitForDataEmpty(): void
	{
		$asyncRequest = new AsyncRequest();
		$asyncRequest->waitForData();
	}

}
