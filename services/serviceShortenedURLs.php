<?php

class ServiceShortenedURLs {
  protected $connection;

  function __construct($dbConnection) {
    $this->connection = $dbConnection;
  }

  //
  // Validate shortenedURL object
  //
  public function validate($shortenedURL) {
    $validationMessages = array();

    if (!isset($shortenedURL['siteID']) || $shortenedURL['siteID'] <= 0) $validationMessages[] = 'Invalid associated site ID';
    if (!isset($shortenedURL['shortURL']) || strlen($shortenedURL['shortURL']) <= 0) $validationMessages[] = 'Invalid short URL';

    return array(
      'isValid' => sizeof($validationMessages) <= 0,
      'validationMessages' => $validationMessages
    );
  }

  //
  // Insert a shortened URL into the db
  //
  public function create($shortenedURL) {
    $params = array(
      'siteID' => $shortenedURL['siteID'],
      'shortURL' => $shortenedURL['shortURL']
    );

    $sqlQuery  = 'INSERT INTO sitesShortenedURLs ';
    $sqlQuery .= 'SET siteID = :siteID, ';
    $sqlQuery .= 'shortURL = :shortURL, ';
    $sqlQuery .= 'timeUpdated = NOW(), ';
    $sqlQuery .= 'timeCreated = NOW() ';

    $statement = $this->connection->prepare($sqlQuery);
    $createResult = $statement->execute($params);

    $shortenedURL['ID'] = $this->connection->lastInsertId();

    return $shortenedURL;
  }

  //
  // Get a shortenedURL from the db based on criteria
  //
  public function get($shortenedURL) {
    $params = array();

    $sqlQuery  = 'SELECT * FROM sitesShortenedURLs ';
    $sqlQuery .= 'WHERE 1 ';

    if (isset($shortenedURL['ID'])) {
      $params['ID'] = $shortenedURL['ID'];
      $sqlQuery .= 'AND ID = :ID ';
    }

    if (isset($shortenedURL['siteID'])) {
      $params['siteID'] = $shortenedURL['siteID'];
      $sqlQuery .= 'AND siteID = :siteID ';
    }

    if (isset($shortenedURL['shortURL'])) {
      $params['shortURL'] = $shortenedURL['shortURL'];
      $sqlQuery .= 'AND shortURL = :shortURL ';
    }

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }

  //
  // Generate a shortened URL
  // TODO:
  //  [] Generate IDs from English words
  //
  public function generateUniqueURL($length = 5, $restrictedLinkPaths) {
    while (1) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
      $shortenedURL = '';

      for ($p = 0; $p < $length; $p++) {
          $shortenedURL .= $characters[mt_rand(0, strlen($characters) - 1)];
      }

      if (in_array($shortenedURL, $restrictedLinkPaths)) {
        continue;
      }

      // Check if the short URL already exists
      $existing = $this->get(array(
        'shortURL' => $shortenedURL
      ));

      if (!isset($existing) || sizeof($existing) <= 0) {
        break;
      }
    }

    return $shortenedURL;
  }

  // 
  // Delete the give shortened URL
  //
  public function delete($shortenedURL) {
    $params = array(
      'ID' => $shortenedURL['ID']
    );

    $sqlQuery  = 'DELETE FROM sitesShortenedURLs ';
    $sqlQuery .= 'WHERE ID = :ID ';

    $statement = $this->connection->prepare($sqlQuery);
    $deleteResult = $statement->execute($params);

    return $deleteResult;
  }
}


