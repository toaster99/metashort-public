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
		case 'login':
			// Process user account creation
		$user = array(
			'emailAddress' => $_REQUEST['emailAddress'],
			'password' => $_REQUEST['password']
		);
			// Get the user object
		$fetchedUser = $serviceUsers->get(array(
			'emailAddress' => $user['emailAddress']
		));

		if (isset($fetchedUser) && sizeof($fetchedUser) > 0) {
			$fetchedUser = $fetchedUser[0];

			if (strtotime($fetchedUser['activeThrough']) < time()) {
				$params['errorMessages'][] = array(
					'role' => 'warning',
					'text' => 'Your subscription has expired, please <a href="/pro">signup</a> again to reactivate your account. <a href="mailto: devalexwhite@gmail.com">Contact support</a> if you have any questions.'
				);
			} else {
				// Log the user in
				$loginResult = $serviceUsers->login($user);
				if (!$loginResult) {
					$params['errorMessages'][] = array(
						'role' => 'warning',
						'text' => 'Invalid username or password, please try again. <a href="mailto: devalexwhite@gmail.com">Contact support</a> if you have any questions.'
					);
				} else {
					header('Location: /account');
				}
			}
		} else {
			$params['errorMessages'][] = array(
				'role' => 'warning',
				'text' => 'Invalid username or password, please try again. <a href="mailto: devalexwhite@gmail.com">Contact support</a> if you have any questions.'
			);
		}

		break;
		case 'logout':
		$params = $serviceUsers->logout($params);
		$params['errorMessages'][] = array(
			'role' => 'success',
			'text' => 'You have been logged out 👋'
		);
		break;
		default:
		break;
	}
}

//
// Output HTML
//
$output[] = $twig->render('account/login.twig', $params);
echo implode($output);