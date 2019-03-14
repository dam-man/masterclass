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
		$stock = new Stock;

		$payment = $transaction->getData();

		$products = (new Order)->getOrderProductsForOrder($payment['orderId']);

		// Doing a stock check
		$stock->checkStock($products);

		// If there is no stock at all.
		if ( ! empty($stock->getUnavailableProducts()))
		{
			$this->saveStubDatatoTxtFile($stock->getUnavailableProducts(), 'not-in-stock-admin.txt');

			if ( ! $stock->orderNewStock())
			{
				$transaction->setTransactionResults('COULD NOT CONNECT TO THE API OF THE DISTRIBITOR');
			}

			$transaction->setTransactionResults('NO STOCK AVAILABLE FONR ONE OR MORE PRODUCTS - EMAIL SENT TO ADMIN');

			return false;
		}

		// Notification for failure when no barcode is generated
		if ( ! $sticker = (new Transport($payment['orderId']))->getBarcodeStickerFporTransport())
		{
			$transaction->setTransactionResults('Looks like the POST NL API is down :D');

			return false;
		}

		// Updating the order in the database with a barcode from the PostNL API
		if ( ! (new Order)->updateOrderDetails($payment['orderId'], ['postnl_barcode' => $sticker]))
		{
			$transaction->setTransactionResults('FAILED UPDATING THE ORDER IN DB');

			return false;
		}

		$this->saveStubDatatoTxtFile($sticker, 'transport-sticker.txt');

		// Saving the barcode to the transaction to use it in the confirmation
		$transaction->setTransactionResults($sticker, 'barcode');

		return true;
	}
}