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
	private $data      = null;
	private $client    = null;
	private $orderId   = null;

	public function __construct($orderId)
	{
		$this->orderId = $orderId;
	}

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

	/**
	 * Setting an array with observer results.
	 *
	 * @param $result
	 */
	public function setTransactionResults($result, $type = 'log')
	{
		if ($type === 'errorlog')
		{
			$this->results[$type][] = $result;
		}
		else
		{
			$this->results[$type] = $result;
		}
	}

	/**
	 * Returning all messages from the Observers.
	 *
	 * @return array
	 */
	public function getTransactionResults()
	{
		return $this->results;
	}

	/**
	 * Getting the invoice ID.
	 *
	 * @return mixed
	 */
	public function getInvoiceId()
	{
		return $this->results['invoice_id'];
	}

	/**
	 * Getting the invoice ID which has been created
	 *
	 * @return mixed
	 */
	public function getPostNLBarcode()
	{
		return $this->results['barcode'];
	}

	/**
	 * Setting client data for later usage
	 *
	 * @param $data
	 */
	public function setClientData($data)
	{
		$this->client = $data;
	}

	/**
	 * Getting client data
	 *
	 * @return null
	 */
	public function getClientData()
	{
		return $this->client;
	}

	/**
	 * Setting order information
	 *
	 * @param $data
	 */
	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
	}

	/**
	 * Getting order ID from the transaction
	 *
	 * @return mixed
	 */
	public function getOrderId()
	{
		return $this->orderId;
	}
}