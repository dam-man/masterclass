<?php

define('BASE_PATH', __DIR__);

use App\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceToCustomers extends TestCase
{
	private $invoice;
	private $ordernumber;

	public function __construct()
	{
		parent::__construct();

		$this->ordernumber = 1552395313;

		$payment = [
			'transId' => '069E46384829D511B9A0E62BCE6C011A',
			'orderId' => $this->ordernumber,
			'amount'  => 233.00,
			'state'   => 'PAID',
		];

		$this->invoice = new Invoice($payment);
	}

	public function testIfInvoiceCanBeCreated()
	{
		$invoice_id = $this->invoice->createDebetInvoice();

		$this->assertGreaterThanOrEqual(1, $invoice_id);
	}

	public function testIfCreditInvoicesCanBeSent()
	{
		$send = $this->invoice->createCreditInvoice();

		$this->assertTrue($send);
	}

	public function testIfInvoiceCanBeSent()
	{
		$send = $this->invoice->send(1);

		$this->assertTrue($send);
	}

	public function testIfWeCanGetAllArtistFeesForAnOrder()
	{
		$results = $this->invoice->getArtistsFeesByOrderId($this->ordernumber);

		$this->assertIsArray($results);
	}
}
