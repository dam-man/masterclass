<?php

// Created the vendor autloader for new Packagist classes.
require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function($class)
{
	$path = __DIR__ . '/App/' . str_replace("\\", "/", $class) . '.php';

	if(file_exists($path))
	{
		include_once $path;
	}
});
