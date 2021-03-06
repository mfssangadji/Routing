<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Routing\RouteDispatcher;

use ICanBoogie\Event;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Routing\RouteDispatcher;
use ICanBoogie\Routing\Route;

/**
 * Event class for the `ICanBoogie\Routing\RouteDispatcher::dispatch:before` event.
 *
 * Third parties may use this event to provide a response to the request before the route is
 * mapped. The event is usually used by third parties to redirect requests or provide cached
 * responses.
 *
 * @property-read Route $route
 * @property-read Request $request
 * @property Response $response
 */
class BeforeDispatchEvent extends Event
{
	const TYPE = 'dispatch:before';

	/**
	 * The route.
	 *
	 * @var Route
	 */
	private $route;

	/**
	 * @return Route
	 */
	protected function get_route()
	{
		return $this->route;
	}

	/**
	 * The HTTP request.
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * @return Request
	 */
	protected function get_request()
	{
		return $this->request;
	}

	/**
	 * Reference to the HTTP response.
	 *
	 * @var Response|null
	 */
	private $response;

	/**
	 * @return Response|null
	 */
	protected function get_response()
	{
		return $this->response;
	}

	/**
	 * @param Response|null $response
	 */
	protected function set_response(Response &$response = null)
	{
		$this->response = $response;
	}

	/**
	 * The event is constructed with the type {@link self::TYPE}.
	 *
	 * @param RouteDispatcher $target
	 * @param Route $route
	 * @param Request $request
	 * @param Response|null $response
	 */
	public function __construct(RouteDispatcher $target, Route $route, Request $request, Response &$response = null)
	{
		$this->route = $route;
		$this->request = $request;
		$this->response = &$response;

		parent::__construct($target, self::TYPE);
	}
}
