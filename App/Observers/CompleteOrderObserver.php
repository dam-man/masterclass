<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 14-3-2019
 * Time: 12:00
 */

namespace App\Observers;

use App\Traits\Log;

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
		$confirmation = [
			'info'    => 'This array contains only data which is obtained during the process.',
			'payment' => $transaction->getData(),
			'barcode' => $transaction->getPostNLBarcode(),
			'invoice' => $transaction->getInvoiceId(),
			'client'  => $transaction->getClientData(),
		];

		if ( ! $this->saveStubDatatoTxtFile($confirmation, 'order-completed.txt'))
		{
			return false;
		}

		return true;
	}
}