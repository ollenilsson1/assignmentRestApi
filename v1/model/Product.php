<?php

class ProductException extends Exception
{}

class Product
{
    private $_id;
    private $_title;
    private $_description;
    private $_imgUrl;
    private $_price;
    private $_quantity;
    private $_createdAt;
    private $_updatedAt;

    public function __construct($id, $title, $description, $imgUrl, $price, $quantity, $updatedAt)
    {
        $this->setID($id);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setImgUrl($imgUrl);
        $this->setPrice($price);
        $this->setQuantity($quantity);
        $this->setUpdatedAt($updatedAt);

    }

    public function getID()
    {
        return $this->_id;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getImgUrl()
    {
        return $this->_imgUrl;
    }

    public function getPrice()
    {
        return $this->_price;
    }

    public function getQuantity()
    {
        return $this->_quantity;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->_updatedAt;
    }

    public function setID($id)
    { // Största talet som får finnas i en SQL DATABAS
        if (($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
            throw new ProductException("Product ID Error");
        }

        $this->_id = $id;
    }

    public function setTitle($title)
    {
        if (strlen($title) < 0 || strlen($title) > 255) {
            throw new ProductException("Product title error");
        }

        $this->_title = $title;
    }

    public function setDescription($description)
    {
        if (strlen($description) < 0 || strlen($description) > 500) {
            throw new ProductException("Product description error");
        }

        $this->_description = $description;
    }

    public function setImgUrl($imgUrl)
    {
        if (strlen($imgUrl) < 0 || strlen($imgUrl) > 1000) {
            throw new ProductException("Product imgUrl error");
        }

        $this->_imgUrl = $imgUrl;
    }

    public function setPrice($price)
    { //Max value för int(11)
        if (($price !== null) && (!is_numeric($price) || $price <= 0 || $price > 2147483648)) {
            throw new ProductException("Product price Error");
        }

        $this->_price = $price;
    }

    public function setQuantity($quantity)
    { //Max value för int(11)
        if (($quantity !== null) && (!is_numeric($quantity) || $quantity <= 0 || $quantity > 2147483648)) {
            throw new ProductException("Product quantity Error");
        }

        $this->_quantity = $quantity;
    }

    public function setUpdatedAt($updatedAt)
    {
        if (($updatedAt !== null) && date_format(date_create_from_format('d/m/Y H:i', $updatedAt), 'd/m/Y H:i') != $updatedAt) {
            throw new ProductException("Product update date time error");
        }

        $this->_updatedAt = $updatedAt;
    }

    public function returnProductAsArray()
    {
        $product = array();
        $product['product_id'] = $this->getID();
        $product['title'] = $this->getTitle();
        $product['description'] = $this->getDescription();
        $product['imgUrl'] = $this->getImgUrl();
        $product['price'] = $this->getPrice();
        $product['quantity'] = $this->getQuantity();
        $product['created_at'] = $this->getCreatedAt();
        $product['updated_at'] = $this->getUpdatedAt();

        return $product;
    }

}
