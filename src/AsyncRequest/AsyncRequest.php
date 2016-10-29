<?php

namespace AsyncRequest;

class AsyncRequest
{

	const DEFAULT_PRIORITY = 1;

	/** @var resource */
	protected $handle;

	/** @var RequestCallback[] */
	protected $requests = [];

	/** @var \SplPriorityQueue */
	protected $queue;

	/** @var int */
	protected $runningCount = 0;

	/** @var ?int */
	private $parallelLimit = NULL;

	public function __construct()
	{
		$this->handle = curl_multi_init();
		$this->queue = new \SplPriorityQueue();
	}

	/**
	 * Sets number of requests that can be sent in parallel.
	 * Null means no limit (default value).
	 * @param int $parallelLimit
	 * @return void
	 */
	public function setParallelLimit($parallelLimit)
	{
		$this->parallelLimit = $parallelLimit;
	}

	/**
	 * Adds new request to downloading.
	 * @param IRequest $request
	 * @param ?callable $callback
	 * @return void
	 */
	public function enqueue(IRequest $request, $callback = null)
	{
		$this->enqueueWithPriority(static::DEFAULT_PRIORITY, $request, $callback);
	}

	/**
	 * Adds new request to downloading and sets its priority.
	 * Requests with higher priority will be send first.
	 * @param int $priority
	 * @param IRequest $request
	 * @param ?callable $callback
	 * @return void
	 */
	public function enqueueWithPriority($priority, IRequest $request, $callback = null)
	{
		$uuid = (int) $request->getHandle();
		$this->requests[$uuid] = new RequestCallback($request, $callback);
		$this->queue->insert($uuid, $priority);
		$this->startFromQueue();
	}

	/**
	 * Returns number of requests that are running or waiting.
	 * @return int
	 */
	public function count()
	{
		return count($this->requests);
	}

	/**
	 * Download all pages.
	 * This is blocking call so this method ends when all pages are downloaded.
	 * @return void
	 */
	public function run()
	{
		while ($this->count()) {
			$this->waitForData();
			$this->processCompleted();
		}
	}

	/**
	 * Waits for next request to complete but maximum $timeout seconds.
	 * @param float $timeout
	 * @return void
	 */
	public function waitForData($timeout = 1.0)
	{
		if ($this->count() == 0) {
			throw new Exception('No requests are running.');
		}

		while (curl_multi_exec($this->handle, $runningCount) === CURLM_CALL_MULTI_PERFORM);
		curl_multi_select($this->handle, $timeout);
	}

	/**
	 * Process downloaded requests.
	 * @return void
	 */
	public function processCompleted()
	{
		while ($info = curl_multi_info_read($this->handle)) {
			$this->callCallback($info);
		}
	}

	/**
	 * Creates response object and calls callback.
	 * @param array $info
	 * @return void
	 */
	protected function callCallback(array $info)
	{
		$this->runningCount--;

		$uuid = (int) $info['handle'];
		$requestCallback = $this->requests[$uuid];
		$request = $requestCallback->getRequest();
		$callback = $requestCallback->getCallback();
		$handle = $request->getHandle();

		$curlResponse = curl_multi_getcontent($handle);
		curl_multi_remove_handle($this->handle, $handle);
		unset($this->requests[$uuid]);

		$response = $request->createResponse($curlResponse);
		if ($callback !== null) {
			$callback($response, $this);
		}

		$this->startFromQueue();
	}

	/**
	 * Starts new request from queue if there is free space in parallel limit.
	 * @return void
	 */
	protected function startFromQueue()
	{
		$freeSlots = $this->parallelLimit === NULL || $this->runningCount < $this->parallelLimit;

		if (!$this->queue->isEmpty() && $freeSlots) {
			$uuid = $this->queue->extract();
			$request = $this->requests[$uuid]->getRequest();
			curl_multi_add_handle($this->handle, $request->getHandle());
			$this->runningCount++;
		}
	}

}
