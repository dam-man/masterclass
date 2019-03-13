<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 12-3-2019
 * Time: 14:58
 */

namespace App;

class Order
{
	/**
	 * @var DBConnect
	 */
	protected $db;

	/**
	 * Order constructor.
	 */
	public function __construct()
	{
		$this->db = Factory::getDatabaseConnection();
	}

	/**
	 * Getting all order for a specific orderID.
	 *
	 * @param $ordernumber
	 *
	 * @return array
	 */
	public function getOrderProductsForOrder($ordernumber)
	{
		$this->db->select(['*'])
		         ->from('ordered_products')
		         ->where('order_id', $ordernumber);

		return $this->db->loadResultList();
	}

	/**
	 * Getting the order information for a particular ordernumber
	 *
	 * @param $ordernumber
	 *
	 * @return array|object
	 */
	public function getOrderDetails($ordernumber)
	{
		$this->db->select(['ordernumber', 'orderstate', 'paymentstate', 'orderdate', 'client_id'])
		         ->from('orders')
		         ->where('ordernumber', $ordernumber);

		return $this->db->loadResult();
	}

	/**
	 * Getting the order state from the orders table.
	 *
	 * @param $ordernumber
	 *
	 * @return bool
	 */
	public function isExsistingOrderNumber($ordernumber)
	{
		$result = $this->getOrderDetails($ordernumber);

		return ! empty($result) ? true : false;
	}

	/**
	 * Checking if the order has not been processed before.
	 * Mostly the IPN messages from providers are coming more than once.
	 *
	 * @param $ordernumber
	 *
	 * @return bool
	 */
	public function hasBeenPaidAlready($ordernumber)
	{
		$order = $this->getOrderDetails($ordernumber);

		return ($order->paymentstate) ? true : false;
	}

	/**
	 * Checks if the order has been processed already
	 *
	 * @param $ordernumer
	 *
	 * @return bool
	 */
	public function isOrderCompleted($ordernumer)
	{
		$order = $this->getOrderDetails($ordernumer);

		return ($order->orderstate) ? true : false;
	}
}