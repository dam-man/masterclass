<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 12:00
 */

namespace App\Observers;

use App\DBConnect;
use App\Factory;
use App\Traits\Log;

class CompleteOrderObserver extends AbstractObserver
{
	use Log;

	/**
	 * @var DBConnect
	 */
	private $db;

	public function __construct()
	{
		$this->db = Factory::getDatabaseConnection();
	}

	/**
	 * Listner for the observer.
	 *
	 * @param AbstractTransaction $transaction
	 */
	public function update(AbstractTransaction $transaction)
	{
		// getting the order ID.
		$orderId = $transaction->getOrderId();

		$confirmation = [
			'info'    => 'This array contains only data which is obtained during the process.',
			'orderId' => $orderId,
			'payment' => $transaction->getData(),
			'barcode' => $transaction->getPostNLBarcode(),
			'invoice' => $transaction->getInvoiceId(),
			'client'  => $transaction->getClientData(),
		];

		if ( ! $this->saveStubDatatoTxtFile($confirmation, 'order-completed.txt'))
		{
			return false;
		}

		$update = [
			'orderstate'   => 1,
			'paymentstate' => 1,
		];

		$this->db->where('ordernumber', $orderId);

		if ( ! $this->db->update('orders', $update))
		{
			return false;
		}

		return true;
	}
}