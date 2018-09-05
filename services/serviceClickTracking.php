<?php

class ServiceClickTracking {
  protected $connection;

  function __construct($dbConnection) {
    $this->connection = $dbConnection;
  }

  //
  // Validate shortenedURL object
  //
  public function validate($clickTracking) {
    $validationMessages = array();

    return array(
      'isValid' => sizeof($validationMessages) <= 0,
      'validationMessages' => $validationMessages
    );
  }

  //
  // Insert a shortened URL into the db
  //
  public function create($clickTracking) {
    $params = array(
      'siteID' => $clickTracking['siteID'],
      'referer' => isset($clickTracking['referer']) ? $clickTracking['referer'] : '',
      'ipAddress' => isset($clickTracking['ipAddress']) ? $clickTracking['ipAddress'] : ''
    );

    $sqlQuery  = 'INSERT INTO clickTracking ';
    $sqlQuery .= 'SET siteID = :siteID, ';
    $sqlQuery .= 'referer = :referer, ';
    $sqlQuery .= 'ipAddress = :ipAddress, ';
    $sqlQuery .= 'timeCreated = NOW() ';

    $statement = $this->connection->prepare($sqlQuery);
    $createResult = $statement->execute($params);

    $clickTracking['ID'] = $this->connection->lastInsertId();

    return $clickTracking;
  }

  //
  // Get a clickTracking from the db based on criteria
  //
  public function get($clickTracking) {
    $params = array();

    $sqlQuery  = 'SELECT * FROM clickTracking ';
    $sqlQuery .= 'WHERE 1 ';

    if (isset($clickTracking['ID'])) {
      $params['ID'] = $clickTracking['ID'];
      $sqlQuery .= 'AND ID = :ID ';
    }

    if (isset($clickTracking['siteID'])) {
      $params['siteID'] = $clickTracking['siteID'];
      $sqlQuery .= 'AND siteID = :siteID ';
    }

    if (isset($clickTracking['ipAddress'])) {
      $params['ipAddress'] = $clickTracking['ipAddress'];
      $sqlQuery .= 'AND ipAddress = :ipAddress ';
    }

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }

  // 
  // Delete the give shortened URL
  //
  public function delete($clickTracking) {
    $params = array(
      'ID' => $clickTracking['ID']
    );

    $sqlQuery  = 'DELETE FROM clickTracking ';
    $sqlQuery .= 'WHERE ID = :ID ';

    $statement = $this->connection->prepare($sqlQuery);
    $deleteResult = $statement->execute($params);

    return $deleteResult;
  }

  //
  //  Visit summary for past x days
  //
  public function visitSummary($siteID, $dayInterval) {
    $params = array (
      'siteID' => $siteID
    );

    $sqlQuery  = 'SELECT COUNT(*) as visits, timeCreated as date FROM clickTracking ';
    $sqlQuery .= 'WHERE timeCreated >= (NOW() - INTERVAL ' . (int) $dayInterval . ' DAY) ';
    $sqlQuery .= 'AND siteID = :siteID ';
    $sqlQuery .= 'GROUP BY DATE_FORMAT(timeCreated, "%c%d%Y") ';
    $sqlQuery .= 'ORDER BY date ASC ';

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    $trackingData =  $statement->fetchAll();

    $trackingCounts = array();
    for ($i=(int) $dayInterval; $i >= 0 ; $i--) { 
      $currentDay = date('m d Y', strtotime('-' . $i . ' day'));
      $trackingCounts[$currentDay] =  0;

      foreach ($trackingData as $key => $value) {
        if (date('m d Y', strtotime($value['date'])) == $currentDay) {
          $trackingCounts[$currentDay] = (int) $value['visits'];
          break;
        }
      }
    }

    return $trackingCounts;
  }

  public function referralSummary($siteID, $dayInterval, $limit = 0) {
    $params = array(
      'siteID' => $siteID
    );

    $sqlQuery  = 'SELECT COUNT(referer) as times, referer FROM clickTracking ';
    $sqlQuery .= 'WHERE timeCreated >= (NOW() - INTERVAL ' . (int) $dayInterval . ' DAY) ';
    $sqlQuery .= 'AND siteID = :siteID ';
    $sqlQuery .= 'GROUP BY referer ';
    if ($limit > 0) {
      $sqlQuery .= 'LIMIT ' . (int) $limit . ' ';
    }

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }
}


