<?php

define('BASE_PATH', __DIR__);

use App\Transport;
use App\Order;
use PHPUnit\Framework\TestCase;

class TransportToCustomer extends TestCase
{
	private $transport;
	private $ordernumber;

	public function __construct()
	{
		parent::__construct();

		$this->ordernumber = 1552395313;

		$this->transport = new Transport($this->ordernumber);
	}

	public function testIfWeCanGetABarcodeStickerFromPostNLApi()
	{
		$sticker = $this->transport->getBarcodeStickerFporTransport();

		$this->assertEquals('3SAB83691658823', $sticker);
	}

	public function testIfThereIsWeightInOurPackage()
	{
		$weight = $this->transport->getWeight();

		$this->assertGreaterThan(0, $weight);
	}

	public function testIfThereAreMoreThanOneCollieInOurPackage()
	{
		$collie = $this->transport->getCollie();

		$this->assertGreaterThan(0, $collie);
	}

	public function testIfWeCanUpdateTheBarcodeTNTInTheDatabase()
	{
		$result = (new Order)->updateOrderDetails($this->ordernumber, ['postnl_barcode' => rand()]);

		$this->assertNotEmpty($result);
	}
}
