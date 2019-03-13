<?php

use App\Order;
use PHPUnit\Framework\TestCase;

class ConfirmationCustomer extends TestCase
{
	private $order;

	private $ordernumber;

	/**
	 * ConfirmationCustomer constructor.
	 *
	 * @param null   $name
	 * @param array  $data
	 * @param string $dataName
	 */
	public function __construct()
	{
		parent::__construct();

		$this->order       = new Order;
		$this->ordernumber = 1552395313;
	}

	/**
	 * Checks if the ordernumber is exsisting in our order database.
	 */
	public function testIfOrderNumberIsInDatabase()
	{
		$ordernumber = $this->order->isExsistingOrderNumber($this->ordernumber);

		$this->assertTrue($ordernumber);
	}

	/**
	 * Checks if the order has a completed state
	 * If not the test was succesfull
	 */
	public function testIfOrderHasBeenCompleted()
	{
		$paid = $this->order->hasBeenPaidAlready($this->ordernumber);

		$this->assertFalse($paid);
	}

	/**
	 * Checks if the order has been processed.
	 */
	public function testCheckOrderState()
	{
		$paid = $this->order->isOrderCompleted($this->ordernumber);

		$this->assertFalse($paid);
	}

	/**
	 * Checks if an object is being returned with data
	 */
	public function testIfOrdersArePresentInOrderedProductsTable()
	{
		$results = $this->order->getOrderProductsForOrder($this->ordernumber);

		$this->assertIsObject($results);
	}

}