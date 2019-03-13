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
	private $results   = [];
	private $data      = "";

	/**
	 * Calling notifiers in the specified classes
	 */
	protected function notify()
	{
		foreach ($this->observers as $obs)
		{
			$obs->update($this);
		}
	}

	/**
	 * Attaching the observer to the list of Observers.
	 *
	 * @param AbstractObserver $observer_in
	 */
	public function attach(AbstractObserver $observer_in)
	{
		$this->observers[] = $observer_in;
	}

	/**
	 * Unsetting specific observer
	 *
	 * @param AbstractObserver $observer_in
	 */
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

	/**
	 * Calling updateDate method in the specific Class to perfrom actions.
	 *
	 * @param $data
	 */
	public function updateData($data)
	{
		$this->data = $data;
		$this->notify();
	}

	/**
	 * Updating data from the specific class.
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	public function setTransactionResults($result)
	{
		$this->results[] = $result;
	}

	public function getTransactionResults()
	{
		return $this->results;
	}
}