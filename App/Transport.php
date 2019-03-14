<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 12:07
 */

namespace App;

class Transport
{
	/**
	 * @var DBConnect
	 */
	private $db;
	/**
	 * @var
	 */
	private $orderId;
	/**
	 * @var int
	 */
	private $weigth  = 0;
	/**
	 * @var null
	 */
	private $collie = null;
	/**
	 * @var int
	 */
	private $package;

	public function __construct($orderId)
	{
		$this->db      = Factory::getDatabaseConnection();
		$this->orderId = $orderId;
		$this->package = $this->getAmountOfOrderedProducts();
	}

	/**
	 * Detecting the transport type
	 */
	public function getBarcodeStickerFporTransport()
	{
		if ( ! $this->package)
		{
			return false;
		}

		return $this->getPostNLBarcode();
	}

	/**
	 * Getting barcode from the POSTNL API
	 *
	 * @param $package
	 *
	 * @return string
	 */
	private function getPostNLBarcode()
	{
		return '3SAB83691658823';
	}

	/**
	 * Getting amount of collie inside the package/box
	 *
	 * @return null
	 */
	public function getCollie()
	{
		return $this->collie;
	}

	/**
	 * Making weigth available for the sticker or something else.
	 *
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weigth;
	}

	/**
	 * Getting amount of ordered products
	 *
	 * @param $orderId
	 *
	 * @return int
	 */
	private function getAmountOfOrderedProducts()
	{
		$this->db->select(['COUNT(op.product_id) as total', 'SUM(p.weigth) as weight'])
		         ->from('ordered_products as op')
		         ->join('products as p', 'op.product_id = p.id')
		         ->where('op.order_id', $this->orderId)
		         ->group('op.order_id');

		$result = $this->db->loadResult();

		$this->collie = $result->total;
		$this->weigth = $result->weight;

		return ! empty($result) ? $result : false;
	}
}