<?php

require_once '../config/db.php';
require_once '../model/Response.php';

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

if (array_key_exists('sessionid', $_GET)) {
    $sessionid = $_GET['sessionid'];

    if ($sessionid === '' || !is_numeric($sessionid)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        ($sessionid === '' ? $response->addMessage("Session ID cannot be blank") : false);
        (!is_numeric($sessionid) ? $response->addMessage("Session ID must be numeric") : false);
        $response->send();
        exit;
    }

    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
        (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
        $response->send();
        exit;
    }

    $accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $query = $DB->prepare('DELETE FROM sessions WHERE session_id = :sessionid AND accesstoken = :accesstoken');
            $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Failed to log out of this session using access token provided');
                $response->send();
                exit;
            }

            $returnData = array();
            $returnData['session_id'] = intval($sessionid);

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Logged out");
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage('There was an issue logging out - please try again');
            $response->send();
            exit;
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Content Type header not set to JSON');
            $response->send();
            exit;
        }

        $rawPatchData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawPatchData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Request body is not valid JSON');
            $response->send();
            exit;
        }

        if (!isset($jsonData->refresh_token) || strlen($jsonData->refresh_token) < 1) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($jsonData->refresh_token) ? $response->addMessage('Refresh token not supplied') : false);
            (strlen($jsonData->refresh_token) < 1 ? $response->addMessage('Refresh token cannot be blank') : false);
            $response->send();
            exit;
        }

        try {

            $refreshtoken = $jsonData->refresh_token;

            $query = $DB->prepare('SELECT sessions.session_id as sessionid, sessions.session_user_id as userid, accesstoken, refreshtoken, useractive, loginattempts, accesstokenexpiry, refreshtokenexpiry from sessions, users WHERE users.user_id = sessions.session_user_id AND sessions.session_id = :sessionid AND sessions.accesstoken = :accesstoken AND sessions.refreshtoken = :refreshtoken');
            $query->bindParam(':sessionid', $sessionid, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage('Access token or refresh token is incorrect for session id');
                $response->send();
                exit;
            }

            $row = $query->fetch(PDO::FETCH_ASSOC);

            $returned_sessionid = $row['sessionid'];
            $returned_userid = $row['userid'];
            $returned_accesstoken = $row['accesstoken'];
            $returned_refreshtoken = $row['refreshtoken'];
            $returned_useractive = $row['useractive'];
            $returned_loginattempts = $row['loginattempts'];
            $returned_accesstokenexpiry = $row['accesstokenexpiry'];
            $returned_refreshtokenexpiry = $row['refreshtokenexpiry'];

            if ($returned_useractive !== 'Y') {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage('User account is not active');
                $response->send();
                exit;
            }

            if ($returned_loginattempts >= 3) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage('User account is currently locked out');
                $response->send();
                exit;
            }

            // Om tiden i databasen är mindre än nutid, har den expired
            if (strtotime($returned_refreshtokenexpiry) < time()) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage('Refresh token has expired - please log in again');
                $response->send();
                exit;
            }

            $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
            $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());

            $access_token_expiry_seconds = 1200;
            $refresh_token_expiry_seconds = 1209600;

            $query = $DB->prepare('UPDATE sessions SET accesstoken = :accesstoken, accesstokenexpiry = date_add(NOW(), INTERVAL :accesstokenexpiryseconds SECOND), refreshtoken = :refreshtoken, refreshtokenexpiry = date_add(NOW(), INTERVAL :refreshtokenexpiryseconds SECOND) WHERE session_id = :sessionid AND session_user_id = :userid AND accesstoken =:returnedaccesstoken AND refreshtoken = :returnedrefreshtoken');
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->bindParam(':sessionid', $returned_sessionid, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
            $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
            $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
            $query->bindParam(':returnedaccesstoken', $returned_accesstoken, PDO::PARAM_STR);
            $query->bindParam(':returnedrefreshtoken', $returned_refreshtoken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage('Access token could not be refreshed, please log in again');
                $response->send();
                exit;
            }

            $returnData = array();
            $returnData['session_id'] = $returned_sessionid;
            $returnData['access_token'] = $accesstoken;
            $returnData['access_token_expiry'] = $access_token_expiry_seconds;
            $returnData['refresh_token'] = $refreshtoken;
            $returnData['refresh_token_expiry'] = $refresh_token_expiry_seconds;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Token refreshed");
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage('There was an issue refreshing access token - please log in again');
            $response->send();
            exit;
        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Request method not allowed');
        $response->send();
        exit;
    }

} elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }

    //Hjälper mot bruteforce attacker, 1 post per sekund max
    sleep(1);

    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content type header not set to JSON");
        $response->send();
        exit;
    }

    $rawPostData = file_get_contents('php://input');

    if (!$jsonData = json_decode($rawPostData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
    }

    if (!isset($jsonData->username) || !isset($jsonData->password)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->username) ? $response->addMessage("Username not supplied") : false);
        (!isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
        $response->send();
        exit;
    }

    if (strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (strlen($jsonData->username) < 1 ? $response->addMessage("Username cannot be blank") : false);
        (strlen($jsonData->username) > 255 ? $response->addMessage("Username must be less than 255 characters") : false);
        (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
        (strlen($jsonData->password) > 255 ? $response->addMessage("Password must be less than 255 characters") : false);
        $response->send();
        exit;
    }

    try {
        $username = $jsonData->username;
        $password = $jsonData->password;

        $query = $DB->prepare('SELECT user_id, fullname, username, email, password, role, useractive, loginattempts FROM users WHERE username = :username');
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        //Error meddelandet är att det inte finns en användare, men onödigt att skriva ut det(säkerhetsrisk).
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect");
            $response->send();
            exit;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);

        $returned_userid = $row['user_id'];
        $returned_fullname = $row['fullname'];
        $returned_username = $row['username'];
        $returned_email = $row['email'];
        $returned_password = $row['password'];
        $returned_role = $row['role'];
        $returned_useractive = $row['useractive'];
        $returned_loginattempts = $row['loginattempts'];

        if ($returned_useractive !== 'Y') {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account not active");
            $response->send();
            exit;
        }

        if ($returned_loginattempts >= 3) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account is currently locked out");
            $response->send();
            exit;
        }
        //Krypterar tillbaka lösenordet, om det är fel lägg till 1 på loginattempts
        if (!password_verify($password, $returned_password)) {
            $query = $DB->prepare("UPDATE users set loginattempts = loginattempts+1 WHERE user_id = :userid");
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect");
            $response->send();
            exit;
        }

        //skapar accesstoken, använder time för att göra den ännu mer unik
        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());

        $access_token_expiry_seconds = 1200; //20 minuter
        $refresh_token_expiry_seconds = 1209600; //14 dagar

    } catch (PDOException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue logging in");
        $response->send();
        exit;
    }

    try {
        //Används för att det är två queries, kör allt först när den andra querien fungerar också
        $DB->beginTransaction();
        $query = $DB->prepare('UPDATE users set loginattempts = 0 WHERE user_id = :userid');
        $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
        $query->execute();

        $query = $DB->prepare('INSERT INTO sessions (session_user_id, accesstoken, accesstokenexpiry, refreshtoken, refreshtokenexpiry) VALUES(:session_user_id, :accesstoken, date_add(NOW(), INTERVAL :accesstokenexpiryseconds SECOND), :refreshtoken, date_add(NOW(), INTERVAL :refreshtokenexpiryseconds SECOND))');
        $query->bindParam(':session_user_id', $returned_userid, PDO::PARAM_INT);
        $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
        $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
        $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
        $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
        $query->execute();

        $lastSessionID = $DB->lastInsertId();

        //Sparar datan i databasen, behövs pga begintransaction.
        $DB->commit();

        $returnData = array();
        $returnData['session_id'] = intval($lastSessionID);
        $returnData['access_token'] = $accesstoken;
        $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
        $returnData['refresh_token'] = $refreshtoken;
        $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Log in successful");
        $response->setData($returnData);
        $response->send();
        exit;

    } catch (PDOException $ex) {
        $DB->rollBack();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue loggin in - please try again");
        $response->send();
        exit;
    }

} else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit;
}
