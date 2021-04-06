<?php

class CartException extends Exception
{}

class Cart
{
    private $_cartID;
    private $_cartUserID;
    private $_cartProductID;
    private $_productAdded;
    private $_productTitle;
    private $_productPrice;

    public function __construct($cartID, $cartUserID, $cartProductID, $productAdded, $productTitle, $productPrice)
    {
        $this->setCartID($cartID);
        $this->setCartUserID($cartUserID);
        $this->setCartProductID($cartProductID);
        $this->setProductAdded($productAdded);
        $this->setProductTitle($productTitle);
        $this->setProductPrice($productPrice);
    }

    public function getCartID()
    {
        return $this->_cartID;
    }

    public function getCartUserID()
    {
        return $this->_cartUserID;
    }

    public function getCartProductID()
    {
        return $this->_cartProductID;
    }

    public function getProductAdded()
    {
        return $this->_productAdded;
    }

    public function getProductTitle()
    {
        return $this->_productTitle;
    }

    public function getProductPrice()
    {
        return $this->_productPrice;
    }

    public function setCartID($cartID)
    { // Största talet som får finnas i en SQL DATABAS
        if (($cartID !== null) && (!is_numeric($cartID) || $cartID <= 0 || $cartID > 9223372036854775807 || $this->_cartID !== null)) {
            throw new ProductException("Cart ID Error");
        }

        $this->_cartID = $cartID;
    }

    public function setCartUserID($cartUserID)
    {
        if (($cartUserID !== null) && (!is_numeric($cartUserID) || $cartUserID <= 0 || $cartUserID > 9223372036854775807 || $this->_cartUserID !== null)) {
            throw new ProductException("User ID Error");
        }

        $this->_cartUserID = $cartUserID;
    }

    public function setCartProductID($cartProductID)
    {
        if (($cartProductID !== null) && (!is_numeric($cartProductID) || $cartProductID <= 0 || $cartProductID > 9223372036854775807 || $this->_cartProductID !== null)) {
            throw new ProductException("Product ID Error");
        }

        $this->_cartProductID = $cartProductID;
    }

    public function setProductAdded($productAdded)
    {
        if (($productAdded !== null) && date_format(date_create_from_format('d/m/Y H:i', $productAdded), 'd/m/Y H:i') != $productAdded) {
            throw new ProductException("Product added date time error");
        }

        $this->_productAdded = $productAdded;
    }

    public function setProductTitle($productTitle)
    {
        if (strlen($productTitle) < 0 || strlen($productTitle) > 255) {
            throw new ProductException("Product title error");
        }

        $this->_productTitle = $productTitle;
    }

    public function setProductPrice($productPrice)
    { //Max value för int(11)
        if (($productPrice !== null) && (!is_numeric($productPrice) || $productPrice <= 0 || $productPrice > 2147483648)) {
            throw new ProductException("Product price Error");
        }

        $this->_productPrice = $productPrice;
    }

    public function returnCartAsArray()
    {
        $cart = array();
        $cart['cart_id'] = $this->getCartID();
        $cart['cart_user_id'] = $this->getCartUserID();
        $cart['cart_product_id'] = $this->getCartProductID();
        $cart['product_added_in_cart'] = $this->getProductAdded();
        $cart['product_title'] = $this->getProductTitle();
        $cart['product_price'] = $this->getProductPrice();

        return $cart;
    }

}
