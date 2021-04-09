<?php

require_once '../config/db.php';
require_once '../model/Response.php';
require_once '../model/User.php';

try {
    $DB = DB::connectDB();
} catch (PDOException $ex) {
    error_log("Connection Error:" . $ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit;
}

if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Content Type header not set to JSON");
    $response->send();
    exit;
}

//Hämtar input som raw JSON data
$rawPostData = file_get_contents('php://input');

//Returnerar false om json_decode inte kan köras(det är inte JSON då), annars lagras decodad json i $jsonData
if (!$jsonData = json_decode($rawPostData)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Request body is not valid JSON");
    $response->send();
    exit;
}

// RADERA USER HÄMTA EN USER?

if (array_key_exists('user_id', $_GET)) {

} elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        //GET ALL USERS kräver att man är ADMIN
        require_once '../config/authorization.php';
        if ($returned_role !== 'admin') {
            $response = new Response();
            $response->setHttpStatusCode(405);
            $response->setSuccess(false);
            $response->addMessage("Request method not allowed");
            $response->send();
            exit;

        } else {

            try {
                $query = $DB->prepare('SELECT user_id, fullname, username, email, role, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at, useractive, loginattempts FROM users');
                $query->execute();

                $rowCount = $query->rowCount();

                $userArray = array();

                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $user = new User($row['user_id'], $row['fullname'], $row['username'], $row['email'], $row['role'], $row['created_at'], $row['useractive'], $row['loginattempts']);
                    $userArray[] = $user->returnUserAsArray();
                }

                $returnData = array();
                $returnData['rows_returned'] = $rowCount;
                $returnData['users'] = $userArray;

                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->setData($returnData);
                $response->send();
                exit;

            } catch (UserException $ex) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage($ex->getMessage());
                $response->send();
                exit;
            } catch (PDOEXception $ex) {
                error_log("Database query error - " . $ex, 0);
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to get users");
                $response->send();
                exit;
            }

        }} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($jsonData->fullname) || !isset($jsonData->username) || !isset($jsonData->password) || !isset($jsonData->email)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            if (!isset($jsonData->fullname) ? $response->addMessage("Full name not supplied") : false);
            if (!isset($jsonData->username) ? $response->addMessage("Username not supplied") : false);
            if (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
            if (!isset($jsonData->email) ? $response->addMessage("Email not supplied") : false);
            $response->send();
            exit;
        }

        if (strlen($jsonData->fullname) < 1 || strlen($jsonData->fullname) > 255 || strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255 || strlen($jsonData->email) < 1 || strlen($jsonData->email) > 255) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            if (strlen($jsonData->fullname) < 1 ? $response->addMessage("Full name cannot be blank") : false);
            if (strlen($jsonData->fullname) > 255 ? $response->addMessage("Full name cannot be greater than 255 characters") : false);
            if (strlen($jsonData->username) < 1 ? $response->addMessage("Username cannot be blank") : false);
            if (strlen($jsonData->username) > 255 ? $response->addMessage("Username cannot be greater than 255 characters") : false);
            if (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
            if (strlen($jsonData->password) > 255 ? $response->addMessage("Password cannot be greater than 255 characters") : false);
            if (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank") : false);
            if (strlen($jsonData->email) > 255 ? $response->addMessage("Email cannot be greater than 255 characters") : false);
            $response->send();
            exit;
        }

        $fullname = trim($jsonData->fullname);
        $username = trim($jsonData->username);
        $password = $jsonData->password;
        $email = trim($jsonData->email);

        try {
            $query = $DB->prepare('SELECT user_id from users WHERE username = :username OR email = :email');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount !== 0) {
                $response = new Response();
                $response->setHttpStatusCode(409); //conflict
                $response->setSuccess(false);
                $response->addMessage("Username or email already exists");
                $response->send();
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = $DB->prepare('INSERT INTO users (fullname, username, password, email) VALUES(:fullname, :username, :password, :email)');
            $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("There was an issue creating a user account - please try again");
                $response->send();
                exit;
            }

            $lastUserID = $DB->lastInsertId();
            //Returnerar inte password av säkerhetsrisk.
            $returnData = array();
            $returnData['user_id'] = $lastUserID;
            $returnData['fullname'] = $fullname;
            $returnData['username'] = $username;
            $returnData['email'] = $email;

            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("user created");
            $response->setData($returnData);
            $response->send();
            exit();

        } catch (PDOException $ex) {
            error_log("Database query error: " . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("There was an issue creating a user account - please try again");
            $response->send();
            exit;
        }
    }

}
