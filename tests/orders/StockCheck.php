<?php

define('BASE_PATH', __DIR__);

use PHPUnit\Framework\TestCase;

class StockCheck extends TestCase
{
	private $stock;

	public function __construct()
	{
		parent::__construct();

		$this->stock = new \App\Stock;
	}

	public function testIfThereIsAnyStockOfGivenProducts()
	{
		$stock = $this->stock->inStock(['1', '2']);

		$this->assertNotEmpty($stock);
	}
}
