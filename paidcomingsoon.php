<?php
//
// Todo
//	[] Create expiration on editing for non-pro members
//

// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';


//
// Services
//
$serviceSites = new ServiceSites($dbConnection);
$serviceShortenedURLs = new ServiceShortenedURLs($dbConnection);
$serviceMeta = new ServiceMeta($dbConnection);

$output = array();

//
// Handle page actions
//
if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		//
		// Action for creating a site entry
		//
		case 'subscribe':
			if (isset($_REQUEST['emailAddress']) && trim($_REQUEST['emailAddress']) != '') {
				if (filter_var($_REQUEST['emailAddress'], FILTER_VALIDATE_EMAIL)) {
					$params = array(
						'emailAddress' => $_REQUEST['emailAddress'],
					);

					$sqlQuery  = 'SELECT * FROM subscribers ';
					$sqlQuery .=  'WHERE emailAddress = :emailAddress ';

					$statement = $dbConnection->prepare($sqlQuery);
				    $getResult = $statement->execute($params);
				    $existingEmail = $statement->fetchAll();

				    if (isset($existingEmail) && sizeof($existingEmail) > 0) {
				    	$params['errorMessages'][] = array(
				    	'role' => 'warning',
				    	'text' => 'You have already subscribed!'
					    );
				    } else {	
						$params['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
						$sqlQuery  = 'INSERT INTO subscribers ';
						$sqlQuery .= 'SET emailAddress = :emailAddress, ';
						$sqlQuery .= 'referer = :referer ';

						$statement = $dbConnection->prepare($sqlQuery);
					    $createResult = $statement->execute($params);

					    $params['errorMessages'][] = array(
					    	'role' => 'success',
					    	'text' => 'You have been successfully subscribed 🎉'
					    );
				    }

				}
			}
			break;
	}
}


//
// Output HTML
//
$output[] = $twig->render('paidcomingsoon.twig', $params);
echo implode($output);