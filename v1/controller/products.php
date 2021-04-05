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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try { //Inbyggd SQL funktion som gör deadline till rätt date-format.
            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") as updated_at FROM products WHERE product_id = :productid');
            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404); //Not found
                $response->setSuccess(false);
                $response->addMessage("Product not found");
                $response->send();
                exit;
            }

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
                $productArray[] = $product->returnProductAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['products'] = $productArray;

            $response = new Response();
            $response->setHttpStatusCode(200); // OK
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (ProductException $ex) {
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
            $response->addMessage("Failed to get product");
            $response->send();
            exit();
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $query = $DB->prepare('DELETE FROM products WHERE product_id = :productid');
            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Product not found");
                $response->send();
                exit;
            }

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Product deleted");
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

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        try {

            if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Content Type header not set to JSON");
                $response->send();
                exit;
            }

            $rawPatchData = file_get_contents('php://input');

            if (!$jsonData = json_decode($rawPatchData)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Request body is not valid JSON");
                $response->send();
                exit;
            }

            $title_updated = false;
            $description_updated = false;
            $imgUrl_updated = false;
            $price_updated = false;
            $quantity_updated = false;

            $queryFields = "";

            if (isset($jsonData->title)) {
                $title_updated = true;
                $queryFields .= "title = :title, "; // .= sparar värden som ligger innan i queryFields och lägger till ny text
            }

            if (isset($jsonData->description)) {
                $description_updated = true;
                $queryFields .= "description = :description, ";
            }

            if (isset($jsonData->imgUrl)) {
                $imgUrl_updated = true;
                $queryFields .= "imgUrl = :imgUrl, ";
            }

            if (isset($jsonData->price)) {
                $price_updated = true;
                $queryFields .= "price = :price, ";
            }

            if (isset($jsonData->quantity)) {
                $quantity_updated = true;
                $queryFields .= "quantity = :quantity, ";
            }

            if (isset($jsonData->title) || isset($jsonData->description) || isset($jsonData->imgUrl) || isset($jsonData->price) || isset($jsonData->quantity)) {
                $queryFields .= "updated_at = CURRENT_TIMESTAMP, ";
            }

            //Tabort "," på sista query stringen
            $queryFields = rtrim($queryFields, ", ");

            if ($title_updated === false && $description_updated === false && $imgUrl_updated === false && $price_updated === false && $quantity_updated === false) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("No product fields provided");
                $response->send();
                exit;
            }

            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") AS created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") AS updated_at FROM products WHERE product_id = :productid');
            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No product found to update");
                $response->send();
                exit;
            }

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
            }

            $queryString = "update products set " . $queryFields . " WHERE product_id = :productid";
            $query = $DB->prepare($queryString);

            if ($title_updated === true) {
                $product->setTitle($jsonData->title);
                $up_title = $product->getTitle();
                $query->bindParam(":title", $up_title, PDO::PARAM_STR);
            }

            if ($description_updated === true) {
                $product->setDescription($jsonData->description);
                $up_description = $product->getDescription();
                $query->bindParam(":description", $up_description, PDO::PARAM_STR);
            }

            if ($imgUrl_updated === true) {
                $product->setImgUrl($jsonData->imgUrl);
                $up_imgUrl = $product->getImgUrl();
                $query->bindParam(":imgUrl", $up_imgUrl, PDO::PARAM_STR);
            }

            if ($price_updated === true) {
                $product->setPrice($jsonData->price);
                $up_price = $product->getPrice();
                $query->bindParam(":price", $up_price, PDO::PARAM_INT);
            }

            if ($quantity_updated === true) {
                $product->setQuantity($jsonData->quantity);
                $up_quantity = $product->getQuantity();
                $query->bindParam(":quantity", $up_quantity, PDO::PARAM_INT);
            }

            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Product not updated");
                $response->send();
                exit;
            }

            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") AS created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") AS updated_at FROM products WHERE product_id = :productid');
            $query->bindParam(':productid', $productid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No product found after update");
                $response->send();
                exit;
            }

            $taskArray = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
                $productArray[] = $product->returnProductAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['products'] = $productArray;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("product updated");
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
        } catch (PDOException $ex) {
            error_log("Database query error -" . $ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to update product - check your data for errors");
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

} elseif (array_key_exists("keyword", $_GET)) {
    $keyword_IN = $_GET['keyword'];

    if (strlen($keyword_IN) < 1 || strlen($keyword_IN) > 50) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        if (strlen($keyword_IN) < 1 ? $response->addMessage("Search cannot be empty") : false);
        if (strlen($keyword_IN) > 255 ? $response->addMessage("Search cannot be greater than 50 characters") : false);
        $response->send();
        exit;
    }

    $keyword = '%' . $keyword_IN . '%';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
            $query = $DB->prepare('SELECT product_id, title, description, imgUrl, price, quantity, DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as created_at, DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") as updated_at FROM products WHERE title LIKE :keyword_IN OR description LIKE :keyword_IN');
            $query->bindParam(':keyword_IN', $keyword, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            $productArray = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['description'], $row['imgUrl'], $row['price'], $row['quantity'], $row['created_at'], $row['updated_at']);
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

    }

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
