<?php

require_once 'db.php';
require_once '../model/Product.php';
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

if (array_key_exists("productid", $_GET)) {

} elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") as updated_at FROM products');
            $query->execute();

            $rowCount = $query->rowCount();

            $productArray = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
                $productArray[] = $product->returnProductAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['products'] = $productArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (ProductException $ex) {
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
            $response->addMessage("Failed to get products");
            $response->send();
            exit;
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

        try {

            if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Content type header is not set to JSON");
                $response->send();
                exit;
            }

            $rawPOSTData = file_get_contents('php://input');

            //Om det inte är json data Error.
            if (!$jsonData = json_decode($rawPOSTData)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Request body is not valid JSON");
                $response->send();
                exit;
            }

            if (!isset($jsonData->title) || !isset($jsonData->description) || !isset($jsonData->imgUrl) || !isset($jsonData->price) || !isset($jsonData->quantity)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($jsonData->title) ? $response->addMessage("Title field is mandatory and must be provided") : false); // if statement för error meddelanden
                (!isset($jsonData->description) ? $response->addMessage("Description field is mandatory and must be provided") : false);
                (!isset($jsonData->imgUrl) ? $response->addMessage("Image Url field is mandatory and must be provided") : false);
                (!isset($jsonData->price) ? $response->addMessage("Price field is mandatory and must be provided") : false);
                (!isset($jsonData->quantity) ? $response->addMessage("Quantity field is mandatory and must be provided") : false);
                $response->send();
                exit;
            }

            //Null första värdet, id sätts av databasen.,
            $newProduct = new Product(null, $jsonData->title, $jsonData->description, $jsonData->imgUrl, $jsonData->price, $jsonData->quantity, null, null);

            $title = $newProduct->getTitle();
            $description = $newProduct->getDescription();
            $imgUrl = $newProduct->getImgUrl();
            $price = $newProduct->getPrice();
            $quantity = $newProduct->getQuantity();

            $query = $DB->prepare('INSERT INTO products (title, description, imgUrl, price, quantity) VALUES(:title, :description, :imgUrl, :price, :quantity)');
            $query->bindParam(':title', $title, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':imgUrl', $imgUrl, PDO::PARAM_STR);
            $query->bindParam(':price', $price, PDO::PARAM_INT);
            $query->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to create product");
                $response->send();
                exit;
            }

            $lastProductID = $DB->LastInsertId();

            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") as updated_at FROM products where product_id = :productid');
            $query->bindParam(':productid', $lastProductID, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Failed to retrive product after creation");
                $response->send();
                exit;
            }

            $productArray = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
                $productArray[] = $product->returnProductAsArray();
            }

            //Varje gång man skapar något ska man returnera det till klienten i en API.
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['products'] = $productArray;

            $response = new Response();
            $response->setHttpStatusCode(201); // Successful create.
            $response->setSuccess(true);
            $response->addMessage("Product created");
            $response->setData($returnData);
            $response->send();
            exit;
        } catch (ProductException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;

        } catch (PDOEXception $ex) {
            error_log("Database query error" . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to insert product into database - check submitted data for errors");
            $response->send();
            exit;

        }

    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
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
