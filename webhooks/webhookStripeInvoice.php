<?php
// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

//
// Headers
//
header("HTTP/1.1 200 OK");
header("Content-Type: text/html");

//
// Services
//
$serviceUsers = new ServiceUsers($dbConnection);

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $siteConfig['stripe']['invoiceSucceedSecret'];

$payload = @file_get_contents("php://input");
$sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400); // PHP 5.4 or greater
  exit();
} catch(\Stripe\Error\SignatureVerification $e) {
  // Invalid signature
  http_response_code(400); // PHP 5.4 or greater
  exit();
}

// Grab the customer email from the invoice
$invoice = $event->data->object;
try {
	$customer = \Stripe\Customer::retrieve($invoice->customer);
	$customerEmail = $customer->email;

	// Grab the customer object from the email address
	$customerObject = $serviceUsers->get(array(
		'emaiLAddress' => $customerEmail
	));

	if (!isset($customerObject) || sizeof($customerObject) <= 0) {
		// Customer not in database...odd
		http_response_code(500); 
	} else {
		$customerObject = $customerObject[0];
		// Now we will update the customers subscription expire data by 33 days
		if ($serviceUsers->renewSubscription($customerObject['ID'])) {
			http_response_code(200); 
		} else {
			http_response_code(500); 
		}
	}
} catch (Exception $e) {
	// No customer found
	http_response_code(500); 
}
