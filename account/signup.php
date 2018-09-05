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

$output = array();

//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'signup':
		// Process user account creation
		$user = array(
			'emailAddress' => $_REQUEST['emailAddress'],
			'password' => $serviceUsers->hashUserPassword($_REQUEST['password']),
			'active' => 'y'
		);
			// Validate the user
		$validation = $serviceUsers->validate($user);
		if ($validation['isValid']) {
			// First, handle the stripe work
			$token  = $_POST['stripeToken'];
			$paymentSuccess = true;
			try {
				$customer = \Stripe\Customer::create(array(
					'email' => $user['emailAddress'],
					'source'  => $token
				));

				$subscription = \Stripe\Subscription::create(array(
					"customer" => $customer->id,
					"trial_period_days" => 31,
					"items" => array(
						array(
							"plan" => "pro",
						),
					)
				));
			} catch (Exception $e) {
				$params['errorMessages'][] = array(
					'role' => 'warning',
					'text' => $e->getMessage()
				);
				$paymentSuccess = false;
			}

			if ($paymentSuccess) {			
				$user['stripeCustID'] = $customer->id;
				$user['stripeSubscriptionID'] = $subscription->id;
				// Create the user on MetaShort
				$user = $serviceUsers->create($user);

				if (isset($user['ID']) && $user['ID'] > 0) {
					if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
					$_SESSION['userID'] = $user['ID'];
					$_SESSION['emailAddress'] = $user['emailAddress'];
					header("Location: /account?firstLogin=y");
				}
			}
		} else {
			$params['errorMessages'][] = array(
				'role' => 'warning',
				'text' => implode($validation['validationMessages'])
			);
		}
		break;
		default:
		break;
	}
}

//
// Output HTML
//
$output[] = $twig->render('account/signup.twig', $params);
echo implode($output);