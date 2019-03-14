<?php

define('BASE_PATH', __DIR__);

use App\Order;
use App\DBConnect;
use App\Confirmation;
use PHPUnit\Framework\TestCase;

class ConfirmationCustomer extends TestCase
{
	private $order;
	private $confirmation;
	private $ordernumber;
	/**
	 * @var DBConnect
	 */
	private $db;

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

		$this->db = \App\Factory::getDatabaseConnection();

		$this->ordernumber = 1552395313;

		$this->resetDBToDefaults();

		$this->order        = new Order;
		$this->confirmation = new Confirmation($this->ordernumber);
	}

	/**
	 * Resetting some shizzle before starting the test.
	 *
	 * @return bool
	 */
	private function resetDBToDefaults()
	{
		$update = [
			'orderstate'   => 0,
			'paymentstate' => 0,
		];

		$this->db->where('ordernumber', $this->ordernumber);

		if ( ! $this->db->update('orders', $update))
		{
			return false;
		}
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

		$this->assertNotEmpty($results);
	}

	public function testIConfirmationCanBeCreated()
	{
		$result = $this->confirmation->create();

		$this->assertEquals($result, true);
	}

	public function testIConfirmationCanBeSend()
	{
		$result = $this->confirmation->send();

		$this->assertEquals($result, true);
	}

	public function testIfWeCanGetAllOrderInformationAtOnce()
	{
		$results = $this->order->getFullOrderDetails($this->ordernumber);

		$this->assertNotEmpty($results);
	}

}