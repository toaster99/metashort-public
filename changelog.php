<?php
// 
// Includes
//
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/environment.php';

$output = array();

//
// Output HTML
//
$output[] = $twig->render('changelog.twig', $params);
echo implode($output);