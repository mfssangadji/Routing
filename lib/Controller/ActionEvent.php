<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Routing\Controller;

use ICanBoogie\Event;
use ICanBoogie\Routing\Controller;

/**
 * Event class for the `ICanBoogie\Routing\Controller::action:before` event.
 *
 * Event hooks may use this event to alter the result returned by the `action()` method.
 */
class ActionEvent extends Event
{
	const TYPE = 'action';

	/**
	 * Reference to the result.
	 *
	 * @var mixed
	 */
	public $result;

	/**
	 * The event is constructed with the type {@link self::TYPE}.
	 *
	 * @param Controller $target
	 * @param string $result
	 */
	public function __construct(Controller $target, &$result)
	{
		$this->result = &$result;

		parent::__construct($target, self::TYPE);
	}
}
