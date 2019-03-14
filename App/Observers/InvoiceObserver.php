<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 14:48
 */

namespace App\Observers;

use App\Order;
use App\Invoice;

class InvoiceObserver extends AbstractObserver
{
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

		// Instaiate Invoice class
		$invoice = new Invoice($payment);

		// Stop if not paid
		if ($payment['state'] !== 'PAID')
		{
			return false;
		}

		// Getting invoice id
		if ( ! $invoice_id = $invoice->createDebetInvoice())
		{
			return false;
		}

		// Saving invoice ID to the transaction results.
		// We may need it later on when confirming the sending of the order. (Confirmation)
		$transaction->setTransactionResults($invoice_id, 'invoice_id');

		if ( ! $invoice->send($invoice_id, 'D'))
		{
			$transaction->setTransactionResults('INVOICES COULD NOT BE SENT');

			return false;
		}

		if ( ! $invoice->createCreditInvoice())
		{
			$transaction->setTransactionResults('CREDIT INVOICES COULD NOT BE SENT');

			return false;
		}

		return true;
	}
}