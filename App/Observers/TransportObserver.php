<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 12:00
 */

namespace App\Observers;

use App\Traits\Log;
use App\Transport;

class TransportObserver extends AbstractObserver
{
	use Log;
	/**
	 * Listner for the observer.
	 *
	 * @param AbstractTransaction $transaction
	 */
	public function update(AbstractTransaction $transaction)
	{
		$result = null;

		// Payment details received form observer
		$payment = $transaction->getData();

		$sticker = (new Transport($payment['orderId']))->getBarcodeStickerFporTransport();

		$this->saveStubDatatoTxtFile($sticker, 'transport-sticker.txt');

		$transaction->setTransactionResults('POSTNL STICKER HAS BEEN CREATED');

		return true;
	}
}