<?php

class ServiceUsers {
  protected $connection;

  function __construct($dbConnection) {
    $this->connection = $dbConnection;
  }

  //
  // Validate user object
  //
  public function validate($user) {
    $validationMessages = array();

    if (!isset($user['emailAddress']) || !filter_var($user['emailAddress'], FILTER_VALIDATE_EMAIL)) $validationMessages[] = 'Invalid email address';
    else {
      $existingUser = $this->get(array('emailAddress' => $user['emailAddress']));
      if (isset($existingUser) && sizeof($existingUser) > 0) $validationMessages[] = 'Email already exists, please login instead';
    }
    if (!isset($user['password']) || trim($user['password']) == '') $validationMessages[] = 'No password specified';
    return array(
      'isValid' => sizeof($validationMessages) <= 0,
      'validationMessages' => $validationMessages
    );
  }

  //
  // Insert a user into the db
  //
  public function create($user, $isUpdate = false) {
    $params = array(
      'emailAddress' => $user['emailAddress'],
      'password' => $user['password'], 
    );

    if (!$isUpdate) $sqlQuery  = 'INSERT INTO users ';
    else $sqlQuery = 'UPDATE users ';
    $sqlQuery .= 'SET emailAddress = :emailAddress, ';
    $sqlQuery .= 'password = :password, ';
    if (!$isUpdate) $sqlQuery .= 'activeThrough = (NOW() + INTERVAL 33 DAY), ';

    if (isset($user['firstName'])) {
      $params['firstName'] = $user['firstName'];
      $sqlQuery .= 'firstName = :firstName, ';
    }

    if (isset($user['lastName'])) {
      $params['lastName'] = $user['lastName'];
      $sqlQuery .= 'lastName = :lastName, ';
    }

    if (isset($user['stripeCustID'])) {
      $params['stripeCustID'] = $user['stripeCustID'];
      $sqlQuery .= 'stripeCustID = :stripeCustID, ';
    }

    if (isset($user['stripeSubscriptionID'])) {
      $params['stripeSubscriptionID'] = $user['stripeSubscriptionID'];
      $sqlQuery .= 'stripeSubscriptionID = :stripeSubscriptionID, ';
    }

    if (!$isUpdate) $sqlQuery .= 'timeCreated = NOW(), ';
    $sqlQuery .= 'timeUpdated = NOW() ';

    if ($isUpdate && isset($user['ID'])) {
      $params['ID'] = $user['ID'];
      $sqlQuery .= 'WHERE ID = :ID ';
    }

    $statement = $this->connection->prepare($sqlQuery);
    $createResult = $statement->execute($params);
    if (!$isUpdate) $user['ID'] = $this->connection->lastInsertId();

    return $user;
  }

  //
  // Renew a customers subscription
  //
  public function renewSubscription($userID, $length = 33) {
    $params = array(
      'ID' => $userID
    );

    $sqlQuery  = 'UPDATE users ';
    $sqlQuery .= 'SET activeThrough = (NOW() + INTERVAL ' . (int) $length . ' DAY) ';
    $sqlQuery .= 'WHERE ID = :ID ';

    $statement = $this->connection->prepare($sqlQuery);
    return $statement->execute($params);
  }

  //
  // Get a user object
  //
  public function get($user) {
    $params = array();

    $sqlQuery  = 'SELECT * FROM users ';
    $sqlQuery .= 'WHERE 1 ';

    if (isset($user['ID'])) {
      $params['ID'] = $user['ID'];
      $sqlQuery .= 'AND ID = :ID ';
    }

    if (isset($user['emailAddress'])) {
      $params['emailAddress'] = $user['emailAddress'];
      $sqlQuery .= 'AND emailAddress = :emailAddress ';
    }

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    return $statement->fetchAll();
  }

  //
  // Hashes a password and returns the hash
  //
  public function hashUserPassword($password) {
    if (trim($password) == '') return '';
    return password_hash($password, PASSWORD_BCRYPT);
  }

  //
  // Logs a user in. Returns true if successful, false if not
  //
  public function login($user) {
    $params = array(
      'emailAddress' => $user['emailAddress']
    );
    $sqlQuery  = 'SELECT * FROM users ';
    $sqlQuery .= 'WHERE emailAddress = :emailAddress ';
    $sqlQuery .= 'LIMIT 1 ';

    $statement = $this->connection->prepare($sqlQuery);
    $getResult = $statement->execute($params);
    $selectResult = $statement->fetch();
    if (!isset($selectResult)) return false;
    $userHash = $selectResult['password'];
    if (password_verify($user['password'], $userHash)) {
      if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
      $_SESSION['userID'] = $selectResult['ID'];
      $_SESSION['emailAddress'] = $selectResult['emailAddress'];
      return true;
    } else {
      return false;
    }
  }

    //
    // Logs a user out
    //
    public function logout($params) {
      if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
      $_SESSION['userID'] = '';
      $_SESSION['emailAddress'] = '';
      $params['loggedIn'] = false;
      $params['userID'] = null;
      $params['emailAddress'] = null;
      session_destroy();
    }
}


