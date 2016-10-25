<?php

namespace AsyncRequest;

class RequestCallback
{

	/** @var IRequest */
	private $request;

	/** @var ?callable */
	private $callback;

	public function __construct(IRequest $request, ?callable $callback = NULL)
	{
		if ($callback !== null && !is_callable($callback)) {
			throw new Exception('Invalid callback');
		}

		$this->request = $request;
		$this->callback = $callback;
	}

	public function getRequest(): IRequest
	{
		return $this->request;
	}

	public function getCallback(): ?callable
	{
		return $this->callback;
	}

}
