<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 11:06
 */

namespace App;

use App\Order;

class Confirmation
{
	private $orderId;

	/**
	 * Confirmation constructor.
	 */
	public function __construct($orderId)
	{
		$this->db      = Factory::getDatabaseConnection();
		$this->orderId = $orderId;
	}

	public function create()
	{
		$orders = (new Order)->getOrderProductsForOrder($this->orderId);
	}


}