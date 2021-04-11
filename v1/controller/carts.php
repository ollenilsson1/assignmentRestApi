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
    $productid = $_GET['productid'];
    //Validation
    if ($productid == '' || !is_numeric($productid)) {
        $response = new Response();
        $response->setHttpStatusCode(400); //Client error
        $response->setSuccess(false);
        $response->addMessage("Product ID cannot be blank or must be numeric");
        $response->send();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            //USER ADD PRODUCT TO CART
            $newCart = new Cart(null, $returned_userid, $productid, null, null, null);

            $cartUserID = $newCart->getCartUserID();
            $cartProductID = $newCart->getCartProductID();

            $query = $DB->prepare('INSERT INTO carts (cart_user_id, cart_product_id) VALUES(:userid, :productid)');
            $query->bindParam(':userid', $cartUserID, PDO::PARAM_INT);
            $query->bindParam(':productid', $cartProductID, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to add product in cart");
                $response->send();
                exit;
            }

            $lastCartID = $DB->LastInsertId();

            $query = $DB->prepare('SELECT cart_id, cart_user_id, cart_product_id, DATE_FORMAT(product_added, "%d/%m/%Y %H:%i") AS product_added FROM carts WHERE cart_id = :cartid AND cart_user_id = :userid');
            $query->bindParam(':cartid', $lastCartID, PDO::PARAM_INT);
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to retrive product after added to cart");
                $response->send();
                exit;
            }

            $cartArray = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $cart = new Cart($row['cart_id'], $row['cart_user_id'], $row['cart_product_id'], $row['product_added'], null, null);
                $cartArray[] = $cart->returnAddedProductAsArray();
            }

            //Varje gång man skapar något ska man returnera det till klienten i en API.
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['carts'] = $cartArray;

            $response = new Response();
            $response->setHttpStatusCode(201); // Successful create.
            $response->setSuccess(true);
            $response->addMessage("Product added to cart");
            $response->setData($returnData);
            $response->send();
            exit;
        } catch (CartException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;

        } catch (PDOException $ex) {
            error_log("Database query error" . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to insert product into cart database - check submitted data for errors");
            $response->send();
            exit;

        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        //USER delete product from cart
        try {
            $query = $DB->prepare('DELETE FROM carts WHERE cart_product_id = :productid AND cart_user_id = :userid');
            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Product not found in cart");
                $response->send();
                exit;
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Product deleted from cart");
            $response->send();
            exit;
        } catch (PDOEXception $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to delete product");
            $response->send();
            exit;
        }
    }

} elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // USER GET CART
        try { //Inbyggd SQL funktion som gör deadline till rätt date-format.
            $query = $DB->prepare('SELECT c.cart_id, c.cart_user_id, c.cart_product_id, DATE_FORMAT(c.product_added, "%d/%m/%Y %H:%i") as product_added, p.title, p.price FROM carts c INNER JOIN products p ON c.cart_product_id = p.product_id WHERE c.cart_user_id = :userid');
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404); //Not found
                $response->setSuccess(false);
                $response->addMessage("No products in cart");
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
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //USER checkout cart
        try { //Inbyggd SQL funktion som gör deadline till rätt date-format.
            $query = $DB->prepare('SELECT c.cart_id, c.cart_user_id, c.cart_product_id, DATE_FORMAT(c.product_added, "%d/%m/%Y %H:%i") as product_added, p.title, p.price, SUM(p.price) AS total FROM carts c INNER JOIN products p ON c.cart_product_id = p.product_id WHERE c.cart_user_id = :userid GROUP BY c.cart_id ASC');
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404); //Not found
                $response->setSuccess(false);
                $response->addMessage("No products in cart");
                $response->send();
                exit;
            }

            //Sätter total till 0, lägger till värde i while loop
            $total = 0;

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $cart = new Cart($row['cart_id'], $row['cart_user_id'], $row['cart_product_id'], $row['product_added'], $row['title'], $row['price']);
                $cartArray[] = $cart->returnCartAsArray();
                $total += $row['total'];
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['cart_total_price'] = $total;
            $returnData['carts'] = $cartArray;

            $response = new Response();
            $response->setHttpStatusCode(200); // OK
            $response->setSuccess(true);
            $response->addMessage("Cart checked out");
            $response->setData($returnData);
            $response->send();

            //IF cart checked out- Delete cart
            if ($returnData = true) {
                try {
                    $query = $DB->prepare('DELETE FROM carts WHERE cart_user_id = :userid');
                    $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
                    $query->execute();

                } catch (PDOException $error) {
                    $response = new Response();
                    $response->setHttpStatusCode(500);
                    $response->setSuccess(false);
                    $response->addMessage("Failed to delete cart after checkout");
                    $response->send();
                    exit;
                }
            }
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
            $response->addMessage("Failed to checkout cart");
            $response->send();
            exit();
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        //USER DELETE CART
        try {
            $query = $DB->prepare('DELETE FROM carts WHERE cart_user_id = :userid');
            $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No products found in cart");
                $response->send();
                exit;
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Cart deleted");
            $response->send();
            exit;
        } catch (PDOEXception $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to delete cart");
            $response->send();
            exit;
        }

    }}
