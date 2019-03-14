<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 12:00
 */

namespace App\Observers;

use App\Order;
use App\Stock;
use App\Traits\Log;
use App\Transport;

class CompleteOrderObserver extends AbstractObserver
{
	use Log;

	/**
	 * Listner for the observer.
	 *
	 * @param AbstractTransaction $transaction
	 */
	public function update(AbstractTransaction $transaction)
	{
		$payment = $transaction->getData();

		$products = (new Order)->getOrderProductsForOrder($payment['orderId']);

		return true;
	}
}