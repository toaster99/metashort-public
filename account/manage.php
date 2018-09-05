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
// Permissions check
//
if (!$params['loggedIn']) {
	header('Location: /pro?needPro=y');
}

//
// Services
//
$output = array();
$serviceUsers = new ServiceUsers($dbConnection);

//
// Set data params
//
$params['user'] = $serviceUsers->get(array('ID' => $params['userID']))[0];

//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'updateCC':
			if (isset($params['user']['stripeCustID'])) {
				if (isset($_POST['stripeToken'])){
				  try {
				    $cu = \Stripe\Customer::retrieve($params['user']['stripeCustID']); // stored in your application
				    $cu->source = $_POST['stripeToken']; // obtained with Checkout
				    $cu->save();

					$params['errorMessages'][] = array(
						'role' => 'success',
						'text' => 'Your credit card details have been updated.'
					);
				  }
				  catch(\Stripe\Error\Card $e) {
				    $body = $e->getJsonBody();
				    $err  = $body['error'];
				    $error = $err['message'];

				    $params['errorMessages'][] = array(
						'role' => 'warning',
						'text' => $err['message']
					);
				  }
				} else {
					$params['errorMessages'][] = array(
						'role' => 'warning',
						'text' => 'An error has occurred, please try again or <a href="mailto: devalexwhite@gmail.com">contact support</a>.'
					);
				}
			} else {
				$params['errorMessages'][] = array(
					'role' => 'warning',
					'text' => 'An error has occurred, please try again or <a href="mailto: devalexwhite@gmail.com">contact support</a>.'
				);
			}
			break;
		case 'cancelAccount':
			if (isset($params['user']['stripeSubscriptionID'])) {
				try {
					$subscription = \Stripe\Subscription::retrieve($params['user']['stripeSubscriptionID']);
					$subscription->cancel(array('at_period_end' => true));

					$params['errorMessages'][] = array(
						'role' => 'warning',
						'text' => 'Your subscription has been cancelled and your account will be closed at the end of the billing period.'
					);
					$params['user']['stripeSubscriptionID'] = '';
					$serviceUsers->create($params['user'], true);
				} catch (Exception $e) {
					$params['errorMessages'][] = array(
						'role' => 'warning',
						'text' => 'An error has occurred, please try again or <a href="mailto: devalexwhite@gmail.com">contact support</a>.'
					);	
				}
			} else {
				$params['errorMessages'][] = array(
					'role' => 'warning',
					'text' => 'An error has occurred, please try again or <a href="mailto: devalexwhite@gmail.com">contact support</a>.'
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
$output[] = $twig->render('account/manage.twig', $params);
echo implode($output);