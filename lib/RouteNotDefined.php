<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Routing;

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\HTTP\Status;

/**
 * Exception thrown when a route does not exists.
 *
 * @property-read string $id The identifier of the route.
 */
class RouteNotDefined extends \Exception implements Exception
{
	use AccessorTrait;

	/**
	 * @var string
	 */
	private $id;

	protected function get_id()
	{
		return $this->id;
	}

	/**
	 * @param string $id Identifier of the route.
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($id, $code = Status::NOT_FOUND, \Exception $previous = null)
	{
		$this->id = $id;

		parent::__construct($this->format_message($id), $code, $previous);
	}

	/**
	 * Formats exception message.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	protected function format_message($id)
	{
		return "The route `$id` is not defined.";
	}
}
