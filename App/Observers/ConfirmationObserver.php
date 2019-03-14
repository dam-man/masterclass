<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 14:48
 */

namespace App\Observers;

use App\Order;
use App\Confirmation;

class ConfirmationObserver extends AbstractObserver
{
	/**
	 * Listner for the observer.
	 *
	 * @param AbstractTransaction $transaction
	 */
	public function update(AbstractTransaction $transaction)
	{
		$result = null;
		$order  = new Order;

		// Payment details received form observer
		$payment = $transaction->getData();

		// Check if this is one of our own orders.
		if ( ! $order->isExsistingOrderNumber($payment['orderId']))
		{
			$transaction->setTransactionResults('UNKNOWN ORDER IN OUR DATABASE -- LOOKS FAKE IPN MESSAGE');

			return false;
		}

		// Check the order status, we don't want to process it again
		if ($order->isOrderCompleted($payment['orderId']))
		{
			$transaction->setTransactionResults('ORDER HAS BEEN PROCESSED BEFORE!');

			return false;
		}

		// Checks if the payment has not been processed before.
		if ($order->hasBeenPaidAlready($payment['orderId']))
		{
			$transaction->setTransactionResults('ORDER HAS BEEN PAID BEFORE!');

			return false;
		}

		// instantiate the confirmation class
		// Perform this action after checks to prevent useless loading
		$confirmation = new Confirmation($payment['orderId']);

		if ( ! $confirmation->send())
		{
			$transaction->setTransactionResults('CONFIRMATION COULD NOT BE SENT ');

			return false;
		}

		// Setting client data in Abstract Transaction for later usage
		$transaction->setClientData($confirmation->getClientDetails());

		return true;
	}
}