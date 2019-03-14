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
	 * Getting full order details for all kind of usage.
	 *
	 * @param $ordernumber
	 *
	 * @return object
	 */
	public function getFullOrderDetails($ordernumber)
	{
		// Getting the order details
		$details  = $this->getOrderDetails($ordernumber);
		$products = $this->getOrderProductsForOrder($ordernumber);

		// Getting client information
		$client = (new Client)->getClientById($details->client_id);

		return (object) [
			'client'   => $client,
			'order'    => $details,
			'products' => $products,
		];
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
		$this->db->select(['*', 'pp.artist_id'])
		         ->from('ordered_products as p')
		         ->join('products as pp', 'p.product_id = pp.id')
		         ->where('p.order_id', $ordernumber);

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
		$this->db->select([
			'orders.ordernumber', 'orders.orderstate', 'orders.paymentstate', 'orders.orderdate', 'orders.client_id', 'SUM(products.total_price) as price',
			'SUM(products.vat_amount) as vat_amount',
		])
		         ->from('orders as orders')
		         ->join('ordered_products as products', 'orders.ordernumber = products.order_id')
		         ->where('orders.ordernumber', $ordernumber)
		         ->group('orders.ordernumber');

		return $this->db->loadResult();
	}

	/**
	 * Updating order details in the database.
	 *
	 * @param       $order_id
	 * @param array $data
	 *
	 * @return $this
	 */
	public function updateOrderDetails($order_id, $data = [])
	{
		$this->db->where('ordernumber', $order_id);

		return $this->db->update('orders', $data);
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