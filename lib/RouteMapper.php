<?php

namespace ICanBoogie\Routing;

use ICanBoogie\HTTP\Request;

class RouteMapper
{
	const PARSED_PATTERN = '__PARSED_PATTERN__';
	const DEFINITION = '//DEFINITION';

	static private function split_uri($uri)
	{
		return explode('/', ltrim($uri, '/'));
	}

	/**
	 * @var RouteCollection
	 */
	private $routes;

	private $tree;

	public function __construct(RouteCollection $routes)
	{
		$this->routes = $routes;

		$key = 'icanboogie:routing:tree';
		$tree = apc_fetch($key);

		if (!$tree)
		{
			$tree = $this->build();
			apc_store($key, $tree);
		}

		$this->tree = $tree;
	}

	protected function build()
	{
		$routes = $this->prepare_routes($this->routes);
		$routes = $this->dispatch_routes($routes);

		return $routes;
	}

	protected function prepare_routes(RouteCollection $routes)
	{
		$prepared_routes = [];

		foreach ($routes as $definition)
		{
			$definition[self::PARSED_PATTERN] = $this->parse_pattern($definition[RouteDefinition::PATTERN]);
			$prepared_routes[] = $definition;
		}

		return $prepared_routes;
	}

	protected function dispatch_routes(array $routes)
	{
		$insert = function(array &$container, array $parts, $value) use (&$insert) {

			$part = array_shift($parts);

			if (count($parts))
			{
				$children = &$container[$part];

				if ($children === null)
				{
					$children = [];
				}

				$insert($children, $parts, $value);

				return;
			}

			$container[$part][self::DEFINITION] = $value;

		};

		$rc = [];

		foreach ($routes as $definition)
		{
			foreach ((array) $definition[RouteDefinition::VIA] as $method)
			{
				$parts = array_merge([ $method ], $definition[self::PARSED_PATTERN]);

				$insert($rc, $parts, $definition);
			}
		}

		return $rc;
	}

	protected function unwind_via(array $routes)
	{
		$unwind = [];

		foreach ($routes as $definition)
		{
			foreach ((array) $definition[RouteDefinition::VIA] as $method)
			{
				$unwind[$method][] = $definition;
			}
		}

		return $unwind;
	}

	/**
	 * @param $pattern
	 *
	 * @return array
	 */
	protected function parse_pattern($pattern)
	{
		$parts = self::split_uri($pattern);

		array_walk($parts, function(&$v) {

			if (!Pattern::is_pattern($v))
			{
				return;
			}

			$v = Pattern::from($v)->regex;

		});

		return $parts;
	}

	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $captured
	 *
	 * @return Route|null
	 */
	public function map($method, $uri, array $captured = null)
	{
		$parts = self::split_uri($uri);

		if (0)
		{
			header("Content-Type: application/json");
			echo json_encode($this->tree);
			exit;
		}

		$definition = $this->search($this->tree, array_merge([ $method ], $parts));

		if (!$definition && $method === Request::METHOD_HEAD)
		{
			$definition = $this->search($this->tree, array_merge([ Request::METHOD_GET ], $parts));
		}

		if (!$definition && $method !== Request::METHOD_ANY)
		{
			$definition = $this->search($this->tree, array_merge([ Request::METHOD_ANY ], $parts));
		}

		if (!$definition)
		{
			return null;
		}

		unset($definition[self::PARSED_PATTERN]);

		$route = Route::from($definition);
		$route->pattern->match($uri, $captured);

		return $route;
	}

	public function search(array $node, array $parts)
	{
		while ($parts)
		{
			$part = array_shift($parts);

			if (isset($node[$part]))
			{
				$node = $node[$part];
			}
			else
			{
				foreach ($node as $pattern => $sub_node)
				{
					if ($pattern{0} !== '#')
					{
						continue;
					}

					if (preg_match($pattern, $part))
					{
						$node = $sub_node;

						break;
					}
				}
			}

			if (!$parts && isset($node[self::DEFINITION]))
			{
				return $node[self::DEFINITION];
			}
		}
	}
}
