<?php

namespace App\Observers;

abstract class AbstractTransaction
{
	// Attach Observer to the Observer Class.
	abstract public function attach(AbstractObserver $observer_in);

	// Detach Observer to the Observer Class.
	abstract public function detach(AbstractObserver $observer_in);

	// Notifier
	abstract protected function notify();
}