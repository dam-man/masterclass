<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 14:38
 */

namespace App;

use App\Traits\Log;

class Stock
{
	use Log;
	/**
	 * @var DBConnect
	 */
	private $db;
	/**
	 * @var array
	 */
	private $notInStock = [];

	/**
	 * Stock constructor.
	 */
	public function __construct()
	{
		$this->db = Factory::getDatabaseConnection();
	}

	/**
	 * Very simple stock check. Normally this should be extended with an awesome stock table with all colors and sizes
	 *But due the time we're having, this cannot be done.
	 *
	 * @param $products
	 *
	 * @return bool
	 */
	public function checkStock($products)
	{
		$product_ids = [];

		foreach ($products as $product)
		{
			$product_ids[] = $product['product_id'];
		}

		$stock = $this->inStock($product_ids);

		foreach ($products as $product)
		{
			if ($product['amount'] > $stock[$product['product_id']])
			{
				$this->notInStock[] = ['Product ID: ' . $product['product_id'], $product['name']];
			}
		}

		return true;
	}

	/**
	 * Returning an array wuith products which are not in stck
	 *
	 * @return array
	 */
	public function getUnavailableProducts()
	{
		return $this->notInStock;
	}

	/**
	 * Ordering new stock at the distribotor -- Doing it by an API :P
	 * @return bool
	 */
	public function orderNewStock()
	{
		$order = [
			'company'    => 'Robert T-Shirts',
			'firstname'  => 'Robert',
			'lastname'   => 'Dam',
			'email'      => 'info@mijn-winkeltje.nl',
			'tel'        => '0123456789',
			'debiteur'   => 'TSHIRT-13544',
			'bestelling' => $this->notInStock,
		];

		if ( ! $this->saveStubDatatoTxtFile($order, 'bestelling-bij-leverancier.txt'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get stock for ordered items
	 *
	 * @param array $product_ids
	 *
	 * @return array
	 */
	public function inStock($product_ids = [])
	{
		$stock = [];

		$this->db->select(['id', 'inStock'])
		         ->from('products')
		         ->where_in('id', $product_ids);

		$items = $this->db->loadResultList();

		foreach ($items as $item)
		{
			$stock[$item['id']] = $item['inStock'];
		}

		return $stock;
	}
}