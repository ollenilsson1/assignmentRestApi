<?php

require_once '../config/db.php';
require_once '../model/Cart.php';
require_once '../model/Response.php';

try {
    $DB = DB::connectDB();

} catch (PDOException $ex) {
    error_log("Connection error - " . $ex, 0); // 0 är för att spara felmeddelandet i PHP logfile
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}
// Allt med cart behöver token validering
require_once '../config/authorization.php';

if (array_key_exists("productid", $_GET)) {

} elseif (array_key_exists("cartid", $_GET)) {
    $cartid = $_GET['cartid'];
    //Validation
    if ($cartid == '' || !is_numeric($cartid)) {
        $response = new Response();
        $response->setHttpStatusCode(400); //Client error
        $response->setSuccess(false);
        $response->addMessage("Cart ID cannot be blank or must be numeric");
        $response->send();
        exit;
    }

    try { //Inbyggd SQL funktion som gör deadline till rätt date-format.
        $query = $DB->prepare('SELECT c.cart_id, c.cart_user_id, c.cart_product_id, DATE_FORMAT(c.product_added, "%d/%m/%Y %H:%i") as product_added, p.title, p.price FROM carts c INNER JOIN products p ON c.cart_product_id = p.product_id WHERE c.cart_user_id = :userid');
        /* TAGIT BORT USERID I QUERY- FLYTTA TILL EMPTY GET? $query->bindParam(':cartid', $cartid, PDO::PARAM_INT); */
        $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404); //Not found
            $response->setSuccess(false);
            $response->addMessage("Cart not found");
            $response->send();
            exit;
        }

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $cart = new Cart($row['cart_id'], $row['cart_user_id'], $row['cart_product_id'], $row['product_added'], $row['title'], $row['price']);
            $cartArray[] = $cart->returnCartAsArray();
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['carts'] = $cartArray;

        $response = new Response();
        $response->setHttpStatusCode(200); // OK
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit;

    } catch (CartException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500); // Server error
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit;

    } catch (PDOException $ex) {
        error_log("Database query error - " . $ex, 0); // Spara felmeddelandet i PHP logfile
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to get cart");
        $response->send();
        exit();
    }

} elseif (empty($_GET)) {

}
