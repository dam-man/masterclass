<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 11:06
 */

namespace App;

use App\Adapter\EmailAdapter;
use App\Traits\Log;

class Confirmation
{
	use Log;

	private $orderId;
	private $details;
	private $client;
	private $products;

	/**
	 * Confirmation constructor.
	 */
	public function __construct($orderId)
	{
		$this->db      = Factory::getDatabaseConnection();
		$this->orderId = $orderId;

		if ( ! empty($orderId))
		{
			$this->create();
		}
	}

	/**
	 * Creating a confirmation email.
	 */
	public function create()
	{
		// instantiate order
		$order = new Order;

		// Getting the order details
		$this->details  = $order->getOrderDetails($this->orderId);
		$this->products = $order->getOrderProductsForOrder($this->orderId);

		// Getting client information
		$this->client = (new Client)->getClientById($this->details->client_id);

		$data = [
			'client'   => $this->client,
			'order'    => $this->details,
			'products' => $this->products,
		];

		// Faking email with data from the objects
		if ( ! $this->saveStubDatatoTxtFile($data, 'email.txt'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Sending fake email
	 *
	 * @return bool
	 */
	public function send()
	{
		// We cannot send any email as we have no data.
		if (empty($this->details) || empty($this->products) || empty($this->client))
		{
			return false;
		}

		$client  = new Client($this->details->client_id);
		$adapter = new EmailAdapter($client);

		// Now we can send an awesome email with the fule name in the to list :)
		// The email address will be present in the client object
		$adapter->getUserFullname();

		return true;
	}

}