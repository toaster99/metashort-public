<?php

$siteConfig = array();

//
// Database
//
$siteConfig['database'] = array(
	'host' => 'localhost',
	'database' => 'metashort',
	'user' => 'metashort',
	'password' => 'metashort'
);

//
// Stripe
//
$siteConfig['stripe'] = array(
	'secret_key' => 'SECRET',
	'publishable_key' => 'SECRET',
	'invoiceSucceedSecret' => 'SECRET'
);