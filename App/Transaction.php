<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 12-3-2019
 * Time: 15:49
 */

namespace App;

use App\Observers\AbstractTransaction;
use App\Observers\AbstractObserver;

class Transaction extends AbstractTransaction
{
	private $observers = [];
	private $data      = "";

	protected function notify()
	{
		foreach ($this->observers as $obs)
		{
			$obs->update($this);
		}
	}

	public function attach(AbstractObserver $observer_in)
	{
		$this->observers[] = $observer_in;
	}

	public function detach(AbstractObserver $observer_in)
	{
		foreach ($this->observers as $key => $val)
		{
			if ($val == $observer_in)
			{
				unset($this->observers[$key]);
			}
		}
	}

	public function updateData($data)
	{
		$this->data = $data;
		$this->notify();
	}

	public function getData()
	{
		return $this->data;
	}
}