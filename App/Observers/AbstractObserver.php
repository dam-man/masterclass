<?php

namespace App\Observers;

abstract class AbstractObserver
{
	abstract public function update(AbstractTransaction $transaction_in);
}